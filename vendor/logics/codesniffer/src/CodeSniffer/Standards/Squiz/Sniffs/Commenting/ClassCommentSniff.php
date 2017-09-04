<?php

/**
 * PHP version 5
 *
 * @package Logics\BuildTools\CodeSniffer\Squiz
 */

namespace Logics\BuildTools\CodeSniffer\Squiz;

use \Logics\BuildTools\CodeSniffer\File;
use \Logics\BuildTools\CodeSniffer\PEAR\ClassCommentSniff as PEAR_ClassCommentSniff;

/**
 * Parses and verifies the class doc comment.
 *
 * Verifies that :
 * <ul>
 *  <li>A class doc comment exists.</li>
 *  <li>There is exactly one blank line before the class comment.</li>
 *  <li>Short description ends with a full stop.</li>
 *  <li>There is a blank line after the short description.</li>
 *  <li>Each paragraph of the long description ends with a full stop.</li>
 *  <li>There is a blank line between the description and the tags.</li>
 *  <li>Check the format of the since tag (x.x.x).</li>
 * </ul>
 *
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @author    Marc McIntyre <mmcintyre@squiz.net>
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2006-2012 Squiz Pty Ltd (ABN 77 084 670 600)
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 * @version   SVN: $Date: 2016-08-16 23:39:01 +0900 (Tue, 16 Aug 2016) $ $Revision: 26 $
 * @link      $HeadURL: https://open.logics.net.au/buildtools/codesniffer/tags/0.1.3/src/CodeSniffer/Standards/Squiz/Sniffs/Commenting/ClassCommentSniff.php $
 */

class ClassCommentSniff extends PEAR_ClassCommentSniff
    {

	/**
	 * Returns an array of tokens this test wants to listen for.
	 *
	 * @return array
	 */

	public function register()
	    {
		return array(T_CLASS);
	    } //end register()


	/**
	 * Processes each required or optional tag.
	 *
	 * @param File $phpcsFile    The file being scanned.
	 * @param int  $stackPtr     The position of the current token in the stack passed in $tokens.
	 * @param int  $commentStart Position in the stack where the comment started.
	 *
	 * @return void
	 *
	 * @internalconst T_DOC_COMMENT_CLOSE_TAG T_DOC_COMMENT_CLOSE_TAG token
	 *
	 * @untranslatable SpacingAfter
	 * @untranslatable SpacingBefore
	 * @untranslatable TagNotAllowed
	 * @untranslatable %s
	 */

	protected function processTags(File &$phpcsFile, $stackPtr, $commentStart)
	    {
		$tokens     = &$phpcsFile->tokens;
		$commentEnd = $tokens[$commentStart]["comment_closer"];
		if ($tokens[$commentEnd]["line"] !== ($tokens[$stackPtr]["line"] - 1))
		    {
			$error = _("There must be no blank lines after the class comment");
			$phpcsFile->addError($error, $commentEnd, "SpacingAfter");
		    }

		if ($tokens[$commentEnd]["code"] === T_DOC_COMMENT_CLOSE_TAG)
		    {
			$start = ($tokens[$commentEnd]["comment_opener"] - 1);
		    }
		else
		    {
			$start = $phpcsFile->findPrevious(T_COMMENT, ($commentEnd - 1), null, true);
		    }

		$prev = $phpcsFile->findPrevious(T_WHITESPACE, $start, null, true);
		if ($tokens[$prev]["line"] !== ($tokens[$commentStart]["line"] - 2))
		    {
			$error = _("There must be exactly one blank line before the class comment");
			$phpcsFile->addError($error, $commentStart, "SpacingBefore");
		    }

		foreach ($tokens[$commentStart]["comment_tags"] as $tag)
		    {
			$error = "%s" . _(" tag is not allowed in class comment");
			$data  = array($tokens[$tag]["content"]);
			$phpcsFile->addWarning($error, $tag, "TagNotAllowed", $data);
		    }
	    } //end processTags()


    } //end class

?>
