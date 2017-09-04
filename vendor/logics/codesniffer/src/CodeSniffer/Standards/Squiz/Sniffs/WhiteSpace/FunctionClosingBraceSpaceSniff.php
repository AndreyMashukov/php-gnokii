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
 * FunctionClosingBraceSpaceSniff
 *
 * Checks that there is one empty line before the closing brace of a function.
 *
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @author    Marc McIntyre <mmcintyre@squiz.net>
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2006-2012 Squiz Pty Ltd (ABN 77 084 670 600)
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 * @version   SVN: $Date: 2016-08-16 23:39:01 +0900 (Tue, 16 Aug 2016) $ $Revision: 26 $
 * @link      $HeadURL: https://open.logics.net.au/buildtools/codesniffer/tags/0.1.3/src/CodeSniffer/Standards/Squiz/Sniffs/WhiteSpace/FunctionClosingBraceSpaceSniff.php $
 *
 * @untranslatable PHP
 * @untranslatable JS
 */

class FunctionClosingBraceSpaceSniff implements Sniff
    {

	/**
	 * A list of tokenizers this sniff supports.
	 *
	 * @var array
	 */
	public $supportedTokenizers = array(
				       "PHP",
				       "JS",
				      );

	/**
	 * Returns an array of tokens this test wants to listen for.
	 *
	 * @return array
	 *
	 * @internalconst T_CLOSURE T_CLOSURE token
	 */

	public function register()
	    {
		return array(
			T_FUNCTION,
			T_CLOSURE,
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
	 * @internalconst T_CLOSURE T_CLOSURE token
	 *
	 * @untranslatable JS
	 * @untranslatable SpacingBetween
	 * @untranslatable ContentBeforeClose
	 * @untranslatable SpacingBeforeNestedClose
	 * @untranslatable SpacingBeforeClose
	 * @untranslatable function () {}
	 */

	public function process(File $phpcsFile, $stackPtr)
	    {
		$tokens = &$phpcsFile->tokens;

		if (isset($tokens[$stackPtr]["scope_closer"]) === false)
		    {
			// Probably an interface method.
			return;
		    }

		$closeBrace  = $tokens[$stackPtr]["scope_closer"];
		$prevContent = $phpcsFile->findPrevious(T_WHITESPACE, ($closeBrace - 1), null, true);

		// Special case for empty JS functions.
		if ($phpcsFile->tokenizerType === "JS" && $prevContent === $tokens[$stackPtr]["scope_opener"])
		    {
			// In this case, the opening and closing brace must be right next to each other.
			if ($tokens[$stackPtr]["scope_closer"] !== ($tokens[$stackPtr]["scope_opener"] + 1))
			    {
				$error = _("The opening and closing braces of empty functions must be directly next to each other; e.g.,") . "function () {}";
				$phpcsFile->addError($error, $closeBrace, "SpacingBetween");
			    }

			return;
		    }

		$nestedFunction = false;
		if ($phpcsFile->hasCondition($stackPtr, T_FUNCTION) === true || $phpcsFile->hasCondition($stackPtr, T_CLOSURE) === true
		|| isset($tokens[$stackPtr]["nested_parenthesis"]) === true)
		    {
			$nestedFunction = true;
		    }

		$braceLine = $tokens[$closeBrace]["line"];
		$prevLine  = $tokens[$prevContent]["line"];
		$found     = ($braceLine - $prevLine - 1);

		$afterKeyword  = $phpcsFile->findNext(T_WHITESPACE, ($stackPtr + 1), null, true);
		$beforeKeyword = $phpcsFile->findPrevious(T_WHITESPACE, ($stackPtr - 1), null, true);
		if ($nestedFunction === true)
		    {
			if ($found < 0)
			    {
				$error = _("Closing brace of nested function must be on a new line");
				$phpcsFile->addError($error, $closeBrace, "ContentBeforeClose");
			    }
			else if ($found > 0)
			    {
				$error = _("Expected 0 blank lines before closing brace of nested function; %s found");
				$data  = array($found);
				$phpcsFile->addError($error, $closeBrace, "SpacingBeforeNestedClose", $data);
			    } //end if
		    }
		else
		    {
			if ($found !== 1)
			    {
				if ($found < 0)
				    {
					$found = 0;
				    }

				$error = _("Expected 1 blank line before closing function brace; %s found");
				$data  = array($found);
				$phpcsFile->addError($error, $closeBrace, "SpacingBeforeClose", $data);
			    } //end if
		    } //end if
	    } //end process()


    } //end class

?>