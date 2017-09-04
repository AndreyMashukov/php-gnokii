<?php

/**
 * PHP version 5
 *
 * @package Logics\BuildTools\CodeSniffer\Squiz
 */

namespace Logics\BuildTools\CodeSniffer\Squiz;

use \Logics\BuildTools\CodeSniffer\AbstractMemberSniff;
use \Logics\BuildTools\CodeSniffer\File;
use \Logics\BuildTools\CodeSniffer\Tokens;

/**
 * Verifies that class members are spaced correctly.
 *
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @author    Marc McIntyre <mmcintyre@squiz.net>
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2006-2012 Squiz Pty Ltd (ABN 77 084 670 600)
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 * @version   SVN: $Date: 2016-08-16 23:39:01 +0900 (Tue, 16 Aug 2016) $ $Revision: 26 $
 * @link      $HeadURL: https://open.logics.net.au/buildtools/codesniffer/tags/0.1.3/src/CodeSniffer/Standards/Squiz/Sniffs/WhiteSpace/MemberVarSpacingSniff.php $
 */

class MemberVarSpacingSniff extends AbstractMemberSniff
    {

	/**
	 * Processes the function tokens within the class.
	 *
	 * @param File $phpcsFile The file where this token was found.
	 * @param int  $stackPtr  The position where the token was found.
	 *
	 * @return void
	 *
	 * @internalconst T_DOC_COMMENT_CLOSE_TAG T_DOC_COMMENT_CLOSE_TAG token
	 *
	 * @untranslatable AfterComment
	 * @untranslatable Incorrect
	 * @untranslatable %s
	 */

	protected function processMemberVar(File &$phpcsFile, $stackPtr)
	    {
		$tokens = &$phpcsFile->tokens;

		$ignore   = Tokens::$methodPrefixes;
		$ignore[] = T_VAR;
		$ignore[] = T_WHITESPACE;

		$start = $stackPtr;
		$prev  = $phpcsFile->findPrevious($ignore, ($stackPtr - 1), null, true);
		if (isset(Tokens::$commentTokens[$tokens[$prev]["code"]]) === true)
		    {
			// Assume the comment belongs to the member var if it is on a line by itself.
			$prevContent = $phpcsFile->findPrevious(Tokens::$emptyTokens, ($prev - 1), null, true);
			if ($tokens[$prevContent]["line"] !== $tokens[$prev]["line"])
			    {
				// Check the spacing, but then skip it.
				$foundLines = ($tokens[$stackPtr]["line"] - $tokens[$prev]["line"] - 1);
				if ($foundLines > 0)
				    {
					$error = _("Expected 0 blank lines after member var comment; ") . "%s" . _(" found");
					$data  = array($foundLines);
					$phpcsFile->addError($error, $prev, "AfterComment", $data);
				    } //end if

				$start = $prev;
			    } //end if
		    } //end if

		// There needs to be 1 blank line before the var, not counting comments.
		if ($start === $stackPtr)
		    {
			// No comment found.
			$first = $phpcsFile->findFirstOnLine(Tokens::$emptyTokens, $start, true);
			if ($first === false)
			    {
				$first = $start;
			    }
		    }
		else if ($tokens[$start]["code"] === T_DOC_COMMENT_CLOSE_TAG)
		    {
			$first = $tokens[$start]["comment_opener"];
		    }
		else
		    {
			$first = $phpcsFile->findPrevious(Tokens::$emptyTokens, ($start - 1), null, true);
			$first = $phpcsFile->findNext(Tokens::$commentTokens, ($first + 1));
		    }

		$prev       = $phpcsFile->findPrevious(Tokens::$emptyTokens, ($first - 1), null, true);
		$foundLines = ($tokens[$first]["line"] - $tokens[$prev]["line"] - 1);
		if ($foundLines === 1)
		    {
			return;
		    }
		else
		    {
			$error = _("Expected 1 blank line before member var;") . "%s" . _(" found");
			$data  = array($foundLines);
			$phpcsFile->addError($error, $stackPtr, "Incorrect", $data);
		    }
	    } //end processMemberVar()


    } //end class

?>
