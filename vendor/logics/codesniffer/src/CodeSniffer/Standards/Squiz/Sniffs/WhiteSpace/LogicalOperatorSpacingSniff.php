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
 * Sniffs_Squiz_WhiteSpace_OperatorSpacingSniff.
 *
 * Verifies that operators have valid spacing surrounding them.
 *
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @author    Marc McIntyre <mmcintyre@squiz.net>
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2006-2012 Squiz Pty Ltd (ABN 77 084 670 600)
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 * @version   SVN: $Date: 2016-08-16 23:39:01 +0900 (Tue, 16 Aug 2016) $ $Revision: 26 $
 * @link      $HeadURL: https://open.logics.net.au/buildtools/codesniffer/tags/0.1.3/src/CodeSniffer/Standards/Squiz/Sniffs/WhiteSpace/LogicalOperatorSpacingSniff.php $
 *
 * @untranslatable PHP
 * @untranslatable JS
 */

class LogicalOperatorSpacingSniff implements Sniff
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
	 */

	public function register()
	    {
		return Tokens::$booleanOperators;
	    } //end register()


	/**
	 * Processes this sniff, when one of its tokens is encountered.
	 *
	 * @param File $phpcsFile The current file being checked.
	 * @param int  $stackPtr  The position of the current token in the stack passed in $tokens.
	 *
	 * @return void
	 *
	 * @untranslatable NoSpaceBefore
	 * @untranslatable TooMuchSpaceBefore
	 * @untranslatable NoSpaceAfter
	 * @untranslatable TooMuchSpaceAfter
	 * @untranslatable %s
	 */

	public function process(File $phpcsFile, $stackPtr)
	    {
		$tokens = &$phpcsFile->tokens;

		// Check there is one space before the operator.
		if ($tokens[($stackPtr - 1)]["code"] !== T_WHITESPACE)
		    {
			$error = _("Expected 1 space before logical operator; 0 found");
			$phpcsFile->addError($error, $stackPtr, "NoSpaceBefore");
		    }
		else
		    {
			$prev = $phpcsFile->findPrevious(T_WHITESPACE, ($stackPtr - 1), null, true);
			if ($tokens[$stackPtr]["line"] === $tokens[$prev]["line"] && strlen($tokens[($stackPtr - 1)]["content"]) !== 1)
			    {
				$found = strlen($tokens[($stackPtr - 1)]["content"]);
				$error = _("Expected 1 space before logical operator;") . " %s " . _("found");
				$data  = array($found);
				$phpcsFile->addError($error, $stackPtr, "TooMuchSpaceBefore", $data);
			    }
		    }

		// Check there is one space after the operator.
		if ($tokens[($stackPtr + 1)]["code"] !== T_WHITESPACE)
		    {
			$error = _("Expected 1 space after logical operator; 0 found");
			$phpcsFile->addError($error, $stackPtr, "NoSpaceAfter");
		    }
		else
		    {
			$next = $phpcsFile->findNext(T_WHITESPACE, ($stackPtr - 1), null, true);
			if ($tokens[$stackPtr]["line"] === $tokens[$next]["line"] && strlen($tokens[($stackPtr + 1)]["content"]) !== 1)
			    {
				$found = strlen($tokens[($stackPtr + 1)]["content"]);
				$error = _("Expected 1 space after logical operator;") . " %s " . _("found");
				$data  = array($found);
				$phpcsFile->addError($error, $stackPtr, "TooMuchSpaceAfter", $data);
			    }
		    }
	    } //end process()


    } //end class

?>
