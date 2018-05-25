<?php
/**
 * Created by PhpStorm.
 * User: Lamer
 * Date: 02.03.2017
 * Time: 23:15
 */

namespace projects\ProjectX\Controller;


class Index extends \Core\Parent\Controller {

	public function run() {
		$view = $this->dic->getView('html');

		$view->assign('newsList', $this->dic->getRepositoryNews()->getAll());
		$view->assign('route', $this->dic->getRouter()->route);
		$view->display('Index/Index', 1);
	}
}