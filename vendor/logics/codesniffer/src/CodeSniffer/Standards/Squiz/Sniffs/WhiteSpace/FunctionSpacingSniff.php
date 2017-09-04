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
 * Squiz_Sniffs_WhiteSpace_FunctionSpacingSniff.
 *
 * Checks the separation between methods in a class or interface.
 *
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @author    Marc McIntyre <mmcintyre@squiz.net>
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2006-2012 Squiz Pty Ltd (ABN 77 084 670 600)
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 * @version   SVN: $Date: 2016-08-16 23:39:01 +0900 (Tue, 16 Aug 2016) $ $Revision: 26 $
 * @link      $HeadURL: https://open.logics.net.au/buildtools/codesniffer/tags/0.1.3/src/CodeSniffer/Standards/Squiz/Sniffs/WhiteSpace/FunctionSpacingSniff.php $
 */

class FunctionSpacingSniff implements Sniff
    {

	/**
	 * The number of blank lines between functions.
	 *
	 * @var int
	 */
	public $spacing = 2;

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
	 * Processes this sniff when one of its tokens is encountered.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $stackPtr  The position of the current token in the stack passed in $tokens.
	 *
	 * @return void
	 */

	public function process(File $phpcsFile, $stackPtr)
	    {
		$tokens        = &$phpcsFile->tokens;
		$this->spacing = (int) $this->spacing;

		$this->_checkBlankLinesAfterFunction($phpcsFile, $stackPtr, $tokens);
		$this->_checkBlankLinesBeforeFunction($phpcsFile, $stackPtr, $tokens);
	    } //end process()


	/**
	 * Check the number of blank lines after the function.
	 *
	 * @param File  $phpcsFile The file being scanned.
	 * @param int   $stackPtr  The position of the current token in the stack passed in $tokens.
	 * @param array $tokens    All tokens
	 *
	 * @return void
	 *
	 * @internalconst T_SEMICOLON T_SEMICOLON token
	 *
	 * @untranslatable After
	 * @untranslatable %s
	 * @untranslatable ; %s
	 */

	private function _checkBlankLinesAfterFunction(File &$phpcsFile, $stackPtr, array &$tokens)
	    {
		if (isset($tokens[$stackPtr]["scope_closer"]) === false)
		    {
			// Must be an interface method, so the closer is the semi-colon.
			$closer = $phpcsFile->findNext(T_SEMICOLON, $stackPtr);
		    }
		else
		    {
			$closer = $tokens[$stackPtr]["scope_closer"];
		    }

		$nextLineToken = null;
		for ($i = $closer; $i < $phpcsFile->numTokens; $i++)
		    {
			if (strpos($tokens[$i]["content"], $phpcsFile->eolChar) !== false)
			    {
				$nextLineToken = ($i + 1);
				break;
			    }
		    }

		if ($nextLineToken === null)
		    {
			// Never found the next line, which means
			// there are 0 blank lines after the function.
			$foundLines = 0;
		    }
		else
		    {
			$nextContent = $phpcsFile->findNext(array(T_WHITESPACE), ($nextLineToken + 1), null, true);
			if ($nextContent === false)
			    {
				// We are at the end of the file.
				$foundLines = 0;
			    }
			else
			    {
				$foundLines = ($tokens[$nextContent]["line"] - $tokens[$nextLineToken]["line"]);
			    }
		    }

		if ($foundLines !== $this->spacing)
		    {
			$error = _("Expected") . " %s " . (($this->spacing === 1) ? _("blank line after function") : _("blank lines after function")) . "; %s " . _("found");
			$data  = array(
				  $this->spacing,
				  $foundLines,
				 );
			$phpcsFile->addError($error, $closer, "After", $data);
		    }
	    } //end _checkBlankLinesAfterFunction()


	/**
	 * Check the number of blank lines before the function.
	 *
	 * @param File  $phpcsFile The file being scanned.
	 * @param int   $stackPtr  The position of the current token in the stack passed in $tokens.
	 * @param array $tokens    All tokens
	 *
	 * @return void
	 *
	 * @internalconst T_DOC_COMMENT_CLOSE_TAG T_DOC_COMMENT_CLOSE_TAG token
	 *
	 * @untranslatable Before
	 * @untranslatable %s
	 * @untranslatable ; %s
	 */

	private function _checkBlankLinesBeforeFunction(File &$phpcsFile, $stackPtr, array &$tokens)
	    {
		$prevLineToken = null;
		for ($i = $stackPtr; $i > 0; $i--)
		    {
			if (strpos($tokens[$i]["content"], $phpcsFile->eolChar) !== false)
			    {
				$prevLineToken = $i;
				break;
			    }
		    }

		if ($prevLineToken === null)
		    {
			// Never found the previous line, which means there are 0 blank lines before the function.
			$foundLines = 0;
		    }
		else
		    {
			$currentLine = $tokens[$stackPtr]["line"];
			$prevContent = $phpcsFile->findPrevious(T_WHITESPACE, $prevLineToken, null, true);
			if ($tokens[$prevContent]["code"] === T_DOC_COMMENT_CLOSE_TAG)
			    {
				// Account for function comments.
				$prevContent = $phpcsFile->findPrevious(T_WHITESPACE, ($tokens[$prevContent]["comment_opener"] - 1), null, true);
			    }

			// Before we throw an error, check that we are not throwing an error  for another function.
			// We don't want to error for no blank lines after the previous function and no blank lines before this one as well.
			$prevLine   = ($tokens[$prevContent]["line"] - 1);
			$i          = ($stackPtr - 1);
			$foundLines = 0;
			while ($currentLine != $prevLine && $currentLine > 1 && $i > 0)
			    {
				if (isset($tokens[$i]["scope_condition"]) === true && $tokens[$tokens[$i]["scope_condition"]]["code"] === T_FUNCTION)
				    {
					// Found a previous function.
					$foundLines = false;
					break;
				    }
				else if ($tokens[$i]["code"] === T_FUNCTION)
				    {
					// Found another interface function.
					$foundLines = false;
					break;
				    }

				$currentLine = $tokens[$i]["line"];
				if ($currentLine === $prevLine)
				    {
					break;
				    }

				// This token is on a line by itself. If it is whitespace, the line is empty.
				if ($tokens[($i - 1)]["line"] < $currentLine && $tokens[($i + 1)]["line"] > $currentLine && $tokens[$i]["code"] === T_WHITESPACE)
				    {
					$foundLines++;
				    }

				$i--;
			    } //end while
		    } //end if

		if ($foundLines !== false && $foundLines !== $this->spacing)
		    {
			$error = _("Expected") . " %s " . (($this->spacing === 1) ? _("blank line before function") : _("blank lines before function")) . "; %s " . _("found");
			$data  = array(
				  $this->spacing,
				  $foundLines,
				 );
			$phpcsFile->addError($error, $stackPtr, "Before", $data);
		    }
	    } //end _checkBlankLinesBeforeFunction()


    } //end class

?>
