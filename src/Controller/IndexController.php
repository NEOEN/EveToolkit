<?php

namespace App\Controller;

use App\EveApi\Authentication;
use App\EveApi\Character as CharacterApi;
use App\EveApi\Entity\CharacterRepository;
use App\Entity\InvItems;
use ESI\Api\AllianceApi;
use ESI\Api\SkillsApi;
use ESI\Api\UniverseApi;
use ESI\ApiClient;
use ESI\Configuration;
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
        $conf->setCurlTimeout(6000);
        $client = new ApiClient($conf);

        $skills = [];
        $skillApi = new SkillsApi($client);
        $universeApi = new UniverseApi($client);
        foreach ($skillApi->getCharactersCharacterIdSkills($char->getId())->getSkills() as $key => $skill) {
            $skills[] = [
                'id' => $skill->getSkillId(),
                'name' => $universeApi->getUniverseTypesTypeId($skill->getSkillId())->getTypeName(),
                'points' => $skill->getSkillpointsInSkill(),
                'level' => $skill->getCurrentSkillLevel()
            ];
            if ($key > 100) {
                break;
            }
        }

        return $this->render('character/skills.html.twig', ['skills' => $skills]);
    }

}
