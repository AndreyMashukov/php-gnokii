<?php

/**
 * PHP version 5
 *
 * @package Logics\BuildTools\CodeSniffer\PEAR
 */

namespace Logics\BuildTools\CodeSniffer\PEAR;

use \Logics\BuildTools\CodeSniffer\File;
use \Logics\BuildTools\CodeSniffer\Sniff;
use \Logics\BuildTools\CodeSniffer\Tokens;

/**
 * ValidDefaultValueSniff.
 *
 * A Sniff to ensure that parameters defined for a function that have a default
 * value come at the end of the function signature.
 *
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @author    Marc McIntyre <mmcintyre@squiz.net>
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2006-2012 Squiz Pty Ltd (ABN 77 084 670 600)
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 * @version   SVN: $Date: 2016-08-16 23:39:01 +0900 (Tue, 16 Aug 2016) $ $Revision: 26 $
 * @link      $HeadURL: https://open.logics.net.au/buildtools/codesniffer/tags/0.1.3/src/CodeSniffer/Standards/PEAR/Sniffs/Functions/ValidDefaultValueSniff.php $
 */

class ValidDefaultValueSniff implements Sniff
    {

	/**
	 * Returns an array of tokens this test wants to listen for.
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
	 * @internalconst T_EQUAL T_EQUAL token
	 *
	 * @untranslatable NotAtEnd
	 */

	public function process(File $phpcsFile, $stackPtr)
	    {
		$tokens = &$phpcsFile->tokens;

		$argStart = $tokens[$stackPtr]["parenthesis_opener"];
		$argEnd   = $tokens[$stackPtr]["parenthesis_closer"];

		// Flag for when we have found a default in our arg list.
		// If there is a value without a default after this, it is an error.
		$defaultFound = false;

		$nextArg = $phpcsFile->findNext(T_VARIABLE, ($argStart + 1), $argEnd);
		while ($nextArg !== false)
		    {
			$prevToken     = $phpcsFile->findPrevious(Tokens::$emptyTokens, ($nextArg - 1), null, true);
			$variadic      = ($tokens[$prevToken]["code"] === T_ELLIPSIS);
			$nextToken     = $phpcsFile->findNext(Tokens::$emptyTokens, ($nextArg + 1), null, true);
			$argHasDefault = ($tokens[$nextToken]["code"] === T_EQUAL);
			if ($argHasDefault === false && $defaultFound === true && $variadic === false)
			    {
				$error = _("Arguments with default values must be at the end of the argument list");
				$phpcsFile->addError($error, $nextArg, "NotAtEnd");
				break;
			    }

			if ($argHasDefault === true)
			    {
				$defaultFound = true;
			    }

			$nextArg = $phpcsFile->findNext(T_VARIABLE, ($nextArg + 1), $argEnd);
		    }
	    } //end process()


    } //end class

?>