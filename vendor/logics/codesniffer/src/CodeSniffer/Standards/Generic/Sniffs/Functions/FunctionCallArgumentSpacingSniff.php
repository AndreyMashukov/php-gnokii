<?php

/**
 * PHP version 5
 *
 * @package Logics\BuildTools\CodeSniffer\Generic
 */

namespace Logics\BuildTools\CodeSniffer\Generic;

use \Logics\BuildTools\CodeSniffer\File;
use \Logics\BuildTools\CodeSniffer\Sniff;
use \Logics\BuildTools\CodeSniffer\Tokens;

/**
 * FunctionCallArgumentSpacingSniff.
 *
 * Checks that calls to methods and functions are spaced correctly.
 *
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @author    Marc McIntyre <mmcintyre@squiz.net>
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2006-2012 Squiz Pty Ltd (ABN 77 084 670 600)
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 * @version   SVN: $Date: 2016-08-16 23:39:01 +0900 (Tue, 16 Aug 2016) $ $Revision: 26 $
 * @link      $HeadURL: https://open.logics.net.au/buildtools/codesniffer/tags/0.1.3/src/CodeSniffer/Standards/Generic/Sniffs/Functions/FunctionCallArgumentSpacingSniff.php $
 */

class FunctionCallArgumentSpacingSniff implements Sniff
    {

	/**
	 * Returns an array of tokens this test wants to listen for.
	 *
	 * @return array
	 */

	public function register()
	    {
		return array(T_STRING);
	    } //end register()


	/**
	 * Processes this test, when one of its tokens is encountered.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $stackPtr  The position of the current token in the stack passed in $tokens.
	 *
	 * @return void
	 *
	 * @internalconst T_BITWISE_AND      T_BITWISE_AND token
	 * @internalconst T_OPEN_PARENTHESIS T_OPEN_PARENTHESIS token
	 * @internalconst T_COMMA            T_COMMA token
	 * @internalconst T_CLOSURE          T_CLOSURE token
	 */

	public function process(File $phpcsFile, $stackPtr)
	    {
		$tokens = &$phpcsFile->tokens;

		// Skip tokens that are the names of functions or classes
		// within their definitions. For example: function myFunction...
		// "myFunction" is T_STRING but we should skip because it is not a function or method *call*.
		$functionName    = $stackPtr;
		$ignoreTokens    = Tokens::$emptyTokens;
		$ignoreTokens[]  = T_BITWISE_AND;
		$functionKeyword = $phpcsFile->findPrevious($ignoreTokens, ($stackPtr - 1), null, true);

		// If the next non-whitespace token after the function or method call
		// is not an opening parenthesis then it cant really be a *call*.
		$openBracket = $phpcsFile->findNext(Tokens::$emptyTokens, ($functionName + 1), null, true);

		if ($tokens[$functionKeyword]["code"] !== T_FUNCTION && $tokens[$functionKeyword]["code"] !== T_CLASS &&
		    $tokens[$openBracket]["code"] === T_OPEN_PARENTHESIS)
		    {
			$closeBracket = $tokens[$openBracket]["parenthesis_closer"];

			$nextSeparator = $phpcsFile->findNext(array(T_COMMA, T_VARIABLE, T_CLOSURE), ($openBracket + 1), $closeBracket);
			while ($nextSeparator !== false)
			    {
				if ($tokens[$nextSeparator]["code"] === T_CLOSURE)
				    {
					$nextSeparator = $tokens[$nextSeparator]["scope_closer"];
				    }
				else
				    {
					// Make sure the comma or variable belongs directly to this function call,
					// and is not inside a nested function call or array.
					$brackets    = $tokens[$nextSeparator]["nested_parenthesis"];
					$lastBracket = array_pop($brackets);
					if ($lastBracket === $closeBracket)
					    {
						$this->_processNext($phpcsFile, $tokens, $nextSeparator, $closeBracket, $stackPtr);
					    } //end if
				    } //end if

				$nextSeparator = $phpcsFile->findNext(array(T_COMMA, T_VARIABLE, T_CLOSURE), ($nextSeparator + 1), $closeBracket);
			    } //end while
		    } //end if
	    } //end process()


	/**
	 * Processes next token
	 *
	 * @param File  $phpcsFile     The file being scanned.
	 * @param array $tokens        All tokens
	 * @param int   $nextSeparator Next separator position
	 * @param int   $closeBracket  Close bracket position
	 * @param int   $stackPtr      Current token position
	 *
	 * @return void
	 *
	 * @internalconst T_COMMA T_COMMA token
	 * @internalconst T_EQUAL T_EQUAL token
	 *
	 * @untranslatable SpaceBeforeComma
	 * @untranslatable NoSpaceAfterComma
	 * @untranslatable %s
	 * @untranslatable TooMuchSpaceAfterComma
	 * @untranslatable NoSpaceBeforeEquals
	 * @untranslatable NoSpaceAfterEquals
	 */

	private function _processNext(File &$phpcsFile, array &$tokens, $nextSeparator, $closeBracket, $stackPtr)
	    {
		if ($tokens[$nextSeparator]["code"] === T_COMMA)
		    {
			if ($tokens[($nextSeparator - 1)]["code"] === T_WHITESPACE)
			    {
				$phpcsFile->addError(_("Space found before comma in function call"), $stackPtr, "SpaceBeforeComma");
			    }

			if ($tokens[($nextSeparator + 1)]["code"] !== T_WHITESPACE)
			    {
				$phpcsFile->addError(_("No space found after comma in function call"), $stackPtr, "NoSpaceAfterComma");
			    }
			else
			    {
				// If there is a newline in the space, then the must be formatting
				// each argument on a newline, which is valid, so ignore it.
				if (strpos($tokens[($nextSeparator + 1)]["content"], $phpcsFile->eolChar) === false)
				    {
					$space = strlen($tokens[($nextSeparator + 1)]["content"]);
					if ($space > 1)
					    {
						$data = array($space);
						$phpcsFile->addError(
						    _("Expected 1 space after comma in function call;") . " %s " . _("found"), $stackPtr, "TooMuchSpaceAfterComma", $data
						);
					    }
				    }
			    }
		    }
		else
		    {
			// Token is a variable.
			$nextToken = $phpcsFile->findNext(Tokens::$emptyTokens, ($nextSeparator + 1), $closeBracket, true);
			if ($nextToken !== false)
			    {
				if ($tokens[$nextToken]["code"] === T_EQUAL)
				    {
					if (($tokens[($nextToken - 1)]["code"]) !== T_WHITESPACE)
					    {
						$phpcsFile->addError(_("Expected 1 space before = sign of default value"), $stackPtr, "NoSpaceBeforeEquals");
					    }

					if ($tokens[($nextToken + 1)]["code"] !== T_WHITESPACE)
					    {
						$phpcsFile->addError(_("Expected 1 space after = sign of default value"), $stackPtr, "NoSpaceAfterEquals");
					    }
				    }
			    }
		    } //end if
	    } //end _processNext()


    } //end class

?>
