<?php
/**
 * Created by IntelliJ IDEA.
 * User: jan.schumann
 * Date: 26.05.18
 * Time: 06:28
 */

namespace App\Api;


class Session
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
     * Session constructor.
     */
    public function __construct(String $clientId, String $secretKey, array $scopes)
    {
        $this->clientId = $clientId;
        $this->secretKey = $secretKey;
        $this->scopes = $scopes;
    }

    /**
     * @return String
     */
    public function getClientId(): String
    {
        return $this->clientId;
    }

    /**
     * @return String
     */
    public function getSecretKey(): String
    {
        return $this->secretKey;
    }

    /**
     * @return array
     */
    public function getScopes(): array
    {
        return $this->scopes;
    }
}
