<?php

/**
 * PHP version 5
 *
 * @package Logics\BuildTools\CodeSniffer\PSR2
 */

namespace Logics\BuildTools\CodeSniffer\PSR2;

use \Logics\BuildTools\CodeSniffer\File;
use \Logics\BuildTools\CodeSniffer\PEAR\FunctionCallSignatureSniff as PEAR_FunctionCallSignatureSniff;
use \Logics\BuildTools\CodeSniffer\Tokens;

/**
 * PSR2_Sniffs_Methods_FunctionCallSignatureSniff.
 *
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2006-2012 Squiz Pty Ltd (ABN 77 084 670 600)
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 * @version   SVN: $Date: 2016-08-16 23:39:01 +0900 (Tue, 16 Aug 2016) $ $Revision: 26 $
 * @link      $HeadURL: https://open.logics.net.au/buildtools/codesniffer/tags/0.1.3/src/CodeSniffer/Standards/PSR2/Sniffs/Methods/FunctionCallSignatureSniff.php $
 */

class FunctionCallSignatureSniff extends PEAR_FunctionCallSignatureSniff
    {

	/**
	 * If TRUE, multiple arguments can be defined per line in a multi-line call.
	 *
	 * @var bool
	 */
	public $allowMultipleArguments = false;

	/**
	 * Processes single-line calls.
	 *
	 * @param File  $phpcsFile   The file being scanned.
	 * @param int   $stackPtr    The position of the current token in the stack passed in $tokens.
	 * @param int   $openBracket The position of the opening bracket in the stack passed in $tokens.
	 * @param array $tokens      The stack of tokens that make up the file.
	 *
	 * @return bool
	 *
	 * @internalconst T_COMMA T_COMMA token
	 */

	public function isMultiLineCall(File $phpcsFile, $stackPtr, $openBracket, array $tokens)
	    {
		// If the first argument is on a new line, this is a multi-line
		// function call, even if there is only one argument.
		$next = $phpcsFile->findNext(Tokens::$emptyTokens, ($openBracket + 1), null, true);
		if ($tokens[$next]["line"] !== $tokens[$stackPtr]["line"])
		    {
			return true;
		    }

		$closeBracket = $tokens[$openBracket]["parenthesis_closer"];

		$end = $phpcsFile->findEndOfStatement($openBracket + 1);
		while ($tokens[$end]["code"] === T_COMMA)
		    {
			// If the next bit of code is not on the same line, this is a
			// multi-line function call.
			$next = $phpcsFile->findNext(Tokens::$emptyTokens, ($end + 1), $closeBracket, true);
			if ($next === false)
			    {
				return false;
			    }

			if ($tokens[$next]["line"] !== $tokens[$end]["line"])
			    {
				return true;
			    }

			$end = $phpcsFile->findEndOfStatement($next);
		    }

		// We've reached the last argument, so see if the next content
		// (should be the close bracket) is also on the same line.
		$next = $phpcsFile->findNext(Tokens::$emptyTokens, ($end + 1), $closeBracket, true);
		if ($next !== false && $tokens[$next]["line"] !== $tokens[$end]["line"])
		    {
			return true;
		    }

		return false;
	    } //end isMultiLineCall()


    } //end class

?>
