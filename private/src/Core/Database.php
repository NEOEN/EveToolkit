<?php

namespace Core;

/**
 * Class Database
 * @desc      Initialisiert die Datenbankklasse PDO
 * @author    Nicolas Andreas <andreas.nicolas@gmx.net>
 * @since     2011-07-18
 * @copyright Nicolas Andreas
 * @package   Core
 */

class Database {

	/**
	 * @desc Baut eine Verbindung zur Datenbank auf über die PDO Klasse. Speichert das entstandene Objekt in $oInstance und gibt es zurück
	 *
	 * @param    string $host
	 * @param    string $database
	 * @param    string $port
	 * @param    string $user
	 * @param    string $password
	 * @param    string $persistent
	 *
	 * @return    \PDO    $database
	 * @throws \Exception
	 */
	public function get($host, $database, $port, $user, $password, $persistent) {
		try {
			$db = new \PDO('mysql:dbname=' . $database .';host=' . $host, $user, $password, array(\PDO::ATTR_PERSISTENT => $persistent));
			$db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
			$db->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC);
			$db->setAttribute(\PDO::ATTR_EMULATE_PREPARES, true);
			$db->exec('SET CHARACTER SET utf8');
		} catch(\PDOException $e) {
			throw new \Exception($e->getMessage());
		}

		return $db;
	}

}
