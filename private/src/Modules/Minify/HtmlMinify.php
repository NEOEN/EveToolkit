<?php
/**
 * Created by PhpStorm.
 * User: Lamer
 * Date: 07.03.2017
 * Time: 00:16
 */

namespace Modules\Minify;


class HtmlMinify {

	/**
	 * @param string $text
	 * @param int $minifyDeep 0|1|2
	 * @param bool $htmlFormatted
	 * @param bool $htmlMinified
	 * @return mixed|string
	 */
	static public function minify($text, $minifyDeep = 0, $htmlFormatted = false, $htmlMinified = false) {
		if ($htmlMinified) {
			if ($minifyDeep === 2) {
				$htmlMinify = new Minify_HTML;
				$text = $htmlMinify->minify($text);
			} elseif ($minifyDeep === 1) {
				$text = self::getHtmlMinify($text);
			}
		}

		if($htmlFormatted){
			$text = self::htmlFormatted($text);
		}

		return $text;

	}

	static protected function getHtmlMinify($text) {
		$replace = array(
			"\r\n",
			"\r",
			"\n",
		);

		$text = str_replace($replace, '', $text);
		$text = str_replace("\t", ' ', $text);
		$text =  preg_replace('/\s+/', ' ', $text);
		return str_replace("> <", '><', $text);
	}

	static protected function htmlFormatted($text) {
		$text = str_replace("\n", ' ', $text);
		$parseOff = false;
		$tabs = 0;
		$htmlArray = preg_split('/(<.*?>)/', $text, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
		$htmlArrayCount = count($htmlArray);
		$text = '';
		for ($x = 0; $x < $htmlArrayCount; $x++) {
			if (strpos($htmlArray[$x], '<script>') !== false) {
				$parseOff = true;
				$text .= str_repeat("\t", $tabs) . trim($htmlArray[$x]);
				continue;
			}
			if ($parseOff) {
				if (strpos($htmlArray[$x], '</script>') !== false) {
					$parseOff = false;
					$text .= trim($htmlArray[$x]) . "\n";
					continue;
				}
				if (trim($htmlArray[$x])) {
					$text .= $htmlArray[$x];
				}
				continue;
			}
			if (substr($htmlArray[$x], 0, 2) == '</') {
				$tabs--;
				if ($tabs < 0) {
					$tabs = 0;
				}
			}
			if (trim($htmlArray[$x])) {
				$text .= str_repeat("\t", $tabs) . $htmlArray[$x] . "\n";
			}
			if ((substr($htmlArray[$x], 0, 1) == '<') && (substr($htmlArray[$x], 1, 1) != '/') && (substr($htmlArray[$x], 1, 1) != '!')) {
				if ((substr($htmlArray[$x], 1, 1) != ' ') && (strpos($htmlArray[$x], '/>') === false)) {
					$tabs++;
				}
			}
		}
		if ($tabs !== 0) {
			$text .= '<!--' . $tabs . " open elements found-->\r\n";
		}

		return $text;
	}

}


/**
 * Class Minify_HTML
 * @package Minify
 */

/**
 * Compress HTML
 * This is a heavy regex-based removal of whitespace, unnecessary comments and
 * tokens. IE conditional comments are preserved. There are also options to have
 * STYLE and SCRIPT blocks compressed by callback functions.
 * A test suite is available.
 * @package Minify
 * @author  Stephen Clay <steve@mrclay.org>
 */
class Minify_HTML {

	/**
	 * Defines which class to call as part of callbacks, change this
	 * if you extend Minify_HTML
	 * @var string
	 */
	protected static $className = 'Minify_HTML';

