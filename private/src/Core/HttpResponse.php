<?php

namespace Core;

/**
 * Class Response
 * @desc      handelt alles ab was mit der Response zu tun hat
 * @author    Nicolas Andreas <andreas.nicolas@gmx.net>
 * @since     2011-07-18
 * @copyright Nicolas Andreas
 * @package   Core
 */

class HttpResponse {

	public $acceptBase64;
	protected $httpRequest;
	protected $session;

	/**
	 * @desc Constructor
	 *
	 * @param HttpRequest $httpRequest
	 */
	public function __construct(HttpRequest $httpRequest, Session $session) {
		$this->httpRequest = $httpRequest;
		$this->session = $session;
		$this->acceptBase64();
	}

	/**
	 * Wertet den übergebenen Status aus und leitet das Skript über location weiter oder gibt nur den Header mit dem State aus. Danach wird das Skript auf jedenfall immer mit einem Exit beendet.
	 *
	 * @param int $state
	 * @param string $url
	 */
	public function setState($state, $url = null) {
		switch ($state) {
			case 301:
				header('HTTP/1.0 301 Moved Permanently');
				header('location:' . $url);
				break;
			case 302:
				header('HTTP/1.1 302 Moved Temporarily');
				header('location:' . $url);
				break;
			case 401:
				header('HTTP/1.0 401 Unauthorized');
				break;
			case 403:
				header('HTTP/1.1 403 Forbidden');
				break;
			case 404:
				header('HTTP/1.0 404 Not Found');
				break;
			case 500:
				header('HTTP/1.0 500 Internal Server Error');
				break;
			case 503:
				header('HTTP/1.0 503 Service Unavailable');
				break;
		}

        $this->session->__destruct();
        gc_collect_cycles();
		exit;
	}

	public function send($data) {
		$contentType = $this->HttpRequest->getVar('server', 'Content-Type', 'application/json', 'string');
		switch ($contentType) {
			case 'application/json':
				echo ")]}',\n" . json_encode($data);
				break;
		}

	}

	/**
	 * @desc überpfrüft ob der Client (Browser) Base64 als Datenformat versteht
	 * @return bool
	 */
	protected function acceptBase64() {
		if (empty($this->acceptBase64)) {
			$this->acceptBase64 = false;
			$httpUserAgent = $this->httpRequest->getVar('server', 'HTTP_USER_AGENT');
			if ($httpUserAgent && strpos($httpUserAgent, 'MSIE 6.0') === false && strpos($httpUserAgent, 'MSIE 7.0') === false) {
				$this->acceptBase64 = true;
			}
		}

		return $this->acceptBase64;
	}

}