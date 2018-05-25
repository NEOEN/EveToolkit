<?php
/**
 * Created by PhpStorm.
 * User: nicolas
 * Date: 19.01.14
 * Time: 11:19
 */

namespace Modules\DateTime;

class PerformanceTimer {

	public $times;

	/**
	 * @param string $name
	 */
	public function start($name) {
		$this->times[$name]['start'] = $this->microtime_float();
	}

	public function end($name) {
		$this->times[$name]['end'] = $this->microtime_float();
	}

	public function getTime($name) {
		$times = $this->times;
		if(!isset($times[$name]) || count($times[$name]) !== 2) {
			throw new \Exception('Block (' . $name . ') nicht gefunden', 1, null);
		}

		return $times[$name]['end'] - $times[$name]['start'];
	}

	protected function microtime_float() {
		list($usec, $sec) = explode(' ', microtime());

		return ((float)$usec + (float)$sec);
	}
}