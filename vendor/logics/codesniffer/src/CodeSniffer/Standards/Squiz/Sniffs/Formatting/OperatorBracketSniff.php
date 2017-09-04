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
 * Squiz_Sniffs_Formatting_OperationBracketSniff.
 *
 * Tests that all arithmetic operations are bracketed.
 *
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @author    Marc McIntyre <mmcintyre@squiz.net>
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2006-2012 Squiz Pty Ltd (ABN 77 084 670 600)
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 * @version   SVN: $Date: 2016-08-16 23:39:01 +0900 (Tue, 16 Aug 2016) $ $Revision: 26 $
 * @link      $HeadURL: https://open.logics.net.au/buildtools/codesniffer/tags/0.1.3/src/CodeSniffer/Standards/Squiz/Sniffs/Formatting/OperatorBracketSniff.php $
 *
 * @untranslatable PHP
 * @untranslatable JS
 */

class OperatorBracketSniff implements Sniff
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
	 */

	public function register()
	    {
		return Tokens::$operators;
	    } //end register()


	/**
	 * Processes this test, when one of its tokens is encountered.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $stackPtr  The position of the current token in the stack passed in $tokens.
	 *
	 * @return void
	 *
	 * @internalconst T_PLUS        T_PLUS token
	 * @internalconst T_BITWISE_AND T_BITWISE_AND token
	 *
	 * @untranslatable JS
	 */

	public function process(File $phpcsFile, $stackPtr)
	    {
		$tokens = &$phpcsFile->tokens;

		// JavaScript uses the plus operator for string concatenation as well
		// so we cannot accurately determine if it is a string concat or addition.
		// So just ignore it.
		if ($phpcsFile->tokenizerType !== "JS" || $tokens[$stackPtr]["code"] !== T_PLUS)
		    {
			// If the & is a reference, then we don't want to check for brackets.
			if ($tokens[$stackPtr]["code"] !== T_BITWISE_AND || $phpcsFile->isReference($stackPtr) === false)
			    {
				if ($this->_checkMinus($phpcsFile, $stackPtr, $tokens) === false)
				    {
					$lastBracket = $this->_findLastBracket($phpcsFile, $stackPtr, $tokens);

					$this->_checkBrackets($phpcsFile, $stackPtr, $tokens, $lastBracket);
				    }
			    }
		    }
	    } //end process()


	/**
	 * There is one instance where brackets aren't needed, which involves
	 * the minus sign being used to assign a negative number to a variable.
	 *
	 * @param File  $phpcsFile The file being scanned.
	 * @param int   $stackPtr  The position of the current token in the stack passed in $tokens.
	 * @param array $tokens    All tokens
	 *
	 * @return boolean True if minus encountered
	 *
	 * @internalconst T_MINUS T_MINUS token
	 *
	 * @untranslatable SpacingAfterMinus
	 */

	private function _checkMinus(File &$phpcsFile, $stackPtr, array &$tokens)
	    {
		$minus = false;

		if ($tokens[$stackPtr]["code"] === T_MINUS)
		    {
			// Check to see if we are trying to return -n.
			$prev = $phpcsFile->findPrevious(Tokens::$emptyTokens, ($stackPtr - 1), null, true);
			if ($tokens[$prev]["code"] === T_RETURN)
			    {
				$minus = true;
			    }
			else
			    {
				$number   = $phpcsFile->findNext(T_WHITESPACE, ($stackPtr + 1), null, true);
				$previous = $phpcsFile->findPrevious(T_WHITESPACE, ($stackPtr - 1), null, true);
				if (($tokens[$number]["code"] === T_LNUMBER || $tokens[$number]["code"] === T_DNUMBER) && $previous !== false)
				    {
					$isAssignment = in_array($tokens[$previous]["code"], Tokens::$assignmentTokens);
					$isEquality   = in_array($tokens[$previous]["code"], Tokens::$equalityTokens);
					$isComparison = in_array($tokens[$previous]["code"], Tokens::$comparisonTokens);
					if ($isAssignment === true || $isEquality === true || $isComparison === true)
					    {
						// This is a negative assignment or comparison.
						// We need to check that the minus and the number are
						// adjacent.
						if (($number - $stackPtr) !== 1)
						    {
							$error = _("No space allowed between minus sign and number");
							$phpcsFile->addError($error, $stackPtr, "SpacingAfterMinus");
						    }

						$minus = true;
					    }
				    } //end if
			    } //end if
		    } //end if

		return $minus;
	    } //end _checkMinus()


	/**
	 * Find last bracket
	 *
	 * @param File  $phpcsFile The file being scanned.
	 * @param int   $stackPtr  The position of the current token in the stack passed in $tokens.
	 * @param array $tokens    All tokens
	 *
	 * @return int Last bracket position
	 *
	 * @internalconst T_THIS                 T_THIS token
	 * @internalconst T_OPEN_SQUARE_BRACKET  T_OPEN_SQUARE_BRACKET token
	 * @internalconst T_CLOSE_SQUARE_BRACKET T_CLOSE_SQUARE_BRACKET token
	 * @internalconst T_MODULUS              T_MODULUS token
	 * @internalconst T_OPEN_PARENTHESIS     T_OPEN_PARENTHESIS token
	 */

	private function _findLastBracket(File &$phpcsFile, $stackPtr, array &$tokens)
	    {
		$lastBracket = false;
		if (isset($tokens[$stackPtr]["nested_parenthesis"]) === true)
		    {
			$parenthesis = array_reverse($tokens[$stackPtr]["nested_parenthesis"], true);
			foreach ($parenthesis as $bracket => $endBracket)
			    {
				$prevToken = $phpcsFile->findPrevious(T_WHITESPACE, ($bracket - 1), null, true);
				$prevCode  = $tokens[$prevToken]["code"];

				if ($prevCode === T_ISSET)
				    {
					// This operation is inside an isset() call, but has
					// no bracket of it's own.
					break;
				    }

				if ($prevCode === T_STRING || $prevCode === T_SWITCH)
				    {
					// We allow very simple operations to not be bracketed. For example, ceil($one / $two).
					$allowed = array(
						    T_VARIABLE,
						    T_LNUMBER,
						    T_DNUMBER,
						    T_STRING,
						    T_WHITESPACE,
						    T_THIS,
						    T_OBJECT_OPERATOR,
						    T_OPEN_SQUARE_BRACKET,
						    T_CLOSE_SQUARE_BRACKET,
						    T_MODULUS,
						   );

					$prev = $this->_findPreviousBracket($tokens, $stackPtr, $bracket, $allowed);

					if ($prev !== $bracket)
					    {
						break;
					    }

					$next = $this->_findNextBracket($tokens, $stackPtr, $endBracket, $allowed);

					if ($next !== $endBracket)
					    {
						break;
					    }
				    } //end if

				// This operation is inside a control structure like FOREACH
				// or IF, but has no bracket of it's own.
				// The only control structure allowed to do this is SWITCH.
				if (in_array($prevCode, Tokens::$scopeOpeners) === true && $prevCode !== T_SWITCH)
				    {
					break;
				    }

				// These are two open parenthesis in a row. If the current
				// one doesn't enclose the operator, go to the previous one.
				if ($prevCode !== T_OPEN_PARENTHESIS || $endBracket >= $stackPtr)
				    {
					$lastBracket = $bracket;
					break;
				    }
			    } //end foreach
		    } //end if

		return $lastBracket;
	    } //end _findLastBracket()


	/**
	 * Find previous bracket
	 *
	 * @param array $tokens   All tokens
	 * @param int   $stackPtr The position of the current token in the stack passed in $tokens.
	 * @param int   $bracket  Current bracket
	 * @param array $allowed  List of operations not to be bracketed
	 *
	 * @return int Previous bracket position
	 *
	 * @internalconst T_CLOSE_PARENTHESIS T_CLOSE_PARENTHESIS token
	 */

	private function _findPreviousBracket(array &$tokens, $stackPtr, $bracket, array $allowed)
	    {
		for ($prev = ($stackPtr - 1); $prev > $bracket; $prev--)
		    {
			if (in_array($tokens[$prev]["code"], $allowed) === false)
			    {
				if ($tokens[$prev]["code"] === T_CLOSE_PARENTHESIS)
				    {
					$prev = $tokens[$prev]["parenthesis_opener"];
				    }
				else
				    {
					break;
				    }
			    }
		    }

		return $prev;
	    } //end _findPreviousBracket()


	/**
	 * Find next bracket
	 *
	 * @param array $tokens     All tokens
	 * @param int   $stackPtr   The position of the current token in the stack passed in $tokens.
	 * @param int   $endBracket End bracket
	 * @param array $allowed    List of operations not to be bracketed
	 *
	 * @return int Next bracket position
	 *
	 * @internalconst T_OPEN_PARENTHESIS T_OPEN_PARENTHESIS token
	 */

	private function _findNextBracket(array &$tokens, $stackPtr, $endBracket, array $allowed)
	    {
		for ($next = ($stackPtr + 1); $next < $endBracket; $next++)
		    {
			if (in_array($tokens[$next]["code"], $allowed) === false)
			    {
				if ($tokens[$next]["code"] === T_OPEN_PARENTHESIS)
				    {
					$next = $tokens[$next]["parenthesis_closer"];
				    }
				else
				    {
					break;
				    }
			    }
		    }

		return $next;
	    } //end _findNextBracket()


	/**
	 * Check brackets
	 *
	 * @param File  $phpcsFile   The file being scanned.
	 * @param int   $stackPtr    The position of the current token in the stack passed in $tokens.
	 * @param array $tokens      All tokens
	 * @param int   $lastBracket Last bracket position
	 *
	 * @return void
	 *
	 * @internalconst T_COMMA                T_COMMA token
	 * @internalconst T_COLON                T_COLON token
	 * @internalconst T_OPEN_PARENTHESIS     T_OPEN_PARENTHESIS token
	 * @internalconst T_OPEN_SQUARE_BRACKET  T_OPEN_SQUARE_BRACKET token
	 * @internalconst T_CLOSE_SQUARE_BRACKET T_CLOSE_SQUARE_BRACKET token
	 *
	 * @untranslatable MissingBrackets
	 */

	private function _checkBrackets(File &$phpcsFile, $stackPtr, array &$tokens, $lastBracket)
	    {
		if ($lastBracket === false)
		    {
			// It is not in a bracketed statement at all.
			$previousToken = $phpcsFile->findPrevious(T_WHITESPACE, ($stackPtr - 1), null, true, null, true);
			if ($previousToken !== false)
			    {
				// A list of tokens that indicate that the token is not
				// part of an arithmetic operation.
				$invalidTokens = array(
						  T_COMMA,
						  T_COLON,
						  T_OPEN_PARENTHESIS,
						  T_OPEN_SQUARE_BRACKET,
						  T_CASE,
						 );

				if (in_array($tokens[$previousToken]["code"], $invalidTokens) === false)
				    {
					$error = _("Arithmetic operation must be bracketed");
					$phpcsFile->addError($error, $stackPtr, "MissingBrackets");
				    }
			    }
			else
			    {
				$lastAssignment = $phpcsFile->findPrevious(Tokens::$assignmentTokens, $stackPtr, null, false, null, true);
				if ($lastAssignment !== false && $lastAssignment > $lastBracket)
				    {
					$error = _("Arithmetic operation must be bracketed");
					$phpcsFile->addError($error, $stackPtr, "MissingBrackets");
				    }
			    } //end if
		    }
		else if ($tokens[$lastBracket]["parenthesis_closer"] < $stackPtr)
		    {
			// There are a set of brackets in front of it that don't include it.
			$error = _("Arithmetic operation must be bracketed");
			$phpcsFile->addError($error, $stackPtr, "MissingBrackets");
		    }
		else
		    {
			// We are enclosed in a set of bracket, so the last thing to check is that
			// we are not also enclosed in square brackets like this: ($array[$index + 1]), which is invalid.
			$brackets = array(
				     T_OPEN_SQUARE_BRACKET,
				     T_CLOSE_SQUARE_BRACKET,
				    );

			$squareBracket = $phpcsFile->findPrevious($brackets, ($stackPtr - 1), $lastBracket);
			if ($squareBracket !== false && $tokens[$squareBracket]["code"] === T_OPEN_SQUARE_BRACKET)
			    {
				$closeSquareBracket = $phpcsFile->findNext($brackets, ($stackPtr + 1));
				if ($closeSquareBracket !== false && $tokens[$closeSquareBracket]["code"] === T_CLOSE_SQUARE_BRACKET)
				    {
					$error = _("Arithmetic operation must be bracketed");
					$phpcsFile->addError($error, $stackPtr, "MissingBrackets");
				    }
			    }
		    } //end if
	    } //end _checkBrackets()


    } //end class

?>
