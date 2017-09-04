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
 * ClosingDeclarationCommentSniff
 *
 * Checks the //end ... comments on classes, interfaces and functions.
 *
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @author    Marc McIntyre <mmcintyre@squiz.net>
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2006-2012 Squiz Pty Ltd (ABN 77 084 670 600)
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 * @version   SVN: $Date: 2016-08-16 23:39:01 +0900 (Tue, 16 Aug 2016) $ $Revision: 26 $
 * @link      $HeadURL: https://open.logics.net.au/buildtools/codesniffer/tags/0.1.3/src/CodeSniffer/Standards/Squiz/Sniffs/Commenting/ClosingDeclarationCommentSniff.php $
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
			T_INTERFACE,
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
	 * @untranslatable Abstract
	 * @untranslatable //end
	 * @untranslatable //end class
	 * @untranslatable //end interface
	 * @untranslatable %s
	 * @untranslatable MissingBrace
	 * @untranslatable Missing
	 * @untranslatable Incorrect
	 */

	public function process(File $phpcsFile, $stackPtr)
	    {
		$tokens = &$phpcsFile->tokens;

		if ($tokens[$stackPtr]["code"] === T_FUNCTION)
		    {
			$methodProps = $phpcsFile->getMethodProperties($stackPtr);

			// Abstract methods do not require a closing comment.
			// Closures do not require a closing comment.
			// If this function is in an interface then we don't require a closing comment.
			if ($methodProps["is_abstract"] === true || $methodProps["is_closure"] === true ||
			    $phpcsFile->hasCondition($stackPtr, T_INTERFACE) === true)
			    {
				$comment = "";
			    }
			else if (isset($tokens[$stackPtr]["scope_closer"]) === false)
			    {
				$error = _("Possible parse error: non-abstract method defined as abstract");
				$phpcsFile->addWarning($error, $stackPtr, "Abstract");
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
		else
		    {
			$comment = "//end interface";
		    } //end if

		if ($comment !== "")
		    {
			if (isset($tokens[$stackPtr]["scope_closer"]) === false)
			    {
				$error = _("Possible parse error:") . " %s " . _("missing opening or closing brace");
				$data  = array($tokens[$stackPtr]["content"]);
				$phpcsFile->addWarning($error, $stackPtr, "MissingBrace", $data);
			    }
			else
			    {
				$closingBracket = $tokens[$stackPtr]["scope_closer"];

				// Possible inline structure. Other tests will handle it.
				if ($closingBracket !== null)
				    {
					$error = _("Expected") . " " . $comment;
					if (isset($tokens[($closingBracket + 1)]) === false || $tokens[($closingBracket + 1)]["code"] !== T_COMMENT)
					    {
						$phpcsFile->addError($error, $closingBracket, "Missing");
					    }
					else if (rtrim($tokens[($closingBracket + 1)]["content"]) !== $comment)
					    {
						$phpcsFile->addError($error, $closingBracket, "Incorrect");
					    }
				    }
			    } //end if
		    } //end if
	    } //end process()


    } //end class

?>