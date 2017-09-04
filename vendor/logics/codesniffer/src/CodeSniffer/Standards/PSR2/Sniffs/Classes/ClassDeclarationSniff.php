<?php

/**
 * PHP version 5
 *
 * @package Logics\BuildTools\CodeSniffer\PSR2
 */

namespace Logics\BuildTools\CodeSniffer\PSR2;

use \Logics\BuildTools\CodeSniffer\File;
use \Logics\BuildTools\CodeSniffer\PEAR\ClassDeclarationSniff as PEAR_ClassDeclarationSniff;
use \Logics\BuildTools\CodeSniffer\Tokens;

/**
 * Class Declaration Test.
 *
 * Checks the declaration of the class and its inheritance is correct.
 *
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2006-2012 Squiz Pty Ltd (ABN 77 084 670 600)
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 * @version   SVN: $Date: 2016-08-16 23:39:01 +0900 (Tue, 16 Aug 2016) $ $Revision: 26 $
 * @link      $HeadURL: https://open.logics.net.au/buildtools/codesniffer/tags/0.1.3/src/CodeSniffer/Standards/PSR2/Sniffs/Classes/ClassDeclarationSniff.php $
 */

class ClassDeclarationSniff extends PEAR_ClassDeclarationSniff
    {

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
		// We want all the errors from the PEAR standard, plus some of our own.
		parent::process($phpcsFile, $stackPtr);
		$this->processOpen($phpcsFile, $stackPtr);
		$this->processClose($phpcsFile, $stackPtr);
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
	 * @untranslatable SpaceAfterKeyword
	 * @untranslatable SpaceAfterName
	 * @untranslatable extends
	 * @untranslatable implements
	 * @untranslatable T_
	 * @untranslatable Line
	 * @untranslatable %s
	 * @untranslatable SpaceBefore
	 */

	public function processOpen(File &$phpcsFile, $stackPtr)
	    {
		$tokens = &$phpcsFile->tokens;

		// Check alignment of the keyword and braces.
		if ($tokens[($stackPtr - 1)]["code"] === T_WHITESPACE)
		    {
			$prevContent = $tokens[($stackPtr - 1)]["content"];
			if ($prevContent !== $phpcsFile->eolChar)
			    {
				$blankSpace = substr($prevContent, strpos($prevContent, $phpcsFile->eolChar));
				$spaces     = strlen($blankSpace);

				if (in_array($tokens[($stackPtr - 2)]["code"], array(T_ABSTRACT, T_FINAL)) === true && $spaces !== 1)
				    {
					$type        = strtolower($tokens[$stackPtr]["content"]);
					$prevContent = strtolower($tokens[($stackPtr - 2)]["content"]);
					$error       = _("Expected 1 space between") . " %s " . _("and") . " %s " . _("keywords;") . " %s " . _("found");
					$data        = array(
							$prevContent,
							$type,
							$spaces,
						       );
					$phpcsFile->addError($error, $stackPtr, "SpaceBeforeKeyword", $data);
				    }
			    }
		    } //end if

		// We'll need the indent of the class/interface keyword for later.
		$classIndent = (strpos($tokens[($stackPtr - 1)]["content"], $phpcsFile->eolChar) === false) ? strlen($tokens[($stackPtr - 1)]["content"]) : 0;

		$keyword      = $stackPtr;
		$openingBrace = $tokens[$stackPtr]["scope_opener"];
		$className    = $phpcsFile->findNext(T_STRING, $stackPtr);

		$classOrInterface = strtolower($tokens[$keyword]["content"]);

		// Spacing of the keyword.
		$gap = $tokens[($stackPtr + 1)]["content"];
		if (strlen($gap) !== 1)
		    {
			$found = strlen($gap);
			$error = _("Expected 1 space between") . " %s " . _("keyword and") . " %s " . _("name;") . " %s " . _("found");
			$data  = array(
				  $classOrInterface,
				  $classOrInterface,
				  $found,
				 );
			$phpcsFile->addError($error, $stackPtr, "SpaceAfterKeyword", $data);
		    }

		// Check after the class/interface name.
		$gap = $tokens[($className + 1)]["content"];
		if (strlen($gap) !== 1)
		    {
			$found = strlen($gap);
			$error = _("Expected 1 space after") . " %s " . _("name;") . " %s " . _("found");
			$data  = array(
				  $classOrInterface,
				  $found,
				 );
			$phpcsFile->addError($error, $stackPtr, "SpaceAfterName", $data);
		    }

		// Check positions of the extends and implements keywords.
		foreach (array("extends", "implements") as $keywordType)
		    {
			$keyword = $phpcsFile->findNext(constant("T_" . strtoupper($keywordType)), ($stackPtr + 1), $openingBrace);
			if ($keyword !== false)
			    {
				if ($tokens[$keyword]["line"] !== $tokens[$stackPtr]["line"])
				    {
					$error = _("The ") . $keywordType . _(" keyword must be on the same line as the") . " %s " . _("name");
					$data  = array($classOrInterface);
					$phpcsFile->addError($error, $keyword, ucfirst($keywordType) . "Line", $data);
				    }
				else
				    {
					// Check the whitespace before. Whitespace after is checked
					// later by looking at the whitespace before the first class name
					// in the list.
					$gap = strlen($tokens[($keyword - 1)]["content"]);
					if ($gap !== 1)
					    {
						$error = _("Expected 1 space before ") . $keywordType . _(" keyword;") . " %s " . _("found");
						$data  = array($gap);
						$phpcsFile->addError($error, $keyword, "SpaceBefore" . ucfirst($keywordType), $data);
					    }
				    }
			    } //end if
		    } //end foreach

		$this->_checkImplements($phpcsFile, $tokens, $stackPtr, $classIndent, $className, $openingBrace);
	    } //end processOpen()


	/**
	 * Check each of the extends/implements class names. If the implements
	 * keywords is the last content on the line, it means we need to check for
	 * the multi-line implements format, so we do not include the class names
	 * from the implements list in the following check.
	 *
	 * @param File  $phpcsFile    The file being scanned.
	 * @param array $tokens       All tokens
	 * @param int   $stackPtr     The position of the current token in the stack passed in $tokens.
	 * @param int   $classIndent  Class indentation
	 * @param int   $className    Class name position
	 * @param int   $openingBrace Opening brace position
	 *
	 * @return void
	 *
	 * @internalconst T_COMMA T_COMMA token
	 *
	 * @untranslatable \"%s\"
	 * @untranslatable %s
	 * @untranslatable SpaceBeforeComma
	 */

	private function _checkImplements(File &$phpcsFile, array &$tokens, $stackPtr, $classIndent, $className, $openingBrace)
	    {
		$implements          = $phpcsFile->findNext(T_IMPLEMENTS, ($stackPtr + 1), $openingBrace);
		$multiLineImplements = false;
		if ($implements !== false)
		    {
			$next = $phpcsFile->findNext(Tokens::$emptyTokens, ($implements + 1), $openingBrace, true);
			if ($tokens[$next]["line"] > $tokens[$implements]["line"])
			    {
				$multiLineImplements = true;
			    }
		    }

		$classNames = array();
		$find       = array(
			       T_STRING,
			       T_IMPLEMENTS,
			      );
		$nextClass  = $phpcsFile->findNext($find, ($className + 2), ($openingBrace - 1));
		while ($nextClass !== false)
		    {
			$classNames[] = $nextClass;
			$nextClass    = $phpcsFile->findNext($find, ($nextClass + 1), ($openingBrace - 1));
		    }

		$classCount         = count($classNames);
		$checkingImplements = false;
		$nextComma          = false;
		foreach ($classNames as $i => $className)
		    {
			if ($tokens[$className]["code"] === T_IMPLEMENTS)
			    {
				$checkingImplements = true;
			    }
			else
			    {
				$this->_checkImplementsSpacing(
				    $phpcsFile, $tokens, $classIndent, $className, $nextComma, $implements, $checkingImplements, $multiLineImplements
				);

				if ($tokens[($className + 1)]["code"] !== T_NS_SEPARATOR && $tokens[($className + 1)]["code"] !== T_COMMA)
				    {
					if ($i !== ($classCount - 1))
					    {
						// This is not the last class name, and the comma
						// is not where we expect it to be.
						if ($tokens[($className + 2)]["code"] !== T_IMPLEMENTS)
						    {
							$error = _("Expected 0 spaces between") . " \"%s\" " . _("and comma;") . " %s " . _("found");
							$data  = array(
								  $tokens[$className]["content"],
								  strlen($tokens[($className + 1)]["content"]),
								 );
							$phpcsFile->addError($error, $className, "SpaceBeforeComma", $data);
						    }
					    }

					$nextComma = $phpcsFile->findNext(T_COMMA, $className);
				    }
				else
				    {
					$nextComma = ($className + 1);
				    } //end if
			    } //end if
		    } //end foreach
	    } //end _checkImplements()


	/**
	 * Check spacing of "implenets" keyword arguments
	 *
	 * @param File  $phpcsFile           The file being scanned.
	 * @param array $tokens              All tokens
	 * @param int   $classIndent         Class indentation
	 * @param int   $className           Class name position
	 * @param int   $nextComma           Next comma position
	 * @param int   $implements          Implements statement position
	 * @param bool  $checkingImplements  True if we are in "implements" block
	 * @param bool  $multiLineImplements True if "implements" block uses multiline syntax
	 *
	 * @return void
	 *
	 * @internalconst T_COMMA T_COMMA token
	 *
	 * @untranslatable InterfaceSameLine
	 * @untranslatable %s
	 * @untranslatable InterfaceWrongIndent
	 * @untranslatable \"%s\";
	 * @untranslatable NoSpaceBeforeName
	 * @untranslatable \"%s\"; %s
	 * @untranslatable SpaceBeforeName
	 */

	private function _checkImplementsSpacing(File &$phpcsFile, array &$tokens, $classIndent, $className,
						 $nextComma, $implements, $checkingImplements, $multiLineImplements)
	    {
		if ($checkingImplements === true && $multiLineImplements === true &&
		    ($tokens[($className - 1)]["code"] !== T_NS_SEPARATOR || $tokens[($className - 2)]["code"] !== T_STRING))
		    {
			$prev = $phpcsFile->findPrevious(array(T_NS_SEPARATOR, T_WHITESPACE), ($className - 1), $implements, true);

			if ($tokens[$prev]["line"] !== ($tokens[$className]["line"] - 1))
			    {
				$error = _("Only one interface may be specified per line in a multi-line implements declaration");
				$phpcsFile->addError($error, $className, "InterfaceSameLine");
			    }
			else
			    {
				$prev     = $phpcsFile->findPrevious(T_WHITESPACE, ($className - 1), $implements);
				$found    = strlen($tokens[$prev]["content"]);
				$expected = ($classIndent + $this->indent);
				if ($found !== $expected)
				    {
					$error = _("Expected") . " %s " . _("spaces before interface name;") . " %s " . _("found");
					$data  = array(
						  $expected,
						  $found,
						 );
					$phpcsFile->addError($error, $className, "InterfaceWrongIndent", $data);
				    }
			    }
		    }
		else if ($tokens[($className - 1)]["code"] !== T_NS_SEPARATOR || $tokens[($className - 2)]["code"] !== T_STRING)
		    {
			if ($tokens[($className - 1)]["code"] === T_COMMA ||
			    ($tokens[($className - 1)]["code"] === T_NS_SEPARATOR && $tokens[($className - 2)]["code"] === T_COMMA))
			    {
				$error = _("Expected 1 space before") . " \"%s\"; " . _("0 found");
				$data  = array($tokens[$className]["content"]);
				$phpcsFile->addError($error, ($nextComma + 1), "NoSpaceBeforeName", $data);
			    }
			else
			    {
				$spaceBefore = strlen($tokens[($className - (($tokens[($className - 1)]["code"] === T_NS_SEPARATOR) ? 2 : 1))]["content"]);

				if ($spaceBefore !== 1)
				    {
					$error = _("Expected 1 space before") . " \"%s\"; %s " . _("found");
					$data  = array(
						  $tokens[$className]["content"],
						  $spaceBefore,
						 );
					$phpcsFile->addError($error, $className, "SpaceBeforeName", $data);
				    }
			    } //end if
		    } //end if
	    } //end _checkImplementsSpacing()


	/**
	 * Processes the closing section of a class declaration.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $stackPtr  The position of the current token in the stack passed in $tokens.
	 *
	 * @return void
	 *
	 * @untranslatable %s
	 * @untranslatable CloseBraceAfterBody
	 * @untranslatable CloseBraceSameLine
	 */

	public function processClose(File &$phpcsFile, $stackPtr)
	    {
		$tokens = &$phpcsFile->tokens;

		// Check that the closing brace comes right after the code body.
		$closeBrace  = $tokens[$stackPtr]["scope_closer"];
		$prevContent = $phpcsFile->findPrevious(T_WHITESPACE, ($closeBrace - 1), null, true);
		if ($tokens[$prevContent]["line"] !== ($tokens[$closeBrace]["line"] - 1))
		    {
			$error = _("The closing brace for the") . " %s " . _("must go on the next line after the body");
			$data  = array($tokens[$stackPtr]["content"]);
			$phpcsFile->addError($error, $closeBrace, "CloseBraceAfterBody", $data);
		    }

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


    } //end class

?>
