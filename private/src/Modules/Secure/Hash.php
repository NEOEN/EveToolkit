<?php

namespace Modules\Secure;

/**
 * Class Hash
 * @desc hier wird ein Hash auf einen PlainText generiert
 * @author Nicolas Andreas <andreas.nicolas@gmx.net>
 * @since 2013-10-28
 * @copyright Nicolas Andreas
 * @package Modules
 */

class Hash {

	protected $algorithm;
	protected $saltLength;
	protected $iteration;
	protected $delemiter;

	/**
	 * @desc Constructor
	 *
	 * @param    string $algorithm
	 * @param    int $saltLength
	 * @param    int $iteration
	 * @param    string $delemiter
	 */

	public function __construct($algorithm = 'sha512', $saltLength = 96, $iteration = 1000, $delemiter = '$') {
		$this->algorithm = $algorithm;
		$this->saltLength = $saltLength;
		$this->iteration = $iteration;
		$this->delemiter = $delemiter;
	}

	/**
	 * @desc Setzt den Hash aus den verschiedenen Variablen zusammen.
     *
     * @param $plain
     * @param null $salt
     * @return string
     * @throws \Exception
     */
	public function createHash($plain, $salt = null) {
		return $this->algorithm . $this->delemiter . $this->saltLength . $this->delemiter . $this->iteration . $this->delemiter . $this->generateHash($plain, $salt);
	}

	/**
	 * @desc Generiert den Hash zum Klartext und setzt den zugrundeliegenden Salt hinten dran.
	 *
	 * @param    string $plain
	 * @param    string $salt
	 * @return    string    $hash
	 * @throws \Exception
	 */
	protected function generateHash($plain, $salt = null) {
		if (!in_array($this->algorithm, hash_algos())) {
			throw new \Exception('Verschlüsselungsalgorhytmus (' . $this->algorithm . ') nicht gefunden', 1, null);
		}

		if (!$salt) {
			$salt = $this->generateSalt($this->saltLength);
		}

		$hash = hash($this->algorithm, $salt . $plain);
		for ($i = 0; $i < $this->iteration; ++$i) {
			$hash = hash($this->algorithm, $salt . $hash);
		}

		return $hash . $this->delemiter . $salt;
	}

	/**
	 * @desc Generiert einen Salz in einer bestimmten Länge
	 * @return string
	 */
	public function generateSalt($length, $char = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz1234567890_-.~') {
		for ($token = '', $charLength = strlen($char) - 1, $i = 0; $i < $length; $token .= $char[mt_rand(0, $charLength)], ++$i) {
		}

		return $token;
	}

	/**
	 * @desc Überprüft ein Klartext ob es mit dem übergebenen Hash übereinstimmt.
	 *
	 * @param    string $plain
	 * @param    string $hash
	 *
	 * @return    boolean
	 */
	public function checkHash($plain, $hash) {
		list($this->algorithm, $this->saltLength, $this->iteration, $hashTemp, $salt) = explode($this->delemiter, $hash, 5);
		if ($this->createHash($plain, $salt) === $hash) {
			return true;
		}

		return false;
	}

}
