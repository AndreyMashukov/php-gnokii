<?php

/**
 * Ensures that values submitted via JS are not compared to NULL.
 *
 * PHP version 5
 *
 * @package Logics\BuildTools\CodeSniffer\MySource
 */

namespace Logics\BuildTools\CodeSniffer\MySource;

use \Logics\BuildTools\CodeSniffer\File;
use \Logics\BuildTools\CodeSniffer\Sniff;

/**
 * Ensures that values submitted via JS are not compared to NULL.
 *
 * The jQuery 1.8 changed the behaviour of ajax requests so that null values are
 * submitted as null= instead of null=null.
 *
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2006-2012 Squiz Pty Ltd (ABN 77 084 670 600)
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 * @version   SVN: $Date: 2016-08-16 23:39:01 +0900 (Tue, 16 Aug 2016) $ $Revision: 26 $
 * @link      $HeadURL: https://open.logics.net.au/buildtools/codesniffer/tags/0.1.3/src/CodeSniffer/Standards/MySource/Sniffs/PHP/AjaxNullComparisonSniff.php $
 */

class AjaxNullComparisonSniff implements Sniff
    {

	/**
	 * Returns an array of tokens this test wants to listen for.
	 *
	 * @return array
	 */

	public function register()
	    {
		return array(T_FUNCTION);
	    } //end register()


	/**
	 * Processes this sniff, when one of its tokens is encountered.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $stackPtr  The position of the current token in the stack passed in $tokens.
	 *
	 * @return void
	 *
	 * @internalconst T_DOC_COMMENT_CLOSE_TAG T_DOC_COMMENT_CLOSE_TAG token
	 * @internalconst T_DOC_COMMENT_OPEN_TAG  T_DOC_COMMENT_OPEN_TAG token
	 * @internalconst T_NULL                  T_NULL token
	 *
	 * @untranslatable * @api
	 * @untranslatable Found
	 */

	public function process(File $phpcsFile, $stackPtr)
	    {
		$tokens = &$phpcsFile->tokens;

		// Make sure it is an API function. We know this by the doc comment.
		$commentEnd   = $phpcsFile->findPrevious(T_DOC_COMMENT_CLOSE_TAG, $stackPtr);
		$commentStart = $phpcsFile->findPrevious(T_DOC_COMMENT_OPEN_TAG, ($commentEnd - 1), null, false);
		$comment      = $phpcsFile->getTokensAsString($commentStart, ($commentEnd - $commentStart));
		if (strpos($comment, "* @api") !== false)
		    {
			// Find all the vars passed in as we are only interested in comparisons
			// to NULL for these specific variables.
			$foundVars = array();
			$open      = $tokens[$stackPtr]["parenthesis_opener"];
			$close     = $tokens[$stackPtr]["parenthesis_closer"];
			for ($i = ($open + 1); $i < $close; $i++)
			    {
				if ($tokens[$i]["code"] === T_VARIABLE)
				    {
					$foundVars[] = $tokens[$i]["content"];
				    }
			    }

			if (empty($foundVars) === false)
			    {
				$start = $tokens[$stackPtr]["scope_opener"];
				$end   = $tokens[$stackPtr]["scope_closer"];
				for ($i = ($start + 1); $i < $end; $i++)
				    {
					if ($tokens[$i]["code"] === T_VARIABLE && in_array($tokens[$i]["content"], $foundVars) === true)
					    {
						$operator  = $phpcsFile->findNext(T_WHITESPACE, ($i + 1), null, true);
						$nullValue = $phpcsFile->findNext(T_WHITESPACE, ($operator + 1), null, true);
						if (($tokens[$operator]["code"] === T_IS_IDENTICAL || $tokens[$operator]["code"] === T_IS_NOT_IDENTICAL) &&
						    $tokens[$nullValue]["code"] === T_NULL)
						    {
							$error = _("Values submitted via Ajax requests must not be compared directly to NULL; use empty() instead");
							$phpcsFile->addError($error, $nullValue, "Found");
						    }
					    }
				    } //end for
			    } //end if
		    } //end if
	    } //end process()


    } //end class

?>