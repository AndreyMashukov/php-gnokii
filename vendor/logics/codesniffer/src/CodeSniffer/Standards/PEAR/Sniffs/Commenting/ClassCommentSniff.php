<?php

/**
 * PHP version 5
 *
 * @package Logics\BuildTools\CodeSniffer\PEAR
 */

namespace Logics\BuildTools\CodeSniffer\PEAR;

use \Logics\BuildTools\CodeSniffer\File;
use \Logics\BuildTools\CodeSniffer\Tokens;

/**
 * Parses and verifies the doc comments for classes.
 *
 * Verifies that :
 * <ul>
 *  <li>A doc comment exists.</li>
 *  <li>There is a blank newline after the short description.</li>
 *  <li>There is a blank newline between the long and short description.</li>
 *  <li>There is a blank newline between the long description and tags.</li>
 *  <li>Check the order of the tags.</li>
 *  <li>Check the indentation of each tag.</li>
 *  <li>Check required and optional tags and the format of their content.</li>
 * </ul>
 *
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @author    Marc McIntyre <mmcintyre@squiz.net>
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2006-2012 Squiz Pty Ltd (ABN 77 084 670 600)
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 * @version   SVN: $Date: 2016-08-16 23:39:01 +0900 (Tue, 16 Aug 2016) $ $Revision: 26 $
 * @link      $HeadURL: https://open.logics.net.au/buildtools/codesniffer/tags/0.1.3/src/CodeSniffer/Standards/PEAR/Sniffs/Commenting/ClassCommentSniff.php $
 */

class ClassCommentSniff extends FileCommentSniff
    {

	/**
	 * Returns an array of tokens this test wants to listen for.
	 *
	 * @return array
	 */

	public function register()
	    {
		return array(
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
	 * @internalconst T_DOC_COMMENT_CLOSE_TAG T_DOC_COMMENT_CLOSE_TAG token
	 *
	 * @untranslatable Missing
	 * @untranslatable WrongStyle
	 */

	public function process(File $phpcsFile, $stackPtr)
	    {
		$this->currentFile = $phpcsFile;

		$tokens = &$phpcsFile->tokens;
		$find   = Tokens::$methodPrefixes;
		$find[] = T_WHITESPACE;

		$commentEnd    = $phpcsFile->findPrevious($find, ($stackPtr - 1), null, true);
		$commentTokens = array(
				  T_DOC_COMMENT_CLOSE_TAG,
				  T_COMMENT,
				 );
		if (in_array($tokens[$commentEnd]["code"], $commentTokens) === false)
		    {
			$phpcsFile->addError(_("Missing class doc comment"), $stackPtr, "Missing");
		    }
		else
		    {
			// Try and determine if this is a file comment instead of a class comment.
			// We assume that if this is the first comment after the open PHP tag, then it is most likely a file comment instead of a class comment.
			if ($tokens[$commentEnd]["code"] === T_DOC_COMMENT_CLOSE_TAG)
			    {
				$start = ($tokens[$commentEnd]["comment_opener"] - 1);
			    }
			else
			    {
				$start = $phpcsFile->findPrevious(T_COMMENT, ($commentEnd - 1), null, true);
			    }

			$prev     = $phpcsFile->findPrevious(T_WHITESPACE, $start, null, true);
			$prevOpen = $phpcsFile->findPrevious(T_OPEN_TAG, ($prev - 1));
			if ($tokens[$prev]["code"] === T_OPEN_TAG && $prevOpen === false)
			    {
				// This is a comment directly after the first open tag, so probably a file comment.
				$phpcsFile->addError(_("Missing class doc comment"), $stackPtr, "Missing");
			    }
			else
			    {
				if ($tokens[$commentEnd]["code"] === T_COMMENT)
				    {
					$phpcsFile->addError(_("You must use ") . "\"/**\"" . _(" style comments for a class comment"), $stackPtr, "WrongStyle");
				    }
				else
				    {
					// Check each tag.
					$this->processTags($phpcsFile, $stackPtr, $tokens[$commentEnd]["comment_opener"]);
				    } //end if
			    } //end if
		    } //end if
	    } //end process()


	/**
	 * Process the version tag.
	 *
	 * @param File  $phpcsFile The file being scanned.
	 * @param array $tags      The tokens for these tags.
	 *
	 * @return void
	 *
	 * @internalconst T_DOC_COMMENT_STRING T_DOC_COMMENT_STRING token
	 *
	 * @untranslatable Release:
	 * @untranslatable \"%s\"
	 * @untranslatable InvalidVersion
	 * @untranslatable \"Release: <package_version>\"
	 */

	protected function processVersion(File &$phpcsFile, array $tags)
	    {
		$tokens = &$phpcsFile->tokens;
		foreach ($tags as $tag)
		    {
			if ($tokens[($tag + 2)]["code"] === T_DOC_COMMENT_STRING)
			    {
				$content = $tokens[($tag + 2)]["content"];
				if ((strstr($content, "Release:") === false))
				    {
					$error = _("Invalid version ") . "\"%s\"" . _(" in doc comment; consider ") . "\"Release: <package_version>\"" . _(" instead");
					$data  = array($content);
					$phpcsFile->addWarning($error, $tag, "InvalidVersion", $data);
				    }
			    } //end if
		    } //end foreach
	    } //end processVersion()


    } //end class

?>
