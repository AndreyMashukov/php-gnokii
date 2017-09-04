<?php

/**
 * Parses and verifies the doc comments for functions.
 *
 * PHP version 5
 *
 * @package Logics\BuildTools\CodeSniffer\MySource
 */

namespace Logics\BuildTools\CodeSniffer\MySource;

use \Logics\BuildTools\CodeSniffer\File;
use \Logics\BuildTools\CodeSniffer\Squiz\FunctionCommentSniff as Squiz_FunctionCommentSniff;
use \Logics\BuildTools\CodeSniffer\Tokens;

/**
 * Parses and verifies the doc comments for functions.
 *
 * Same as the Squiz standard, but adds support for API tags.
 *
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2006-2012 Squiz Pty Ltd (ABN 77 084 670 600)
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 * @version   SVN: $Date: 2016-08-16 23:39:01 +0900 (Tue, 16 Aug 2016) $ $Revision: 26 $
 * @link      $HeadURL: https://open.logics.net.au/buildtools/codesniffer/tags/0.1.3/src/CodeSniffer/Standards/MySource/Sniffs/Commenting/FunctionCommentSniff.php $
 */

class FunctionCommentSniff extends Squiz_FunctionCommentSniff
    {

	/**
	 * Processes this test, when one of its tokens is encountered.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $stackPtr  The position of the current token in the stack passed in $tokens.
	 *
	 * @return void
	 *
	 * @internalconst T_DOC_COMMENT_CLOSE_TAG T_DOC_COMMENT_CLOSE_TAG token
	 * @internalconst T_DOC_COMMENT_STRING    T_DOC_COMMENT_STRING token
	 * @internalconst T_DOC_COMMENT_TAG       T_DOC_COMMENT_TAG token
	 *
	 * @untranslatable ApiNotFirst
	 * @untranslatable ApiSpacing
	 * @untranslatable @api-
	 * @untranslatable @%s
	 * @untranslatable ApiTagSpacing
	 * @untranslatable @api
	 * @untranslatable ApiNotLast
	 */

	public function process(File $phpcsFile, $stackPtr)
	    {
		parent::process($phpcsFile, $stackPtr);

		$tokens = &$phpcsFile->tokens;
		$find   = Tokens::$methodPrefixes;
		$find[] = T_WHITESPACE;

		$commentEnd = $phpcsFile->findPrevious($find, ($stackPtr - 1), null, true);
		if ($tokens[$commentEnd]["code"] === T_DOC_COMMENT_CLOSE_TAG)
		    {
			$commentStart = $tokens[$commentEnd]["comment_opener"];
			$hasApiTag    = false;
			foreach ($tokens[$commentStart]["comment_tags"] as $tag)
			    {
				if ($tokens[$tag]["content"] === "@api")
				    {
					if ($hasApiTag === true)
					    {
						// We've come across an API tag already, which means
						// we were not the first tag in the API list.
						$error = _("The @api tag must come first in the @api tag list in a function comment");
						$phpcsFile->addError($error, $tag, "ApiNotFirst");
					    }

					$hasApiTag = true;

					// There needs to be a blank line before the @api tag.
					$prev = $phpcsFile->findPrevious(array(T_DOC_COMMENT_STRING, T_DOC_COMMENT_TAG), ($tag - 1));
					if ($tokens[$prev]["line"] !== ($tokens[$tag]["line"] - 2))
					    {
						$error = _("There must be one blank line before the @api tag in a function comment");
						$phpcsFile->addError($error, $tag, "ApiSpacing");
					    }
				    }
				else if (substr($tokens[$tag]["content"], 0, 5) === "@api-")
				    {
					$hasApiTag = true;

					$prev = $phpcsFile->findPrevious(array(T_DOC_COMMENT_STRING, T_DOC_COMMENT_TAG), ($tag - 1));
					if ($tokens[$prev]["line"] !== ($tokens[$tag]["line"] - 1))
					    {
						$error = _("There must be no blank line before the") . " @%s " . _("tag in a function comment");
						$data  = array($tokens[$tag]["content"]);
						$phpcsFile->addError($error, $tag, "ApiTagSpacing", $data);
					    }
				    } //end if
			    } //end foreach

			if ($hasApiTag === true && substr($tokens[$tag]["content"], 0, 4) !== "@api")
			    {
				// API tags must be the last tags in a function comment.
				$error = _("The @api tags must be the last tags in a function comment");
				$phpcsFile->addError($error, $commentEnd, "ApiNotLast");
			    }
		    } //end if
	    } //end process()


    } //end class

?>
