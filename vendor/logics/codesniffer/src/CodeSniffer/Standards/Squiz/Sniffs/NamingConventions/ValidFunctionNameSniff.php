<?php

/**
 * PHP version 5
 *
 * @package Logics\BuildTools\CodeSniffer\Squiz
 */

namespace Logics\BuildTools\CodeSniffer\Squiz;

use \Logics\BuildTools\CodeSniffer\CodeSniffer;
use \Logics\BuildTools\CodeSniffer\File;
use \Logics\BuildTools\CodeSniffer\PEAR\ValidFunctionNameSniff as PEAR_ValidFunctionNameSniff;

/**
 * ValidFunctionNameSniff
 *
 * Ensures method names are correct depending on whether they are public
 * or private, and that functions are named correctly.
 *
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @author    Marc McIntyre <mmcintyre@squiz.net>
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2006-2012 Squiz Pty Ltd (ABN 77 084 670 600)
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 * @version   SVN: $Date: 2016-08-16 23:39:01 +0900 (Tue, 16 Aug 2016) $ $Revision: 26 $
 * @link      $HeadURL: https://open.logics.net.au/buildtools/codesniffer/tags/0.1.3/src/CodeSniffer/Standards/Squiz/Sniffs/NamingConventions/ValidFunctionNameSniff.php $
 */

class ValidFunctionNameSniff extends PEAR_ValidFunctionNameSniff
    {

	/**
	 * Processes the tokens outside the scope.
	 *
	 * @param File $phpcsFile The file being processed.
	 * @param int  $stackPtr  The position where this token was found.
	 *
	 * @return void
	 *
	 * @untranslatable DoubleUnderscore
	 * @untranslatable NotCamelCaps
	 * @untranslatable \"%s\"
	 */

	protected function processTokenOutsideScope(File &$phpcsFile, $stackPtr)
	    {
		$functionName = $phpcsFile->getDeclarationName($stackPtr);
		if ($functionName !== null)
		    {
			$errorData = array($functionName);

			// Does this function claim to be magical?
			if (preg_match("/^__/", $functionName) !== 0)
			    {
				$error = _("Function name") . " \"%s\" " . _("is invalid; only PHP magic methods should be prefixed with a double underscore");
				$phpcsFile->addError($error, $stackPtr, "DoubleUnderscore", $errorData);
			    }
			else if (CodeSniffer::isCamelCaps($functionName, false, true, false) === false)
			    {
				$error = _("Function name") . " \"%s\" " . _("is not in camel caps format");
				$phpcsFile->addError($error, $stackPtr, "NotCamelCaps", $errorData);
			    }
		    }
	    } //end processTokenOutsideScope()


    } //end class

?>
