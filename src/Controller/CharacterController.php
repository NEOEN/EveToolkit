<?php
/**
 * Created by IntelliJ IDEA.
 * User: jan.schumann
 * Date: 12.06.18
 * Time: 20:34
 */

namespace App\Controller;

use App\EveApi\Repository\CharacterRepository;
use App\EveApi\Esi\Api\CharacterApi;
use App\EveApi\Esi\Api\SkillsApi;
use App\EveApi\Esi\Api\UniverseApi;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class CharacterController extends AbstractController
{
    /**
     * @Route("/character/activate/{id}", requirements={"id" = "\d+"}, name="character.activate")
     */
    public function activate(Request $request, CharacterRepository $repository)
    {
        // @todo error handling
        $repository->setCurrent($repository->fetch((int) $request->get('id')));
        return $this->redirect($this->generateUrl('home'));
    }

    /**
     * @Route("/character/standings", name="character.standings")
     */
    public function standings(CharacterRepository $repository, CharacterApi $api)
    {
        // @todo error handling
        return $this->render(
            'character/standings.html.twig',
            ['standings' => $api->getCharactersCharacterIdStandings($repository->getCurrent()->getId())]
        );
    }

    /**
     * @Route("/character/skills", name="character.skills")
     */
    public function skills(CharacterRepository $repository, SkillsApi $skillsApi, UniverseApi $universeApi)
    {
        // @todo error handling
        $skills = [];
        foreach ($skillsApi->getCharactersCharacterIdSkills($repository->getCurrent()->getId())->getSkills() as $key => $skill) {
            $skills[] = [
                'id' => $skill->getSkillId(),
                'name' => $universeApi->getUniverseTypesTypeId($skill->getSkillId())->getName(),
                'points' => $skill->getSkillpointsInSkill(),
                'active_level' => $skill->getActiveSkillLevel(),
                'trained_level' => $skill->getTrainedSkillLevel(),
            ];
        }

        return $this->render('character/skills.html.twig', [
            'skills' => $skills
        ]);
    }

    /**
     * @Route("/character", name="character")
     */
    public function character(CharacterRepository $repository, CharacterApi $characterApi)
    {
        // @todo error handling
        $char = $characterApi->getCharactersCharacterId($repository->getCurrent()->getId());

        return $this->render('character/character.html.twig', [
            'character' => $char
        ]);
    }
}
