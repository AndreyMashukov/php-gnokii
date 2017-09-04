<?php

/**
 * PHP version 5
 *
 * @package Logics\BuildTools\CodeSniffer\Generic
 */

namespace Logics\BuildTools\CodeSniffer\Generic;

use \Logics\BuildTools\CodeSniffer\File;
use \Logics\BuildTools\CodeSniffer\Sniff;

/**
 * SAPIUsageSniff.
 *
 * Ensures the PHP_SAPI constant is used instead of php_sapi_name().
 *
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2006-2012 Squiz Pty Ltd (ABN 77 084 670 600)
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 * @version   SVN: $Date: 2016-08-16 23:39:01 +0900 (Tue, 16 Aug 2016) $ $Revision: 26 $
 * @link      $HeadURL: https://open.logics.net.au/buildtools/codesniffer/tags/0.1.3/src/CodeSniffer/Standards/Generic/Sniffs/PHP/SAPIUsageSniff.php $
 */

class SAPIUsageSniff implements Sniff
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
	 * @untranslatable php_sapi_name
	 * @untranslatable FunctionFound
	 */

	public function process(File $phpcsFile, $stackPtr)
	    {
		$tokens = &$phpcsFile->tokens;

		$ignore = array(
			   T_DOUBLE_COLON,
			   T_OBJECT_OPERATOR,
			   T_FUNCTION,
			   T_CONST,
			  );

		$prevToken = $phpcsFile->findPrevious(T_WHITESPACE, ($stackPtr - 1), null, true);
		if (in_array($tokens[$prevToken]["code"], $ignore) === false)
		    {
			// It is a call to a PHP function.
			$function = strtolower($tokens[$stackPtr]["content"]);
			if ($function === "php_sapi_name")
			    {
				$error = _("Use the PHP_SAPI constant instead of calling php_sapi_name()");
				$phpcsFile->addError($error, $stackPtr, "FunctionFound");
			    }
		    }
	    } //end process()


    } //end class

?>
