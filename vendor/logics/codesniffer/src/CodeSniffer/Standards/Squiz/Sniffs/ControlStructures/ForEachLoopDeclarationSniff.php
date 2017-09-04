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
 * Squiz_Sniffs_ControlStructures_ForEachLoopDeclarationSniff.
 *
 * Verifies that there is a space between each condition of foreach loops.
 *
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @author    Marc McIntyre <mmcintyre@squiz.net>
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2006-2012 Squiz Pty Ltd (ABN 77 084 670 600)
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 * @version   SVN: $Date: 2016-08-16 23:39:01 +0900 (Tue, 16 Aug 2016) $ $Revision: 26 $
 * @link      $HeadURL: https://open.logics.net.au/buildtools/codesniffer/tags/0.1.3/src/CodeSniffer/Standards/Squiz/Sniffs/ControlStructures/ForEachLoopDeclarationSniff.php $
 */

class ForEachLoopDeclarationSniff implements Sniff
    {

	/**
	 * Returns an array of tokens this test wants to listen for.
	 *
	 * @return array
	 */

	public function register()
	    {
		return array(T_FOREACH);
	    } //end register()


	/**
	 * Processes this test, when one of its tokens is encountered.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $stackPtr  The position of the current token in the stack passed in $tokens.
	 *
	 * @return void
	 *
	 * @internalconst T_OPEN_PARENTHESIS T_OPEN_PARENTHESIS token
	 *
	 * @untranslatable SpaceAfterOpen
	 * @untranslatable SpaceBeforeClose
	 * @untranslatable AsNotLower
	 * @untranslatable NoSpaceBeforeAs
	 * @untranslatable SpacingBeforeAs
	 * @untranslatable NoSpaceAfterAs
	 * @untranslatable SpacingAfterAs
	 * @untranslatable %s
	 * @untranslatable \"%s\"
	 */

	public function process(File $phpcsFile, $stackPtr)
	    {
		$tokens = &$phpcsFile->tokens;

		$openingBracket = $phpcsFile->findNext(T_OPEN_PARENTHESIS, $stackPtr);
		$closingBracket = $tokens[$openingBracket]["parenthesis_closer"];

		if ($tokens[($openingBracket + 1)]["code"] === T_WHITESPACE)
		    {
			$error = _("Space found after opening bracket of FOREACH loop");
			$phpcsFile->addError($error, $stackPtr, "SpaceAfterOpen");
		    }

		if ($tokens[($closingBracket - 1)]["code"] === T_WHITESPACE)
		    {
			$error = _("Space found before closing bracket of FOREACH loop");
			$phpcsFile->addError($error, $stackPtr, "SpaceBeforeClose");
		    }

		$asToken = $phpcsFile->findNext(T_AS, $openingBracket);
		$content = $tokens[$asToken]["content"];
		if ($content !== strtolower($content))
		    {
			$expected = strtolower($content);
			$error    = _("AS keyword must be lowercase; expected") . " \"%s\" " . _("but found") . " \"%s\"";
			$data     = array(
				     $expected,
				     $content,
				    );
			$phpcsFile->addError($error, $stackPtr, "AsNotLower", $data);
		    }

		$this->_checkDoubleArrowSpacing($phpcsFile, $stackPtr, $tokens, $openingBracket, $closingBracket);

		if ($tokens[($asToken - 1)]["code"] !== T_WHITESPACE)
		    {
			$error = _("Expected 1 space before \"as\"; 0 found");
			$phpcsFile->addError($error, $stackPtr, "NoSpaceBeforeAs");
		    }
		else if (strlen($tokens[($asToken - 1)]["content"]) !== 1)
		    {
			$spaces = strlen($tokens[($asToken - 1)]["content"]);
			$error  = _("Expected 1 space before \"as\";") . " %s " . _("found");
			$data   = array($spaces);
			$phpcsFile->addError($error, $stackPtr, "SpacingBeforeAs", $data);
		    }

		if ($tokens[($asToken + 1)]["code"] !== T_WHITESPACE)
		    {
			$error = _("Expected 1 space after \"as\"; 0 found");
			$phpcsFile->addError($error, $stackPtr, "NoSpaceAfterAs");
		    }
		else if (strlen($tokens[($asToken + 1)]["content"]) !== 1)
		    {
			$spaces = strlen($tokens[($asToken + 1)]["content"]);
			$error  = _("Expected 1 space after \"as\";") . " %s " . _("found");
			$data   = array($spaces);
			$phpcsFile->addError($error, $stackPtr, "SpacingAfterAs", $data);
		    }
	    } //end process()


	/**
	 * Check double arrow spacing
	 *
	 * @param File  $phpcsFile      The file being scanned.
	 * @param int   $stackPtr       The position of the current token in the stack passed in $tokens.
	 * @param array $tokens         All tokens
	 * @param int   $openingBracket Opening bracket position
	 * @param int   $closingBracket Closing bracket position
	 *
	 * @return void
	 *
	 * @untranslatable NoSpaceBeforeArrow
	 * @untranslatable SpacingBeforeArrow
	 * @untranslatable NoSpaceAfterArrow
	 * @untranslatable SpacingAfterArrow
	 * @untranslatable %s
	 */

	private function _checkDoubleArrowSpacing(File &$phpcsFile, $stackPtr, array &$tokens, $openingBracket, $closingBracket)
	    {
		$doubleArrow = $phpcsFile->findNext(T_DOUBLE_ARROW, $openingBracket, $closingBracket);

		if ($doubleArrow !== false)
		    {
			if ($tokens[($doubleArrow - 1)]["code"] !== T_WHITESPACE)
			    {
				$error = _("Expected 1 space before \"=>\"; 0 found");
				$phpcsFile->addError($error, $stackPtr, "NoSpaceBeforeArrow");
			    }
			else if (strlen($tokens[($doubleArrow - 1)]["content"]) !== 1)
			    {
				$spaces = strlen($tokens[($doubleArrow - 1)]["content"]);
				$error  = _("Expected 1 space before \"=>\";") . " %s " . _("found");
				$data   = array($spaces);
				$phpcsFile->addError($error, $stackPtr, "SpacingBeforeArrow", $data);
			    }

			if ($tokens[($doubleArrow + 1)]["code"] !== T_WHITESPACE)
			    {
				$error = _("Expected 1 space after \"=>\"; 0 found");
				$phpcsFile->addError($error, $stackPtr, "NoSpaceAfterArrow");
			    }
			else if (strlen($tokens[($doubleArrow + 1)]["content"]) !== 1)
			    {
				$spaces = strlen($tokens[($doubleArrow + 1)]["content"]);
				$error  = _("Expected 1 space after \"=>\";") . " %s " . _("found");
				$data   = array($spaces);
				$phpcsFile->addError($error, $stackPtr, "SpacingAfterArrow", $data);
			    }
		    } //end if
	    } //end _checkDoubleArrowSpacing()


    } //end class

?>
