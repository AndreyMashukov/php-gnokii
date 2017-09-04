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
 * Squiz_Sniffs_ControlStructures_InlineIfDeclarationSniff.
 *
 * Tests the spacing of shorthand IF statements.
 *
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @author    Marc McIntyre <mmcintyre@squiz.net>
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2006-2012 Squiz Pty Ltd (ABN 77 084 670 600)
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 * @version   SVN: $Date: 2016-08-16 23:39:01 +0900 (Tue, 16 Aug 2016) $ $Revision: 26 $
 * @link      $HeadURL: https://open.logics.net.au/buildtools/codesniffer/tags/0.1.3/src/CodeSniffer/Standards/Squiz/Sniffs/ControlStructures/InlineIfDeclarationSniff.php $
 */

class InlineIfDeclarationSniff implements Sniff
    {

	/**
	 * Returns an array of tokens this test wants to listen for.
	 *
	 * @return array
	 *
	 * @internalconst T_INLINE_THEN T_INLINE_THEN token
	 */

	public function register()
	    {
		return array(T_INLINE_THEN);
	    } //end register()


	/**
	 * Processes this sniff, when one of its tokens is encountered.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $stackPtr  The position of the current token in the stack passed in $tokens.
	 *
	 * @return void
	 *
	 * @internalconst T_OPEN_PARENTHESIS  T_OPEN_PARENTHESIS token
	 * @internalconst T_SEMICOLON         T_SEMICOLON token
	 * @internalconst T_CLOSE_PARENTHESIS T_CLOSE_PARENTHESIS token
	 * @internalconst T_INLINE_ELSE       T_INLINE_ELSE token
	 *
	 * @untranslatable NotSingleLine
	 * @untranslatable NoBrackets
	 * @untranslatable SpacingBeforeThen
	 * @untranslatable SpacingAfterThen
	 * @untranslatable SpacingBeforeElse
	 * @untranslatable SpacingAfterElse
	 * @untranslatable %s
	 */

	public function process(File $phpcsFile, $stackPtr)
	    {
		$tokens = &$phpcsFile->tokens;

		// Find the opening bracket of the inline IF.
		for ($i = ($stackPtr - 1); $i > 0; $i--)
		    {
			if (isset($tokens[$i]["parenthesis_opener"]) === true && $tokens[$i]["parenthesis_opener"] < $i)
			    {
				$i = $tokens[$i]["parenthesis_opener"];
			    }
			else if ($tokens[$i]["code"] === T_OPEN_PARENTHESIS)
			    {
				break;
			    }
		    }

		$statementEnd = (($i <= 0) ? $phpcsFile->findNext(T_SEMICOLON, ($stackPtr + 1)) : $tokens[$i]["parenthesis_closer"]);

		// Make sure it's all on the same line.
		if ($tokens[$statementEnd]["line"] !== $tokens[$stackPtr]["line"])
		    {
			$error = _("Inline shorthand IF statement must be declared on a single line");
			$phpcsFile->addError($error, $stackPtr, "NotSingleLine");
		    }
		else
		    {
			// Make sure there are spaces around the question mark.
			$contentBefore = $phpcsFile->findPrevious(T_WHITESPACE, ($stackPtr - 1), null, true);
			$contentAfter  = $phpcsFile->findNext(T_WHITESPACE, ($stackPtr + 1), null, true);
			if ($tokens[$contentBefore]["code"] !== T_CLOSE_PARENTHESIS)
			    {
				$error = _("Inline shorthand IF statement requires brackets around comparison");
				$phpcsFile->addError($error, $stackPtr, "NoBrackets");
			    }
			else
			    {
				$spaceBefore = ($tokens[$stackPtr]["column"] - ($tokens[$contentBefore]["column"] + strlen($tokens[$contentBefore]["content"])));
				if ($spaceBefore !== 1)
				    {
					$error = _("Inline shorthand IF statement requires 1 space before THEN;") . " %s " . _("found");
					$data  = array($spaceBefore);
					$phpcsFile->addError($error, $stackPtr, "SpacingBeforeThen", $data);
				    }

				$spaceAfter = (($tokens[$contentAfter]["column"]) - ($tokens[$stackPtr]["column"] + 1));
				if ($spaceAfter !== 1)
				    {
					$error = _("Inline shorthand IF statement requires 1 space after THEN;") . " %s " . _("found");
					$data  = array($spaceAfter);
					$phpcsFile->addError($error, $stackPtr, "SpacingAfterThen", $data);
				    }

				// Make sure the ELSE has the correct spacing.
				$inlineElse    = $phpcsFile->findNext(T_INLINE_ELSE, ($stackPtr + 1), $statementEnd, false);
				$contentBefore = $phpcsFile->findPrevious(T_WHITESPACE, ($inlineElse - 1), null, true);
				$contentAfter  = $phpcsFile->findNext(T_WHITESPACE, ($inlineElse + 1), null, true);

				$spaceBefore = ($tokens[$inlineElse]["column"] - ($tokens[$contentBefore]["column"] + strlen($tokens[$contentBefore]["content"])));
				if ($spaceBefore !== 1)
				    {
					$error = _("Inline shorthand IF statement requires 1 space before ELSE;") . " %s " . _("found");
					$data  = array($spaceBefore);
					$phpcsFile->addError($error, $inlineElse, "SpacingBeforeElse", $data);
				    }

				$spaceAfter = (($tokens[$contentAfter]["column"]) - ($tokens[$inlineElse]["column"] + 1));
				if ($spaceAfter !== 1)
				    {
					$error = _("Inline shorthand IF statement requires 1 space after ELSE;") . " %s " . _("found");
					$data  = array($spaceAfter);
					$phpcsFile->addError($error, $inlineElse, "SpacingAfterElse", $data);
				    }
			    } //end if
		    } //end if
	    } //end process()


    } //end class

?>
