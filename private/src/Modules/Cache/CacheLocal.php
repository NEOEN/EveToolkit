<?php
/**
 * Created by PhpStorm.
 * User: Nicolas
 * Date: 15.08.14
 * Time: 14:00
 */

namespace Modules\Cache;

class CacheLocal {

	public function set($key, $value, $ttl = 0) {
		apc_store($key, $value, $ttl);
	}

	public function get($key) {
		return apc_fetch($key);
	}

	public function delete($key) {
		return apc_delete($key);
	}
}