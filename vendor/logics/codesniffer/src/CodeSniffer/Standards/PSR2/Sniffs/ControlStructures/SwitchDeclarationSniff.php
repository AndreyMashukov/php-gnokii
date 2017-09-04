<?php

/**
 * PHP version 5
 *
 * @package Logics\BuildTools\CodeSniffer\PSR2
 */

namespace Logics\BuildTools\CodeSniffer\PSR2;

use \Logics\BuildTools\CodeSniffer\File;
use \Logics\BuildTools\CodeSniffer\Sniff;

/**
 * SwitchDeclarationSniff
 *
 * Ensures all switch statements are defined correctly.
 *
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2006-2012 Squiz Pty Ltd (ABN 77 084 670 600)
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 * @version   SVN: $Date: 2016-08-16 23:39:01 +0900 (Tue, 16 Aug 2016) $ $Revision: 26 $
 * @link      $HeadURL: https://open.logics.net.au/buildtools/codesniffer/tags/0.1.3/src/CodeSniffer/Standards/PSR2/Sniffs/ControlStructures/SwitchDeclarationSniff.php $
 */

class SwitchDeclarationSniff implements Sniff
    {

	/**
	 * The number of spaces code should be indented.
	 *
	 * @var int
	 */
	public $indent = 4;

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
	 * @internalconst T_COLON T_COLON token
	 *
	 * @untranslatable default
	 * @untranslatable \"%s\"
	 * @untranslatable NotLower
	 * @untranslatable Indent
	 * @untranslatable %s
	 * @untranslatable SpaceBetweenCase
	 * @untranslatable SpacingAfterCase
	 * @untranslatable SpaceBeforeColon
	 * @untranslatable WrongOpener
	 * @untranslatable BreakIndent
	 * @untranslatable case
	 */

	public function process(File $phpcsFile, $stackPtr)
	    {
		$tokens = &$phpcsFile->tokens;

		// We can't process SWITCH statements unless we know where they start and end.
		if (isset($tokens[$stackPtr]["scope_opener"]) === true && isset($tokens[$stackPtr]["scope_closer"]) === true)
		    {
			$switch        = $tokens[$stackPtr];
			$nextCase      = $this->_findNextCase($phpcsFile, ($stackPtr + 1), $switch["scope_closer"]);
			$caseAlignment = ($switch["column"] + $this->indent);
			$caseCount     = 0;
			$foundDefault  = false;

			while ($nextCase !== false)
			    {
				$type = (($tokens[$nextCase]["code"] === T_DEFAULT) ? "default" : "case");

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

				if ($tokens[$nextCase]["column"] !== $caseAlignment)
				    {
					$error = strtoupper($type) . " " . _("keyword must be indented") . " " . $this->indent . " " . _("spaces from SWITCH keyword");
					$phpcsFile->addError($error, $nextCase, $type . "Indent");
				    }

				$prevCode   = $phpcsFile->findPrevious(T_WHITESPACE, ($nextCase - 1), $stackPtr, true);
				$blankLines = ($tokens[$nextCase]["line"] - $tokens[$prevCode]["line"] - 1);
				if ($blankLines !== 0)
				    {
					$error = _("Blank lines are not allowed between case statements; found") . " %s";
					$data  = array($blankLines);
					$phpcsFile->addError($error, $nextCase, "SpaceBetweenCase", $data);
				    }

				if ($type === "case" && ($tokens[($nextCase + 1)]["code"] !== T_WHITESPACE || $tokens[($nextCase + 1)]["content"] !== " "))
				    {
					$error = _("CASE keyword must be followed by a single space");
					$phpcsFile->addError($error, $nextCase, "SpacingAfterCase");
				    }

				$opener = $tokens[$nextCase]["scope_opener"];
				if ($tokens[$opener]["code"] === T_COLON)
				    {
					if ($tokens[($opener - 1)]["code"] === T_WHITESPACE)
					    {
						$error = _("There must be no space before the colon in a") . " " . strtoupper($type) . " " . _("statement");
						$phpcsFile->addError($error, $nextCase, "SpaceBeforeColon" . $type);
					    }
				    }
				else
				    {
					$error = strtoupper($type) . " " . _("statements must not be defined using curly braces");
					$phpcsFile->addError($error, $nextCase, "WrongOpener" . $type);
				    }

				$nextCloser = $tokens[$nextCase]["scope_closer"];
				// Only need to check some things once, even if the
				// closer is shared between multiple case statements, or even
				// the default case.
				if ($tokens[$nextCloser]["scope_condition"] === $nextCase && $tokens[$nextCloser]["column"] !== ($caseAlignment + $this->indent))
				    {
					$error = _("Terminating statement must be indented to the same level as the CASE body");
					$phpcsFile->addError($error, $nextCloser, "BreakIndent");
				    }

				$this->_checkCase($phpcsFile, $tokens, $type, $nextCase, $nextCloser);

				$nextCase = $this->_findNextCase($phpcsFile, ($nextCase + 1), $switch["scope_closer"]);
			    } //end while
		    } //end if
	    } //end process()


	/**
	 * Check CASE statement
	 *
	 * @param File   $phpcsFile  The file being scanned.
	 * @param array  $tokens     All tokens
	 * @param string $type       Statement type
	 * @param int    $nextCase   Next CASE statement position
	 * @param int    $nextCloser Next closer position
	 *
	 * @return void
	 *
	 * @untranslatable case
	 * @untranslatable TerminatingComment
	 */

	private function _checkCase(File &$phpcsFile, array &$tokens, $type, $nextCase, $nextCloser)
	    {
		// We only want cases from here on in.
		if ($type === "case")
		    {
			$nextCode = $phpcsFile->findNext(T_WHITESPACE, ($tokens[$nextCase]["scope_opener"] + 1), $nextCloser, true);

			if ($tokens[$nextCode]["code"] !== T_CASE && $tokens[$nextCode]["code"] !== T_DEFAULT)
			    {
				// This case statement has content. If the next case or default comes
				// before the closer, it means we dont have a terminating statement
				// and instead need a comment.
				$nextCode = $this->_findNextCase($phpcsFile, ($tokens[$nextCase]["scope_opener"] + 1), $nextCloser);
				if ($nextCode !== false)
				    {
					$prevCode = $phpcsFile->findPrevious(T_WHITESPACE, ($nextCode - 1), $nextCase, true);
					if ($tokens[$prevCode]["code"] !== T_COMMENT)
					    {
						$error = _("There must be a comment when fall-through is intentional in a non-empty case body");
						$phpcsFile->addError($error, $nextCase, "TerminatingComment");
					    }
				    }
			    }
		    }
	    } //end _checkCase()


	/**
	 * Find the next CASE or DEFAULT statement from a point in the file.
	 *
	 * Note that nested switches are ignored.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $stackPtr  The position to start looking at.
	 * @param int  $end       The position to stop looking at.
	 *
	 * @return int | bool
	 */

	private function _findNextCase(File &$phpcsFile, $stackPtr, $end)
	    {
		$tokens   = &$phpcsFile->tokens;
		$stackPtr = $phpcsFile->findNext(array(T_CASE, T_DEFAULT, T_SWITCH), $stackPtr, $end);
		while ($stackPtr !== false)
		    {
			// Skip nested SWITCH statements; they are handled on their own.
			if ($tokens[$stackPtr]["code"] !== T_SWITCH)
			    {
				break;
			    }

			$stackPtr = $phpcsFile->findNext(array(T_CASE, T_DEFAULT, T_SWITCH), $tokens[$stackPtr]["scope_closer"], $end);
		    }

		return $stackPtr;
	    } //end _findNextCase()


    } //end class

?>
