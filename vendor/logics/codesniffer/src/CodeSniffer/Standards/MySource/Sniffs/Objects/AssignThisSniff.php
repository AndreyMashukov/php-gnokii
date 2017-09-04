<?php

/**
 * Ensures this is not assigned to any other var but self.
 *
 * PHP version 5
 *
 * @package Logics\BuildTools\CodeSniffer\MySource
 */

namespace Logics\BuildTools\CodeSniffer\MySource;

use \Logics\BuildTools\CodeSniffer\File;
use \Logics\BuildTools\CodeSniffer\Sniff;

/**
 * Ensures this is not assigned to any other var but self.
 *
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2006-2012 Squiz Pty Ltd (ABN 77 084 670 600)
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 * @version   SVN: $Date: 2016-08-16 23:39:01 +0900 (Tue, 16 Aug 2016) $ $Revision: 26 $
 * @link      $HeadURL: https://open.logics.net.au/buildtools/codesniffer/tags/0.1.3/src/CodeSniffer/Standards/MySource/Sniffs/Objects/AssignThisSniff.php $
 *
 * @untranslatable JS
 */

class AssignThisSniff implements Sniff
    {

	/**
	 * A list of tokenizers this sniff supports.
	 *
	 * @var array
	 */
	public $supportedTokenizers = array("JS");

	/**
	 * Returns an array of tokens this test wants to listen for.
	 *
	 * @return array
	 *
	 * @internalconst T_THIS      T_THIS token
	 */

	public function register()
	    {
		return array(T_THIS);
	    } //end register()


	/**
	 * Processes this test, when one of its tokens is encountered.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $stackPtr  The position of the current token in the stack passed in $tokens.
	 *
	 * @return void
	 *
	 * @internalconst T_SEMICOLON T_SEMICOLON token
	 * @internalconst T_EQUAL     T_EQUAL token
	 *
	 * @untranslatable self
	 * @untranslatable _self
	 * @untranslatable NotSelf
	 */

	public function process(File $phpcsFile, $stackPtr)
	    {
		$tokens = &$phpcsFile->tokens;

		// Ignore this.something and other uses of "this" that are not
		// direct assignments.
		$next = $phpcsFile->findNext(T_WHITESPACE, ($stackPtr + 1), null, true);
		if ($tokens[$next]["code"] === T_SEMICOLON || $tokens[$next]["line"] !== $tokens[$stackPtr]["line"])
		    {
			// Something must be assigned to "this".
			$prev = $phpcsFile->findPrevious(T_WHITESPACE, ($stackPtr - 1), null, true);
			if ($tokens[$prev]["code"] === T_EQUAL)
			    {
				// A variable needs to be assigned to "this".
				$prev = $phpcsFile->findPrevious(T_WHITESPACE, ($prev - 1), null, true);
				if ($tokens[$prev]["code"] === T_STRING)
				    {
					// We can only assign "this" to a var called "self".
					if ($tokens[$prev]["content"] !== "self" && $tokens[$prev]["content"] !== "_self")
					    {
						$error = _("Keyword \"this\" can only be assigned to a variable called \"self\" or \"_self\"");
						$phpcsFile->addError($error, $prev, "NotSelf");
					    }
				    }
			    }
		    }
	    } //end process()


    } //end class

?>
