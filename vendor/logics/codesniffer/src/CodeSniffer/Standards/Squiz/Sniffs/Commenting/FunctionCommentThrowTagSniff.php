<?php

/**
 * PHP version 5
 *
 * @package Logics\BuildTools\CodeSniffer\Squiz
 */

namespace Logics\BuildTools\CodeSniffer\Squiz;

use \Logics\BuildTools\CodeSniffer\AbstractScopeSniff;
use \Logics\BuildTools\CodeSniffer\File;

/**
 * Verifies that a @throws tag exists for a function that throws exceptions.
 * Verifies the number of @throws tags and the number of throw tokens matches.
 * Verifies the exception type.
 *
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @author    Marc McIntyre <mmcintyre@squiz.net>
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2006-2012 Squiz Pty Ltd (ABN 77 084 670 600)
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 * @version   SVN: $Date: 2016-08-16 23:39:01 +0900 (Tue, 16 Aug 2016) $ $Revision: 26 $
 * @link      $HeadURL: https://open.logics.net.au/buildtools/codesniffer/tags/0.1.3/src/CodeSniffer/Standards/Squiz/Sniffs/Commenting/FunctionCommentThrowTagSniff.php $
 */

class FunctionCommentThrowTagSniff extends AbstractScopeSniff
    {

	/**
	 * Constructs a Squiz_Sniffs_Commenting_FunctionCommentThrowTagSniff.
	 *
	 * @return void
	 */

	public function __construct()
	    {
		parent::__construct(array(T_FUNCTION), array(T_THROW));
	    } //end __construct()


	/**
	 * Processes the function tokens within the class.
	 *
	 * @param File $phpcsFile The file where this token was found.
	 * @param int  $stackPtr  The position where the token was found.
	 * @param int  $currScope The current scope opener token.
	 *
	 * @return void
	 *
	 * @internalconst T_DOC_COMMENT_CLOSE_TAG T_DOC_COMMENT_CLOSE_TAG token
	 * @internalconst T_DOC_COMMENT_STRING    T_DOC_COMMENT_STRING token
	 *
	 * @untranslatable @throws
	 * @untranslatable Missing
	 * @untranslatable %s
	 * @untranslatable WrongNumber
	 * @untranslatable \"%s\"
	 */

	protected function processTokenWithinScope(File &$phpcsFile, $stackPtr, $currScope)
	    {
		// Is this the first throw token within the current function scope?
		// If so, we have to validate other throw tokens within the same scope.
		$previousThrow = $phpcsFile->findPrevious(T_THROW, ($stackPtr - 1), $currScope);
		if ($previousThrow !== false)
		    {
			return;
		    }

		$tokens = &$phpcsFile->tokens;

		$find = array(
			 T_COMMENT,
			 T_DOC_COMMENT_CLOSE_TAG,
			 T_CLASS,
			 T_FUNCTION,
			 T_OPEN_TAG,
			);

		$commentEnd = $phpcsFile->findPrevious($find, ($currScope - 1));
		if ($commentEnd === false)
		    {
			return;
		    }

		if ($tokens[$commentEnd]["code"] !== T_DOC_COMMENT_CLOSE_TAG)
		    {
			// Function doesn't have a comment. Let someone else warn about that.
			return;
		    }

		// Find the position where the current function scope ends.
		$currScopeEnd = 0;
		if (isset($tokens[$currScope]["scope_closer"]) === true)
		    {
			$currScopeEnd = $tokens[$currScope]["scope_closer"];
		    }

		// Find all the exception type token within the current scope.
		$throwTokens = array();
		$currPos     = $stackPtr;
		if ($currScopeEnd !== 0)
		    {
			while ($currPos < $currScopeEnd && $currPos !== false)
			    {
				// If we can't find a NEW, we are probably throwing a variable, so we ignore it.
				// But they still need to provide at least one @throws tag, even through we don't know the exception class.
				$nextToken = $phpcsFile->findNext(T_WHITESPACE, ($currPos + 1), null, true);
				if ($tokens[$nextToken]["code"] === T_NEW)
				    {
					$currException = $phpcsFile->findNext(
					    array(
					     T_NS_SEPARATOR,
					     T_STRING,
					    ),
					    $currPos,
					    $currScopeEnd,
					    false,
					    null,
					    true
					);

					if ($currException !== false)
					    {
						$endException = $phpcsFile->findNext(
						    array(
						     T_NS_SEPARATOR,
						     T_STRING,
						    ),
						    ($currException + 1),
						    $currScopeEnd,
						    true,
						    null,
						    true
						);

						if ($endException === false)
						    {
							$throwTokens[] = $tokens[$currException]["content"];
						    }
						else
						    {
							$throwTokens[] = $phpcsFile->getTokensAsString($currException, ($endException - $currException));
						    }
					    } //end if
				    } //end if

				$currPos = $phpcsFile->findNext(T_THROW, ($currPos + 1), $currScopeEnd);
			    } //end while
		    } //end if

		// Only need one @throws tag for each type of exception thrown.
		$throwTokens = array_unique($throwTokens);

		$throwTags    = array();
		$commentStart = $tokens[$commentEnd]["comment_opener"];
		foreach ($tokens[$commentStart]["comment_tags"] as $tag)
		    {
			if ($tokens[$tag]["content"] !== "@throws")
			    {
				continue;
			    }

			if ($tokens[($tag + 2)]["code"] === T_DOC_COMMENT_STRING)
			    {
				$exception = $tokens[($tag + 2)]["content"];
				$space     = strpos($exception, " ");
				if ($space !== false)
				    {
					$exception = substr($exception, 0, $space);
				    }

				$throwTags[$exception] = true;
			    }
		    }

		if (empty($throwTags) === true)
		    {
			$error = _("Missing ") . "@throws" . _(" tag in function comment");
			$phpcsFile->addError($error, $commentEnd, "Missing");
			return;
		    }
		else if (empty($throwTokens) === true)
		    {
			// If token count is zero, it means that only variables are being  thrown, so we need at least one @throws tag (checked above).
			// Nothing more to do.
			return;
		    }

		// Make sure @throws tag count matches throw token count.
		$tokenCount = count($throwTokens);
		$tagCount   = count($throwTags);
		if ($tokenCount !== $tagCount)
		    {
			$error = _("Expected ") . "%s" . " @throws" . _(" tag(s) in function comment; ") . "%s" . _(" found");
			$data  = array(
				  $tokenCount,
				  $tagCount,
				 );
			$phpcsFile->addError($error, $commentEnd, "WrongNumber", $data);
			return;
		    }

		foreach ($throwTokens as $throw)
		    {
			if (isset($throwTags[$throw]) === false)
			    {
				$error = _("Missing ") . "@throws" . _(" tag for ") . "\"%s\"" . _(" exception");
				$data  = array($throw);
				$phpcsFile->addError($error, $commentEnd, "Missing", $data);
			    }
		    } //end foreach
	    } //end processTokenWithinScope()


    } //end class

?>
