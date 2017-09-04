<?php

/**
 * PHP version 5.6
 *
 * @package Logics\Foundation\CLI
 */

namespace Logics\Foundation\CLI;

use \Exception;

/**
 * Handles color output on ANSI terminals
 *
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://opensource.org/licenses/MIT MIT License
 * @version   SVN: $Date: 2016-08-17 23:19:29 +0930 (Wed, 17 Aug 2016) $ $Revision: 63 $
 * @link      $HeadURL: http://svn.logics.net.au/foundation/CLI/trunk/src/CLI.php $
 *
 * @untranslatable black
 * @untranslatable red
 * @untranslatable green
 * @untranslatable yellow
 * @untranslatable blue
 * @untranslatable magenta
 * @untranslatable cyan
 * @untranslatable white
 */

class Colorize
    {

	const BLACK   = "black";
	const RED     = "red";
	const GREEN   = "green";
	const YELLOW  = "yellow";
	const BLUE    = "blue";
	const MAGENTA = "magenta";
	const CYAN    = "cyan";
	const WHITE   = "white";

	/**
	 * Known colors
	 *
	 * @var array
	 */
	static private $_colors = array(
				   self::BLACK   => "0",
				   self::RED     => "1",
				   self::GREEN   => "2",
				   self::YELLOW  => "3",
				   self::BLUE    => "4",
				   self::MAGENTA => "5",
				   self::CYAN    => "6",
				   self::WHITE   => "7",
				  );

	/**
	 * Should colors be used?
	 *
	 * @var bool
	 */
	static private $_enabled = true;

	/**
	 * Checks if we running on an ANSI terminal
	 *
	 * @return bool Color terminal is available
	 *
	 * @untranslatable STDIO
	 */

	static private function _available()
	    {
		$meta = stream_get_meta_data(STDOUT);
		return ($meta["stream_type"] === "STDIO" && posix_isatty(STDOUT) === true);
	    } //end _available()


	/**
	 * Enable color output
	 *
	 * @return void
	 */

	static public function enable()
	    {
		self::$_enabled = true;
	    } //end enable()


	/**
	 * Disable color output
	 *
	 * @return void
	 */

	static public function disable()
	    {
		self::$_enabled = false;
	    } //end disable()


	/**
	 * Returns the given text wrapped in the appropriate color and reset code
	 *
	 * @param string $text       String to wrap
	 * @param string $foreground One of the available color names for foreground
	 * @param string $background One of the available color names for background
	 * @param bool   $bold       Show bold text
	 *
	 * @return string Colorized string
	 */

	static public function text($text, $foreground = "", $background = "", $bold = false)
	    {
		return self::_getColorCode($foreground, $background, $bold) . $text . self::_reset();
	    } //end text()


	/**
	 * Gets the appropriate terminal code for the given colors
	 *
	 * @param string $foreground One of the available color names for foreground
	 * @param string $background One of the available color names for background
	 * @param bool   $bold       Show bold text
	 *
	 * @return string Color code
	 *
	 * @throws Exception No such color
	 *
	 * @exceptioncode EXCEPTION_NO_SUCH_COLOR
	 *
	 * @untranslatable \33[1m
	 * @untranslatable m
	 */

	static private function _getColorCode($foreground, $background, $bold)
	    {
		$code = "";
		if (self::$_enabled === true && self::_available() === true)
		    {
			if ($bold === true)
			    {
				$code .= "\33[1m";
			    }

			if ($foreground !== "")
			    {
				if (isset(self::$_colors[$foreground]) === true)
				    {
					$code .= "\33[3" . self::$_colors[$foreground] . "m";
				    }
				else
				    {
					throw new Exception(_("No such color"), EXCEPTION_NO_SUCH_COLOR);
				    }
			    }

			if ($background !== "")
			    {
				if (isset(self::$_colors[$background]) === true)
				    {
					$code .= "\33[4" . self::$_colors[$background] . "m";
				    }
				else
				    {
					throw new Exception(_("No such color"), EXCEPTION_NO_SUCH_COLOR);
				    }
			    }
		    } //end if

		return $code;
	    } //end _getColorCode()


	/**
	 * Gets the reset terminal code
	 *
	 * @return string Reset code
	 *
	 * @untranslatable \33[0m
	 */

	static private function _reset()
	    {
		$code = "";
		if (self::$_enabled === true && self::_available() === true)
		    {
			$code .= "\33[0m";
		    }

		return $code;
	    } //end _reset()


	/**
	 * Get last active color of the text
	 *
	 * @param string $text Colorized text
	 *
	 * @return array Colors
	 */

	static public function getCurrentColor($text)
	    {
		$foreground = "";
		$background = "";
		$bold       = false;

		preg_match_all("/\\33\[(?P<color>\d{1,2})m/U", $text, $colors);
		foreach ($colors["color"] as $color)
		    {
			if ($color === "0")
			    {
				$foreground = "";
				$background = "";
				$bold       = false;
			    }
			else if ($color === "1")
			    {
				$bold = true;
			    }
			else if ((int) $color >= 30 && (int) $color <= 37)
			    {
				$foreground = array_search((string) ($color % 10), self::$_colors);
			    }
			else if ((int) $color >= 40 && (int) $color <= 47)
			    {
				$background = array_search((string) ($color % 10), self::$_colors);
			    }
		    }

		return array(
			"foreground" => $foreground,
			"background" => $background,
			"bold"       => $bold,
		       );
	    } //end getCurrentColor()


	/**
	 * Prepend the string with color code
	 *
	 * @param string $text  String to colorize
	 * @param array  $color Colors to prepend
	 *
	 * @return string Text with color code prepended
	 */

	static public function addColor($text, array $color)
	    {
		$foreground = (isset($color["foreground"]) === true) ? $color["foreground"] : "";
		$background = (isset($color["background"]) === true) ? $color["background"] : "";
		$bold       = (isset($color["bold"]) === true) ? $color["bold"] : false;
		return self::_getColorCode($foreground, $background, $bold) . $text;
	    } //end addColor()


	/**
	 * Add color reset to the string
	 *
	 * @param string $text Colorized string
	 *
	 * @return string Colorized string with color reset added
	 */

	static public function addReset($text)
	    {
		return $text . self::_reset();
	    } //end addReset()


	/**
	 * Remove colorization from text
	 *
	 * @param string $text Colorized text
	 *
	 * @return string Plain text
	 */

	static public function remove($text)
	    {
		return preg_replace("/\\33\[.*m/U", "", $text);
	    } //end remove()


	/**
	 * Color safe strlen
	 *
	 * @param string $text Colorized string
	 *
	 * @return int String length
	 */

	static public function strlen($text)
	    {
		return mb_strlen(self::remove($text));
	    } //end strlen()


	/**
	 * Color safe substr
	 *
	 * @param string $text   Colorized string
	 * @param int    $pos    Starting position
	 * @param int    $length Length
	 *
	 * @return string Substring
	 */

	static public function substr($text, $pos, $length = 0)
	    {
		preg_match_all("/\\33\[(?P<color>\d{1,2})m/U", $text, $colors);
		$split     = preg_split("/\\33\[\d{1,2}m/U", $text);
		$pos       = ($pos < 0) ? (self::strlen($text) + $pos) : $pos;
		$current   = 0;
		$out       = "";
		$remainder = self::_length(self::strlen($text), $length);
		foreach ($split as $idx => $s)
		    {
			if ((($pos + $remainder) < $current || ($current + mb_strlen($s)) < $pos) === false)
			    {
				$chunk      = mb_substr($s, ($pos - $current), $remainder);
				$remainder -= mb_strlen($chunk);
				$out       .= ((isset($colors[0][($idx - 1)]) === true) ? $colors[0][($idx - 1)] : "") . $chunk;
			    }

			$current += mb_strlen($s);

			if ($remainder <= 0)
			    {
				break;
			    }
		    }

		return $out;
	    } //end substr()


	/**
	 * Figure out the length to use: string length or user provided
	 *
	 * @param int $full String length
	 * @param int $user User provided length
	 *
	 * @return int Length to use
	 */

	static private function _length($full, $user)
	    {
		return ($user === 0) ? self::strlen($full) : $user;
	    } //end _length()


    } //end class

?>
