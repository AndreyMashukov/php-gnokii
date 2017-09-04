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
 * Beauty_Sniffs_PHP_ExceptionsShouldHaveCode
 *
 * All exceptions should have exception code in addition to exception message
 *
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @author    Marc McIntyre <mmcintyre@squiz.net>
 * @author    Gleb An <gleb@logics.net.au>
 * @author    Anastasia Bashkirtseva <anastasia@logics.net.au>
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2006-2012 Squiz Pty Ltd (ABN 77 084 670 600)
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://opensource.org/licenses/MIT MIT License
 * @version   SVN: $Date: 2016-08-16 23:39:01 +0900 (Tue, 16 Aug 2016) $ $Revision: 26 $
 * @link      $HeadURL: https://open.logics.net.au/buildtools/codesniffer/tags/0.1.3/src/CodeSniffer/Standards/Beauty/Sniffs/PHP/ExceptionsShouldHaveCodeSniff.php $
 */

class ExceptionsShouldHaveCodeSniff implements Sniff
    {

	/**
	 * Returns an array of tokens this test wants to listen for.
	 *
	 * @return array
	 */

	public function register()
	    {
		return array(
			T_NEW,
			T_IF,
		       );
	    } //end register()


	/**
	 * Processes this test, when used continue
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $stackPtr  The position of the current token in the stack passed in $tokens.
	 *
	 * @return void
	 *
	 * @internalconst T_OPEN_PARENTHESIS T_OPEN_PARENTHESIS token
	 */

	public function process(File $phpcsFile, $stackPtr)
	    {
		$tokens = &$phpcsFile->tokens;
		if ($tokens[$stackPtr]["code"] === T_NEW)
		    {
			$this->_searchNotCorrectUsingException($phpcsFile, $stackPtr);
		    }
		else if ($tokens[$stackPtr]["code"] === T_IF)
		    {
			$tokenOpenParenthesis = $phpcsFile->findNext(array(T_WHITESPACE), ($stackPtr + 1), null, true);
			if ($tokens[$tokenOpenParenthesis]["code"] === T_OPEN_PARENTHESIS)
			    {
				$this->_searchNotCorrectUsingCondition($phpcsFile, $tokenOpenParenthesis);
			    }
		    }
	    } //end process()


	/**
	 * Continues process() of search not correct using Exception
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $stackPtr  The position of the current token int the stack passed in $tokens.
	 *
	 * @return void
	 *
	 * @internalconst T_OPEN_PARENTHESIS T_OPEN_PARENTHESIS token
	 * @internalconst T_COMMA            T_COMMA token
	 *
	 * @untranslatable Exception
	 * @untranslatable Exceptions
	 */

	private function _searchNotCorrectUsingException(File &$phpcsFile, $stackPtr)
	    {
		$tokens         = &$phpcsFile->tokens;
		$tokenException = $phpcsFile->findNext(array(T_WHITESPACE), ($stackPtr + 2), null, true);
		if (($tokens[$tokenException]["code"] === T_STRING) && ($tokens[$tokenException]["content"] === "Exception"))
		    {
			$tokenOpenParenthesis   = $phpcsFile->findNext(array(T_WHITESPACE), ($tokenException + 1), null, true);
			$countNestedParenthesis = 0;
			if (isset($tokens[$tokenOpenParenthesis]["nested_parenthesis"]) === true)
			    {
				$countNestedParenthesis = count($tokens[$tokenOpenParenthesis]["nested_parenthesis"]);
			    }

			if ($tokens[$tokenOpenParenthesis]["code"] === T_OPEN_PARENTHESIS)
			    {
				$startFind                       = $tokenOpenParenthesis;
				$tokenCloseParenthesis           = $tokens[$tokenOpenParenthesis]["parenthesis_closer"];
				$commaBetweenExceptionParameters = false;
				do
				    {
					$commaToken = $phpcsFile->findNext(T_COMMA, ($startFind + 1), ($tokenCloseParenthesis - 1));
					$startFind  = $commaToken;
					if ($commaToken !== false && count($tokens[$commaToken]["nested_parenthesis"]) === ($countNestedParenthesis + 1))
					    {
						$commaBetweenExceptionParameters = true;
					    }
				    } while($commaToken !== false);

				if ($commaBetweenExceptionParameters === false)
				    {
					$phpcsFile->addError(_("Exception constructor requires use of two paramters: message and code"), $stackPtr, "Exceptions");
				    }
			    } //end if
		    } //end if
	    } //end _searchNotCorrectUsingException()


	/**
	 * Continues process() of search not correct using getMessage() function in condition
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $stackPtr  The position of the current token int the stack passed in $tokens. (T_OPEN_PARENTHESIS)
	 *
	 * @return void
	 *
	 * @internalconst T_OPEN_PARENTHESIS T_OPEN_PARENTHESIS token
	 *
	 * @untranslatable getMessage
	 * @untranslatable Exceptions
	 */

	private function _searchNotCorrectUsingCondition(File &$phpcsFile, $stackPtr)
	    {
		$tokens                = &$phpcsFile->tokens;
		$startFind             = $stackPtr;
		$tokenCloseParenthesis = $tokens[$stackPtr]["parenthesis_closer"];
		do
		    {
			$stringToken = $phpcsFile->findNext(T_STRING, ($startFind + 1), ($tokenCloseParenthesis - 1));
			$startFind   = $stringToken;
			if ($stringToken !== false && $tokens[$stringToken]["content"] === "getMessage")
			    {
				if ($tokens[$phpcsFile->findNext(T_WHITESPACE, ($stringToken + 1), null, true)]["code"] === T_OPEN_PARENTHESIS)
				    {
					$phpcsFile->addError(_("Comparison of exception getMessage() is forbidden"), $stringToken, "Exceptions");
				    }
			    }
		    } while ($stringToken !== false);
	    } //end _searchNotCorrectUsingCondition()


    } //end class

?>
