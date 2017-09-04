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
 * Squiz_Sniffs_PHP_EmbeddedPhpSniff.
 *
 * Checks the indentation of embedded PHP code segments.
 *
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @author    Marc McIntyre <mmcintyre@squiz.net>
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2006-2012 Squiz Pty Ltd (ABN 77 084 670 600)
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 * @version   SVN: $Date: 2016-08-16 23:39:01 +0900 (Tue, 16 Aug 2016) $ $Revision: 26 $
 * @link      $HeadURL: https://open.logics.net.au/buildtools/codesniffer/tags/0.1.3/src/CodeSniffer/Standards/Squiz/Sniffs/PHP/EmbeddedPhpSniff.php $
 */

class EmbeddedPhpSniff implements Sniff
    {

	/**
	 * Returns an array of tokens this test wants to listen for.
	 *
	 * @return array
	 */

	public function register()
	    {
		return array(T_OPEN_TAG);
	    } //end register()


	/**
	 * Processes this test, when one of its tokens is encountered.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $stackPtr  The position of the current token in the stack passed in $tokens.
	 *
	 * @return void
	 */

	public function process(File $phpcsFile, $stackPtr)
	    {
		$tokens = &$phpcsFile->tokens;

		// If the close php tag is on the same line as the opening
		// then we have an inline embedded PHP block.
		$closeTag = $phpcsFile->findNext(array(T_CLOSE_TAG), $stackPtr);
		if ($closeTag !== false)
		    {
			if ($tokens[$stackPtr]["line"] !== $tokens[$closeTag]["line"])
			    {
				$this->_validateMultilineEmbeddedPhp($phpcsFile, $stackPtr);
			    }
			else
			    {
				$this->_validateInlineEmbeddedPhp($phpcsFile, $stackPtr);
			    }
		    }
	    } //end process()


	/**
	 * Validates embedded PHP that exists on multiple lines.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $stackPtr  The position of the current token in the stack passed in $tokens.
	 *
	 * @return void
	 *
	 * @untranslatable Empty
	 */

	private function _validateMultilineEmbeddedPhp(File &$phpcsFile, $stackPtr)
	    {
		$tokens = &$phpcsFile->tokens;

		$prevTag    = $phpcsFile->findPrevious(T_OPEN_TAG, ($stackPtr - 1));
		$closingTag = $phpcsFile->findNext(T_CLOSE_TAG, $stackPtr);
		if ($prevTag !== false && $closingTag !== false)
		    {
			$nextContent = $phpcsFile->findNext(T_WHITESPACE, ($closingTag + 1), $phpcsFile->numTokens, true);
			// Final closing tag. It will be handled elsewhere.
			// Make sure the lines are opening and closing on different lines.
			if ($nextContent !== false && $tokens[$stackPtr]["line"] !== $tokens[$closingTag]["line"])
			    {
				// We have an opening and a closing tag, that lie within other content.
				// They are also on different lines.
				$firstContent = $phpcsFile->findNext(T_WHITESPACE, ($stackPtr + 1), $closingTag, true);
				if ($firstContent === false)
				    {
					$error = _("Empty embedded PHP tag found");
					$phpcsFile->addError($error, $stackPtr, "Empty");
				    }
				else
				    {
					$this->_checkMultilineEmbeddedPhp($phpcsFile, $stackPtr, $firstContent, $closingTag);
				    }
			    }
		    }
	    } //end _validateMultilineEmbeddedPhp()


	/**
	 * Check embedded PHP that exists on multiple lines.
	 *
	 * @param File $phpcsFile    The file being scanned.
	 * @param int  $stackPtr     The position of the current token in the stack passed in $tokens.
	 * @param int  $firstContent First content position
	 * @param int  $closingTag   Closing tag position
	 *
	 * @return void
	 *
	 * @untranslatable SpacingBefore
	 * @untranslatable ContentAfterOpen
	 * @untranslatable Indent
	 * @untranslatable SpacingAfter
	 * @untranslatable ContentAfterEnd
	 * @untranslatable %s
	 */

	private function _checkMultilineEmbeddedPhp(File &$phpcsFile, $stackPtr, $firstContent, $closingTag)
	    {
		$tokens = &$phpcsFile->tokens;

		// Check for a blank line at the top.
		if ($tokens[$firstContent]["line"] > ($tokens[$stackPtr]["line"] + 1))
		    {
			// Find a token on the blank line to throw the error on.
			$i = $stackPtr;
			do
			    {
				$i++;
			    } while ($tokens[$i]["line"] !== ($tokens[$stackPtr]["line"] + 1));

			$error = _("Blank line found at start of embedded PHP content");
			$phpcsFile->addError($error, $i, "SpacingBefore");
		    }
		else if ($tokens[$firstContent]["line"] === $tokens[$stackPtr]["line"])
		    {
			$error = _("Opening PHP tag must be on a line by itself");
			$phpcsFile->addError($error, $stackPtr, "ContentAfterOpen");
		    }

		// Check the indent of the first line.
		$startColumn   = $tokens[$stackPtr]["column"];
		$contentColumn = $tokens[$firstContent]["column"];
		if ($contentColumn !== $startColumn)
		    {
			$error = _("First line of embedded PHP code must be indented") . " %s " . _("spaces;") . " %s " . _("found");
			$data  = array(
				  $startColumn,
				  $contentColumn,
				 );
			$phpcsFile->addError($error, $firstContent, "Indent", $data);
		    }

		// Check for a blank line at the bottom.
		$lastContent = $phpcsFile->findPrevious(T_WHITESPACE, ($closingTag - 1), ($stackPtr + 1), true);
		if ($tokens[$lastContent]["line"] < ($tokens[$closingTag]["line"] - 1))
		    {
			// Find a token on the blank line to throw the error on.
			$i = $closingTag;
			do
			    {
				$i--;
			    } while ($tokens[$i]["line"] !== ($tokens[$closingTag]["line"] - 1));

			$error = _("Blank line found at end of embedded PHP content");
			$phpcsFile->addError($error, $i, "SpacingAfter");
		    }
		else if ($tokens[$lastContent]["line"] === $tokens[$closingTag]["line"])
		    {
			$error = _("Closing PHP tag must be on a line by itself");
			$phpcsFile->addError($error, $closingTag, "ContentAfterEnd");
		    }
	    } //end _checkMultilineEmbeddedPhp()


	/**
	 * Validates embedded PHP that exists on one line.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $stackPtr  The position of the current token in the stack passed in $tokens.
	 *
	 * @return void
	 */

	private function _validateInlineEmbeddedPhp(File &$phpcsFile, $stackPtr)
	    {
		$tokens = &$phpcsFile->tokens;

		// We only want one line PHP sections, so return if the closing tag is
		// on the next line.
		$closeTag = $phpcsFile->findNext(array(T_CLOSE_TAG), $stackPtr, null, false);
		if ($tokens[$stackPtr]["line"] === $tokens[$closeTag]["line"])
		    {
			$this->_checkOneLineEmbeddedPhp($phpcsFile, $stackPtr, $closeTag);
		    } //end if
	    } //end _validateInlineEmbeddedPhp()


	/**
	 * Check one line of embedded PHP that exists on one line.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $stackPtr  The position of the current token in the stack passed in $tokens.
	 * @param int  $closeTag  Closing tag position
	 *
	 * @return void
	 *
	 * @internalconst T_SEMICOLON T_SEMICOLON token
	 *
	 * @untranslatable Empty
	 * @untranslatable SpacingAfterOpen
	 * @untranslatable NoSemicolon
	 * @untranslatable MultipleStatements
	 * @untranslatable NoSpaceBeforeClose
	 * @untranslatable SpacingBeforeClose
	 * @untranslatable %s
	 */

	private function _checkOneLineEmbeddedPhp(File &$phpcsFile, $stackPtr, $closeTag)
	    {
		$tokens = &$phpcsFile->tokens;

		// Check that there is one, and only one space at the start of the statement.
		$firstContent = $phpcsFile->findNext(array(T_WHITESPACE), ($stackPtr + 1), null, true);

		if ($firstContent === false || $tokens[$firstContent]["code"] === T_CLOSE_TAG)
		    {
			$error = _("Empty embedded PHP tag found");
			$phpcsFile->addError($error, $stackPtr, "Empty");
		    }
		else
		    {
			$leadingSpace = "";
			for ($i = ($stackPtr + 1); $i < $firstContent; $i++)
			    {
				$leadingSpace .= $tokens[$i]["content"];
			    }

			if (strlen($leadingSpace) >= 1)
			    {
				$error = _("Expected 1 space after opening PHP tag;") . " %s " . _("found");
				$data  = array((strlen($leadingSpace) + 1));
				$phpcsFile->addError($error, $stackPtr, "SpacingAfterOpen", $data);
			    }

			$semiColonCount = 0;
			$lastSemiColon  = $stackPtr;
			$semiColon      = $phpcsFile->findNext(array(T_SEMICOLON), ($stackPtr + 1), $closeTag);

			while ($semiColon !== false)
			    {
				$lastSemiColon = $semiColon;
				$semiColonCount++;
				$semiColon = $phpcsFile->findNext(array(T_SEMICOLON), ($semiColon + 1), $closeTag);
			    }

			$semiColon = $lastSemiColon;
			$error     = "";

			// Make sure there is atleast 1 semicolon.
			if ($semiColonCount === 0)
			    {
				$error = _("Inline PHP statement must end with a semicolon");
				$phpcsFile->addError($error, $stackPtr, "NoSemicolon");
			    }
			else
			    {
				// Make sure that there aren't more semicolons than are allowed.
				if ($semiColonCount > 1)
				    {
					$error = _("Inline PHP statement must contain one statement per line;") . " %s " . _("found");
					$data  = array($semiColonCount);
					$phpcsFile->addError($error, $stackPtr, "MultipleStatements", $data);
				    }

				// The statement contains only 1 semicolon, now it must be spaced properly.
				$whitespace = "";
				for ($i = ($semiColon + 1); $i < $closeTag; $i++)
				    {
					if ($tokens[$i]["code"] !== T_WHITESPACE)
					    {
						$error = _("Expected 1 space before closing PHP tag; 0 found");
						$phpcsFile->addError($error, $stackPtr, "NoSpaceBeforeClose");
						$whitespace = " ";
						break;
					    }

					$whitespace .= $tokens[$i]["content"];
				    }

				if (strlen($whitespace) !== 1)
				    {
					$error = _("Expected 1 space before closing PHP tag;") . " %s " . _("found");
					$data  = array(strlen($whitespace));
					$phpcsFile->addError($error, $stackPtr, (strlen($whitespace) === 0) ? "NoSpaceBeforeClose" : "SpacingBeforeClose", $data);
				    }
			    } //end if
		    } //end if
	    } //end _checkOneLineEmbeddedPhp()


    } //end class

?>
