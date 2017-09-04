<?php

/**
 * PHP version 5
 *
 * @package Logics\BuildTools\CodeSniffer\Generic
 */

namespace Logics\BuildTools\CodeSniffer\Generic;

use \Logics\BuildTools\CodeSniffer\File;
use \Logics\BuildTools\CodeSniffer\Sniff;

/**
 * OpeningFunctionBraceKernighanRitchieSniff.
 *
 * Checks that the opening brace of a function is on the same line
 * as the function declaration.
 *
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @author    Marc McIntyre <mmcintyre@squiz.net>
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2006-2012 Squiz Pty Ltd (ABN 77 084 670 600)
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 * @version   SVN: $Date: 2016-08-16 23:39:01 +0900 (Tue, 16 Aug 2016) $ $Revision: 26 $
 * @link      $HeadURL: https://open.logics.net.au/buildtools/codesniffer/tags/0.1.3/src/CodeSniffer/Standards/Generic/Sniffs/Functions/OpeningFunctionBraceKernighanRitchieSniff.php $
 */

class OpeningFunctionBraceKernighanRitchieSniff implements Sniff
    {

	/**
	 * Registers the tokens that this sniff wants to listen for.
	 *
	 * @return array
	 */

	public function register()
	    {
		return array(T_FUNCTION);
	    } //end register()


	/**
	 * Processes this test, when one of its tokens is encountered.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $stackPtr  The position of the current token in the stack passed in $tokens.
	 *
	 * @return void
	 *
	 * @untranslatable BraceOnNewLine
	 * @untranslatable %s
	 * @untranslatable SpaceAfterBracket
	 * @untranslatable SpaceBeforeBrace
	 */

	public function process(File $phpcsFile, $stackPtr)
	    {
		$tokens = &$phpcsFile->tokens;

		if (isset($tokens[$stackPtr]["scope_opener"]) === true)
		    {
			$openingBrace = $tokens[$stackPtr]["scope_opener"];

			// The end of the function occurs at the end of the argument list. Its
			// like this because some people like to break long function declarations
			// over multiple lines.
			$functionLine = $tokens[$tokens[$stackPtr]["parenthesis_closer"]]["line"];
			$braceLine    = $tokens[$openingBrace]["line"];

			$lineDifference = ($braceLine - $functionLine);

			if ($lineDifference > 0)
			    {
				$error = _("Opening brace should be on the same line as the declaration");
				$phpcsFile->addError($error, $openingBrace, "BraceOnNewLine");
			    }
			else
			    {
				$closeBracket = $tokens[$stackPtr]["parenthesis_closer"];
				if ($tokens[($closeBracket + 1)]["code"] !== T_WHITESPACE)
				    {
					$length = 0;
				    }
				else if ($tokens[($closeBracket + 1)]["content"] === "\t")
				    {
					$length = '\t';
				    }
				else
				    {
					$length = strlen($tokens[($closeBracket + 1)]["content"]);
				    }

				if ($length !== 1)
				    {
					$error = _("Expected 1 space after closing parenthesis; found") . " %s";
					$data  = array($length);
					$phpcsFile->addError($error, $closeBracket, "SpaceAfterBracket", $data);
				    }
				else
				    {
					$closeBrace = $tokens[$stackPtr]["scope_opener"];
					if ($tokens[($closeBrace - 1)]["code"] !== T_WHITESPACE)
					    {
						$length = 0;
					    }
					else if ($tokens[($closeBrace - 1)]["content"] === "\t")
					    {
						$length = '\t';
					    }
					else
					    {
						$length = strlen($tokens[($closeBrace - 1)]["content"]);
					    }

					if ($length !== 1)
					    {
						$error = _("Expected 1 space before opening brace; found") . " %s";
						$data  = array($length);
						$phpcsFile->addError($error, $openingBrace, "SpaceBeforeBrace", $data);
					    }
				    } //end if
			    } //end if
		    } //end if
	    } //end process()


    } //end class

?>
