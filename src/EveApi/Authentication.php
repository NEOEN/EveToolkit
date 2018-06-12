<?php
/**
 * Created by IntelliJ IDEA.
 * User: jan.schumann
 * Date: 26.05.18
 * Time: 06:28
 */

namespace App\EveApi;

use App\EveApi\Entity\AuthData;
use App\EveApi\Esi\Configuration;
use App\EveApi\Repository\CharacterRepository;
use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use App\EveApi\Entity\Character;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class Authentication implements EventSubscriberInterface
{
    private const OAUTH_URL = "https://login.eveonline.com/oauth/token";
    private const OAUTH_HTTP_CLIENT_OPTIONS = [
        'headers' => [
            'Host' => 'login.eveonline.com',
            'Content-Type' => 'application/x-www-form-urlencoded',
            'Authorization' => '',
        ],
        'form_params' => [
            'grant_type' => 'authorization_code',
            'code' => '',
        ]
    ];
    private const VERIFY_CHARACTER_URL = "https://esi.tech.ccp.is/verify";
    private const VERIFY_CHARACTER_HTTP_CLIENT_OPTIONS = [
        'headers' => [
            'User-Agent' => 'NEOEN EVE Toolkit',
            'Host' => 'esi.tech.ccp.is',
            'Authorization' => ''
        ]
    ];
    private const TOKEN_EXPIRES_DATE_FORMAT = 'Y-m-d H:i:s';
    private const TOKEN_MIN_VALID_TIME_INTERVAL_SPEC = 'PT1M';

    /**
     * @var String
     */
    private $clientId;
    /**
     * @var String
     */
    private $secretKey;
    /**
     * @var array
     */
    private $scopes;
    /**
     * @var Client
     */
    private $client;
    /**
     * @var CharacterRepository
     */
    private $characterRepository;
    /**
     * @var Configuration
     */
    private $apiConfig;

    /**
     * Session constructor.
     */
    public function __construct(array $appConfig, ClientInterface $client, CharacterRepository $characterRepository, Configuration $apiConfig)
    {
        $this->clientId = $appConfig['clientId'];
        $this->secretKey = $appConfig['secretKey'];
        $this->scopes = $appConfig['scopes'];
        $this->client = $client;
        $this->characterRepository = $characterRepository;
        $this->apiConfig = $apiConfig;
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::REQUEST => [
                // first refresh tokens
                ['refreshTokensOnRequest', 10],
                // then promote the current char to the API
                ['promoteAccessTokenToApi', -10]
            ]
        ];
    }

    /**
     * Authenticate current char with the api
     *
     * @param GetResponseEvent $event
     * @throws
     */
    public function promoteAccessTokenToApi(GetResponseEvent $event)
    {
        if (!$event->isMasterRequest()) {
            // don't do anything if it's not the master request
            return;
        }

        $char = $this->characterRepository->getCurrent();
        if (!is_null($char)) {
            $this->apiConfig->setAccessToken($char->getAuthentication()->getAccessToken());
        }
    }

    /**
     * Refresh tokens for all characters if necessary
     *
     * @param GetResponseEvent $event
     * @throws
     */
    public function refreshTokensOnRequest(GetResponseEvent $event)
    {
        if (!$event->isMasterRequest()) {
            // don't do anything if it's not the master request
            return;
        }

        foreach ($this->characterRepository->fetchAll() as $character) {
            $auth = $character->getAuthentication();
            if ($this->tokenExpires($auth)) {
                $token = $this->refreshToken($auth->getRefreshToken());
                $auth->setAccessToken($token['access_token']);
                $auth->setRefreshToken($token['refresh_token']);
                $auth->setTokenExpires($token['token_expires']);
                $this->characterRepository->set($character);
            }
        }
    }

    /**
     * @param AuthData $auth
     * @throws \Exception
     */
    private function tokenExpires(AuthData $auth)
    {
        $expired = new \DateTime();
        $expired->add(new \DateInterval(self::TOKEN_MIN_VALID_TIME_INTERVAL_SPEC));
        $expires = \DateTime::createFromFormat(self::TOKEN_EXPIRES_DATE_FORMAT, $auth->getTokenExpires());
        return $expires <= $expired;
    }

    public function getAuthorizeUrl(string $callbackUrl)
    {
        $state = md5(uniqid());
        $this->characterRepository->setAuthState($state);

        return 'https://login.eveonline.com/oauth/authorize/?' . http_build_query([
            'response_type' => 'code',
            'client_id' => $this->clientId,
            'scope' => implode(' ', $this->scopes),
            'state' => $state,
            'redirect_uri' => $callbackUrl
        ]);
    }

    /**
     * @param $authCode
     * @param $state
     * @throws
     */
    public function authorize(string $authCode, string $state)
    {
        if (!$this->characterRepository->isValidAuthState($state)) {
            throw new \RuntimeException("Mismatched session state");
        }

        // @todo error handling
        $authData = $this->getToken($authCode);
        $characterData = $this->verify($authData);

        $authData['scopes'] = $characterData['Scopes'];
        $authData['client_id'] = $this->clientId;
        $authData['secret'] = $this->secretKey;

        $this->characterRepository->set(Character::fromArray([
            'id' => (int) $characterData['CharacterID'],
            'name' => $characterData['CharacterName'],
            'original_data' => $characterData,
            'auth_data' => $authData
        ]));

        // invalidate auth session
        $this->characterRepository->invalidateAuthState();
    }

    /**
     * Refresh an access token using the refresh token.
     */
    public function refreshToken(string $refreshToken)
    {
        $options = self::OAUTH_HTTP_CLIENT_OPTIONS;
        $options['headers']['Authorization'] = 'Basic '.base64_encode($this->clientId.":".$this->secretKey);
        unset($options['form_params']);
        $params = [
            'grant_type' => 'refresh_token',
            'refresh_token' => $refreshToken
        ];
        $url = self::OAUTH_URL . '?' . http_build_query($params);

        return $this->fetchToken($url, $options);
    }

    /**
     * @param string $authCode
     * @return array
     */
    private function getToken(string $authCode): array
    {
        $options = self::OAUTH_HTTP_CLIENT_OPTIONS;
        $options['headers']['Authorization'] = 'Basic '.base64_encode($this->clientId.":".$this->secretKey);
        $options['form_params']['code'] = $authCode;

        return $this->fetchToken(self::OAUTH_URL, $options);
    }

    /**
     * @param $options
     * @return array
     */
    private function fetchToken($url, $options): array
    {
        $response = $this->client->request('POST', $url, $options);

        $data = json_decode($response->getBody(), true);
        $expires = new \DateTime();
        $expires->add(new \DateInterval('PT'.$data['expires_in'].'S'));

        return [
            'access_token' => $data['access_token'],
            'refresh_token' => $data['refresh_token'],
            'token_expires' => $expires->format(self::TOKEN_EXPIRES_DATE_FORMAT),
        ];
    }

    /**
     * @param array $tokenData
     * @return array
     */
    private function verify(array $tokenData): array
    {
        $options = self::VERIFY_CHARACTER_HTTP_CLIENT_OPTIONS;
        $options['headers']['Authorization'] = 'Bearer '.$tokenData['access_token'];

        $response = $this->client->request('GET', self::VERIFY_CHARACTER_URL, $options);

        return json_decode($response->getBody(), true);
    }
}