	/**
	 * "Minify" an HTML page
	 *
	 * @param string $html
	 * @param array $options
	 * 'cssMinifier' : (optional) callback function to process content of STYLE
	 * elements.
	 * 'jsMinifier' : (optional) callback function to process content of SCRIPT
	 * elements. Note: the type attribute is ignored.
	 * 'xhtml' : (optional boolean) should content be treated as XHTML1.0? If
	 * unset, minify will sniff for an XHTML doctype.
	 *
	 * @return string
	 */
	public static function minify($html, $options = array()) {

		if (isset($options['cssMinifier'])) {
			self::$_cssMinifier = $options['cssMinifier'];
		}
		if (isset($options['jsMinifier'])) {
			self::$_jsMinifier = $options['jsMinifier'];
		}

		$html = str_replace("\r\n", "\n", trim($html));

		self::$_isXhtml = (isset($options['xhtml']) ? (bool)$options['xhtml'] : (false !== strpos($html, '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML')));

		self::$_replacementHash = 'MINIFYHTML' . md5(time());
		self::$_placeholders = array();

		// replace SCRIPTs (and minify) with placeholders
		$html = preg_replace_callback('/\\s*(<script\\b[^>]*?>)([\\s\\S]*?)<\\/script>\\s*/i', 'self::_removeScriptCB', $html);

		// replace STYLEs (and minify) with placeholders
		$html = preg_replace_callback('/\\s*(<style\\b[^>]*?>)([\\s\\S]*?)<\\/style>\\s*/i', 'self::_removeStyleCB', $html);

		// remove HTML comments (not containing IE conditional comments).
		/*$html = preg_replace_callback(
			'/<!--([\\s\\S]*?)-->/'
			,array(self::$className, '_commentCB')
			,$html);
*/
		// replace PREs with placeholders
		$html = preg_replace_callback('/\\s*(<pre\\b[^>]*?>[\\s\\S]*?<\\/pre>)\\s*/i', 'self::_removePreCB', $html);

		// replace TEXTAREAs with placeholders
		$html = preg_replace_callback('/\\s*(<textarea\\b[^>]*?>[\\s\\S]*?<\\/textarea>)\\s*/i', 'self::_removeTaCB', $html);

		// trim each line.
		$html = preg_replace('/^\\s+|\\s+$/m', '', $html);

		// remove ws around block/undisplayed elements
		$html = preg_replace('/\\s+(<\\/?(?:area|base(?:font)?|blockquote|body' . '|caption|center|cite|col(?:group)?|dd|dir|div|dl|dt|fieldset|form' . '|frame(?:set)?|h[1-6]|head|hr|html|legend|li|link|map|menu|meta' . '|ol|opt(?:group|ion)|p|param|t(?:able|body|head|d|h||r|foot|itle)' . '|ul)\\b[^>]*>)/i', '$1', $html);

		// remove ws outside of all elements
		$html = preg_replace_callback('/>([^<]+)</', 'self::_outsideTagCB', $html);

		// use newlines before 1st attribute in open tags (to limit line lengths)
		$html = preg_replace('/(<[a-z\\-]+)\\s+([^>]+>)/i', "$1\n$2", $html);

		// fill placeholders
		$html = str_replace(array_keys(self::$_placeholders), array_values(self::$_placeholders), $html);
		self::$_placeholders = array();

		self::$_cssMinifier = self::$_jsMinifier = null;

		return $html;
	}

	protected static function _commentCB($m) {
		return (0 === strpos($m[1], '[') || false !== strpos($m[1], '<![')) ? $m[0] : '';
	}

	protected static function _reservePlace($content) {
		$placeholder = '%' . self::$_replacementHash . count(self::$_placeholders) . '%';
		self::$_placeholders[$placeholder] = $content;

		return $placeholder;
	}

	protected static $_isXhtml = false;
	protected static $_replacementHash = null;
	protected static $_placeholders = array();
	protected static $_cssMinifier = null;
	protected static $_jsMinifier = null;

	protected static function _outsideTagCB($m) {
		return '>' . preg_replace('/^\\s+|\\s+$/', ' ', $m[1]) . '<';
	}

	protected static function _removePreCB($m) {
		return self::_reservePlace($m[1]);
	}

	protected static function _removeTaCB($m) {
		return self::_reservePlace($m[1]);
	}

	protected static function _removeStyleCB($m) {
		$openStyle = $m[1];
		$css = $m[2];
		// remove HTML comments
		$css = preg_replace('/(?:^\\s*<!--|-->\\s*$)/', '', $css);

		// remove CDATA section markers
		$css = self::_removeCdata($css);

		// minify
		$minifier = self::$_cssMinifier ? self::$_cssMinifier : 'trim';
		$css = call_user_func($minifier, $css);

		return self::_reservePlace(self::_needsCdata($css) ? "{$openStyle}/*<![CDATA[*/{$css}/*]]>*/</style>" : "{$openStyle}{$css}</style>");
	}

	protected static function _removeScriptCB($m) {
		$openScript = $m[1];
		$js = $m[2];

		// remove HTML comments (and ending "//" if present)
		$js = preg_replace('/(?:^\\s*<!--\\s*|\\s*(?:\\/\\/)?\\s*-->\\s*$)/', '', $js);

		// remove CDATA section markers
		$js = self::_removeCdata($js);

		// minify
		$minifier = self::$_jsMinifier ? self::$_jsMinifier : 'trim';
		$js = call_user_func($minifier, $js);

		return self::_reservePlace(self::_needsCdata($js) ? "{$openScript}/*<![CDATA[*/{$js}/*]]>*/</script>" : "{$openScript}{$js}</script>");
	}

	protected static function _removeCdata($str) {
		return (false !== strpos($str, '<![CDATA[')) ? str_replace(array(
			'<![CDATA[',
			']]>'
		), '', $str) : $str;
	}

	protected static function _needsCdata($str) {
		return (self::$_isXhtml && preg_match('/(?:[<&]|\\-\\-|\\]\\]>)/', $str));
	}
}