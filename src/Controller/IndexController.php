<?php

namespace App\Controller;

use App\EveApi\Repository\CharacterRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class IndexController extends AbstractController
{
    /**
     * @Route("/", name="home")
     */
    public function index(CharacterRepository $repository)
    {
        $chars = [];

        // @todo error handling
        foreach($repository->fetchAll() as $char) {
            $chars[$char->getId()] = $char->getName();
        }

        return $this->render('index.html.twig', [
            'characters' => $chars
        ]);
    }
}
