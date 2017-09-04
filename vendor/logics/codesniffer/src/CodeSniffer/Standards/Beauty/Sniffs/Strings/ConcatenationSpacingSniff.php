<?php

/**
 * PHP version 5
 *
 * @package Logics\BuildTools\CodeSniffer\Beauty
 */

namespace Logics\BuildTools\CodeSniffer\Beauty;

use \Logics\BuildTools\CodeSniffer\File;
use \Logics\BuildTools\CodeSniffer\Sniff;

/**
 * ConcatenationSpacingSniff.
 *
 * Makes sure there are no spaces between the concatenation operator (.) and
 * the strings being concatenated.
 *
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @author    Marc McIntyre <mmcintyre@squiz.net>
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2006-2012 Squiz Pty Ltd (ABN 77 084 670 600)
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   http://matrix.squiz.net/developer/tools/php_cs/licence BSD Licence
 * @version   SVN: $Date: 2016-08-16 23:39:01 +0900 (Tue, 16 Aug 2016) $ $Revision: 26 $
 * @link      $HeadURL: https://open.logics.net.au/buildtools/codesniffer/tags/0.1.3/src/CodeSniffer/Standards/Beauty/Sniffs/Strings/ConcatenationSpacingSniff.php $
 */

class ConcatenationSpacingSniff implements Sniff
    {

	/**
	 * Returns an array of tokens this test wants to listen for.
	 *
	 * @return array
	 *
	 * @internalconst T_STRING_CONCAT T_STRING_CONCAT token
	 */

	public function register()
	    {
		return array(T_STRING_CONCAT);
	    } //end register()


	/**
	 * Processes this test, when one of its tokens is encountered.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $stackPtr  The position of the current token in the stack passed in $tokens.
	 *
	 * @return void
	 */

	public function process(File $phpcsFile, $stackPtr)
	    {
		$tokens = &$phpcsFile->tokens;

		$found    = "";
		$expected = "";
		$error    = false;

		if ($tokens[($stackPtr - 1)]["code"] === T_WHITESPACE && strlen($tokens[($stackPtr - 1)]["content"]) === 1)
		    {
			$found    .= "..." . substr($tokens[($stackPtr - 1)]["content"], -5) . $tokens[$stackPtr]["content"];
			$expected .= "..." . substr($tokens[($stackPtr - 1)]["content"], -5) . $tokens[$stackPtr]["content"];
		    }
		else
		    {
			$expected .= "..." . substr($tokens[($stackPtr - 2)]["content"], -5) . " " . $tokens[$stackPtr]["content"];
			$found    .= "..." . substr($tokens[($stackPtr - 2)]["content"], -5) . $tokens[($stackPtr - 1)]["content"] . $tokens[$stackPtr]["content"];
			$error     = true;
		    }

		if ($tokens[($stackPtr + 1)]["code"] === T_WHITESPACE && strlen($tokens[($stackPtr + 1)]["content"]) === 1)
		    {
			$found    .= $tokens[($stackPtr + 1)]["content"];
			$expected .= $tokens[($stackPtr + 1)]["content"];
		    }
		else
		    {
			$expected .= substr($tokens[($stackPtr + 2)]["content"], 0, 5) . "...";
			$found    .= $tokens[($stackPtr + 1)]["content"] . substr($tokens[($stackPtr + 2)]["content"], 0, 5) . "...";
			$error     = true;
		    }

		if ($error === true)
		    {
			$found    = str_replace("\n", '\n', $found);
			$expected = str_replace("\n", '\n', $expected);

			$message = _("Concat operator must not be surrounded by one space on each side. Found") . " \"" . $found . "\"; " .
				   _("expected") . " \"" . $expected . "\"";
			$phpcsFile->addError($message, $stackPtr);
		    }
	    } //end process()


    } //end class

?>
