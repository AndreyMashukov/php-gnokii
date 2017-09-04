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
 * SwitchDeclarationSniff
 *
 * Ensures all the breaks and cases are aligned correctly according to their
 * parent switch's alignment and enforces other switch formatting.
 *
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @author    Marc McIntyre <mmcintyre@squiz.net>
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2006-2012 Squiz Pty Ltd (ABN 77 084 670 600)
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 * @version   SVN: $Date: 2016-08-16 23:39:01 +0900 (Tue, 16 Aug 2016) $ $Revision: 26 $
 * @link      $HeadURL: https://open.logics.net.au/buildtools/codesniffer/tags/0.1.3/src/CodeSniffer/Standards/Squiz/Sniffs/ControlStructures/SwitchDeclarationSniff.php $
 *
 * @untranslatable PHP
 * @untranslatable JS
 */

class SwitchDeclarationSniff implements Sniff
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
		return array(T_SWITCH);
	    } //end register()


	/**
	 * Processes this test, when one of its tokens is encountered.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $stackPtr  The position of the current token in the stack passed in $tokens.
	 *
	 * @return void
	 *
	 * @untranslatable \"%s\"
	 * @untranslatable NotLower
	 * @untranslatable MissingDefault
	 * @untranslatable CloseBraceAlign
	 * @untranslatable MissingCase
	 * @untranslatable Case
	 * @untranslatable Default
	 */

	public function process(File $phpcsFile, $stackPtr)
	    {
		$tokens = &$phpcsFile->tokens;

		// We can't process SWITCH statements unless we know where they start and end.
		if (isset($tokens[$stackPtr]["scope_opener"]) === true && isset($tokens[$stackPtr]["scope_closer"]) === true)
		    {
			$switch        = $tokens[$stackPtr];
			$nextCase      = $phpcsFile->findNext(array(T_CASE, T_DEFAULT, T_SWITCH), ($stackPtr + 1), $switch["scope_closer"]);
			$caseAlignment = ($switch["column"] + 4);
			$caseCount     = 0;
			$foundDefault  = false;

			while ($nextCase !== false)
			    {
				// Skip nested SWITCH statements; they are handled on their own.
				if ($tokens[$nextCase]["code"] === T_SWITCH)
				    {
					$nextCase = $tokens[$nextCase]["scope_closer"];
				    }
				else
				    {
					if ($tokens[$nextCase]["code"] === T_DEFAULT)
					    {
						$type         = "Default";
						$foundDefault = true;
					    }
					else
					    {
						$type = "Case";
						$caseCount++;
					    }

					if ($tokens[$nextCase]["content"] !== strtolower($tokens[$nextCase]["content"]))
					    {
						$expected = strtolower($tokens[$nextCase]["content"]);
						$error    = strtoupper($type) . " " . _("keyword must be lowercase; expected") . " \"%s\" " . _("but found") . " \"%s\"";
						$data     = array(
							     $expected,
							     $tokens[$nextCase]["content"],
							    );
						$phpcsFile->addError($error, $nextCase, $type . "NotLower", $data);
					    }

					$this->_checkNextCase($phpcsFile, $stackPtr, $tokens, $nextCase, $caseAlignment, $type);
				    } //end if

				$nextCase = $phpcsFile->findNext(array(T_CASE, T_DEFAULT, T_SWITCH), ($nextCase + 1), $switch["scope_closer"]);
			    } //end while

			if ($foundDefault === false)
			    {
				$error = _("All SWITCH statements must contain a DEFAULT case");
				$phpcsFile->addError($error, $stackPtr, "MissingDefault");
			    }

			if ($tokens[$switch["scope_closer"]]["column"] !== $switch["column"])
			    {
				$error = _("Closing brace of SWITCH statement must be aligned with SWITCH keyword");
				$phpcsFile->addError($error, $switch["scope_closer"], "CloseBraceAlign");
			    }

			if ($caseCount === 0)
			    {
				$error = _("SWITCH statements must contain at least one CASE statement");
				$phpcsFile->addError($error, $stackPtr, "MissingCase");
			    }
		    } //end if
	    } //end process()


	/**
	 * Check next case
	 *
	 * @param File   $phpcsFile     The file being scanned.
	 * @param int    $stackPtr      The position of the current token in the stack passed in $tokens.
	 * @param array  $tokens        All tokens
	 * @param int    $nextCase      Next CASE position
	 * @param int    $caseAlignment Required CASE alignment
	 * @param string $type          Case type
	 *
	 * @return void
	 *
	 * @untranslatable Indent
	 * @untranslatable SpacingAfterCase
	 * @untranslatable SpaceBeforeColon
	 * @untranslatable T_WHITESPACE
	 * @untranslatable DefaultNoBreak
	 * @untranslatable Case
	 * @untranslatable Default
	 */

	private function _checkNextCase(File &$phpcsFile, $stackPtr, array &$tokens, $nextCase, $caseAlignment, $type)
	    {
		if ($tokens[$nextCase]["column"] !== $caseAlignment)
		    {
			$error = strtoupper($type) . " " . _("keyword must be indented 4 spaces from SWITCH keyword");
			$phpcsFile->addError($error, $nextCase, $type . "Indent");
		    }

		if ($type === "Case" && ($tokens[($nextCase + 1)]["type"] !== "T_WHITESPACE" || $tokens[($nextCase + 1)]["content"] !== " "))
		    {
			$error = _("CASE keyword must be followed by a single space");
			$phpcsFile->addError($error, $nextCase, "SpacingAfterCase");
		    }

		$opener = $tokens[$nextCase]["scope_opener"];
		if ($tokens[($opener - 1)]["type"] === "T_WHITESPACE")
		    {
			$error = _("There must be no space before the colon in a") . " " . strtoupper($type) . " " . _("statement");
			$phpcsFile->addError($error, $nextCase, "SpaceBeforeColon" . $type);
		    }

		$nextBreak = $tokens[$nextCase]["scope_closer"];
		if ($tokens[$nextBreak]["code"] === T_BREAK || $tokens[$nextBreak]["code"] === T_RETURN)
		    {
			if ($tokens[$nextBreak]["scope_condition"] === $nextCase)
			    {
				$this->_checkCaseEnd($phpcsFile, $stackPtr, $tokens, $nextCase, $nextBreak, $caseAlignment, $type, $opener);
			    } //end if

			if ($tokens[$nextBreak]["code"] === T_BREAK)
			    {
				$this->_checkBreak($phpcsFile, $tokens, $type, $nextCase, $nextBreak);
			    }
		    }
		else if ($type === "Default")
		    {
			$error = _("DEFAULT case must have a breaking statement");
			$phpcsFile->addError($error, $nextCase, "DefaultNoBreak");
		    } //end if
	    } //end _checkNextCase()


	/**
	 * Check case end
	 *
	 * @param File   $phpcsFile     The file being scanned.
	 * @param int    $stackPtr      The position of the current token in the stack passed in $tokens.
	 * @param array  $tokens        All tokens
	 * @param int    $nextCase      Next CASE position
	 * @param int    $nextBreak     Next BREAK position
	 * @param int    $caseAlignment Required CASE alignment
	 * @param string $type          Case type
	 * @param int    $opener        Scope opener
	 *
	 * @return void
	 *
	 * @internalconst T_SEMICOLON T_SEMICOLON token
	 *
	 * @untranslatable BreakIndent
	 * @untranslatable SpacingBeforeBreak
	 * @untranslatable SpacingAfterBreak
	 * @untranslatable SpacingAfterDefaultBreak
	 * @untranslatable SpacingAfter
	 * @untranslatable T_WHITESPACE
	 * @untranslatable Case
	 */

	private function _checkCaseEnd(File &$phpcsFile, $stackPtr, array &$tokens, $nextCase, $nextBreak, $caseAlignment, $type, $opener)
	    {
		// Only need to check a couple of things once, even if the
		// break is shared between multiple case statements, or even
		// the default case.
		if ($tokens[$nextBreak]["column"] !== $caseAlignment)
		    {
			$error = _("Case breaking statement must be indented 4 spaces from SWITCH keyword");
			$phpcsFile->addError($error, $nextBreak, "BreakIndent");
		    }

		$breakLine = $tokens[$nextBreak]["line"];
		$prevLine  = 0;
		for ($i = ($nextBreak - 1); $i > $stackPtr; $i--)
		    {
			if ($tokens[$i]["type"] !== "T_WHITESPACE")
			    {
				$prevLine = $tokens[$i]["line"];
				break;
			    }
		    }

		if ($prevLine !== ($breakLine - 1))
		    {
			$error = _("Blank lines are not allowed before case breaking statements");
			$phpcsFile->addError($error, $nextBreak, "SpacingBeforeBreak");
		    }

		$breakLine = $tokens[$nextBreak]["line"];
		$semicolon = $phpcsFile->findNext(T_SEMICOLON, $nextBreak);
		$nextLine  = $this->_getNextLine($tokens, $tokens[$tokens[$stackPtr]["scope_closer"]]["line"], ($semicolon + 1), $tokens[$stackPtr]["scope_closer"], $i);

		if ($type === "Case")
		    {
			// Ensure the BREAK statement is followed by
			// a single blank line, or the end switch brace.
			if ($nextLine !== ($breakLine + 2) && $i !== $tokens[$stackPtr]["scope_closer"])
			    {
				$error = _("Case breaking statements must be followed by a single blank line");
				$phpcsFile->addError($error, $nextBreak, "SpacingAfterBreak");
			    }
		    }
		else
		    {
			// Ensure the BREAK statement is not followed by a blank line.
			if ($nextLine !== ($breakLine + 1))
			    {
				$error = _("Blank lines are not allowed after the DEFAULT case's breaking statement");
				$phpcsFile->addError($error, $nextBreak, "SpacingAfterDefaultBreak");
			    }
		    }

		$caseLine = $tokens[$nextCase]["line"];
		$nextLine = $this->_getNextLine($tokens, $tokens[$nextBreak]["line"], ($opener + 1), $nextBreak);
		if ($nextLine !== ($caseLine + 1))
		    {
			$error = _("Blank lines are not allowed after") . " " . strtoupper($type) . " " . _("statements");
			$phpcsFile->addError($error, $nextCase, "SpacingAfter" . $type);
		    }
	    } //end _checkCaseEnd()


	/**
	 * Get next line
	 *
	 * @param array $tokens   All tokens
	 * @param int   $nextLine Next line
	 * @param int   $from     From token position
	 * @param int   $to       To token position
	 * @param int   $i        Optional iterator
	 *
	 * @return int Next line
	 *
	 * @untranslatable T_WHITESPACE
	 */

	private function _getNextLine(array &$tokens, $nextLine, $from, $to, &$i = 0)
	    {
		for ($i = $from; $i < $to; $i++)
		    {
			if ($tokens[$i]["type"] !== "T_WHITESPACE")
			    {
				$nextLine = $tokens[$i]["line"];
				break;
			    }
		    }

		return $nextLine;
	    } //end _getNextLine()


	/**
	 * Check next break
	 *
	 * @param File   $phpcsFile The file being scanned.
	 * @param array  $tokens    All tokens
	 * @param string $type      Case type
	 * @param int    $nextCase  Next CASE position
	 * @param int    $nextBreak Next BREAK position
	 *
	 * @return void
	 *
	 * @untranslatable EmptyCase
	 * @untranslatable T_WHITESPACE
	 * @untranslatable EmptyDefault
	 * @untranslatable Case
	 */

	private function _checkBreak(File &$phpcsFile, array &$tokens, $type, $nextCase, $nextBreak)
	    {
		if ($type === "Case")
		    {
			// Ensure empty CASE statements are not allowed.
			// They must have some code content in them. A comment is not enough.
			// But count RETURN statements as valid content if they also
			// happen to close the CASE statement.
			$foundContent = false;
			for ($i = ($tokens[$nextCase]["scope_opener"] + 1); $i < $nextBreak; $i++)
			    {
				if ($tokens[$i]["code"] === T_CASE)
				    {
					$i = $tokens[$i]["scope_opener"];
				    }
				else if (in_array($tokens[$i]["code"], Tokens::$emptyTokens) === false)
				    {
					$foundContent = true;
					break;
				    }
			    }

			if ($foundContent === false)
			    {
				$error = _("Empty CASE statements are not allowed");
				$phpcsFile->addError($error, $nextCase, "EmptyCase");
			    }
		    }
		else
		    {
			// Ensure empty DEFAULT statements are not allowed.
			// They must (at least) have a comment describing why
			// the default case is being ignored.
			$foundContent = false;
			for ($i = ($tokens[$nextCase]["scope_opener"] + 1); $i < $nextBreak; $i++)
			    {
				if ($tokens[$i]["type"] !== "T_WHITESPACE")
				    {
					$foundContent = true;
					break;
				    }
			    }

			if ($foundContent === false)
			    {
				$error = _("Comment required for empty DEFAULT case");
				$phpcsFile->addError($error, $nextCase, "EmptyDefault");
			    }
		    } //end if
	    } //end _checkBreak()


    } //end class

?>