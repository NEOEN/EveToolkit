<?php

namespace Modules;

/**
 * Class Dic
 * @desc Der Module DependencyInjectionContainer
 * @author Nicolas Andreas <andreas.nicolas@gmx.net>
 * @since 2012-10-27
 * @copyright Nicolas Andreas
 * @package Modules
 */

class Dic {

	protected $dicManager;

	protected $performanceTimer;
	protected $cache;
	protected $hash;
	protected $errorService;
	protected $restRouterService;
	protected $curl;

	/**
	 * @desc Constructor
	 *
	 * @param DicManager $dicManager
	 */
	public function __construct(\Core\DicManager $dicManager) {
		$this->dicManager = $dicManager;
	}

	/**
	 * @desc Gibt das CachingObject zurück
	 * @return Cache $cache
	 */
	public function getCacheLocal() {
		if(!isset($this->cache)) {
			$this->cache = new Cache\CacheLocal();
//			$configuration = $this->dicManager->getDic('Core')->getConfiguration();
//
//			$memcached = new \Memcached();
//			$memcached->addServer($configuration->getParameter('cache', 'server'), $configuration->getParameter('cache', 'port'));
//
//			$this->cache = new Cache\Cache2($memcached);
		}

		return $this->cache;
	}

	/**
	 * @desc Gibt das PerformanceTimerObject zurück
	 * @return PerformanceTimer $performanceTimer
	 */
	public function getPerformanceTimer() {
		if(!isset($this->performanceTimer)) {
			$this->performanceTimer = new DateTime\PerformanceTimer();
		}

		return $this->performanceTimer;
	}

	/**
	 * @desc Gibt das HashObject zurück
	 * @return Hash $hash
	 */
	public function getHash() {
		if(!isset($this->hash)) {
			$configuration = $this->dicManager->getDic('Core')->getConfiguration();
			$secure = $configuration->getAllParameterToGivenGroup('secure');

			$this->hash = new Secure\Hash($secure['algorithm'], $secure['saltlength'], $secure['iteration'], $secure['delemiter']);
		}

		return $this->hash;
	}

	public function getRestRouterService() {
		if(!isset($this->restRouterService)) {
			$this->restRouterService = new Service\RestRouter($this->dicManager->getDic('Core')->getRequest());
		}

		return $this->restRouterService;
	}

	public function getCurl(){
	    if(!isset($this->curl)){
	        $this->curl = new Curl\Curl();
        }

        return $this->curl;
    }
}
