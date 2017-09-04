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
 * Squiz_Sniffs_Functions_FunctionDeclarationArgumentSpacingSniff.
 *
 * Checks that arguments in function declarations are spaced correctly.
 *
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @author    Marc McIntyre <mmcintyre@squiz.net>
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2006-2012 Squiz Pty Ltd (ABN 77 084 670 600)
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   http://matrix.squiz.net/developer/tools/php_cs/licence BSD Licence
 * @version   SVN: $Date: 2016-08-16 23:39:01 +0900 (Tue, 16 Aug 2016) $ $Revision: 26 $
 * @link      $HeadURL: https://open.logics.net.au/buildtools/codesniffer/tags/0.1.3/src/CodeSniffer/Standards/Beauty/Sniffs/Functions/FunctionDeclarationArgumentSpacingSniff.php $
 */

class FunctionDeclarationArgumentSpacingSniff implements Sniff
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
	 * @internalconst T_COMMA T_COMMA token
	 */

	public function process(File $phpcsFile, $stackPtr)
	    {
		$tokens = &$phpcsFile->tokens;

		$functionName = $phpcsFile->findNext(array(T_STRING), $stackPtr);
		$openBracket  = $tokens[$stackPtr]["parenthesis_opener"];
		$closeBracket = $tokens[$stackPtr]["parenthesis_closer"];

		$multiLine = ($tokens[$openBracket]["line"] !== $tokens[$closeBracket]["line"]);

		$params    = array();
		$nextParam = $phpcsFile->findNext(T_VARIABLE, ($openBracket + 1), $closeBracket);
		while ($nextParam !== false)
		    {
			$nextToken = $phpcsFile->findNext(T_WHITESPACE, ($nextParam + 1), ($closeBracket + 1), true);
			$nextCode  = $tokens[$nextToken]["code"];

			if ($nextCode === T_EQUAL)
			    {
				// Check parameter default spacing.
				if (strlen($tokens[($nextParam + 1)]["content"]) !== 1 || $tokens[($nextParam + 1)]["code"] === T_EQUAL)
				    {
					$gap = ($tokens[($nextParam + 1)]["code"] === T_EQUAL) ? 0 : strlen($tokens[($nextParam + 1)]["content"]);
					$phpcsFile->addError(
					    _("Expected 1 space between argument") . " \"" . $tokens[$nextParam]["content"] . "\" " . _("and equals sign;") . " " .
					    $gap . " " . _("found"), $nextToken
					);
				    }

				if (strlen($tokens[($nextToken + 1)]["content"]) !== 1 || $tokens[($nextToken + 1)]["code"] !== T_WHITESPACE)
				    {
					$gap = ($tokens[($nextToken + 1)]["code"] === T_WHITESPACE) ? strlen($tokens[($nextToken + 1)]["content"]) : 0;
					$phpcsFile->addError(
					    _("Expected 1 space between default value and equals sign for argument") . " \"" . $tokens[$nextParam]["content"] . "\"; " .
					    $gap . " " . _("found"), $nextToken
					);
				    }
			    } //end if

			// Find and check the comma (if there is one).
			$nextComma = $phpcsFile->findNext(T_COMMA, ($nextParam + 1), $closeBracket);
			if ($nextComma !== false && $tokens[($nextComma - 1)]["code"] === T_WHITESPACE)
			    {
				// Comma found.
				$phpcsFile->addError(
				    _("Expected 0 spaces between argument") . " \"" . $tokens[$nextParam]["content"] . "\" " . _("and comma;") . " " .
				    strlen($tokens[($nextComma - 1)]["content"]) . " " . _("found"),
				    $nextToken
				);
			    }

			// Take references into account when expecting the
			// location of whitespace.
			$whitespace = ($phpcsFile->isReference(($nextParam - 1)) === true) ? $tokens[($nextParam - 2)] : $tokens[($nextParam - 1)];

			if (empty($params) === true)
			    {
				$this->_processFirstArgument($phpcsFile, $tokens, $whitespace, $nextParam, $nextToken, $multiLine);
			    }
			else
			    {
				$this->_processNextArgument($phpcsFile, $tokens, $whitespace, $nextParam, $nextToken, $multiLine);
			    }

			$params[] = $nextParam;

			$nextParam = $phpcsFile->findNext(T_VARIABLE, ($nextParam + 1), $closeBracket);
		    } //end while

		if (empty($params) === true && ($closeBracket - $openBracket) !== 1)
		    {
			// There are no parameters for this function.
			$phpcsFile->addError(
			    _("Expected 0 spaces between brackets of function declaration;") . " " . strlen($tokens[($closeBracket - 1)]["content"]) . " " . _("found"),
			    $stackPtr
			);
		    }
		else if (empty($params) === false && $multiLine === false && $tokens[($closeBracket - 1)]["code"] === T_WHITESPACE)
		    {
			$lastParam = array_pop($params);
			$phpcsFile->addError(
			    _("Expected 0 spaces between argument") . " \"" . $tokens[$lastParam]["content"] . "\" " . _("and closing bracket;") . " " .
			    strlen($tokens[($closeBracket - 1)]["content"]) . " " . _("found"),
			    $closeBracket
			);
		    }
	    } //end process()


	/**
	 * Process first argument in function declaration
	 *
	 * @param File  $phpcsFile  The file being scanned.
	 * @param array $tokens     Tokens array
	 * @param array $whitespace Whitespace
	 * @param int   $nextParam  Next parameter
	 * @param int   $nextToken  Next token
	 * @param bool  $multiLine  Function argument list is multiline
	 *
	 * @return void
	 *
	 * @internalconst T_OPEN_PARENTHESIS T_OPEN_PARENTHESIS token
	 */

	private function _processFirstArgument(File &$phpcsFile, array &$tokens, array $whitespace, $nextParam, &$nextToken, $multiLine)
	    {
		// First argument in function declaration.
		if ($whitespace["code"] === T_WHITESPACE)
		    {
			$gap = strlen($whitespace["content"]);
			$arg = $tokens[$nextParam]["content"];

			// Before we throw an error, make sure there is no type hint.
			$bracket   = $phpcsFile->findPrevious(T_OPEN_PARENTHESIS, ($nextParam - 1));
			$nextToken = $phpcsFile->findNext(T_WHITESPACE, ($bracket + 1), null, true);
			if ($phpcsFile->isReference($nextToken) === true)
			    {
				$nextToken++;
			    }

			if ($nextToken !== $nextParam)
			    {
				// There was a type hint, so check the spacing between
				// the hint and the variable as well.
				$hint = $tokens[$nextToken]["content"];

				if ($gap !== 1)
				    {
					$phpcsFile->addError(_("Expected 1 space between type hint and argument") . " \"" . $arg . "\"; " . $gap . " " . _("found"), $nextToken);
				    }

				if ($multiLine === false && $tokens[($bracket + 1)]["code"] === T_WHITESPACE)
				    {
					$phpcsFile->addError(
					    _("Expected 0 spaces between opening bracket and type hint") . " \"" . $hint . "\"; " .
					    strlen($tokens[($bracket + 1)]["content"]) . " " . _("found"),
					    $nextToken
					);
				    }
			    }
			else if ($multiLine === false)
			    {
				$phpcsFile->addError(_("Expected 0 spaces between opening bracket and argument") . " \"" . $arg . "\"; " . $gap . " " . _("found"), $nextToken);
			    } //end if
		    } //end if
	    } //end _processFirstArgument()


	/**
	 * Process rest of arguments in function declaration
	 *
	 * @param File  $phpcsFile  The file being scanned.
	 * @param array $tokens     Tokens array
	 * @param array $whitespace Whitespace
	 * @param int   $nextParam  Next parameter
	 * @param int   $nextToken  Next token
	 * @param bool  $multiLine  Function argument list is multiline
	 *
	 * @return void
	 *
	 * @internalconst T_COMMA T_COMMA token
	 */

	private function _processNextArgument(File &$phpcsFile, array &$tokens, array $whitespace, $nextParam, &$nextToken, $multiLine)
	    {
		// This is not the first argument in the function declaration.
		$arg = $tokens[$nextParam]["content"];

		if ($whitespace["code"] === T_WHITESPACE)
		    {
			$gap = strlen($whitespace["content"]);

			// Before we throw an error, make sure there is no type hint.
			$comma     = $phpcsFile->findPrevious(T_COMMA, ($nextParam - 1));
			$nextToken = $phpcsFile->findNext(T_WHITESPACE, ($comma + 1), null, true);
			if ($phpcsFile->isReference($nextToken) === true)
			    {
				$nextToken++;
			    }

			if ($nextToken !== $nextParam)
			    {
				// There was a type hint, so check the spacing between
				// the hint and the variable as well.
				$hint = $tokens[$nextToken]["content"];

				if ($gap !== 1)
				    {
					$phpcsFile->addError(_("Expected 1 space between type hint and argument") . " \"" . $arg . "\"; " . $gap . " " . _("found"), $nextToken);
				    }

				if ($multiLine === false)
				    {
					if ($tokens[($comma + 1)]["code"] !== T_WHITESPACE)
					    {
						$phpcsFile->addError(_("Expected 1 space between comma and type hint") . " \"" . $hint . "\"; " . _("0 found"), $nextToken);
					    }
					else
					    {
						$gap = strlen($tokens[($comma + 1)]["content"]);
						if ($gap !== 1)
						    {
							$phpcsFile->addError(
							    _("Expected 1 space between comma and type hint") . " \"" . $hint . "\"; " . $gap . " " . _("found"),
							    $nextToken
							);
						    }
					    }
				    }
			    }
			else if ($multiLine === false && $gap !== 1)
			    {
				$phpcsFile->addError(_("Expected 1 space between comma and argument") . " \"" . $arg . "\"; " . $gap . " " . _("found"), $nextToken);
			    } //end if
		    }
		else
		    {
			$phpcsFile->addError(_("Expected 1 space between comma and argument") . " \"" . $arg . "\"; " . _("0 found"), $nextToken);
		    } //end if
	    } //end _processNextArgument()


    } //end class

?>
