<?php

namespace Core;

/**
 * Class Configuration
 * @desc      Laden und zusammenführen der verschiedenen Configurationen
 * @author    Nicolas Andreas <andreas.nicolas@gmx.net>
 * @since     2011-07-18
 * @copyright Nicolas Andreas
 * @package   Core
 */

class Configuration {

	protected $parameters = array();
	protected $configurationPaths = array();

	/**
	 * @desc Constructor
	 *
	 * @param $configurationPath
	 *
	 * @throws \Exception
	 */
	public function __construct($configurationPath) {
		if (!isset($configurationPath)) {
			throw new \Exception('No Configuration Path');
		}
		$this->loadConfiguration($configurationPath);
	}

	/**
	 * @desc Läd die Configuration
	 *
	 * @param $configurationPath
	 */
	public function loadConfiguration($configurationPath) {
		if (!in_array($configurationPath, $this->configurationPaths)) {
			$this->configurationPaths[] = $configurationPath;

            $parameters = parse_ini_file($configurationPath, true);
			$this->mergeParameters($parameters);
		}
	}

	/**
	 * @desc führt verschiedene Configurationen zusammen
	 *
     * @param $parameters
     *
     * @return bool
     */
	protected function mergeParameters($parameters) {
	    if(!is_array($parameters)){
	        return false;
        }
		foreach ($parameters as $key => $parameter) {
			if (isset($this->parameters[$key])) {
				$this->parameters[$key] = is_array($this->parameters[$key]) ? array_merge($this->parameters[$key], $parameter) : $parameter;
			} else {
				$this->parameters[$key] = $parameter;
			}
		}
	}

	/**
	 * @desc Gibt den Wert zum Parameter aus der Gruppe die übergeben wurden zurück
	 *
	 * @param    string $group
	 * @param    string $parameter
	 * @throws    \Exception
	 * @return    string
	 */
	public function getParameter($group, $parameter) {
		if (!is_array($this->parameters[$group])) {
			throw new \Exception('The searched Group (' . $group . ') was not found!');
		}
		if (!isset($this->parameters[$group][$parameter])) {
			throw new \Exception('The searched Parameter (' . $parameter . ') is not in Group (' . $group . ')');
		}

		return $this->parameters[$group][$parameter];
	}

	/**
	 * @desc Gibt alle Parameter die in dieser Konfiguration gespeichert sind als Array zurück.
	 *
	 * @return array $aParam
	 */
	public function getAllParameter() {
		return $this->parameters;
	}

	/**
	 * @desc Gibt alle Werte die zu den übergebenen Gruppen gehören als Array zurück
	 *
	 * @param	string 		$group
	 * @return	array
	 * @throws	\Exception
	 */
	public function getAllParameterToGivenGroup($group) {
		if (isset($this->parameters[$group]) && is_array($this->parameters[$group])) {
			return $this->parameters[$group];
		}

		throw new \Exception('Die gesuchte Gruppe (' . $group . ') existiert nicht');
	}

}
