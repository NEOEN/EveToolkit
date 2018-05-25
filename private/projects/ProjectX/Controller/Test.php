<?php
/**
 * Created by PhpStorm.
 * User: Lamer
 * Date: 24.05.2018
 * Time: 20:43
 */

namespace projects\ProjectX\Controller;

use projects\ProjectX\Helper\HelperEsi;
use \projects\ProjectX\Helper;

class Test extends \Core\Parent\Controller
{

    /** @var \projects\ProjectX\Dic */
    public $dic;

    public function run()
    {
        Helper\HelperAuthed::isAuthed($this->dic);

        set_time_limit(0);
        $session = $this->dic->getSession();
        if (!$session->hasVar('esi')) {
            echo "grummel grummel";
            exit;
        }

        $esi = $session->getVar('esi');

        /** @var \Modules\Curl\Curl $curl */
        $curl = $this->dicManager->getDic('Modules')->getCurl();

        echo "<pre>";var_dump(HelperEsi::getCharactersOrderHistory($esi->accessToken, $esi->tokenType, $curl, $esi->characterId));

//        $characterData = HelperEsi::getCharacters($esi->accessToken, $esi->tokenType, $curl, $esi->characterId);
//        $characterImagePath = HelperEsi::getCharactersPortrait($esi->accessToken, $esi->tokenType, $curl, $esi->characterId);
//
//        $characterEve = new \projects\ProjectX\Classes\Character\CharacterEve();
//        $characterEve->id = $esi->characterId;
//        $characterEve->corporationId = $characterData['corporation_id'];
//        $characterEve->name = $characterData['name'];
//        $characterEve->imagePath = $characterImagePath['px512x512'];
//        $this->dic->getCharacterEveRepository()->save($characterEve);
//
//        $view = $this->dic->getView('html');
//        $view->assign('user', $this->dic->getSession()->getVar('user'));
//        $view->assign('route', $this->dic->getRouter()->route);
//        $view->assign('curlResult', $characterEve);
//        $view->display('Test/Test', 1);
    }
}