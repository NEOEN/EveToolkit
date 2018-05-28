<?php

namespace App\Controller;

use App\EveApi\EveApi;
use Seat\Eseye\Eseye;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class IndexController extends AbstractController
{
    /**
     * @Route("/", name="home")
     */
    public function index()
    {
        return $this->render('index.html.twig');
    }

    /**
     * @Route("/character/activate/{id}", requirements={"id" = "\d+"}, name="character.activate")
     */
    public function activate(Request $request, EveApi $api)
    {
        $api->activate((int) $request->get('id'));
        return $this->redirect($this->generateUrl('home'));
    }

    /**
     * @Route("/character/standings", name="character.standings")
     */
    public function standings(EveApi $api)
    {
        return $this->render('character/standings.html.twig');
    }
}
