<?php
/**
 * Application Boot
 * @desc Bereitet alles für die Application vor.
 * @author Nicolas Andreas <andreas.nicolas@gmx.net>
 * @since 2011-07-18
 * @copyright Nicolas Andreas
 * @package Core
 */

error_reporting(E_ALL);
set_error_handler('errorHandler');


if(version_compare(PHP_VERSION, '7.2.5.', '<=')) {
	die('Dieses Framework arbeitet ab PHP 7.2.5 sie benutzen ' . PHP_VERSION . '.');
}

require_once('paths.php');
require_once('autoload.php');
//require_once(VENDOR_PATH . 'autoload.php');

/**
 * Setzt den PHP error
 *
 * @param type $errno
 * @param type $errstr
 * @param type $errfile
 * @param type $errline
 *
 * @return type
 */
function errorHandler($errno, $errstr, $errfile, $errline) {
	return Core\Exception\PhpError::fromPhpError($errno, $errstr, $errfile, $errline);
}

ob_start(/*"ob_gzhandler"*/);

$dicManager = new \Core\DicManager();

$coreApplication = new \Core\Main($dicManager);

try {
	$coreApplication->run();
} catch(Exception $e) {
	errorHandler($e->getCode(), $e->getMessage(), $e->getFile(), $e->getLine());
}

ob_end_flush();