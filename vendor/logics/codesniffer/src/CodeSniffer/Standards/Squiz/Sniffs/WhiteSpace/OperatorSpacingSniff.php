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
 * Sniffs_Squiz_WhiteSpace_OperatorSpacingSniff.
 *
 * Verifies that operators have valid spacing surrounding them.
 *
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @author    Marc McIntyre <mmcintyre@squiz.net>
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2006-2012 Squiz Pty Ltd (ABN 77 084 670 600)
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 * @version   SVN: $Date: 2016-08-16 23:39:01 +0900 (Tue, 16 Aug 2016) $ $Revision: 26 $
 * @link      $HeadURL: https://open.logics.net.au/buildtools/codesniffer/tags/0.1.3/src/CodeSniffer/Standards/Squiz/Sniffs/WhiteSpace/OperatorSpacingSniff.php $
 *
 * @untranslatable PHP
 * @untranslatable JS
 */

class OperatorSpacingSniff implements Sniff
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
		$comparison = Tokens::$comparisonTokens;
		$operators  = Tokens::$operators;
		$assignment = Tokens::$assignmentTokens;

		return array_unique(array_merge($comparison, $operators, $assignment));
	    } //end register()


	/**
	 * Processes this sniff, when one of its tokens is encountered.
	 *
	 * @param File $phpcsFile The current file being checked.
	 * @param int  $stackPtr  The position of the current token in the stack passed in $tokens.
	 *
	 * @return void
	 *
	 * @internalconst T_MINUS       T_MINUS token
	 * @internalconst T_CLOSURE     T_CLOSURE token
	 * @internalconst T_EQUAL       T_EQUAL token
	 * @internalconst T_BITWISE_AND T_BITWISE_AND token
	 */

	public function process(File $phpcsFile, $stackPtr)
	    {
		$tokens = &$phpcsFile->tokens;

		// Skip default values in function declarations.
		$skip = false;
		if ($tokens[$stackPtr]["code"] === T_EQUAL || $tokens[$stackPtr]["code"] === T_MINUS)
		    {
			if (isset($tokens[$stackPtr]["nested_parenthesis"]) === true)
			    {
				$parenthesis = array_keys($tokens[$stackPtr]["nested_parenthesis"]);
				$bracket     = array_pop($parenthesis);
				if (isset($tokens[$bracket]["parenthesis_owner"]) === true)
				    {
					$function = $tokens[$bracket]["parenthesis_owner"];
					if ($tokens[$function]["code"] === T_FUNCTION || $tokens[$function]["code"] === T_CLOSURE)
					    {
						$skip = true;
					    }
				    }
			    }
		    }

		if ($skip === false)
		    {
			// Skip for "=&" case.
			if ($tokens[$stackPtr]["code"] !== T_EQUAL || isset($tokens[($stackPtr + 1)]) === false || $tokens[($stackPtr + 1)]["code"] !== T_BITWISE_AND)
			    {
				if ($tokens[$stackPtr]["code"] === T_BITWISE_AND)
				    {
					$this->_checkAmpSpacing($phpcsFile, $stackPtr, $tokens);
				    }
				else if ($this->_checkMinus($phpcsFile, $stackPtr, $tokens) === false)
				    {
					$this->_checkSpacing($phpcsFile, $stackPtr, $tokens);
				    } //end if
			    }
		    }
	    } //end process()


	/**
	 * Check spacing around amp sign
	 *
	 * @param File  $phpcsFile The current file being checked.
	 * @param int   $stackPtr  The position of the current token in the stack passed in $tokens.
	 * @param array $tokens    All tokens
	 *
	 * @return void
	 *
	 * @untranslatable NoSpaceBeforeAmp
	 * @untranslatable SpacingBeforeAmp
	 * @untranslatable NoSpaceAfterAmp
	 * @untranslatable SpacingAfterAmp
	 * @untranslatable %s
	 */

	private function _checkAmpSpacing(File &$phpcsFile, $stackPtr, array &$tokens)
	    {
		// If it's not a reference, then we expect one space either side of the
		// bitwise operator.
		if ($phpcsFile->isReference($stackPtr) === false)
		    {
			// Check there is one space before the & operator.
			if ($tokens[($stackPtr - 1)]["code"] !== T_WHITESPACE)
			    {
				$error = _("Expected 1 space before \"&\" operator; 0 found");
				$phpcsFile->addError($error, $stackPtr, "NoSpaceBeforeAmp");
			    }
			else
			    {
				if (strlen($tokens[($stackPtr - 1)]["content"]) !== 1)
				    {
					$found = strlen($tokens[($stackPtr - 1)]["content"]);
					$error = _("Expected 1 space before \"&\" operator;") . " %s " . _("found");
					$data  = array($found);
					$phpcsFile->addError($error, $stackPtr, "SpacingBeforeAmp", $data);
				    }
			    }

			// Check there is one space after the & operator.
			if ($tokens[($stackPtr + 1)]["code"] !== T_WHITESPACE)
			    {
				$error = _("Expected 1 space after \"&\" operator; 0 found");
				$phpcsFile->addError($error, $stackPtr, "NoSpaceAfterAmp");
			    }
			else
			    {
				if (strlen($tokens[($stackPtr + 1)]["content"]) !== 1)
				    {
					$found = strlen($tokens[($stackPtr + 1)]["content"]);
					$error = _("Expected 1 space after \"&\" operator;") . " %s " . _("found");
					$data  = array($found);
					$phpcsFile->addError($error, $stackPtr, "SpacingAfterAmp", $data);
				    }
			    }
		    } //end if
	    } //end _checkAmpSpacing()


	/**
	 * Check that operator is actually minus sign
	 *
	 * @param File  $phpcsFile The current file being checked.
	 * @param int   $stackPtr  The position of the current token in the stack passed in $tokens.
	 * @param array $tokens    All tokens
	 *
	 * @return boolean True is operator is minus sign
	 *
	 * @internalconst T_MINUS               T_MINUS token
	 * @internalconst T_COMMA               T_COMMA token
	 * @internalconst T_OPEN_PARENTHESIS    T_OPEN_PARENTHESIS token
	 * @internalconst T_OPEN_SQUARE_BRACKET T_OPEN_SQUARE_BRACKET token
	 * @internalconst T_COLON               T_COLON token
	 * @internalconst T_INLINE_THEN         T_INLINE_THEN token
	 * @internalconst T_INLINE_ELSE         T_INLINE_ELSE token
	 */

	private function _checkMinus(File &$phpcsFile, $stackPtr, array &$tokens)
	    {
		$minus = false;

		if ($tokens[$stackPtr]["code"] === T_MINUS)
		    {
			// A list of tokens that indicate that the token is not
			// part of an arithmetic operation.
			$invalidTokens = array(
					  T_COMMA,
					  T_OPEN_PARENTHESIS,
					  T_OPEN_SQUARE_BRACKET,
					  T_DOUBLE_ARROW,
					  T_COLON,
					  T_INLINE_THEN,
					  T_INLINE_ELSE,
					  T_CASE,
					 );

			// Check minus spacing, but make sure we aren't just assigning
			// a minus value or returning one.
			$prev = $phpcsFile->findPrevious(T_WHITESPACE, ($stackPtr - 1), null, true);
			if ($tokens[$prev]["code"] === T_RETURN)
			    {
				// Just returning a negative value; eg. (return -1).
				$minus = true;
			    }
			else if (in_array($tokens[$prev]["code"], Tokens::$operators) === true)
			    {
				// Just trying to operate on a negative value; eg. ($var * -1).
				$minus = true;
			    }

			if (in_array($tokens[$prev]["code"], Tokens::$comparisonTokens) === true)
			    {
				// Just trying to compare a negative value; eg. ($var === -1).
				$minus = true;
			    }

			if (in_array($tokens[$prev]["code"], Tokens::$assignmentTokens) === true)
			    {
				// Just trying to assign a negative value; eg. ($var = -1).
				$minus = true;
			    }

			if (in_array($tokens[$prev]["code"], $invalidTokens) === true)
			    {
				// Just trying to use a negative value; eg. myFunction($var, -2).
				$minus = true;
			    }
		    } //end if

		return $minus;
	    } //end _checkMinus()


	/**
	 * Check spacing around operator
	 *
	 * @param File  $phpcsFile The current file being checked.
	 * @param int   $stackPtr  The position of the current token in the stack passed in $tokens.
	 * @param array $tokens    All tokens
	 *
	 * @return void
	 *
	 * @untranslatable NoSpaceBefore
	 * @untranslatable SpacingBefore
	 * @untranslatable NoSpaceAfter
	 * @untranslatable SpacingAfter
	 * @untranslatable \"%s\"; %s
	 */

	private function _checkSpacing(File &$phpcsFile, $stackPtr, array &$tokens)
	    {
		$operator = $tokens[$stackPtr]["content"];

		if ($tokens[($stackPtr - 1)]["code"] !== T_WHITESPACE)
		    {
			$error = _("Expected 1 space before") . " \"" . $operator . "\"; 0 " . _("found");
			$phpcsFile->addError($error, $stackPtr, "NoSpaceBefore");
		    }
		else if (strlen($tokens[($stackPtr - 1)]["content"]) !== 1)
		    {
			// Don't throw an error for assignments, because other standards allow
			// multiple spaces there to align multiple assignments.
			if (in_array($tokens[$stackPtr]["code"], Tokens::$assignmentTokens) === false)
			    {
				$found = strlen($tokens[($stackPtr - 1)]["content"]);
				$error = _("Expected 1 space before") . " \"%s\"; %s " . _("found");
				$data  = array(
					  $operator,
					  $found,
					 );
				$phpcsFile->addError($error, $stackPtr, "SpacingBefore", $data);
			    }
		    }

		if ($tokens[($stackPtr + 1)]["code"] !== T_WHITESPACE)
		    {
			$error = _("Expected 1 space after") . " \"" . $operator . "\"; 0 " . _("found");
			$phpcsFile->addError($error, $stackPtr, "NoSpaceAfter");
		    }
		else if (strlen($tokens[($stackPtr + 1)]["content"]) !== 1)
		    {
			$found = strlen($tokens[($stackPtr + 1)]["content"]);
			$error = _("Expected 1 space after") . " \"%s\"; %s " . _("found");
			$data  = array(
				  $operator,
				  $found,
				 );
			$phpcsFile->addError($error, $stackPtr, "SpacingAfter", $data);
		    }
	    } //end _checkSpacing()


    } //end class

?>
