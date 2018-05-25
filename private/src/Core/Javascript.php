<?php

namespace Core;

/**
 * Class Javascript
 * @desc      Fügt alle Javascript Dateien für den Request zusammen, setzt die Pfade, eleminiert doppelte Daten, minimiert sie und speichert sie im cache.
 *       Hier wird ein bisschen Zeit gebraucht, ist aber nicht schlimm da das ganze gecached wird.
 * @author    Nicolas Andreas <andreas.nicolas@gmx.net>
 * @since     2011-07-18
 * @copyright Nicolas Andreas
 * @package   Core
 */
class Javascript {

	protected $configuration;
	protected $directory;

	protected $absolutePaths = array();
	protected $cachePath;
	protected $cacheFile;

	/**
	 * @desc Constructor
	 *
	 * @param    Configuration $configuration
	 * @param    Directory $directory
	 */
	public function __construct(Configuration $configuration, Directory $directory) {
		$this->configuration = $configuration->getAllParameter();
		$this->directory = $directory;
	}

	/**
	 * @desc Fügt einen weiteren Javascript Pfad zum PfadArray dazu. Dabei wird direkt geschaut ob der Pfad existiert und schon mal im PfadArray vorkommt.
	 *
	 * @param    string $relativePath
	 */
	public function add($relativePath) {
		$absolutePath = $this->directory->getRealPath($this->directory->privateDir . $this->configuration['paths']['projects'] . PROJECT_NAME . DIRECTORY_SEPARATOR . $this->configuration['paths']['javascript'] . $relativePath . '.js');
		if(!in_array($absolutePath, $this->absolutePaths) && is_file($absolutePath)) {
			$this->absolutePaths[] = $absolutePath;
		}
	}

	/**
	 * @desc Baut das Javascriptfile zusammen, speichert es im Cacheordner und gibt den Link zum Template zurück
	 *
	 * @return    null|string
	 */
	public function get() {
		$empty = false;
		if(!$this->isCacheFile()) {
			$fileContent = $this->getFileContent();
			if($fileContent) {
				if($this->configuration['js']['minify']) {
					$jsSqueeze = new \Modules\Minify\JSqueeze();
					$fileContent = $jsSqueeze->squeeze($fileContent);
				}
				$fileContent = str_replace('{uri}', $this->configuration['http']['uri'] . '/', $fileContent);
				$fileContent = str_replace('{imageuri}', $this->configuration['http']['staticuri'] . $this->configuration['paths']['images'], $fileContent);
				file_put_contents($this->getCachePath(), $fileContent);
			} else {
				$empty = true;
			}
		}
		if(!$empty) {
			return '<script src="' . $this->configuration['http']['scheme'] . '://' .  $this->configuration['http']['uri'] . '/' . $this->configuration['paths']['cacheJs'] . $this->getCacheFile() . '" async="async"></script>';
		}

		return null;
	}

	/**
	 * @desc schaut ob es schon ein Cachefile gibt
	 * @return bool
	 */
	protected function isCacheFile() {
		return is_file($this->getCachePath());
	}

	/**
	 * @desc setzt das CacheDir und das CacheFile zum CachPath zusammen und speichert das ganze in der ClassVariablen
	 * @return string
	 */
	protected function getCachePath() {
		if(!$this->cachePath) {
			$this->cachePath = $this->getCacheDir() . $this->getCacheFile();
		}

		return $this->cachePath;
	}

	/**
	 * @desc erzeugt das CacheDir
	 * @return string
	 */
	protected function getCacheDir() {
		return $this->directory->getRealPath($this->directory->publicDir . $this->configuration['paths']['cacheJs']);
	}

	/**
	 * @desc erzeugt den CacheFileNamen
	 * @return string
	 */
	protected function getCacheFile() {
		if(!isset($this->cacheFile)) {
			$string = '';
			foreach($this->absolutePaths as $absolutePath) {
				$string .= $absolutePath . filemtime($absolutePath);
			}
			$this->cacheFile = \md5($string);
			if($this->configuration['js']['minify']) {
				$this->cacheFile = 'min-' . $this->cacheFile;
			}
			$this->cacheFile .= '.js';
		}

		return $this->cacheFile;
	}

	/**
	 * @desc baut den FileContent zusammen
	 * @return string
	 */
	protected function getFileContent() {
		$fileContent = '';
		foreach($this->absolutePaths as $absolutePath) {
			$fileContent .= trim(file_get_contents($absolutePath));
		}

		return $fileContent;
	}
}
