<?php

/**
 * PHP version 5.6
 *
 * @package Logics\BuildTools\CodeSniffer\Beauty
 */

namespace Logics\BuildTools\CodeSniffer\Beauty;

use \Logics\BuildTools\CodeSniffer\File;
use \Logics\BuildTools\CodeSniffer\Sniff;

/**
 * Squiz_Sniffs_Commenting_ClosingDeclarationCommentSniff.
 *
 * Checks the //end ... comments on classes, interfaces and functions.
 *
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @author    Marc McIntyre <mmcintyre@squiz.net>
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2006-2012 Squiz Pty Ltd (ABN 77 084 670 600)
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   http://matrix.squiz.net/developer/tools/php_cs/licence BSD Licence
 * @version   SVN: $Date: 2016-08-16 23:39:01 +0900 (Tue, 16 Aug 2016) $ $Revision: 26 $
 * @link      $HeadURL: https://open.logics.net.au/buildtools/codesniffer/tags/0.1.3/src/CodeSniffer/Standards/Beauty/Sniffs/Commenting/ClosingDeclarationCommentSniff.php $
 */

class ClosingDeclarationCommentSniff implements Sniff
    {

	/**
	 * Returns an array of tokens this test wants to listen for.
	 *
	 * @return array
	 */

	public function register()
	    {
		return array(
			T_FUNCTION,
			T_CLASS,
			T_TRAIT,
			T_INTERFACE,
		       );
	    } //end register()


	/**
	 * Processes this test, when one of its tokens is encountered.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $stackPtr  The position of the current token in the stack passed in $tokens
	 *
	 * @return void
	 *
	 * @untranslatable //end
	 * @untranslatable //end class
	 * @untranslatable //end trait
	 * @untranslatable //end interface
	 */

	public function process(File $phpcsFile, $stackPtr)
	    {
		$tokens = &$phpcsFile->tokens;

		if ($tokens[$stackPtr]["code"] === T_FUNCTION)
		    {
			$methodProps = $phpcsFile->getMethodProperties($stackPtr);

			// Abstract methods and closures do not require a closing comment.
			// If this function is in an interface then we don't require
			// a closing comment.
			if ($methodProps["is_abstract"] === true || $methodProps["is_closure"] === true || $phpcsFile->hasCondition($stackPtr, T_INTERFACE) === true)
			    {
				$comment = "";
			    }
			else if (isset($tokens[$stackPtr]["scope_closer"]) === false)
			    {
				$phpcsFile->addWarning(_("Possible parse error: non-abstract method defined as abstract"), $stackPtr);
				$comment = "";
			    }
			else
			    {
				$decName = $phpcsFile->getDeclarationName($stackPtr);
				$comment = "//end " . $decName . "()";
			    }
		    }
		else if ($tokens[$stackPtr]["code"] === T_CLASS)
		    {
			$comment = "//end class";
		    }
		else if ($tokens[$stackPtr]["code"] === T_TRAIT)
		    {
			$comment = "//end trait";
		    }
		else
		    {
			$comment = "//end interface";
		    } //end if

		if ($comment !== "")
		    {
			if (isset($tokens[$stackPtr]["scope_closer"]) === false)
			    {
				$phpcsFile->addWarning(
				    _("Possible parse error:") . " " . $tokens[$stackPtr]["content"] . " " . _("missing opening or closing brace"), $stackPtr
				);
			    }
			else
			    {
				$closingBracket = $tokens[$stackPtr]["scope_closer"];

				if ($closingBracket !== null &&
				    ((isset($tokens[($closingBracket + 2)]) === false || $tokens[($closingBracket + 2)]["code"] !== T_COMMENT) ||
				     (rtrim($tokens[($closingBracket + 2)]["content"]) !== $comment)))
				    {
					$phpcsFile->addError(_("Expected") . " " . $comment, $closingBracket);
				    }
			    }
		    }
	    } //end process()


    } //end class

?>
