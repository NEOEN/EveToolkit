<?php
/**
 * Created by PhpStorm.
 * User: Lamer
 * Date: 19.05.2018
 * Time: 11:09
 */

namespace projects\ProjectX\Controller;

class Login extends \Core\Parent\Controller
{
    public function run()
    {
        $httpRequest = $this->dic->getHttpRequest();
        $userName = $httpRequest->getVar('post', 'user', 'string');
        $password = $httpRequest->getVar('post', 'password', 'string');

        if (isset($userName) && isset($password)) {
            $userRepository = $this->dic->getRepositoryUser();
            $user = $userRepository->getByNameOrEmail($userName);

            if ($user->id) {
                $session = $this->dic->getSession();
                $session->setVar('authed', true);
                $session->setVar('user', $user);
                $session->setVar('esiList', $this->dic->getEsiRepository()->getByUserId($user->id));

                $this->dic->getHttpResponse()->setState(301, $this->dic->getUrl($this->dic->getRouter())->get('Homepage'));
            }
        }

        $view = $this->dic->getView('html');
        $view->assign('route', $this->dic->getRouter()->route);
        $view->display('Login/Login', 1);
    }
}