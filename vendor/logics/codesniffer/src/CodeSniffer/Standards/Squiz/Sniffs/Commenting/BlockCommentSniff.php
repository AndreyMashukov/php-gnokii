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
 * Squiz_Sniffs_Commenting_BlockCommentSniff.
 *
 * Verifies that block comments are used appropriately.
 *
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @author    Marc McIntyre <mmcintyre@squiz.net>
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2006-2012 Squiz Pty Ltd (ABN 77 084 670 600)
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 * @version   SVN: $Date: 2016-08-16 23:39:01 +0900 (Tue, 16 Aug 2016) $ $Revision: 26 $
 * @link      $HeadURL: https://open.logics.net.au/buildtools/codesniffer/tags/0.1.3/src/CodeSniffer/Standards/Squiz/Sniffs/Commenting/BlockCommentSniff.php $
 */

class BlockCommentSniff implements Sniff
    {

	/**
	 * Returns an array of tokens this test wants to listen for.
	 *
	 * @return array
	 *
	 * @internalconst T_DOC_COMMENT_OPEN_TAG T_DOC_COMMENT_OPEN_TAG token
	 */

	public function register()
	    {
		return array(
			T_COMMENT,
			T_DOC_COMMENT_OPEN_TAG,
		       );
	    } //end register()


	/**
	 * Processes this test, when one of its tokens is encountered.
	 *
	 * @param File $phpcsFile The current file being scanned.
	 * @param int  $stackPtr  The position of the current token in the stack passed in $tokens.
	 *
	 * @return void
	 *
	 * @internalconst T_DOC_COMMENT_OPEN_TAG  T_DOC_COMMENT_OPEN_TAG token
	 * @internalconst T_DOC_COMMENT_CLOSE_TAG T_DOC_COMMENT_CLOSE_TAG token
	 *
	 * @untranslatable SingleLine
	 * @untranslatable Empty
	 * @untranslatable WrongStart
	 * @untranslatable WrongEnd
	 * @untranslatable NoNewLine
	 * @untranslatable HasEmptyLine
	 * @untranslatable s
	 * @untranslatable %s
	 * @untranslatable FirstLineIndent
	 * @untranslatable NoCapital
	 * @untranslatable LineIndent
	 * @untranslatable LastLineIndent
	 * @untranslatable HasEmptyLineBefore
	 * @untranslatable NoEmptyLineBefore
	 * @untranslatable NoEmptyLineAfter
	 */

	public function process(File $phpcsFile, $stackPtr)
	    {
		$tokens = &$phpcsFile->tokens;

		// If it's an inline comment, return.
		if (substr($tokens[$stackPtr]["content"], 0, 2) !== "/*")
		    {
			return;
		    }

		// If this is a function/class/interface doc block comment, skip it.
		// We are only interested in inline doc block comments.
		if ($tokens[$stackPtr]["code"] === T_DOC_COMMENT_OPEN_TAG)
		    {
			$nextToken = $phpcsFile->findNext(Tokens::$emptyTokens, ($stackPtr + 1), null, true);
			$ignore    = array(
				      T_CLASS     => true,
				      T_INTERFACE => true,
				      T_TRAIT     => true,
				      T_FUNCTION  => true,
				      T_PUBLIC    => true,
				      T_PRIVATE   => true,
				      T_FINAL     => true,
				      T_PROTECTED => true,
				      T_STATIC    => true,
				      T_ABSTRACT  => true,
				      T_CONST     => true,
				      T_VAR       => true,
				     );

			if (isset($ignore[$tokens[$nextToken]["code"]]) === true)
			    {
				return;
			    }

			$prevToken = $phpcsFile->findPrevious(Tokens::$emptyTokens, ($stackPtr - 1), null, true);
			if ($tokens[$prevToken]["code"] === T_OPEN_TAG)
			    {
				return;
			    }

			$error = _("Block comments must be started with /*");
			$phpcsFile->addError($error, $stackPtr, "WrongStart");

			$end = $tokens[$stackPtr]["comment_closer"];
			if ($tokens[$end]["content"] !== "*/")
			    {
				$error = _("Block comments must be ended with */");
				$phpcsFile->addError($error, $end, "WrongEnd");
			    }

			return;
		    } //end if

		$commentLines  = array($stackPtr);
		$nextComment   = $phpcsFile->findNext(T_WHITESPACE, ($stackPtr + 1), null, true);
		$lastLine      = $tokens[$stackPtr]["line"];
		$commentString = $tokens[$stackPtr]["content"];

		// Construct the comment into an array.
		while ($nextComment !== false)
		    {
			if ($tokens[$nextComment]["code"] !== $tokens[$stackPtr]["code"])
			    {
				// Found the next bit of code.
				break;
			    }

			if (($tokens[$nextComment]["line"] - 1) !== $lastLine)
			    {
				// Not part of the block.
				break;
			    }

			$lastLine       = $tokens[$nextComment]["line"];
			$commentLines[] = $nextComment;
			$commentString .= $tokens[$nextComment]["content"];
			if ($tokens[$nextComment]["code"] === T_DOC_COMMENT_CLOSE_TAG)
			    {
				break;
			    }

			$nextComment = $phpcsFile->findNext(T_WHITESPACE, ($nextComment + 1), null, true);
		    } //end while

		$commentText = str_replace($phpcsFile->eolChar, "", $commentString);
		$commentText = trim($commentText, "/* ");
		if ($commentText === "")
		    {
			$error = _("Empty block comment not allowed");
			$phpcsFile->addError($error, $stackPtr, "Empty");

			return;
		    }

		if (count($commentLines) === 1)
		    {
			$error = _("Single line block comment not allowed; use inline (\"// text\") comment instead");
			$phpcsFile->addError($error, $stackPtr, "SingleLine");

			return;
		    }

		$content = trim($tokens[$stackPtr]["content"]);
		if ($content !== "/*" && $content !== "/**")
		    {
			$error = _("Block comment text must start on a new line");
			$phpcsFile->addError($error, $stackPtr, "NoNewLine");

			return;
		    }

		$starColumn = ($tokens[$stackPtr]["column"] + 3);

		// Make sure first line isn't blank.
		if (trim($tokens[$commentLines[1]]["content"]) === "")
		    {
			$error = _("Empty line not allowed at start of comment");
			$phpcsFile->addError($error, $commentLines[1], "HasEmptyLine");
		    }
		else
		    {
			// Check indentation of first line.
			$content      = $tokens[$commentLines[1]]["content"];
			$commentText  = ltrim($content);
			$leadingSpace = (strlen($content) - strlen($commentText));
			if ($leadingSpace !== $starColumn)
			    {
				$expected = $starColumn . _(" space");
				if ($starColumn !== 1)
				    {
					$expected .= "s";
				    }

				$data = array(
					 $expected,
					 $leadingSpace,
					);

				$error = _("First line of comment not aligned correctly; expected ") . "%s" . _(" but found ") . "%s";
				$phpcsFile->addError($error, $commentLines[1], "FirstLineIndent", $data);
			    }

			if (preg_match("/\p{Lu}|\P{L}/u", $commentText[0]) === 0)
			    {
				$error = _("Block comments must start with a capital letter");
				$phpcsFile->addError($error, $commentLines[1], "NoCapital");
			    }
		    } //end if

		// Check that each line of the comment is indented past the star.
		foreach ($commentLines as $line)
		    {
			$leadingSpace = (strlen($tokens[$line]["content"]) - strlen(ltrim($tokens[$line]["content"])));
			// First and last lines (comment opener and closer) are handled separately.
			if ($line === $commentLines[(count($commentLines) - 1)] || $line === $commentLines[0])
			    {
				continue;
			    }

			// First comment line was handled above.
			if ($line === $commentLines[1])
			    {
				continue;
			    }

			// If it's empty, continue.
			if (trim($tokens[$line]["content"]) === "")
			    {
				continue;
			    }

			if ($leadingSpace < $starColumn)
			    {
				$expected = $starColumn . _(" space");
				if ($starColumn !== 1)
				    {
					$expected .= "s";
				    }

				$data = array(
					 $expected,
					 $leadingSpace,
					);

				$error = _("Comment line indented incorrectly; expected at least ") . "%s" . _(" but found ") . "%s";
				$phpcsFile->addError($error, $line, "LineIndent", $data);
			    } //end if
		    } //end foreach

		// Finally, test the last line is correct.
		$lastIndex = (count($commentLines) - 1);
		$content   = trim($tokens[$commentLines[$lastIndex]]["content"]);
		if ($content !== "*/" && $content !== "**/")
		    {
			$error = _("Comment closer must be on a new line");
			$phpcsFile->addError($error, $commentLines[$lastIndex]);
		    }
		else
		    {
			$content      = $tokens[$commentLines[$lastIndex]]["content"];
			$commentText  = ltrim($content);
			$leadingSpace = (strlen($content) - strlen($commentText));
			if ($leadingSpace !== ($tokens[$stackPtr]["column"] - 1))
			    {
				$expected = ($tokens[$stackPtr]["column"] - 1);
				if ($expected === 1)
				    {
					$expected .= _(" space");
				    }
				else
				    {
					$expected .= _(" spaces");
				    }

				$data = array(
					 $expected,
					 $leadingSpace,
					);

				$error = _("Last line of comment aligned incorrectly; expected ") . "%s" . _(" but found ") . "%s";
				$phpcsFile->addError($error, $commentLines[$lastIndex], "LastLineIndent", $data);
			    } //end if
		    } //end if

		// Check that the lines before and after this comment are blank.
		$contentBefore = $phpcsFile->findPrevious(T_WHITESPACE, ($stackPtr - 1), null, true);
		if (isset($tokens[$contentBefore]["scope_closer"]) === true && $tokens[$contentBefore]["scope_opener"] === $contentBefore)
		    {
			if (($tokens[$stackPtr]["line"] - $tokens[$contentBefore]["line"]) !== 1)
			    {
				$error = _("Empty line not required before block comment");
				$phpcsFile->addError($error, $stackPtr, "HasEmptyLineBefore");
			    }
		    }
		else
		    {
			if (($tokens[$stackPtr]["line"] - $tokens[$contentBefore]["line"]) < 2)
			    {
				$error = _("Empty line required before block comment");
				$phpcsFile->addError($error, $stackPtr, "NoEmptyLineBefore");
			    }
		    }

		$commentCloser = $commentLines[$lastIndex];
		$contentAfter  = $phpcsFile->findNext(T_WHITESPACE, ($commentCloser + 1), null, true);
		if ($contentAfter !== false && ($tokens[$contentAfter]["line"] - $tokens[$commentCloser]["line"]) < 2)
		    {
			$error = _("Empty line required after block comment");
			$phpcsFile->addError($error, $commentCloser, "NoEmptyLineAfter");
		    }
	    } //end process()


    } //end class

?>
