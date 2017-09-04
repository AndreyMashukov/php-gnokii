<?php

/**
 * PHP version 5
 *
 * @package Logics\BuildTools\CodeSniffer\Squiz
 */

namespace Logics\BuildTools\CodeSniffer\Squiz;

use \Logics\BuildTools\CodeSniffer\File;
use \Logics\BuildTools\CodeSniffer\Sniff;
use \Logics\BuildTools\CodeSniffer\Tokens;

/**
 * Squiz_Sniffs_PHP_DisallowMultipleAssignmentsSniff.
 *
 * Ensures that there is only one value assignment on a line, and that it is
 * the first thing on the line.
 *
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @author    Marc McIntyre <mmcintyre@squiz.net>
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2006-2012 Squiz Pty Ltd (ABN 77 084 670 600)
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 * @version   SVN: $Date: 2016-08-16 23:39:01 +0900 (Tue, 16 Aug 2016) $ $Revision: 26 $
 * @link      $HeadURL: https://open.logics.net.au/buildtools/codesniffer/tags/0.1.3/src/CodeSniffer/Standards/Squiz/Sniffs/PHP/DisallowMultipleAssignmentsSniff.php $
 */

class DisallowMultipleAssignmentsSniff implements Sniff
    {

	/**
	 * Returns an array of tokens this test wants to listen for.
	 *
	 * @return array
	 *
	 * @internalconst T_EQUAL T_EQUAL token
	 */

	public function register()
	    {
		return array(T_EQUAL);
	    } //end register()


	/**
	 * Processes this test, when one of its tokens is encountered.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $stackPtr  The position of the current token in the stack passed in $tokens.
	 *
	 * @return void
	 *
	 * @internalconst T_CLOSURE   T_CLOSURE token
	 * @internalconst T_SEMICOLON T_SEMICOLON token
	 */

	public function process(File $phpcsFile, $stackPtr)
	    {
		$tokens = &$phpcsFile->tokens;

		// Ignore default value assignments in function definitions.
		$function = $phpcsFile->findPrevious(array(T_FUNCTION, T_CLOSURE), ($stackPtr - 1));
		if ($function === false ||
		    ($function !== false && ($tokens[$function]["parenthesis_opener"] >= $stackPtr || $stackPtr >= $tokens[$function]["parenthesis_closer"])))
		    {
			/*
			    The general rule is:
			    Find an equal sign and go backwards along the line. If you hit an
			    end bracket, skip to the opening bracket. When you find a variable,
			    stop. That variable must be the first non-empty token on the line
			    or in the statement. If not, throw an error.
			*/

			for ($varToken = ($stackPtr - 1); $varToken >= 0; $varToken--)
			    {
				// Skip brackets.
				if (isset($tokens[$varToken]["parenthesis_opener"]) === true && $tokens[$varToken]["parenthesis_opener"] < $varToken)
				    {
					$varToken = $tokens[$varToken]["parenthesis_opener"];
				    }
				else if (isset($tokens[$varToken]["bracket_opener"]) === true)
				    {
					$varToken = $tokens[$varToken]["bracket_opener"];
				    }
				else if ($tokens[$varToken]["code"] === T_SEMICOLON)
				    {
					// We've reached the next statement, so we
					// didn't find a variable.
					$varToken = 0;
					break;
				    }
				else if ($tokens[$varToken]["code"] === T_VARIABLE)
				    {
					// We found our variable.
					break;
				    }
			    } //end for

			// Didn't find a variable.
			if ($varToken > 0)
			    {
				$this->_checkVariable($phpcsFile, $stackPtr, $tokens, $varToken);
			    }
		    } //end if
	    } //end process()


	/**
	 * Processes this test, when one of its tokens is encountered.
	 *
	 * @param File  $phpcsFile The file being scanned.
	 * @param int   $stackPtr  The position of the current token in the stack passed in $tokens.
	 * @param array $tokens    All tokens
	 * @param int   $varToken  Variable token position
	 *
	 * @return void
	 *
	 * @internalconst T_SEMICOLON        T_SEMICOLON token
	 * @internalconst T_INLINE_THEN      T_INLINE_THEN token
	 * @internalconst T_INLINE_ELSE      T_INLINE_ELSE token
	 * @internalconst T_OPEN_PARENTHESIS T_OPEN_PARENTHESIS token
	 *
	 * @untranslatable Found
	 */

	private function _checkVariable(File &$phpcsFile, $stackPtr, array &$tokens, $varToken)
	    {
		// Deal with this type of variable: self::$var by setting the var
		// token to be "self" rather than "$var".
		$varToken -= ($tokens[($varToken - 1)]["code"] === T_DOUBLE_COLON) ? 2 : 0;

		// Deal with this type of variable: $obj->$var by setting the var
		// token to be "$obj" rather than "$var".
		$varToken -= ($tokens[($varToken - 1)]["code"] === T_OBJECT_OPERATOR) ? 2 : 0;

		// Deal with this type of variable: $$var by setting the var
		// token to be "$" rather than "$var".
		$varToken -= ($tokens[($varToken - 1)]["content"] === "$") ? 1 : 0;

		// Ignore member var definitions.
		$prev = $phpcsFile->findPrevious(T_WHITESPACE, ($varToken - 1), null, true);
		if (in_array($tokens[$prev]["code"], Tokens::$scopeModifiers) === false && $tokens[$prev]["code"] !== T_STATIC)
		    {
			// Make sure this variable is the first thing in the statement.
			$varLine  = $tokens[$varToken]["line"];
			$prevLine = 0;
			for ($i = ($varToken - 1); $i >= 0; $i--)
			    {
				// We reached the end of the statement.
				// We reached the end of the inline THEN statement.
				// We reached the end of the inline ELSE statement.
				if ($tokens[$i]["code"] === T_SEMICOLON || $tokens[$i]["code"] === T_INLINE_THEN || $tokens[$i]["code"] === T_INLINE_ELSE)
				    {
					$prevLine = 0;
					$varLine  = 1;
					break;
				    }

				if (in_array($tokens[$i]["code"], Tokens::$emptyTokens) === false)
				    {
					$prevLine = $tokens[$i]["line"];
					break;
				    }
			    } //end for

			// Ignore the first part of FOR loops as we are allowed to
			// assign variables there even though the variable is not the
			// first thing on the line. Also ignore WHILE loops.
			if ($tokens[$i]["code"] === T_OPEN_PARENTHESIS && isset($tokens[$i]["parenthesis_owner"]) === true)
			    {
				$owner = $tokens[$i]["parenthesis_owner"];
				if ($tokens[$owner]["code"] === T_FOR || $tokens[$owner]["code"] === T_WHILE)
				    {
					$prevLine = 0;
					$varLine  = 1;
				    }
			    }

			if ($prevLine === $varLine)
			    {
				$error = _("Assignments must be the first block of code on a line");
				$phpcsFile->addError($error, $stackPtr, "Found");
			    }
		    } //end if
	    } //end _checkVariable()


    } //end class

?>