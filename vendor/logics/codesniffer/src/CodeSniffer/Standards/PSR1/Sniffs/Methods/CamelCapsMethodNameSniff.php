<?php

/**
 * SideEffectsSniff
 *
 * PHP version 5
 *
 * @package Logics\BuildTools\CodeSniffer\PSR1
 */

namespace Logics\BuildTools\CodeSniffer\PSR1;

use \Logics\BuildTools\CodeSniffer\CodeSniffer;
use \Logics\BuildTools\CodeSniffer\File;
use \Logics\BuildTools\CodeSniffer\Generic\CamelCapsFunctionNameSniff;

/**
 * CamelCapsMethodNameSniff.
 *
 * Ensures method names are defined using camel case.
 *
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2006-2012 Squiz Pty Ltd (ABN 77 084 670 600)
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 * @version   SVN: $Date: 2016-08-16 23:39:01 +0900 (Tue, 16 Aug 2016) $ $Revision: 26 $
 * @link      $HeadURL: https://open.logics.net.au/buildtools/codesniffer/tags/0.1.3/src/CodeSniffer/Standards/PSR1/Sniffs/Methods/CamelCapsMethodNameSniff.php $
 */

class CamelCapsMethodNameSniff extends CamelCapsFunctionNameSniff
    {

	/**
	 * Constructs a PSR1_Sniffs_Methods_CamelCapsMethodNameSniff.
	 *
	 * @return void
	 */

	public function __construct()
	    {
		parent::__construct(array(T_CLASS, T_INTERFACE, T_TRAIT), array(T_FUNCTION), true);
	    } //end __construct()


	/**
	 * Processes the tokens within the scope.
	 *
	 * @param File $phpcsFile The file being processed.
	 * @param int  $stackPtr  The position where this token was found.
	 * @param int  $currScope The position of the current scope.
	 *
	 * @return void
	 *
	 * @untranslatable \"%s\"
	 * @untranslatable NotCamelCaps
	 */

	protected function processTokenWithinScope(File &$phpcsFile, $stackPtr, $currScope)
	    {
		$methodName = $phpcsFile->getDeclarationName($stackPtr);
		if ($methodName === null)
		    {
			// Ignore closures.
			return;
		    }

		// Ignore magic methods.
		if (preg_match("|^__|", $methodName) !== 0)
		    {
			$magicPart = strtolower(substr($methodName, 2));
			if (isset($this->magicMethods[$magicPart]) === true || isset($this->methodsDoubleUnderscore[$magicPart]) === true)
			    {
				return;
			    }
		    }

		$testName = ltrim($methodName, "_");
		if (CodeSniffer::isCamelCaps($testName, false, true, false) === false)
		    {
			$error     = _("Method name") . " \"%s\" " . _("is not in camel caps format");
			$className = $phpcsFile->getDeclarationName($currScope);
			$errorData = array($className . "::" . $methodName);
			$phpcsFile->addError($error, $stackPtr, "NotCamelCaps", $errorData);
		    }
	    } //end processTokenWithinScope()


	/**
	 * Processes the tokens outside the scope.
	 *
	 * @param File $phpcsFile The file being processed.
	 * @param int  $stackPtr  The position where this token was found.
	 *
	 * @return void
	 */

	protected function processTokenOutsideScope(File $phpcsFile, $stackPtr)
	    {
	    } //end processTokenOutsideScope()


    } //end class

?>