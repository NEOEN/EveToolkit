<?php
/**
 * Created by PhpStorm.
 * User: Lamer
 * Date: 19.05.2018
 * Time: 17:28
 */

namespace projects\ProjectX\Controller;


class Logout extends \Core\Parent\Controller
{
    public function run()
    {
        $this->dic->getSession()->setVar('authed', false);
        $this->dic->getSession()->setVar('user', null);

        $this->dic->getHttpResponse()->setState(301, $this->dic->getUrl($this->dic->getRouter())->get('Login'));
    }
}