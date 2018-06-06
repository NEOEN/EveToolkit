<?php

namespace App\Controller;

use App\EveApi\Authentication;
use App\EveApi\Character as CharacterApi;
use App\EveApi\Entity\CharacterRepository;
use App\Entity\InvItems;
use App\EveApi\Esi\Api\AllianceApi;
use App\EveApi\Esi\Api\SkillsApi;
use App\EveApi\Esi\Api\UniverseApi;
use App\EveApi\Esi\Configuration;
use GuzzleHttp\Client;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\Bundle\DoctrineBundle\Registry as Doctrine;

class IndexController extends AbstractController
{
    /**
     * @Route("/", name="home")
     */
    public function index(CharacterRepository $repository)
    {
        $chars = [];

        foreach($repository->fetchAll() as $char) {
            $chars[$char->getId()] = $char->getName();
        }

        return $this->render('index.html.twig', ['characters' => $chars]);
    }

    /**
     * @Route("/character/activate/{id}", requirements={"id" = "\d+"}, name="character.activate")
     */
    public function activate(Request $request, CharacterRepository $repository)
    {
        $repository->setCurrent($repository->fetch((int) $request->get('id')));
        return $this->redirect($this->generateUrl('home'));
    }

    /**
     * @Route("/character/standings", name="character.standings")
     */
    public function standings(CharacterRepository $repository, CharacterApi $api)
    {
        $standings = $api->getStandings($repository->getCurrent());

        return $this->render('character/standings.html.twig', ['standings' => $standings]);
    }

    /**
     * @Route("/character/skills", name="character.skills")
     */
    public function skills(Authentication $auth, CharacterRepository $repository)
    {
        $char = $repository->getCurrent();
        $auth->refreshToken($char);

        $conf = new Configuration();
        $conf->setUserAgent('NEOEN EVE Toolkit DEV');
        $conf->setAccessToken($char->getAuthentication()->access_token);

        $client = new Client();

        $skills = [];
        $skillApi = new SkillsApi($client, $conf);
        $universeApi = new UniverseApi($client, $conf);
        foreach ($skillApi->getCharactersCharacterIdSkills($char->getId())->getSkills() as $key => $skill) {

            $skills[] = [
                'id' => $skill->getSkillId(),
                'name' => $universeApi->getUniverseTypesTypeId($skill->getSkillId())->getName(),
                'points' => $skill->getSkillpointsInSkill(),
                'active_level' => $skill->getActiveSkillLevel(),
                'trained_level' => $skill->getTrainedSkillLevel(),
            ];
            if ($key >= 100 - 1) {
                break;
            }
        }

        return $this->render('character/skills.html.twig', ['skills' => $skills]);
    }

}
