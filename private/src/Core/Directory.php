<?php

namespace Core;

/**
 * Class Directory
 * @desc      Alles für das Directory
 * @author    Nicolas Andreas <andreas.nicolas@gmx.net>
 * @since     2011-07-18
 * @copyright Nicolas Andreas
 * @package   Core
 */
class Directory {

	public $privateDir;
	public $publicDir;
	public $vendorDir;
	public $srcDir;

	protected $directorySeperators = array(
		'\\',
		'/'
	);

	/**
	 * @desc Constructor
	 */
	public function __construct() {
		$this->directorySeperators = DIRECTORY_SEPARATOR === '/' ? '\\' : '/';
		$this->privateDir = PRIVATE_PATH;
		$this->publicDir = PUBLIC_PATH;
		$this->vendorDir = VENDOR_PATH;
		$this->srcDir = SRC_PATH;
	}

	/**
	 * @desc Gibt den Pfad mit den richtigen Directory Separatoren zurück
	 *
	 * @param    string $path
	 * @return    string
	 */
	public function getRealPath($path) {
		return str_replace($this->directorySeperators, DIRECTORY_SEPARATOR, $path);
	}

	/**
	 * @desc Erzeugt ein Verzeichniss
	 *
	 * @param    string $dir
	 * @return    bool
	 */
	public function prepareDir($dir) {
		if (!is_dir($dir)) {
			return mkdir($dir, 0777, true);
		}

		return false;
	}

}
