<?php

namespace App\Controller;

use App\EveApi\Session;
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
     * @Route("/test", name="test")
     */
    public function test(Eseye $eseye)
    {

        return $this->render('test.html.twig');
    }
}
