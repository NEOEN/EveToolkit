<?php

namespace Core\Exception;

use Core\DicManager;

/**
 * Class PhpError
 * @desc      Übernimmt php fehler und logt diese oder gibt sie aus.
 * @author    Nicolas Andreas <andreas.nicolas@gmx.net>
 * @since     2011-07-18
 * @copyright Nicolas Andreas
 * @package   Core
 */
class PhpError {

	public static function fromPhpError($errno, $errstr, $errfile, $errline) {
		$dicManager = new DicManager();
		$dicManager->registerDic('Core');

		$configuration = $dicManager->getDic('Core')->getConfiguration()->getAllParameter();
		$logText = date('d.m.Y H:i:s') . ' | ' . $errno . ' | ' . $errstr . ' | ' . $errfile . ' | ' . $errline;

		switch($configuration['log']['type']) {
			case 'echo':
				header('HTTP/1.1 500 Internal Server Error');
				ob_clean();
				echo $logText . '<br />';
				break;
			case 'log':
				$filePath = PRIVATE_PATH . 'var' . \DIRECTORY_SEPARATOR . $configuration['log']['basis'] . \DIRECTORY_SEPARATOR . $configuration['log']['error'];
				\file_put_contents($filePath, $logText . "\n", FILE_APPEND);
				break;
		}

		return null;
	}

}
