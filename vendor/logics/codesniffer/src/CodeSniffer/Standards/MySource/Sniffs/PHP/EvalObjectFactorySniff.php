<?php

/**
 * Ensures that eval() is not used to create objects.
 *
 * PHP version 5
 *
 * @package Logics\BuildTools\CodeSniffer\MySource
 */

namespace Logics\BuildTools\CodeSniffer\MySource;

use \Logics\BuildTools\CodeSniffer\File;
use \Logics\BuildTools\CodeSniffer\Sniff;
use \Logics\BuildTools\CodeSniffer\Tokens;

/**
 * Ensures that eval() is not used to create objects.
 *
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2006-2012 Squiz Pty Ltd (ABN 77 084 670 600)
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 * @version   SVN: $Date: 2016-08-16 23:39:01 +0900 (Tue, 16 Aug 2016) $ $Revision: 26 $
 * @link      $HeadURL: https://open.logics.net.au/buildtools/codesniffer/tags/0.1.3/src/CodeSniffer/Standards/MySource/Sniffs/PHP/EvalObjectFactorySniff.php $
 */

class EvalObjectFactorySniff implements Sniff
    {

	/**
	 * Returns an array of tokens this test wants to listen for.
	 *
	 * @return array
	 */

	public function register()
	    {
		return array(T_EVAL);
	    } //end register()


	/**
	 * Processes this sniff, when one of its tokens is encountered.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $stackPtr  The position of the current token in the stack passed in $tokens.
	 *
	 * @return void
	 *
	 * @internalconst T_OPEN_PARENTHESIS T_OPEN_PARENTHESIS token
	 * @internalconst T_SEMICOLON        T_SEMICOLON token
	 */

	public function process(File $phpcsFile, $stackPtr)
	    {
		$tokens = &$phpcsFile->tokens;

		// We need to find all strings that will be in the eval to determine if the "new" keyword is being used.
		$openBracket  = $phpcsFile->findNext(T_OPEN_PARENTHESIS, ($stackPtr + 1));
		$closeBracket = $tokens[$openBracket]["parenthesis_closer"];

		$strings = array();
		$vars    = array();

		for ($i = ($openBracket + 1); $i < $closeBracket; $i++)
		    {
			if (in_array($tokens[$i]["code"], Tokens::$stringTokens) === true)
			    {
				$strings[$i] = $tokens[$i]["content"];
			    }
			else if ($tokens[$i]["code"] === T_VARIABLE)
			    {
				$vars[$i] = $tokens[$i]["content"];
			    }
		    }

		// We now have some variables that we need to expand into the strings that were assigned to them, if any.
		foreach ($vars as $varPtr => $varName)
		    {
			$prev = $phpcsFile->findPrevious(T_VARIABLE, ($varPtr - 1));
			while ($prev !== false)
			    {
				// Make sure this is an assignment of the variable. That means
				// it will be the first thing on the line.
				$prevContent = $phpcsFile->findPrevious(T_WHITESPACE, ($prev - 1), null, true);
				if ($tokens[$prevContent]["line"] === $tokens[$prev]["line"] || $tokens[$prev]["content"] !== $varName)
				    {
					// This variable has a different name.
					$varPtr = $prevContent;
					$prev   = $phpcsFile->findPrevious(T_VARIABLE, ($varPtr - 1));
				    }
				else
				    {
					// We found one.
					break;
				    }
			    } //end while

			if ($prev !== false)
			    {
				// Find all strings on the line.
				$lineEnd = $phpcsFile->findNext(T_SEMICOLON, ($prev + 1));
				for ($i = ($prev + 1); $i < $lineEnd; $i++)
				    {
					if (in_array($tokens[$i]["code"], Tokens::$stringTokens) === true)
					    {
						$strings[$i] = $tokens[$i]["content"];
					    }
				    }
			    }
		    } //end foreach

		$this->_report($phpcsFile, $stackPtr, $strings);
	    } //end process()


	/**
	 * Report if there "new" statement in evaluated strings
	 *
	 * @param File  $phpcsFile The file being scanned.
	 * @param int   $stackPtr  The position of the current token in the stack passed in $tokens.
	 * @param array $strings   Evaluated strings
	 *
	 * @return void
	 *
	 * @untranslatable new
	 * @untranslatable Found
	 */

	private function _report(File &$phpcsFile, $stackPtr, array $strings)
	    {
		foreach ($strings as $string)
		    {
			// If the string has "new" in it, it is not allowed.
			// We don't bother checking if the word "new" is echo'd
			// because that is unlikely to happen. We assume the use
			// of "new" is for object instantiation.
			if (strstr($string, " new ") !== false)
			    {
				$error = _("Do not use eval() to create objects dynamically; use reflection instead");
				$phpcsFile->addWarning($error, $stackPtr, "Found");
			    }
		    }
	    } //end _report()


    } //end class

?>