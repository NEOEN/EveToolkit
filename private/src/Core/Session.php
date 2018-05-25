<?php
/**
 * Created by PhpStorm.
 * User: Nicolas
 * Date: 17.07.14
 * Time: 14:12
 */

namespace Core;

class Session {

    protected static $_instance = null;

    public static function getInstance()
    {
        if (null === self::$_instance)
        {
            self::$_instance = new self;
        }
        return self::$_instance;
    }

    protected function __clone() {}

    protected function __construct() {
        session_start();
        $this->var = isset($_SESSION['Core']) ? $_SESSION['Core'] : [];
    }

	protected $var = [];


	public function getVar($key) {
		return isset($this->var[$key]) ? $this->var[$key] : null;
	}

	public function setVar($key, $value) {
		$this->var[$key] = $value;
	}

	public function hasVar($key){
	    return isset($this->var[$key]);
    }

	public function __destruct() {
		$_SESSION['Core'] = $this->var;
		session_write_close();
	}
} 