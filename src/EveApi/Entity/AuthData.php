<?php
/**
 * Created by IntelliJ IDEA.
 * User: jan.schumann
 * Date: 12.06.18
 * Time: 20:59
 */

namespace App\EveApi\Entity;


class AuthData
{
    /**
     * @var string
     */
    private $clientId;
    /**
     * @var string
     */
    private $secret;
    /**
     * @var string
     */
    private $accessToken;
    /**
     * @var string
     */
    private $refreshToken;
    /**
     * @var string
     */
    private $tokenExpires;
    /**
     * @var string
     */
    private $scopes;

    public function __construct($authData)
    {
        $this->clientId = $authData['client_id'];
        $this->secret = $authData['secret'];
        $this->accessToken = $authData['access_token'];
        $this->refreshToken = $authData['refresh_token'];
        $this->tokenExpires = $authData['token_expires'];
        $this->scopes = $authData['scopes'];
    }

    /**
     * @return string
     */
    public function getClientId(): string
    {
        return $this->clientId;
    }

    /**
     * @param string $clientId
     */
    public function setClientId(string $clientId): void
    {
        $this->clientId = $clientId;
    }

    /**
     * @return string
     */
    public function getSecret(): string
    {
        return $this->secret;
    }

    /**
     * @param string $secret
     */
    public function setSecret(string $secret): void
    {
        $this->secret = $secret;
    }

    /**
     * @return string
     */
    public function getAccessToken(): string
    {
        return $this->accessToken;
    }

    /**
     * @param string $accessToken
     */
    public function setAccessToken(string $accessToken): void
    {
        $this->accessToken = $accessToken;
    }

    /**
     * @return string
     */
    public function getRefreshToken(): string
    {
        return $this->refreshToken;
    }

    /**
     * @param string $refreshToken
     */
    public function setRefreshToken(string $refreshToken): void
    {
        $this->refreshToken = $refreshToken;
    }

    /**
     * @return string
     */
    public function getTokenExpires(): string
    {
        return $this->tokenExpires;
    }

    /**
     * @param string $tokenExpires
     */
    public function setTokenExpires(string $tokenExpires): void
    {
        $this->tokenExpires = $tokenExpires;
    }

    /**
     * @return string
     */
    public function getScopes(): string
    {
        return $this->scopes;
    }

    /**
     * @param string $scopes
     */
    public function setScopes(string $scopes): void
    {
        $this->scopes = $scopes;
    }
}
