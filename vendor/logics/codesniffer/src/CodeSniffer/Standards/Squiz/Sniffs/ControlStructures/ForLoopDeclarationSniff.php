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
 * Squiz_Sniffs_ControlStructures_ForLoopDeclarationSniff.
 *
 * Verifies that there is a space between each condition of for loops.
 *
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @author    Marc McIntyre <mmcintyre@squiz.net>
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2006-2012 Squiz Pty Ltd (ABN 77 084 670 600)
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 * @version   SVN: $Date: 2016-08-16 23:39:01 +0900 (Tue, 16 Aug 2016) $ $Revision: 26 $
 * @link      $HeadURL: https://open.logics.net.au/buildtools/codesniffer/tags/0.1.3/src/CodeSniffer/Standards/Squiz/Sniffs/ControlStructures/ForLoopDeclarationSniff.php $
 *
 * @untranslatable PHP
 * @untranslatable JS
 */

class ForLoopDeclarationSniff implements Sniff
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
		return array(T_FOR);
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
	 * @untranslatable NoOpenBracket
	 * @untranslatable SpacingAfterOpen
	 * @untranslatable SpacingBeforeClose
	 */

	public function process(File $phpcsFile, $stackPtr)
	    {
		$tokens = &$phpcsFile->tokens;

		$openingBracket = $phpcsFile->findNext(T_OPEN_PARENTHESIS, $stackPtr);
		if ($openingBracket === false)
		    {
			$error = _("Possible parse error: no opening parenthesis for FOR keyword");
			$phpcsFile->addWarning($error, $stackPtr, "NoOpenBracket");
		    }
		else
		    {
			$closingBracket = $tokens[$openingBracket]["parenthesis_closer"];

			if ($tokens[($openingBracket + 1)]["code"] === T_WHITESPACE)
			    {
				$error = _("Space found after opening bracket of FOR loop");
				$phpcsFile->addError($error, $stackPtr, "SpacingAfterOpen");
			    }

			if ($tokens[($closingBracket - 1)]["code"] === T_WHITESPACE)
			    {
				$error = _("Space found before closing bracket of FOR loop");
				$phpcsFile->addError($error, $stackPtr, "SpacingBeforeClose");
			    }

			$this->_checkSemicolons($phpcsFile, $stackPtr, $tokens, $openingBracket, $closingBracket);
		    } //end if
	    } //end process()


	/**
	 * Check semicolons in FOR statement
	 *
	 * @param File  $phpcsFile      The file being scanned.
	 * @param int   $stackPtr       The position of the current token in the stack passed in $tokens.
	 * @param array $tokens         All tokens
	 * @param int   $openingBracket Opening bracket position
	 * @param int   $closingBracket Closing bracket position
	 *
	 * @return void
	 *
	 * @internalconst T_SEMICOLON T_SEMICOLON token
	 *
	 * @untranslatable SpacingBeforeFirst
	 * @untranslatable NoSpaceAfterFirst
	 * @untranslatable SpacingAfterFirst
	 * @untranslatable SpacingBeforeSecond
	 * @untranslatable NoSpaceAfterSecond
	 * @untranslatable SpacingAfterSecondNoThird
	 * @untranslatable SpacingAfterSecond
	 * @untranslatable %s
	 */

	private function _checkSemicolons(File &$phpcsFile, $stackPtr, array &$tokens, $openingBracket, $closingBracket)
	    {
		$firstSemicolon = $phpcsFile->findNext(T_SEMICOLON, $openingBracket, $closingBracket);

		// Check whitespace around each of the tokens.
		if ($firstSemicolon !== false)
		    {
			if ($tokens[($firstSemicolon - 1)]["code"] === T_WHITESPACE)
			    {
				$error = _("Space found before first semicolon of FOR loop");
				$phpcsFile->addError($error, $stackPtr, "SpacingBeforeFirst");
			    }

			if ($tokens[($firstSemicolon + 1)]["code"] !== T_WHITESPACE)
			    {
				$error = _("Expected 1 space after first semicolon of FOR loop; 0 found");
				$phpcsFile->addError($error, $stackPtr, "NoSpaceAfterFirst");
			    }
			else if (strlen($tokens[($firstSemicolon + 1)]["content"]) !== 1)
			    {
				$spaces = strlen($tokens[($firstSemicolon + 1)]["content"]);
				$error  = _("Expected 1 space after first semicolon of FOR loop;") . " %s " . _("found");
				$data   = array($spaces);
				$phpcsFile->addError($error, $stackPtr, "SpacingAfterFirst", $data);
			    }

			$secondSemicolon = $phpcsFile->findNext(T_SEMICOLON, ($firstSemicolon + 1));

			if ($secondSemicolon !== false)
			    {
				if ($tokens[($secondSemicolon - 1)]["code"] === T_WHITESPACE)
				    {
					$error = _("Space found before second semicolon of FOR loop");
					$phpcsFile->addError($error, $stackPtr, "SpacingBeforeSecond");
				    }

				if (($secondSemicolon + 1) !== $closingBracket && $tokens[($secondSemicolon + 1)]["code"] !== T_WHITESPACE)
				    {
					$error = _("Expected 1 space after second semicolon of FOR loop; 0 found");
					$phpcsFile->addError($error, $stackPtr, "NoSpaceAfterSecond");
				    }
				else if (strlen($tokens[($secondSemicolon + 1)]["content"]) !== 1)
				    {
					$spaces = strlen($tokens[($secondSemicolon + 1)]["content"]);
					$data   = array($spaces);
					if (($secondSemicolon + 2) === $closingBracket)
					    {
						$error = _("Expected no space after second semicolon of FOR loop;") . " %s " . _("found");
						$phpcsFile->addError($error, $stackPtr, "SpacingAfterSecondNoThird", $data);
					    }
					else
					    {
						$error = _("Expected 1 space after second semicolon of FOR loop;") . " %s " . _("found");
						$phpcsFile->addError($error, $stackPtr, "SpacingAfterSecond", $data);
					    }
				    } //end if
			    } //end if
		    } //end if
	    } //end _checkSemicolons()


    } //end class

?>
