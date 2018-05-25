<?php
/**
 * Created by PhpStorm.
 * User: Nicolas
 * Date: 13.08.14
 * Time: 09:25
 */

namespace Core\Parent;


class Repository {

	protected $db;

	public function __construct(\PDO $database) {
		$this->db = $database;
	}

	public function optimize( $table ){
		$this->db->query( 'OPTIMIZE TABLE ' . $table );
	}
}