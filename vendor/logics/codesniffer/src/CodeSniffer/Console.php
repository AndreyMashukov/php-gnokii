<?php

/**
 * PHP version 5.6
 *
 * @package Logics\BuildTools\CodeSniffer
 */

namespace Logics\BuildTools\CodeSniffer;

/**
 * All console input/output should happen through this class only.
 *
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 * @version   SVN: $Date: 2016-08-16 23:39:01 +0900 (Tue, 16 Aug 2016) $ $Revision: 26 $
 * @link      $HeadURL: https://open.logics.net.au/buildtools/codesniffer/tags/0.1.3/src/CodeSniffer/Console.php $
 */

class Console
    {

	/**
	 * Output a message based on verbosity level
	 *
	 * @param string $report Message
	 * @param int    $offset Number of indent tabs
	 * @param int    $level  Verbosity level to be exceeded for message to appear
	 * @param string $eol    End-of-Line character
	 *
	 * @return void
	 *
	 * @internalconst PHP_CODESNIFFER_VERBOSITY Verbosity
	 */

	public static function report($report, $offset, $level, $eol = PHP_EOL)
	    {
		if (PHP_CODESNIFFER_VERBOSITY > $level)
		    {
			echo str_repeat("\t", $offset) . $report . $eol;
		    }
	    } //end report()


	/**
	 * Prepares token content for output to screen.
	 *
	 * Replaces invisible characters so they are visible. On non-Windows
	 * OSes it will also colour the invisible characters.
	 *
	 * @param string $content The content to prepare.
	 *
	 * @return string
	 *
	 * @untranslatable \033[30;1m\\r\033[0m
	 * @untranslatable \033[30;1m\\n\033[0m
	 * @untranslatable \033[30;1m\\t\033[0m
	 * @untranslatable \033[30;1m·\033[0m
	 */

	public static function prepareForOutput($content)
	    {
		if (preg_match("/^WIN/i", PHP_OS) > 1)
		    {
			// @codeCoverageIgnoreStart
			$content = str_replace("\r", '\r', $content);
			$content = str_replace("\n", '\n', $content);
			$content = str_replace("\t", '\t', $content);
		    }
		// @codeCoverageIgnoreEnd
		else
		    {
			$content = str_replace("\r", "\033[30;1m\\r\033[0m", $content);
			$content = str_replace("\n", "\033[30;1m\\n\033[0m", $content);
			$content = str_replace("\t", "\033[30;1m\\t\033[0m", $content);
			$content = str_replace(" ", "\033[30;1m·\033[0m", $content);
		    }

		return $content;
	    } //end prepareForOutput()


    } //end class

?>
