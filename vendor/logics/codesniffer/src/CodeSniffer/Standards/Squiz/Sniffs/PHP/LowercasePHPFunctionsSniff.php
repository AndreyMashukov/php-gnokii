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
 * Squiz_Sniffs_PHP_LowercasePHPFunctionsSniff.
 *
 * Ensures all calls to inbuilt PHP functions are lowercase.
 *
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @author    Marc McIntyre <mmcintyre@squiz.net>
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2006-2012 Squiz Pty Ltd (ABN 77 084 670 600)
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 * @version   SVN: $Date: 2016-08-16 23:39:01 +0900 (Tue, 16 Aug 2016) $ $Revision: 26 $
 * @link      $HeadURL: https://open.logics.net.au/buildtools/codesniffer/tags/0.1.3/src/CodeSniffer/Standards/Squiz/Sniffs/PHP/LowercasePHPFunctionsSniff.php $
 */

class LowercasePHPFunctionsSniff implements Sniff
    {

	/**
	 * Returns an array of tokens this test wants to listen for.
	 *
	 * @return array
	 */

	public function register()
	    {
		return array(T_STRING);
	    } //end register()


	/**
	 * Processes this test, when one of its tokens is encountered.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $stackPtr  The position of the current token in the stack passed in $tokens.
	 *
	 * @return void
	 *
	 * @internalconst T_OPEN_PARENTHESIS T_OPEN_PARENTHESIS token
	 * @internalconst T_BITWISE_AND      T_BITWISE_AND token
	 *
	 * @untranslatable CallUppercase
	 * @untranslatable \"%s\"
	 */

	public function process(File $phpcsFile, $stackPtr)
	    {
		$tokens = &$phpcsFile->tokens;

		// Make sure this is a function call.
		$next = $phpcsFile->findNext(T_WHITESPACE, ($stackPtr + 1), null, true);
		// Not a function call.
		if ($next !== false && $tokens[$next]["code"] === T_OPEN_PARENTHESIS)
		    {
			$prev = $phpcsFile->findPrevious(array(T_WHITESPACE, T_BITWISE_AND), ($stackPtr - 1), null, true);
			// Function declaration, not a function call.
			// Object creation, not an inbuilt function.
			// Not an inbuilt function.
			// Not an inbuilt function.
			if ($tokens[$prev]["code"] !== T_FUNCTION &&
			    $tokens[$prev]["code"] !== T_NEW &&
			    $tokens[$prev]["code"] !== T_OBJECT_OPERATOR &&
			    $tokens[$prev]["code"] !== T_DOUBLE_COLON)
			    {
				// Make sure it is an inbuilt PHP function.
				// CodeSniffer doesn't include/require any files, so no
				// user defined global functions can exist, except for
				// CodeSniffer ones.
				$content = $tokens[$stackPtr]["content"];
				if (function_exists($content) === true)
				    {
					if ($content !== strtolower($content))
					    {
						$error = _("Calls to inbuilt PHP functions must be lowercase; expected") . " \"%s\" " . _("but found") . " \"%s\"";
						$data  = array(
							  strtolower($content),
							  $content,
							 );
						$phpcsFile->addError($error, $stackPtr, "CallUppercase", $data);
					    }
				    }
			    }
		    } //end if
	    } //end process()


    } //end class

?>
