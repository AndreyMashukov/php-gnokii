<?php

/**
 * PHP version 5
 *
 * @package Logics\BuildTools\CodeSniffer\Squiz
 */

namespace Logics\BuildTools\CodeSniffer\Squiz;

use \Logics\BuildTools\CodeSniffer\File;
use \Logics\BuildTools\CodeSniffer\Sniff;

/**
 * FunctionDeclarationArgumentSpacingSniff
 *
 * Checks that arguments in function declarations are spaced correctly.
 *
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @author    Marc McIntyre <mmcintyre@squiz.net>
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2006-2012 Squiz Pty Ltd (ABN 77 084 670 600)
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 * @version   SVN: $Date: 2016-08-16 23:39:01 +0900 (Tue, 16 Aug 2016) $ $Revision: 26 $
 * @link      $HeadURL: https://open.logics.net.au/buildtools/codesniffer/tags/0.1.3/src/CodeSniffer/Standards/Squiz/Sniffs/Functions/FunctionDeclarationArgumentSpacingSniff.php $
 */

class FunctionDeclarationArgumentSpacingSniff implements Sniff
    {

	/**
	 * How many spaces should surround the equals signs.
	 *
	 * @var int
	 */
	public $equalsSpacing = 0;

	/**
	 * Returns an array of tokens this test wants to listen for.
	 *
	 * @return array
	 *
	 * @internalconst T_CLOSURE T_CLOSURE token
	 */

	public function register()
	    {
		return array(
			T_FUNCTION,
			T_CLOSURE,
		       );
	    } //end register()


	/**
	 * Processes this test, when one of its tokens is encountered.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $stackPtr  The position of the current token in the stack passed in $tokens.
	 *
	 * @return void
	 *
	 * @internalconst T_CLOSURE          T_CLOSURE token
	 * @internalconst T_OPEN_PARENTHESIS T_OPEN_PARENTHESIS token
	 */

	public function process(File $phpcsFile, $stackPtr)
	    {
		$this->equalsSpacing = (int) $this->equalsSpacing;

		$tokens      = &$phpcsFile->tokens;
		$openBracket = $tokens[$stackPtr]["parenthesis_opener"];
		$this->processBracket($phpcsFile, $openBracket);

		if ($tokens[$stackPtr]["code"] === T_CLOSURE)
		    {
			$use = $phpcsFile->findNext(T_USE, ($tokens[$openBracket]["parenthesis_closer"] + 1), $tokens[$stackPtr]["scope_opener"]);
			if ($use !== false)
			    {
				$openBracket = $phpcsFile->findNext(T_OPEN_PARENTHESIS, ($use + 1), null);
				$this->processBracket($phpcsFile, $openBracket);
			    }
		    }
	    } //end process()


	/**
	 * Processes the contents of a single set of brackets.
	 *
	 * @param File $phpcsFile   The file being scanned.
	 * @param int  $openBracket The position of the open bracket in the stack passed in $tokens.
	 *
	 * @return void
	 *
	 * @internalconst T_COMMA T_COMMA token
	 *
	 * @untranslatable SpaceBeforeComma
	 * @untranslatable SpacingBetween
	 * @untranslatable SpacingBeforeClose
	 * @untranslatable \"%s\"
	 * @untranslatable %s
	 */

	public function processBracket(File &$phpcsFile, $openBracket)
	    {
		$tokens       = &$phpcsFile->tokens;
		$closeBracket = $tokens[$openBracket]["parenthesis_closer"];
		$multiLine    = ($tokens[$openBracket]["line"] !== $tokens[$closeBracket]["line"]);

		$nextParam = $phpcsFile->findNext(T_VARIABLE, ($openBracket + 1), $closeBracket);
		$params    = array();
		while ($nextParam !== false)
		    {
			$nextToken = $phpcsFile->findNext(T_WHITESPACE, ($nextParam + 1), ($closeBracket + 1), true);
			if ($nextToken === false)
			    {
				break;
			    }

			$this->_checkParameterDefaultSpacing($phpcsFile, $tokens, $nextParam, $nextToken);
			// Find and check the comma (if there is one).
			$nextComma = $phpcsFile->findNext(T_COMMA, ($nextParam + 1), $closeBracket);
			if ($nextComma !== false)
			    {
				// Comma found.
				if ($tokens[($nextComma - 1)]["code"] === T_WHITESPACE)
				    {
					$error = _("Expected 0 spaces between argument") . " \"%s\" " . _("and comma;") . " %s " . _("found");
					$data  = array(
						  $tokens[$nextParam]["content"],
						  strlen($tokens[($nextComma - 1)]["content"]),
						 );
					$phpcsFile->addError($error, $nextToken, "SpaceBeforeComma", $data);
				    }
			    }

			// Take references into account when expecting the
			// location of whitespace.
			if ($phpcsFile->isReference(($nextParam - 1)) === true)
			    {
				$whitespace = ($nextParam - 2);
			    }
			else
			    {
				$whitespace = ($nextParam - 1);
			    }

			if (empty($params) === false)
			    {
				$this->_checkNextArgument($phpcsFile, $tokens, $whitespace, $nextParam, $nextToken, $multiLine);
			    }
			else
			    {
				$this->_checkFirstArgument($phpcsFile, $tokens, $whitespace, $nextParam, $multiLine);
			    } //end if

			$params[]  = $nextParam;
			$nextParam = $phpcsFile->findNext(T_VARIABLE, ($nextParam + 1), $closeBracket);
		    } //end while

		if (empty($params) === true)
		    {
			// There are no parameters for this function.
			if (($closeBracket - $openBracket) !== 1)
			    {
				$error = _("Expected 0 spaces between brackets of function declaration;") . " %s " . _("found");
				$data  = array(strlen($tokens[($closeBracket - 1)]["content"]));
				$phpcsFile->addError($error, $openBracket, "SpacingBetween", $data);
			    }
		    }
		else if ($multiLine === false && $tokens[($closeBracket - 1)]["code"] === T_WHITESPACE)
		    {
			$lastParam = array_pop($params);
			$error     = _("Expected 0 spaces between argument") . " \"%s\" " . _("and closing bracket;") . " %s " . _("found");
			$data      = array(
				      $tokens[$lastParam]["content"],
				      strlen($tokens[($closeBracket - 1)]["content"]),
				     );
			$phpcsFile->addError($error, $closeBracket, "SpacingBeforeClose", $data);
		    }
	    } //end processBracket()


	/**
	 * Check parameter default spacing.
	 *
	 * @param File  $phpcsFile The file being scanned.
	 * @param array $tokens    All tokens
	 * @param int   $nextParam Next parameter position
	 * @param int   $nextToken Next token position
	 *
	 * @return void
	 *
	 * @internalconst T_EQUAL T_EQUAL token
	 *
	 * @untranslatable SpaceBeforeEquals
	 * @untranslatable SpaceAfterDefault
	 * @untranslatable \"%s\";
	 * @untranslatable \"%s\"
	 * @untranslatable %s
	 */

	private function _checkParameterDefaultSpacing(File &$phpcsFile, array &$tokens, $nextParam, $nextToken)
	    {
		$nextCode = $tokens[$nextToken]["code"];

		if ($nextCode === T_EQUAL)
		    {
			$spacesBefore = 0;
			if (($nextToken - $nextParam) > 1)
			    {
				$spacesBefore = strlen($tokens[($nextParam + 1)]["content"]);
			    }

			if ($spacesBefore !== $this->equalsSpacing)
			    {
				$error = _("Incorrect spacing between argument") . " \"%s\" " .
					 _("and equals sign; expected") . " " . $this->equalsSpacing . " " . _("but found") . " %s";
				$data  = array(
					  $tokens[$nextParam]["content"],
					  $spacesBefore,
					 );
				$phpcsFile->addError($error, $nextToken, "SpaceBeforeEquals", $data);
			    }

			$spacesAfter = 0;
			if ($tokens[($nextToken + 1)]["code"] === T_WHITESPACE)
			    {
				$spacesAfter = strlen($tokens[($nextToken + 1)]["content"]);
			    }

			if ($spacesAfter !== $this->equalsSpacing)
			    {
				$error = _("Incorrect spacing between default value and equals sign for argument") . " \"%s\"; " .
					 _("expected") . " " . $this->equalsSpacing . " " . _("but found") . " %s";
				$data  = array(
					  $tokens[$nextParam]["content"],
					  $spacesAfter,
					 );
				$phpcsFile->addError($error, $nextToken, "SpaceAfterDefault", $data);
			    }
		    } //end if
	    } //end _checkParameterDefaultSpacing()


	/**
	 * First argument in function declaration.
	 *
	 * @param File  $phpcsFile  The file being scanned.
	 * @param array $tokens     All tokens
	 * @param int   $whitespace Whitespace position
	 * @param int   $nextParam  Next parameter position
	 * @param bool  $multiLine  True if declaration is multiline
	 *
	 * @return void
	 *
	 * @internalconst T_OPEN_PARENTHESIS T_OPEN_PARENTHESIS token
	 *
	 * @untranslatable SpacingAfterOpenHint
	 * @untranslatable SpacingAfterHint
	 * @untranslatable SpacingAfterOpen
	 * @untranslatable \"%s\"; %s
	 */

	private function _checkFirstArgument(File &$phpcsFile, array &$tokens, $whitespace, $nextParam, $multiLine)
	    {
		if ($tokens[$whitespace]["code"] === T_WHITESPACE)
		    {
			$gap = strlen($tokens[$whitespace]["content"]);
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
					$error = _("Expected 1 space between type hint and argument") . " \"%s\"; %s " . _("found");
					$data  = array(
						  $arg,
						  $gap,
						 );
					$phpcsFile->addError($error, $nextToken, "SpacingAfterHint", $data);
				    }

				if ($multiLine === false && $tokens[($bracket + 1)]["code"] === T_WHITESPACE)
				    {
					$error = _("Expected 0 spaces between opening bracket and type hint") . " \"%s\"; %s " . _("found");
					$data  = array(
						  $hint,
						  strlen($tokens[($bracket + 1)]["content"]),
						 );
					$phpcsFile->addError($error, $nextToken, "SpacingAfterOpenHint", $data);
				    }
			    }
			else if ($multiLine === false)
			    {
				$error = _("Expected 0 spaces between opening bracket and argument") . " \"%s\"; %s " . _("found");
				$data  = array(
					  $arg,
					  $gap,
					 );
				$phpcsFile->addError($error, $nextToken, "SpacingAfterOpen", $data);
			    } //end if
		    } //end if
	    } //end _checkFirstArgument()


	/**
	 * This is not the first argument in the function declaration.
	 *
	 * @param File  $phpcsFile  The file being scanned.
	 * @param array $tokens     All tokens
	 * @param int   $whitespace Whitespace position
	 * @param int   $nextParam  Next parameter position
	 * @param int   $nextToken  Next token position
	 * @param bool  $multiLine  True if declaration is multiline
	 *
	 * @return void
	 *
	 * @internalconst T_COMMA T_COMMA token
	 *
	 * @untranslatable NoSpaceBeforeHint
	 * @untranslatable SpacingBeforeHint
	 * @untranslatable SpacingBeforeArg
	 * @untranslatable NoSpaceBeforeArg
	 * @untranslatable SpacingAfterHint
	 * @untranslatable \"%s\"; %s
	 * @untranslatable \"%s\";
	 */

	private function _checkNextArgument(File &$phpcsFile, array &$tokens, $whitespace, $nextParam, $nextToken, $multiLine)
	    {
		$arg = $tokens[$nextParam]["content"];

		if ($tokens[$whitespace]["code"] === T_WHITESPACE)
		    {
			$gap = strlen($tokens[$whitespace]["content"]);

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
					$error = _("Expected 1 space between type hint and argument") . " \"%s\"; %s " . _("found");
					$data  = array(
						  $arg,
						  $gap,
						 );
					$phpcsFile->addError($error, $nextToken, "SpacingAfterHint", $data);
				    }

				if ($multiLine === false)
				    {
					if ($tokens[($comma + 1)]["code"] !== T_WHITESPACE)
					    {
						$error = _("Expected 1 space between comma and type hint") . " \"%s\"; " . _("0 found");
						$data  = array($hint);
						$phpcsFile->addError($error, $nextToken, "NoSpaceBeforeHint", $data);
					    }
					else
					    {
						$gap = strlen($tokens[($comma + 1)]["content"]);
						if ($gap !== 1)
						    {
							$error = _("Expected 1 space between comma and type hint") . " \"%s\"; %s " . _("found");
							$data  = array(
								  $hint,
								  $gap,
								 );
							$phpcsFile->addError($error, $nextToken, "SpacingBeforeHint", $data);
						    }
					    }
				    } //end if
			    }
			else if ($gap !== 1)
			    {
				// Just make sure this is not actually an indent.
				if ($tokens[$whitespace]["line"] === $tokens[($whitespace - 1)]["line"])
				    {
					$error = _("Expected 1 space between comma and argument") . " \"%s\"; %s " . _("found");
					$data  = array(
						  $arg,
						  $gap,
						 );
					$phpcsFile->addError($error, $nextToken, "SpacingBeforeArg", $data);
				    }
			    } //end if
		    }
		else
		    {
			$error = _("Expected 1 space between comma and argument") . " \"%s\"; " . _("0 found");
			$data  = array($arg);
			$phpcsFile->addError($error, $nextToken, "NoSpaceBeforeArg", $data);
		    } //end if
	    } //end _checkNextArgument()


    } //end class

?>
