<?php

/**
 * PHP version 5
 *
 * @package Logics\BuildTools\CodeSniffer\Squiz
 */

namespace Logics\BuildTools\CodeSniffer\Squiz;

use \Logics\BuildTools\CodeSniffer\File;
use \Logics\BuildTools\CodeSniffer\Sniff;

/**
 * Squiz_Sniffs_WhiteSpace_LanguageConstructSpacingSniff.
 *
 * Ensures all language constructs (without brackets) contain a
 * single space between themselves and their content.
 *
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @author    Marc McIntyre <mmcintyre@squiz.net>
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2006-2012 Squiz Pty Ltd (ABN 77 084 670 600)
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 * @version   SVN: $Date: 2016-08-16 23:39:01 +0900 (Tue, 16 Aug 2016) $ $Revision: 26 $
 * @link      $HeadURL: https://open.logics.net.au/buildtools/codesniffer/tags/0.1.3/src/CodeSniffer/Standards/Squiz/Sniffs/WhiteSpace/LanguageConstructSpacingSniff.php $
 */

class LanguageConstructSpacingSniff implements Sniff
    {

	/**
	 * Returns an array of tokens this test wants to listen for.
	 *
	 * @return array
	 */

	public function register()
	    {
		return array(
			T_ECHO,
			T_PRINT,
			T_RETURN,
			T_INCLUDE,
			T_INCLUDE_ONCE,
			T_REQUIRE,
			T_REQUIRE_ONCE,
			T_NEW,
		       );
	    } //end register()


	/**
	 * Processes this test, when one of its tokens is encountered.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $stackPtr  The position of the current token in the stack passed in $tokens.
	 *
	 * @return void
	 *
	 * @internalconst T_SEMICOLON T_SEMICOLON token
	 *
	 * @untranslatable IncorrectSingle
	 * @untranslatable Incorrect
	 * @untranslatable \"%s\"
	 * @untranslatable %s
	 */

	public function process(File $phpcsFile, $stackPtr)
	    {
		$tokens = &$phpcsFile->tokens;

		// No content for this language construct.
		if ($tokens[($stackPtr + 1)]["code"] !== T_SEMICOLON)
		    {
			if ($tokens[($stackPtr + 1)]["code"] === T_WHITESPACE)
			    {
				$content       = $tokens[($stackPtr + 1)]["content"];
				$contentLength = strlen($content);
				if ($contentLength !== 1)
				    {
					$error = _("Language constructs must be followed by a single space; expected 1 space but found") . " %s";
					$data  = array($contentLength);
					$phpcsFile->addError($error, $stackPtr, "IncorrectSingle", $data);
				    }
			    }
			else
			    {
				$error = _("Language constructs must be followed by a single space; expected") . " \"%s\" " . _("but found") . " \"%s\"";
				$data  = array(
					  $tokens[$stackPtr]["content"] . " " . $tokens[($stackPtr + 1)]["content"],
					  $tokens[$stackPtr]["content"] . $tokens[($stackPtr + 1)]["content"],
					 );
				$phpcsFile->addError($error, $stackPtr, "Incorrect", $data);
			    }
		    } //end if
	    } //end process()


    } //end class

?>
