<?php
/**
 * Created by IntelliJ IDEA.
 * User: jan.schumann
 * Date: 06.06.18
 * Time: 02:53
 */

namespace App\EveApi\Entity;

class Character
{
    /**
     * @var int
     */
    private $id;
    /**
     * @var string
     */
    private $name;
    /**
     * @var AuthData
     */
    private $authentication;
    /**
     * @var array
     */
    private $originalData;

    public function __construct(int $id)
    {
        $this->id = $id;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return array
     */
    public function getOriginalData(): array
    {
        return $this->originalData;
    }

    /**
     * @param array $originalData
     */
    public function setOriginalData(array $originalData): void
    {
        $this->originalData = $originalData;
    }

    /**
     * @param AuthData $authentication
     */
    public function setAuthentication(AuthData $authentication): void
    {
        $this->authentication = $authentication;
    }

    /**
     * @return AuthData
     */
    public function getAuthentication(): AuthData
    {
        return $this->authentication;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'original_data' => $this->originalData,
            'auth_data' => [
                'client_id'     => $this->authentication->getClientId(),
                'secret'        => $this->authentication->getSecret(),
                'access_token'  => $this->authentication->getAccessToken(),
                'refresh_token' => $this->authentication->getRefreshToken(),
                'token_expires' => $this->authentication->getTokenExpires(),
                'scopes'        => $this->authentication->getScopes(),
            ]
        ];
    }

    /**
     * @param array $characterData
     * @return Character
     * @throws \Seat\Eseye\Exceptions\InvalidContainerDataException
     */
    public static function fromArray(array $characterData)
    {
        //@todo error handling
        $char = new self($characterData['id']);
        $char->setName($characterData['name']);
        $char->setAuthentication(new AuthData($characterData['auth_data']));
        $char->setOriginalData($characterData['original_data']);
        return $char;
    }

}
