<?php

namespace App\Controller;

use App\EveApi\EveApi;
use Seat\Eseye\Eseye;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    /**
     * @Route("/login", name="login")
     */
    public function login(Request $request, AuthenticationUtils $authenticationUtils)
    {
        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();

        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', array(
            'last_username' => $lastUsername,
            'error'         => $error,
        ));
    }

    /**
     * @Route("/callback", name="callback")
     * @throws
     */
    public function callback(Request $request, EveApi $api)
    {
        $api->fetchCharacter($request->get('code'), $request->get('state'));
        return $this->redirect($this->generateUrl('home'));
    }

}
