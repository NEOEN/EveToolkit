<?php
/**
 * Created by PhpStorm.
 * User: Lamer
 * Date: 06.03.2017
 * Time: 22:07
 */

namespace Modules\Minify;

class CssMinify {

	static public function minify($content) {
		$content = self::stripComments($content);
		$content = self::stripWhitespaces($content);
		$content = self::stripColor($content);
		$content = self::stripZeros($content);
		$content = self::stripEmptyTags($content);
		$content = self::moveImportsToTop($content);
		return $content;
	}

	protected static function stripComments($content) {
		return preg_replace('#\/\*.*?\*\/#s', '', $content);
	}

	protected static function stripWhitespaces($content) {
		$aPattern = array(
			'/\n/',
			'/\r\n/',
			'/\n\r/',
			'/\r/',
			'#/\*.+\*/#sU'
		);
		$content = preg_replace($aPattern, '', $content);
		$content = preg_replace('/\s+/', ' ', $content);
		$content = preg_replace('/\s*{\s*/', '{', $content);
		$content = preg_replace('/\s*}\s*/', '}', $content);
		$content = preg_replace('/\s*:\s*/', ':', $content);
		$content = preg_replace('/\s*;\s*/', ';', $content);
		$content = preg_replace('/\s*,\s*/', ',', $content);
		$content = preg_replace('/\s*([\*$~^|]?+=|[{};,>~]|!important\b)\s*/', '$1', $content);
		$content = preg_replace('/([\[(:])\s+/', '$1', $content);
		$content = preg_replace('/\s+([\]\)])/', '$1', $content);
		$content = preg_replace('/\s+(:)(?![^\}]*\{)/', '$1', $content);
		$content = preg_replace('/\s*([+-])\s*(?=[^}]*{)/', '$1', $content);
		$content = str_replace(';}', '}', $content);

		return trim($content);
	}

	protected static function stripColor($content) {
		return preg_replace('/(?<=[: ])#([0-9a-f])\\1([0-9a-f])\\2([0-9a-f])\\3(?=[; }])/i', '#$1$2$3', $content);
	}

	protected static function stripZeros($content) {
		// reusable bits of code throughout these regexes:
		// before & after are used to make sure we don't match lose unintended
		// 0-like values (e.g. in #000, or in http://url/1.0)
		// units can be stripped from 0 values, or used to recognize non 0
		// values (where wa may be able to strip a .0 suffix)
		$before = '(?<=[:(, ])';
		$after = '(?=[ ,);}])';
		$units = '(em|ex|%|px|cm|mm|in|pt|pc|ch|rem|vh|vw|vmin|vmax|vm)';
		// strip units after zeroes (0px -> 0)
		// NOTE: it should be safe to remove all units for a 0 value, but in
		// practice, Webkit (especially Safari) seems to stumble over at least
		// 0%, potentially other units as well. Only stripping 'px' for now.
		// @see https://github.com/matthiasmullie/minify/issues/60
		$content = preg_replace('/' . $before . '(-?0*(\.0+)?)(?<=0)px' . $after . '/', '\\1', $content);
		// strip 0-digits (.0 -> 0)
		$content = preg_replace('/' . $before . '\.0+' . $units . '?' . $after . '/', '0\\1', $content);
		// strip trailing 0: 50.10 -> 50.1, 50.10px -> 50.1px
		$content = preg_replace('/' . $before . '(-?[0-9]+\.[0-9]+)0+' . $units . '?' . $after . '/', '\\1\\2', $content);
		// strip trailing 0: 50.00 -> 50, 50.00px -> 50px
		$content = preg_replace('/' . $before . '(-?[0-9]+)\.0+' . $units . '?' . $after . '/', '\\1\\2', $content);
		// strip leading 0: 0.1 -> .1, 01.1 -> 1.1
		$content = preg_replace('/' . $before . '(-?)0+([0-9]*\.[0-9]+)' . $units . '?' . $after . '/', '\\1\\2\\3', $content);
		// strip negative zeroes (-0 -> 0) & truncate zeroes (00 -> 0)
		$content = preg_replace('/' . $before . '-?0+' . $units . '?' . $after . '/', '0\\1', $content);
		// remove zeroes where they make no sense in calc: e.g. calc(100px - 0)
		// the 0 doesn't have any effect, and this isn't even valid without unit
		// strip all `+ 0` or `- 0` occurrences: calc(10% + 0) -> calc(10%)
		// looped because there may be multiple 0s inside 1 group of parentheses
		do {
			$previous = $content;
			$content = preg_replace('/\(([^\(\)]+) [\+\-] 0( [^\(\)]+)?\)/', '(\\1\\2)', $content);
		} while ($content !== $previous);
		// strip all `0 +` occurrences: calc(0 + 10%) -> calc(10%)
		$content = preg_replace('/\(0 \+ ([^\(\)]+)\)/', '(\\1)', $content);
		// strip all `0 -` occurrences: calc(0 - 10%) -> calc(-10%)
		$content = preg_replace('/\(0 \- ([^\(\)]+)\)/', '(-\\1)', $content);
		// I'm not going to attempt to optimize away `x * 0` instances:
		// it's dumb enough code already that it likely won't occur, and it's
		// too complex to do right (order of operations would have to be
		// respected etc)
		// what I cared about most here was fixing incorrectly truncated units
		// IE doesn't seem to understand a unitless flex-basis value, so let's
		// add it in again (make it `%`, which is only 1 char: 0%, 0px, 0
		// anything, it's all just the same)
		$content = preg_replace('/flex:([^ ]+ [^ ]+ )0([;\}])/', 'flex:${1}0%${2}', $content);
		$content = preg_replace('/flex-basis:0([;\}])/', 'flex-basis:0%${1}', $content);

		return $content;
	}

	protected static function stripEmptyTags($content){
		return preg_replace('/(^|\}|;)[^\{\};]+\{\s*\}/', '\\1', $content);
	}

	protected static function moveImportsToTop($content){
		if (preg_match_all('/@import[^;]+;/', $content, $matches)) {
			// remove from content
			foreach ($matches[0] as $import) {
				$content = str_replace($import, '', $content);
			}
			// add to top
			$content = implode('', $matches[0]).$content;
		}

		return $content;
	}

}