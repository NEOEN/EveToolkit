<?php
/**
 * Created by PhpStorm.
 * User: Lamer
 * Date: 19.05.2018
 * Time: 16:17
 */

namespace projects\ProjectX\Controller;

use \projects\ProjectX\Helper;

class Homepage extends \Core\Parent\Controller
{
    /** @var \projects\ProjectX\Dic */
    public $dic;

    public function run()
    {
        Helper\HelperAuthed::isAuthed($this->dic);

        $view = $this->dic->getView('html');

        $view->assign('user', $this->dic->getSession()->getVar('user'));
        $view->assign('esiList', $this->dic->getSession()->getVar('esiList'));
        $view->assign('characterEveRepository', $this->dic->getCharacterEveRepository());
        $view->assign('route', $this->dic->getRouter()->route);
        $view->display('Homepage/Homepage', 1);
    }
}