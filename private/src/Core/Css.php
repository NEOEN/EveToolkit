<?php

namespace Core;

/**
 * Class Css
 * @desc      Fügt alle CSS Dateien für den Request zusammen, setzt die Pfade, eleminiert doppelte Daten, minimiert sie, speichert sie im cache und gibt den CSS-Link an das Template zurück.
 *       Hier wird ein bisschen Zeit gebraucht, ist aber nicht schlimm da das ganze gecached wird.
 * @author    Nicolas Andreas <andreas.nicolas@gmx.net>
 * @since     2011-07-18
 * @copyright Nicolas Andreas
 * @package   Core
 */
class Css {

	protected $configuration;
	protected $directory;
	protected $httpResponse;

	protected $absolutePaths = array();
	protected $cachePath;
	protected $cacheFile;

	/**
	 * @desc Constructor
	 *
	 * @param Configuration $configuration
	 * @param Directory $directory
	 * @param HttpResponse $httpResponse
	 */
	public function __construct(Configuration $configuration, Directory $directory, HttpResponse $httpResponse) {
		$this->configuration = $configuration->getAllParameter();
		$this->directory = $directory;
		$this->httpResponse = $httpResponse;
	}

	/**
	 * @desc fügt einen CSS-Pfad hinzu.
	 *
	 * @param $relativePath
	 */
	public function add($relativePath) {
		$absolutePath = $this->directory->getRealPath($this->directory->privateDir . $this->configuration['paths']['projects'] . PROJECT_NAME . DIRECTORY_SEPARATOR . $this->configuration['paths']['css'] . $relativePath . '.css');
		if(!in_array($absolutePath, $this->absolutePaths) && is_file($absolutePath)) {
			$this->absolutePaths[] = $absolutePath;
		}
	}

	/**
	 * @desc Minifizieren, Base64 für Images und ersetzen der ImagePfade
	 *
	 * @return    null|string
	 */
	public function get() {
		$empty = false;
		if(!$this->isCacheFile()) {
			$fileContent = $this->getFileContent();
			if(!empty($fileContent)) {
				if($this->configuration['css']['minify']) {
					$fileContent = \Modules\Minify\CssMinify::minify($fileContent);
				}
				$fileContent = $this->getBase64Images($fileContent);
				$fileContent = str_replace('{imageuri}', $this->configuration['http']['scheme'] . '://' . $this->configuration['http']['staticuri'] . '/' . $this->configuration['paths']['images'], $fileContent);
				file_put_contents($this->getCachePath(), '@CHARSET "' . $this->configuration['css']['charset'] . '";' . "\n" . $fileContent);
			} else {
				$empty = true;
			}
		}
		if(!$empty) {
			return '<link rel="stylesheet" type="text/css" href="' . $this->configuration['http']['scheme'] . '://' .  $this->configuration['http']['staticuri'] . '/' . $this->configuration['paths']['cacheCss'] . $this->getCacheFile() . '" />';
		}

		return null;
	}

	/**
	 * @desc Ersetzt im FileContent die Image Platzhalter durch die ImageUrl oder ImageBase64
	 *
	 * @param    string $fileContent
	 *
	 * @return    string
	 */
	protected function getBase64Images($fileContent) {
		$array = array();
		$matchs = array();
		if(\preg_match_all('#url\(([[:alnum:]/\\\_-]+\.(png|jpg|gif)\S?[0-9/,]*)\)#ui', $fileContent, $matchs)) {
			foreach($matchs[1] as $fileTemp) {
				$array[] = $fileTemp;
			}
			$array = array_unique($array);
			usort($array, array(
				'\\Core\\Css',
				'strLenSort'
			));
			foreach($array as $imageRelativePath) {
				$fileContent = str_replace('url(' . $imageRelativePath . ')', 'url(' . $this->getDataUrl($imageRelativePath) . ')', $fileContent);
			}
		}

		return $fileContent;
	}

	/**
	 * @desc Sortierung
	 *
	 * @param    string $a
	 * @param    string $b
	 *
	 * @return    int
	 */
	public function strLenSort($a, $b) {
		if(strlen($a) == strlen($b)) {
			return 0;
		} else {
			return (strlen($a) < strlen($b)) ? 1 : -1;
		}
	}

	/**
	 * @desc Wandelt, wenn die Datei klein genug ist, diese um in Base64. Sollte das nicht zutreffen wird der ImagePath zurück gegeben.
	 *
	 * @param    string $imageRelativePath
	 *
	 * @return    string
	 */
	protected function getDataUrl($imageRelativePath) {
		if($this->httpResponse->acceptBase64) {
			$imageAbsolutePath = $this->directory->publicDir . $this->configuration['paths']['images'] . $imageRelativePath;
			if(is_file($imageAbsolutePath)) {
				$imageSize = getimagesize($imageAbsolutePath);
				if($imageSize && filesize($imageAbsolutePath) < $this->configuration['css']['base64imagesize']) {
					if($imageSize && file_get_contents($imageAbsolutePath)) {
						$return = 'data:' . $imageSize['mime'] . ';base64,' . base64_encode(file_get_contents($imageAbsolutePath));
					}
				}
			}
		}
		if(!isset($return)) {
			$return = '{imageuri}' . $imageRelativePath;
		}

		return $return;
	}

	/**
	 * @desc Fügt die einzelnen CSS-Files zusammen
	 * @return string
	 */
	protected function getFileContent() {
		$fileContent = '';
		foreach($this->absolutePaths as $absolutePath) {
			$fileContent .= "\n" . trim(file_get_contents($absolutePath));
		}

		return $fileContent;
	}

	/**
	 * @desc Gibt zurück ob es die Datei schon im Cache gibt.
	 * @return bool
	 */
	protected function isCacheFile() {
		return is_file($this->getCachePath());
	}

	/**
	 * @desc Baut den CachePath aus CacheDir und CacheFile zusammen
	 * @return string
	 */
	protected function getCachePath() {
		if(!$this->cachePath) {
			$this->cachePath = $this->getCacheDir() . $this->getCacheFile();
		}

		return $this->cachePath;
	}

	/**
	 * @desc Baut das CacheDir zusammen
	 * @return string
	 */
	protected function getCacheDir() {
		return $this->directory->publicDir . $this->configuration['paths']['cacheCss'];
	}

	/**
	 * @desc Baut den CacheFileName zusammen und gibt ihn zurück.
	 * @return string
	 */
	protected function getCacheFile() {
		if(!isset($this->cacheFile)) {
			$string = '';
			foreach($this->absolutePaths as $absolutePath) {
				$string .= $absolutePath . filemtime($absolutePath);
			}
			$this->cacheFile = \md5($string);
			if($this->configuration['css']['minify']) {
				$this->cacheFile = 'min-' . $this->cacheFile;
			}
			if($this->httpResponse->acceptBase64) {
				$this->cacheFile = 'b64-' . $this->cacheFile;
			}
			$this->cacheFile .= '.css';
		}

		return $this->cacheFile;
	}

}
