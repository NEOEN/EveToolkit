<?php

namespace Core;

/**
 * Class DicManager
 * @desc      Verwaltet und Managed die verschiedenen DependenciInjectionContainer
 * @author    Nicolas Andreas <andreas.nicolas@gmx.net>
 * @since     2011-07-18
 * @copyright Nicolas Andreas
 * @package   Core
 */
class DicManager {

	protected $registeredDics = array();

	/**
	 * @desc Registriert ein DependencyInjectionContainer
	 *
	 * @param    string $dicName
	 *
	 * @return    Dic
	 */
	public function registerDic($dicName) {
		$this->addDic($dicName);

		return $this->getDic($dicName);
	}

	/**
	 * @desc Registriert mehrere DICs auf einmal
	 *
	 * @param array $dicNameList
	 */
	public function registerDics(array $dicNameList) {
		foreach($dicNameList as $dicName) {
			$this->addDic($dicName);
		}
	}

	/**
	 * @desc Erzeugt ein Dic und fügt ihn zu dem DicArray hinzu
	 *
	 * @param    string $dicName
	 *
	 * @return    int
	 */
	protected function addDic($dicName) {
 		$className = "\\$dicName\\Dic";
		if(!isset($this->registeredDics[$dicName])) {
			$this->registeredDics[$dicName] = new $className($this);
		}
		uksort($this->registeredDics, function ($a, $b) {
			return strlen($a) > strlen($b) ? 1 : -1;
		});
	}

	/**
	 * @desc Gibt einen bestimmten Dic zurück
	 *
	 * @param    $dicName
	 *
	 * @return    mixed
	 * @throws \RunTimeException
	 */
	public function getDic($dicName) {
		foreach($this->registeredDics as $namespace => $dic) {
			if(strpos($namespace, $dicName) !== false) {
				return $dic;
			}
		}

		return $this->registerDic($dicName);

	}

}
