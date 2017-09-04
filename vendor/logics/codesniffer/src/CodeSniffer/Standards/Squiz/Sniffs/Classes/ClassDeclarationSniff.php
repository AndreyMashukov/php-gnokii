<?php

/**
 * PHP version 5
 *
 * @package Logics\BuildTools\CodeSniffer\Squiz
 */

namespace Logics\BuildTools\CodeSniffer\Squiz;

use \Logics\BuildTools\CodeSniffer\File;
use \Logics\BuildTools\CodeSniffer\PSR2\ClassDeclarationSniff as PSR2_ClassDeclarationSniff;

/**
 * Class Declaration Test.
 *
 * Checks the declaration of the class and its inheritance is correct.
 *
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @author    Marc McIntyre <mmcintyre@squiz.net>
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2006-2012 Squiz Pty Ltd (ABN 77 084 670 600)
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 * @version   SVN: $Date: 2016-08-16 23:39:01 +0900 (Tue, 16 Aug 2016) $ $Revision: 26 $
 * @link      $HeadURL: https://open.logics.net.au/buildtools/codesniffer/tags/0.1.3/src/CodeSniffer/Standards/Squiz/Sniffs/Classes/ClassDeclarationSniff.php $
 */

class ClassDeclarationSniff extends PSR2_ClassDeclarationSniff
    {

	/**
	 * Processes this test, when one of its tokens is encountered.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $stackPtr  The position of the current token in the stack passed in $tokens.
	 *
	 * @return void
	 *
	 * @untranslatable MultipleClasses
	 */

	public function process(File $phpcsFile, $stackPtr)
	    {
		// We want all the errors from the PSR2 standard, plus some of our own.
		parent::process($phpcsFile, $stackPtr);

		$tokens = &$phpcsFile->tokens;

		// Check that this is the only class or interface in the file.
		$nextClass = $phpcsFile->findNext(array(T_CLASS, T_INTERFACE), ($stackPtr + 1));
		if ($nextClass !== false)
		    {
			// We have another, so an error is thrown.
			$error = _("Only one interface or class is allowed in a file");
			$phpcsFile->addError($error, $nextClass, "MultipleClasses");
		    }
	    } //end process()


	/**
	 * Processes the opening section of a class declaration.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $stackPtr  The position of the current token in the stack passed in $tokens.
	 *
	 * @return void
	 *
	 * @untranslatable SpaceBeforeKeyword
	 * @untranslatable %s
	 */

	public function processOpen(File &$phpcsFile, $stackPtr)
	    {
		parent::processOpen($phpcsFile, $stackPtr);

		$tokens = &$phpcsFile->tokens;

		if ($tokens[($stackPtr - 1)]["code"] === T_WHITESPACE)
		    {
			$prevContent = $tokens[($stackPtr - 1)]["content"];
			if ($prevContent !== $phpcsFile->eolChar)
			    {
				$blankSpace = substr($prevContent, strpos($prevContent, $phpcsFile->eolChar));
				$spaces     = strlen($blankSpace);

				if (in_array($tokens[($stackPtr - 2)]["code"], array(T_ABSTRACT, T_FINAL)) === false)
				    {
					if ($spaces !== 0)
					    {
						$type  = strtolower($tokens[$stackPtr]["content"]);
						$error = _("Expected 0 spaces before") . " %s " . _("keyword;") . " %s " . _("found");
						$data  = array(
							  $type,
							  $spaces,
							 );
						$phpcsFile->addError($error, $stackPtr, "SpaceBeforeKeyword", $data);
					    }
				    }
			    }
		    } //end if
	    } //end processOpen()


	/**
	 * Processes the closing section of a class declaration.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $stackPtr  The position of the current token in the stack passed in $tokens.
	 *
	 * @return void
	 *
	 * @untranslatable EndFileAfterCloseBrace
	 * @untranslatable NoNewlineAfterCloseBrace
	 * @untranslatable NewlinesAfterCloseBrace
	 * @untranslatable CloseBraceSameLine
	 * @untranslatable %s
	 */

	public function processClose(File &$phpcsFile, $stackPtr)
	    {
		$tokens = &$phpcsFile->tokens;

		$closeBrace = $tokens[$stackPtr]["scope_closer"];
		if ($tokens[($closeBrace - 1)]["code"] === T_WHITESPACE)
		    {
			$prevContent = $tokens[($closeBrace - 1)]["content"];
			if ($prevContent !== $phpcsFile->eolChar)
			    {
				$blankSpace = substr($prevContent, strpos($prevContent, $phpcsFile->eolChar));
				$spaces     = strlen($blankSpace);
				if ($spaces !== 0)
				    {
					$this->_generateSpaceError($phpcsFile, $closeBrace, $spaces);
				    }
			    }
		    } //end if

		// Check that the closing brace has one blank line after it.
		$nextContent = $phpcsFile->findNext(array(T_WHITESPACE, T_COMMENT), ($closeBrace + 1), null, true);
		if ($nextContent === false)
		    {
			// No content found, so we reached the end of the file.
			// That means there was no closing tag either.
			$error = _("Closing brace of a") . " %s " . _("must be followed by a blank line and then a closing PHP tag");
			$data  = array($tokens[$stackPtr]["content"]);
			$phpcsFile->addError($error, $closeBrace, "EndFileAfterCloseBrace", $data);
		    }
		else
		    {
			$nextLine  = $tokens[$nextContent]["line"];
			$braceLine = $tokens[$closeBrace]["line"];
			if ($braceLine === $nextLine)
			    {
				$error = _("Closing brace of a") . " %s " . _("must be followed by a single blank line");
				$data  = array($tokens[$stackPtr]["content"]);
				$phpcsFile->addError($error, $closeBrace, "NoNewlineAfterCloseBrace", $data);
			    }
			else if ($nextLine !== ($braceLine + 2))
			    {
				$difference = ($nextLine - $braceLine - 1);
				$error      = _("Closing brace of a") . " %s " . _("must be followed by a single blank line; found") . " %s";
				$data       = array(
					       $tokens[$stackPtr]["content"],
					       $difference,
					      );
				$phpcsFile->addError($error, $closeBrace, "NewlinesAfterCloseBrace", $data);
			    }
		    } //end if

		// Check the closing brace is on it's own line, but allow
		// for comments like "//end class".
		$nextContent = $phpcsFile->findNext(T_COMMENT, ($closeBrace + 1), null, true);
		if ($tokens[$nextContent]["content"] !== $phpcsFile->eolChar && $tokens[$nextContent]["line"] === $tokens[$closeBrace]["line"])
		    {
			$type  = strtolower($tokens[$stackPtr]["content"]);
			$error = _("Closing") . " %s " . _("brace must be on a line by itself");
			$data  = array($tokens[$stackPtr]["content"]);
			$phpcsFile->addError($error, $closeBrace, "CloseBraceSameLine", $data);
		    }
	    } //end processClose()


	/**
	 * Generate space error
	 *
	 * @param File $phpcsFile  The file being scanned.
	 * @param int  $closeBrace The position of the close brace in the stack passed in $tokens.
	 * @param int  $spaces     Count spaces
	 *
	 * @return void
	 *
	 * @untranslatable SpaceBeforeCloseBrace
	 * @untranslatable NewLineBeforeCloseBrace
	 * @untranslatable %s
	 */

	private function _generateSpaceError(File &$phpcsFile, $closeBrace, $spaces)
	    {
		$tokens = &$phpcsFile->tokens;
		if ($tokens[($closeBrace - 1)]["line"] !== $tokens[$closeBrace]["line"])
		    {
			$error = _("Expected 0 spaces before closing brace; newline found");
			$phpcsFile->addError($error, $closeBrace, "NewLineBeforeCloseBrace");
		    }
		else
		    {
			$error = _("Expected 0 spaces before closing brace;") . " %s " . _("found");
			$phpcsFile->addError($error, $closeBrace, "SpaceBeforeCloseBrace", array($spaces));
		    }
	    } //end _generateSpaceError()


    } //end class

?>