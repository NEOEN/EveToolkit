<?php
/**
 * Created by PhpStorm.
 * User: Lamer
 * Date: 19.05.2018
 * Time: 17:20
 */

namespace projects\ProjectX\Helper;


class HelperAuthed
{
    static public function isAuthed($dic){
        $session = $dic->getSession();
        if(!$session->getVar('authed')){
            $dic->getHttpResponse()->setState(301, $dic->getUrl($dic->getRouter())->get('Login'));
        }
    }
}