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
 * Squiz_Sniffs_Commenting_DocCommentAlignmentSniff.
 *
 * Tests that the stars in a doc comment align correctly.
 *
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @author    Marc McIntyre <mmcintyre@squiz.net>
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2006-2012 Squiz Pty Ltd (ABN 77 084 670 600)
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 * @version   SVN: $Date: 2016-08-16 23:39:01 +0900 (Tue, 16 Aug 2016) $ $Revision: 26 $
 * @link      $HeadURL: https://open.logics.net.au/buildtools/codesniffer/tags/0.1.3/src/CodeSniffer/Standards/Squiz/Sniffs/Commenting/DocCommentAlignmentSniff.php $
 *
 * @untranslatable PHP
 * @untranslatable JS
 */

class DocCommentAlignmentSniff implements Sniff
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
	 *
	 * @internalconst T_DOC_COMMENT_OPEN_TAG T_DOC_COMMENT_OPEN_TAG token
	 */

	public function register()
	    {
		return array(T_DOC_COMMENT_OPEN_TAG);
	    } //end register()


	/**
	 * Processes this test, when one of its tokens is encountered.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $stackPtr  The position of the current token in the stack passed in $tokens.
	 *
	 * @return void
	 *
	 * @internalconst T_EQUAL                  T_EQUAL token
	 * @internalconst T_PROPERTY               T_PROPERTY token
	 * @internalconst T_OBJECT                 T_OBJECT token
	 * @internalconst T_PROTOTYPE              T_PROTOTYPE token
	 * @internalconst T_DOC_COMMENT_CLOSE_TAG  T_DOC_COMMENT_CLOSE_TAG token
	 * @internalconst T_DOC_COMMENT_STAR       T_DOC_COMMENT_STAR token
	 * @internalconst T_DOC_COMMENT_WHITESPACE T_DOC_COMMENT_WHITESPACE token
	 * @internalconst T_DOC_COMMENT_TAG        T_DOC_COMMENT_TAG token
	 *
	 * @untranslatable %s
	 * @untranslatable SpaceBeforeStar
	 * @untranslatable NoSpaceAfterStar
	 * @untranslatable SpaceAfterStar
	 */

	public function process(File $phpcsFile, $stackPtr)
	    {
		$tokens = &$phpcsFile->tokens;

		// We are only interested in function/class/interface doc block comments.
		$ignore = Tokens::$emptyTokens;
		if ($phpcsFile->tokenizerType === "JS")
		    {
			$ignore[] = T_EQUAL;
			$ignore[] = T_STRING;
			$ignore[] = T_OBJECT_OPERATOR;
		    }

		$nextToken = $phpcsFile->findNext($ignore, ($stackPtr + 1), null, true);
		$ignore    = array(
			      T_CLASS     => true,
			      T_INTERFACE => true,
			      T_FUNCTION  => true,
			      T_PUBLIC    => true,
			      T_PRIVATE   => true,
			      T_PROTECTED => true,
			      T_STATIC    => true,
			      T_ABSTRACT  => true,
			      T_PROPERTY  => true,
			      T_OBJECT    => true,
			      T_PROTOTYPE => true,
			     );

		if (isset($ignore[$tokens[$nextToken]["code"]]) === false)
		    {
			// Could be a file comment.
			$prevToken = $phpcsFile->findPrevious(Tokens::$emptyTokens, ($stackPtr - 1), null, true);
			if ($tokens[$prevToken]["code"] !== T_OPEN_TAG)
			    {
				return;
			    }
		    }

		// There must be one space after each star (unless it is an empty comment line) and all the stars must be aligned correctly.
		$requiredColumn = ($tokens[$stackPtr]["column"] + 1);
		$endComment     = $tokens[$stackPtr]["comment_closer"];
		for ($i = ($stackPtr + 1); $i <= $endComment; $i++)
		    {
			if ($tokens[$i]["code"] !== T_DOC_COMMENT_STAR && $tokens[$i]["code"] !== T_DOC_COMMENT_CLOSE_TAG)
			    {
				continue;
			    }

			if ($tokens[$i]["code"] === T_DOC_COMMENT_CLOSE_TAG)
			    {
				// Can"t process the close tag if it is not the first thing on the line.
				$prev = $phpcsFile->findPrevious(T_DOC_COMMENT_WHITESPACE, ($i - 1), $stackPtr, true);
				if ($tokens[$prev]["line"] === $tokens[$i]["line"])
				    {
					continue;
				    }
			    }

			if ($tokens[$i]["column"] !== $requiredColumn)
			    {
				$error = _("Expected ") . "%s" . _(" space(s) before asterisk; ") . "%s" . _(" found");
				$data  = array(
					  ($requiredColumn - 1),
					  ($tokens[$i]["column"] - 1),
					 );
				$phpcsFile->addError($error, $i, "SpaceBeforeStar", $data);
			    }

			if ($tokens[$i]["code"] !== T_DOC_COMMENT_STAR)
			    {
				continue;
			    }

			if ($tokens[($i + 2)]["line"] !== $tokens[$i]["line"])
			    {
				// Line is empty.
				continue;
			    }

			if ($tokens[($i + 1)]["code"] !== T_DOC_COMMENT_WHITESPACE)
			    {
				$error = _("Expected 1 space after asterisk; 0 found");
				$phpcsFile->addError($error, $i, "NoSpaceAfterStar");
			    }
			else if ($tokens[($i + 2)]["code"] === T_DOC_COMMENT_TAG && $tokens[($i + 1)]["content"] !== " ")
			    {
				$error = _("Expected 1 space after asterisk; ") . "%s" . _(" found");
				$data  = array(strlen($tokens[($i + 1)]["content"]));
				$phpcsFile->addError($error, $i, "SpaceAfterStar", $data);
			    }
		    } //end for
	    } //end process()


    } //end class

?>
