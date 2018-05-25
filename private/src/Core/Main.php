<?php

namespace Core;

/**
 * Class Main
 * @desc      Baut die Application zusammen übergibt beim ausführen den DICManager
 * @author    Nicolas Andreas <andreas.nicolas@gmx.net>
 * @since     2011-07-18
 * @copyright Nicolas Andreas
 * @package   Core
 */

class Main {

	/**
	 * @var object dicManager
	 */
	protected $dicManager;

	/**
	 * @desc Constructor
	 *
	 * @param $dicManager
	 */
	public function __construct(DicManager $dicManager) {
		$this->dicManager = $dicManager;
	}

	/**
	 * @desc Baut die Apllication aus der Configuration zusammen und ruft diese dann auf.
	 * @throws \Exception
	 */
	public function run() {
		$coreDic = $this->dicManager->getDic( __NAMESPACE__);

		$configuration = $coreDic->getConfiguration();
		$httpRequest = $coreDic->getHttpRequest();

		define("PROJECT_NAME", $configuration->getParameter( 'projects', 'name' )[$httpRequest->getVar( 'server', 'HTTP_HOST' )]);

		$applicationName = '\\'. substr($configuration->getParameter( 'paths', 'projects' ), 0, -1) . '\\' . PROJECT_NAME  . '\\Main';
		if(class_exists($applicationName, true)) {
			$application = new $applicationName($this->dicManager);
			$application->run();
		} else {
			throw new \Exception('Die Klasse ' . $applicationName . ' existiert nicht!');
		}
	}

}
