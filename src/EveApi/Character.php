<?php
/**
 * Created by IntelliJ IDEA.
 * User: jan.schumann
 * Date: 27.05.18
 * Time: 18:39
 */

namespace App\EveApi;

use App\EveApi\Entity\Character as CharacterEntity;
use Seat\Eseye\Eseye;

class Character
{
    /**
     * @var Eseye
     */
    private $client;

    public function __construct(Eseye $client)
    {
        $this->client = $client;
    }

    /**
     * @param CharacterEntity $character
     * @throws \Seat\Eseye\Exceptions\EsiScopeAccessDeniedException
     * @throws \Seat\Eseye\Exceptions\InvalidContainerDataException
     * @throws \Seat\Eseye\Exceptions\UriDataMissingException
     */
    public function getStandings(CharacterEntity $character)
    {
        $this->client->setAuthentication($character->getAuthentication());
        $data = $this->client->invoke('get', '/characters/{character_id}/standings', [
            'character_id' => $character->getId(),
        ]);

        return $data;
    }
}
