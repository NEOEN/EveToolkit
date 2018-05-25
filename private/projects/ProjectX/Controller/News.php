<?php
/**
 * Created by PhpStorm.
 * User: Lamer
 * Date: 19.05.2018
 * Time: 20:00
 */

namespace projects\ProjectX\Controller;

use \projects\ProjectX\Helper;

class News extends \Core\Parent\Controller
{
    /** @var \projects\ProjectX\Dic */
    public $dic;

    public function run(){
        Helper\HelperAuthed::isAuthed($this->dic);
        $user = $this->dic->getRepositoryUser()->findById($this->dic->getSession()->getVar('userId'));

        $view = $this->dic->getView('html');
        $view->assign('user', $user);
        $view->assign('route', $this->dic->getRouter()->route);
        $view->assign('newsList', $this->dic->getRepositoryNews()->getAll());
        $view->display('News/News', 1);
    }
}