<?php
/**
 * Created by IntelliJ IDEA.
 * User: jan.schumann
 * Date: 26.05.18
 * Time: 06:28
 */

namespace App\EveApi;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Uri;
use Seat\Eseye\Containers\EsiAuthentication;
use Seat\Eseye\Containers\EsiResponse;
use Seat\Eseye\Eseye;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class EveApi
{
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
     * @var Session
     */
    private $session;
    /**
     * @var bool
     */
    private $authenticated = false;
    /**
     * @var array
     */
    private $characters = [];

    /**
     * Session constructor.
     */
    public function __construct(array $appConfig, Eseye $client, Session $session)
    {
        $this->clientId = $appConfig['clientId'];
        $this->secretKey = $appConfig['secretKey'];
        $this->scopes = $appConfig['scopes'];
        $this->client = $client;
        $this->session = $session;
    }

    public function getAuthorizeUrl(string $callbackUrl)
    {
        $state = md5(uniqid());
        $this->session->set('eve_api_auth_state', $state);
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
     * @throws
     */
    public function fetchCharacter(string $authCode, string $state)
    {
        if ($state !== $this->session->get('eve_api_auth_state')) {
            throw new \RuntimeException("Wrong session state");
        }

        // fetch token data
        $url = "https://login.eveonline.com/oauth/token";
        $options = [
            'headers' => [
                'Host' => 'login.eveonline.com',
                'Content-Type' => 'application/x-www-form-urlencoded',
                'Authorization' => 'Basic ' . base64_encode($this->clientId . ":" . $this->secretKey),
            ],
            'form_params' => [
                'grant_type' => 'authorization_code',
                'code' => $authCode
            ]
        ];
        $client = new Client();
        $response = $client->post($url, $options);
        $data = json_decode($response->getBody(), true);
        $expires = new \DateTime();
        $expires->add(new \DateInterval('PT' . $data['expires_in'] . 'S'));
        $authData = [
            'access_token'  => $data['access_token'],
            'refresh_token' => $data['refresh_token'],
            'token_expires' => $expires->format('Y-m-d H:i:s'),
        ];

        // fetch authenticated character
        $options = [
            'headers' => [
                'User-Agent' => 'Eseye Default Library',
                'Host' => 'esi.tech.ccp.is',
                'Authorization' => 'Bearer ' . $authData['access_token']
            ]
        ];
        $response = $client->get('https://esi.tech.ccp.is/verify/', $options);
        $data = json_decode($response->getBody(), true);
        $authData['scopes'] = explode(' ', $data['Scopes']);

        // store character auth data
        $chars = $this->session->get('eve_characters', []);
        $chars[$data['CharacterID']] = [
            'name' => $data['CharacterName'],
            'original_data' => $data,
            'auth_data' => $authData
        ];
        $this->session->set('eve_characters', $chars);
        $this->activate($data['CharacterID']);
    }

    public function activate(int $id)
    {
        $this->characters = $this->session->get('eve_characters', []);
        if (array_key_exists($id, $this->characters)) {
            $this->setAuthentication($this->characters[$id]['auth_data']);
        }
        $this->session->set('eve_active_character', $id);
    }

    public function isActive(int $id)
    {
        $this->characters = $this->session->get('eve_characters', []);
        return array_key_exists($id, $this->characters) && $this->session->get('eve_active_character', 0) === $id;
    }

    public function getActiveCharacterName()
    {
        $this->characters = $this->session->get('eve_characters', []);
        $id = $this->session->get('eve_active_character', 0);
        return array_key_exists($id, $this->characters) ? $this->characters[$id]['name'] : '';
    }

    public function getCharacters()
    {
        $this->characters = $this->session->get('eve_characters', []);
        return $this->characters;
    }

    /**
     * @throws
     */
    public function setAuthentication(array $authData)
    {
        if (0 == count($authData)) {
            return;
        }
        $this->client->setAuthentication(new EsiAuthentication(array_merge([
            'client_id'     => $this->clientId,
            'secret'        => $this->secretKey,
        ], $authData)));
    }

    /**
     * @return array
     * @throws \Seat\Eseye\Exceptions\EsiScopeAccessDeniedException
     * @throws \Seat\Eseye\Exceptions\InvalidContainerDataException
     * @throws \Seat\Eseye\Exceptions\UriDataMissingException
     */
    public function getCharacterStandings()
    {
        $standings = [];

        $characterId = $this->session->get('eve_active_character', 0);
        if (0 == $characterId)
        {
            return $standings;
        }

        $this->activate($characterId);
        $data = $this->client->invoke('get', '/characters/{character_id}/standings', [
            'character_id' => $characterId,
        ]);

        foreach ($data as $standing)
        {
            $standings[$standing->from_id] = [
                'standing' => $standing->standing,
                'corporation' => $this->getCooperationNames([$standing->from_id])[$standing->from_id]
            ];
        }

        return $standings;
    }

    /**
     * @return EsiResponse
     * @throws \Seat\Eseye\Exceptions\EsiScopeAccessDeniedException
     * @throws \Seat\Eseye\Exceptions\InvalidContainerDataException
     * @throws \Seat\Eseye\Exceptions\UriDataMissingException
     */
    public function getCooperation($id)
    {
        return $this->client->invoke('get', '/corporations/{corporation_id}', ['corporation_id' => $id]);
    }

    /**
     * @return array
     * @throws \Seat\Eseye\Exceptions\EsiScopeAccessDeniedException
     * @throws \Seat\Eseye\Exceptions\InvalidContainerDataException
     * @throws \Seat\Eseye\Exceptions\UriDataMissingException
     */
    public function getCooperationNames($ids)
    {
        $names = [];
        $this->client->setQueryString(['corporation_ids' => $ids]);
        foreach ($this->client->invoke('get', '/corporations/names') as $corp)
        {
            $names[$corp->corporation_id] = $corp->corporation_name;
        }
        return $names;
    }
}
