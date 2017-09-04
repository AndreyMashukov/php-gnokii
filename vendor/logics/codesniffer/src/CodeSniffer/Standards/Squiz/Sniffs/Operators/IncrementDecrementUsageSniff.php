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
 * Squiz_Sniffs_Operators_IncrementDecrementUsageSniff.
 *
 * Tests that the ++ operators are used when possible and not
 * used when it makes the code confusing.
 *
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @author    Marc McIntyre <mmcintyre@squiz.net>
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2006-2012 Squiz Pty Ltd (ABN 77 084 670 600)
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 * @version   SVN: $Date: 2016-08-16 23:39:01 +0900 (Tue, 16 Aug 2016) $ $Revision: 26 $
 * @link      $HeadURL: https://open.logics.net.au/buildtools/codesniffer/tags/0.1.3/src/CodeSniffer/Standards/Squiz/Sniffs/Operators/IncrementDecrementUsageSniff.php $
 */

class IncrementDecrementUsageSniff implements Sniff
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
		return array(
			T_EQUAL,
			T_PLUS_EQUAL,
			T_MINUS_EQUAL,
			T_INC,
			T_DEC,
		       );
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

		if ($tokens[$stackPtr]["code"] === T_INC || $tokens[$stackPtr]["code"] === T_DEC)
		    {
			$this->processIncDec($phpcsFile, $stackPtr);
		    }
		else
		    {
			$this->processAssignment($phpcsFile, $stackPtr);
		    }
	    } //end process()


	/**
	 * Checks to ensure increment and decrement operators are not confusing.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $stackPtr  The position of the current token in the stack passed in $tokens.
	 *
	 * @return void
	 *
	 * @internalconst T_STRING_CONCAT T_STRING_CONCAT token
	 *
	 * @untranslatable NotAllowed
	 * @untranslatable NoBrackets
	 */

	protected function processIncDec(File &$phpcsFile, $stackPtr)
	    {
		$tokens = &$phpcsFile->tokens;

		// Work out where the variable is so we know where to
		// start looking for other operators.
		if ($tokens[($stackPtr - 1)]["code"] === T_VARIABLE)
		    {
			$start = ($stackPtr + 1);
		    }
		else
		    {
			$start = ($stackPtr + 2);
		    }

		$next = $phpcsFile->findNext(Tokens::$emptyTokens, $start, null, true);
		if ($next !== false)
		    {
			if (in_array($tokens[$next]["code"], Tokens::$arithmeticTokens) === true)
			    {
				$error = _("Increment and decrement operators cannot be used in an arithmetic operation");
				$phpcsFile->addError($error, $stackPtr, "NotAllowed");
			    }
			else
			    {
				$prev = $phpcsFile->findPrevious(Tokens::$emptyTokens, ($start - 3), null, true);
				if ($prev !== false)
				    {
					// Check if this is in a string concat.
					if ($tokens[$next]["code"] === T_STRING_CONCAT || $tokens[$prev]["code"] === T_STRING_CONCAT)
					    {
						$error = _("Increment and decrement operators must be bracketed when used in string concatenation");
						$phpcsFile->addError($error, $stackPtr, "NoBrackets");
					    }
				    }
			    }
		    }
	    } //end processIncDec()


	/**
	 * Checks to ensure increment and decrement operators are used.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $stackPtr  The position of the current token in the stack passed in $tokens.
	 *
	 * @return void
	 *
	 * @internalconst T_SEMICOLON            T_SEMICOLON token
	 * @internalconst T_CLOSE_PARENTHESIS    T_CLOSE_PARENTHESIS token
	 * @internalconst T_CLOSE_SQUARE_BRACKET T_CLOSE_SQUARE_BRACKET token
	 * @internalconst T_CLOSE_CURLY_BRACKET  T_CLOSE_CURLY_BRACKET token
	 * @internalconst T_OPEN_PARENTHESIS     T_OPEN_PARENTHESIS token
	 * @internalconst T_EQUAL                T_EQUAL token
	 * @internalconst T_PLUS                 T_PLUS token
	 * @internalconst T_MINUS                T_MINUS token
	 */

	protected function processAssignment(File &$phpcsFile, $stackPtr)
	    {
		$tokens = &$phpcsFile->tokens;

		$assignedVar = $phpcsFile->findPrevious(T_WHITESPACE, ($stackPtr - 1), null, true);
		// Not an assignment, return.
		if ($tokens[$assignedVar]["code"] === T_VARIABLE)
		    {
			$statementEnd = $phpcsFile->findNext(array(T_SEMICOLON, T_CLOSE_PARENTHESIS, T_CLOSE_SQUARE_BRACKET, T_CLOSE_CURLY_BRACKET), $stackPtr);

			// If there is anything other than variables, numbers, spaces or operators we need to return.
			$noiseTokens = $phpcsFile->findNext(array(T_LNUMBER, T_VARIABLE, T_WHITESPACE, T_PLUS, T_MINUS, T_OPEN_PARENTHESIS),
							    ($stackPtr + 1), $statementEnd, true);

			if ($noiseTokens === false)
			    {
				// We have only one variable, and it's the same as what is being assigned,
				// so we need to check what is being added or subtracted.
				$nextNumber     = $phpcsFile->findNext(array(T_LNUMBER), ($stackPtr + 2), $statementEnd, false);
				$previousNumber = ($nextNumber !== false) ? $nextNumber : false;
				$nextNumber     = ($nextNumber !== false) ? $phpcsFile->findNext(array(T_LNUMBER), ($nextNumber + 1), $statementEnd, false) : false;

				if ($previousNumber !== false && $nextNumber === false && $tokens[$previousNumber]["content"] === "1")
				    {
					$operator = false;

					if ($tokens[$stackPtr]["code"] === T_EQUAL)
					    {
						$nextVar = $phpcsFile->findNext(T_VARIABLE, ($stackPtr + 2), $statementEnd);
						$var     = ($nextVar !== false) ? $nextVar : false;
						$nextVar = ($nextVar !== false) ? $phpcsFile->findNext(T_VARIABLE, ($nextVar + 1), $statementEnd) : false;

						if ($var !== false && $nextVar === false && $tokens[$var]["content"] === $tokens[$assignedVar]["content"])
						    {
							$opToken = $phpcsFile->findNext(array(T_PLUS, T_MINUS), ($var + 1), $statementEnd);
							// Operator was before the variable, like: $var = 1 + $var; So we ignore it.
							$operator = ($opToken !== false) ? $tokens[$opToken]["content"] : false;
						    }
					    }
					else
					    {
						// If we are already using += or -=, we need to ignore
						// the statement if a variable is being used.
						$var = $phpcsFile->findNext(T_VARIABLE, ($stackPtr + 1), $statementEnd);
						if ($var === false)
						    {
							$operator = substr($tokens[$stackPtr]["content"], 0, 1);

							// If we are adding or subtracting negative value, the operator
							// needs to be reversed.
							$negative = $phpcsFile->findPrevious(T_MINUS, ($previousNumber - 1), $stackPtr);
							$operator = ($negative !== false) ? (($operator === "+") ? "-" : "+") : $operator;
						    }
					    } //end if

					if ($operator !== false)
					    {
						$expected = $tokens[$assignedVar]["content"] . $operator . $operator;
						$found    = $phpcsFile->getTokensAsString($assignedVar, ($statementEnd - $assignedVar + 1));

						$error  = ($operator === "+") ? _("Increment") : _("Decrement");
						$error .= " " . _("operators should be used where possible; found") . " \"" . $found .
							  "\" " . _("but expected") . " \"" . $expected . "\"";
						$phpcsFile->addError($error, $stackPtr);
					    }
				    } //end if
			    } //end if
		    } //end if
	    } //end processAssignment()


    } //end class

?>
