<?php
/**
 * Created by IntelliJ IDEA.
 * User: jan.schumann
 * Date: 06.06.18
 * Time: 03:14
 */

namespace App\EveApi\Repository;

use App\EveApi\Entity\Character;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class CharacterRepository
{
    private const CHARACTER_KEY = 'evetoolkit:characters';
    private const CURRENT_CHARACTER_KEY = 'evetoolkit:current_character';
    private const SESSION_STATE = 'evetoolkit:session_state';

    /**
     * @var SessionInterface
     */
    private $session;

    /**
     * @param SessionInterface $session
     */
    public function __construct(SessionInterface $session)
    {
        $this->session = $session;
    }

    public function setAuthState(string $state)
    {
        $this->session->set(self::SESSION_STATE, $state);
    }

    /**
     * @param $state
     * @return bool
     */
    public function isValidAuthState($state)
    {
        return $state === $this->session->get(self::SESSION_STATE);
    }

    public function invalidateAuthState()
    {
        return $this->session->set(self::SESSION_STATE, null);
    }

    /**
     * @param Character $character
     */
    public function set(Character $character)
    {
        $chars = $this->session->get(self::CHARACTER_KEY, []);
        $chars[$character->getId()] = $character->toArray();
        $this->session->set(self::CHARACTER_KEY, $chars);
    }

    /**
     * @param int $id
     * @return Character
     * @throws \Seat\Eseye\Exceptions\InvalidContainerDataException
     */
    public function fetch(int $id)
    {
        $chars = $this->session->get(self::CHARACTER_KEY, []);
        return Character::fromArray($chars[$id]);
    }

    /**
     * @return Character[]
     * @throws \Seat\Eseye\Exceptions\InvalidContainerDataException
     */
    public function fetchAll()
    {
        $chars = [];

        foreach($this->session->get(self::CHARACTER_KEY, []) as $id => $data) {
            $chars[$id] = Character::fromArray($data);
        }

        return $chars;
    }

    /**
     * @param Character $character
     */
    public function setCurrent(Character $character)
    {
        $this->session->set(self::CURRENT_CHARACTER_KEY, $character->getId());
    }

    /**
     * @return Character
     * @throws \Seat\Eseye\Exceptions\InvalidContainerDataException
     */
    public function getCurrent()
    {
        if ($this->session->get(self::CURRENT_CHARACTER_KEY, false)) {
            return $this->fetch($this->session->get(self::CURRENT_CHARACTER_KEY));
        }

        return null;
    }

    /**
     * @return string
     * @throws \Seat\Eseye\Exceptions\InvalidContainerDataException
     */
    public function getCurrentName()
    {
        $char = $this->getCurrent();
        return is_null($char) ? "": $char->getName();
    }
}
