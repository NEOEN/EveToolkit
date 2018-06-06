<?php
/**
 * Created by IntelliJ IDEA.
 * User: jan.schumann
 * Date: 26.05.18
 * Time: 06:28
 */

namespace App\EveApi;

use App\EveApi\Entity\CharacterRepository;
use GuzzleHttp\Client as HttpClient;
use Seat\Eseye\Eseye;
use Symfony\Component\HttpFoundation\Session\Session;
use App\EveApi\Entity\Character;

class Authentication
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
     * @var Eseye
     */
    private $client;
    /**
     * @var CharacterRepository
     */
    private $characterRepository;

    /**
     * Session constructor.
     */
    public function __construct(array $appConfig, Eseye $client, CharacterRepository $characterRepository)
    {
        $this->clientId = $appConfig['clientId'];
        $this->secretKey = $appConfig['secretKey'];
        $this->scopes = $appConfig['scopes'];
        $this->client = $client;
        $this->characterRepository = $characterRepository;
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
    public function authorizeCharacter(string $authCode, string $state)
    {
        if (!$this->characterRepository->isValidAuthState($state)) {
            throw new \RuntimeException("Wrong session state");
        }

        // @todo error handling
        $character = $this->validate($authCode);

        $this->characterRepository->set(Character::fromArray($character));

        // invalidate auth session
        $this->characterRepository->invalidateAuthState();
    }

    /**
     * Refresh the Access token that we have in the EsiAccess container.
     *
     * @throws \Seat\Eseye\Exceptions\RequestFailedException
     * @throws \Seat\Eseye\Exceptions\InvalidAuthenticationException
     * @throws \Seat\Eseye\Exceptions\InvalidContainerDataException
     */
    public function refreshToken(Character $character)
    {
        $client = new HttpClient();

        $options = self::OAUTH_HTTP_CLIENT_OPTIONS;
        $options['headers']['Authorization'] = 'Basic '.base64_encode($this->clientId.":".$this->secretKey);
        unset($options['form_params']);

        $auth = $character->getAuthentication();

        $response = $client->post(self::OAUTH_URL . '?grant_type=refresh_token&refresh_token=' . $character->getAuthentication()->refresh_token, $options);
        $data = json_decode($response->getBody(), true);
        $expires = new \DateTime();
        $expires->add(new \DateInterval('PT'.$data['expires_in'].'S'));

        $auth->access_token = $data['access_token'];
        $auth->refresh_token = $data['refresh_token'];
        $auth->token_expires = $expires->format('Y-m-d H:i:s');

        $this->characterRepository->set($character);
    }

    /**
     * @param string $authCode
     * @param $client
     * @return array
     * @throws \Exception
     */
    private function validate(string $authCode): array
    {
        $client = new HttpClient();

        $options = self::OAUTH_HTTP_CLIENT_OPTIONS;
        $options['headers']['Authorization'] = 'Basic '.base64_encode($this->clientId.":".$this->secretKey);
        $options['form_params']['code'] = $authCode;

        $response = $client->post(self::OAUTH_URL, $options);

        $data = json_decode($response->getBody(), true);
        $expires = new \DateTime();
        $expires->add(new \DateInterval('PT'.$data['expires_in'].'S'));

        return $this->fetchCharacter([
            'access_token' => $data['access_token'],
            'refresh_token' => $data['refresh_token'],
            'token_expires' => $expires->format('Y-m-d H:i:s'),
        ]);
    }

    /**
     * @param $tokenData
     * @return array
     */
    private function fetchCharacter($tokenData): array
    {
        $client = new HttpClient();

        $options = self::VERIFY_CHARACTER_HTTP_CLIENT_OPTIONS;
        $options['headers']['Authorization'] = 'Bearer '.$tokenData['access_token'];

        $response = $client->get(self::VERIFY_CHARACTER_URL, $options);

        $data = json_decode($response->getBody(), true);
        $tokenData['scopes'] = explode(' ', $data['Scopes']);
        $tokenData['client_id'] = $this->clientId;
        $tokenData['secret'] = $this->secretKey;

        return [
            'id' => (int) $data['CharacterID'],
            'name' => $data['CharacterName'],
            'original_data' => $data,
            'auth_data' => $tokenData
        ];
    }
}
