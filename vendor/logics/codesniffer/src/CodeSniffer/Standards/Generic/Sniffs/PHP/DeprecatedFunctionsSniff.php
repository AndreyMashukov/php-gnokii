<?php

/**
 * PHP version 5
 *
 * @package Logics\BuildTools\CodeSniffer\Generic
 */

namespace Logics\BuildTools\CodeSniffer\Generic;

use \Logics\BuildTools\CodeSniffer\File;
use \ReflectionFunction;

/**
 * DeprecatedFunctionsSniff.
 *
 * Discourages the use of deprecated functions that are kept in PHP for
 * compatibility with older versions.
 *
 * @author    Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @author    Marc McIntyre <mmcintyre@squiz.net>
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2006-2012 Squiz Pty Ltd (ABN 77 084 670 600)
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 * @version   SVN: $Date: 2016-08-16 23:39:01 +0900 (Tue, 16 Aug 2016) $ $Revision: 26 $
 * @link      $HeadURL: https://open.logics.net.au/buildtools/codesniffer/tags/0.1.3/src/CodeSniffer/Standards/Generic/Sniffs/PHP/DeprecatedFunctionsSniff.php $
 */

class DeprecatedFunctionsSniff extends ForbiddenFunctionsSniff
    {

	/**
	 * A list of forbidden functions with their alternatives.
	 *
	 * The value is NULL if no alternative exists. IE, the
	 * function should just not be used.
	 *
	 * @var array(string => string|null)
	 */
	protected $forbiddenFunctions = array();

	/**
	 * Constructor.
	 *
	 * Uses the Reflection API to get a list of deprecated functions.
	 *
	 * @return void
	 */

	public function __construct()
	    {
		$functions = get_defined_functions();

		foreach ($functions["internal"] as $functionName)
		    {
			$function = new ReflectionFunction($functionName);

			if ($function->isDeprecated() === true)
			    {
				$this->forbiddenFunctions[$functionName] = null;
			    }
		    }
	    } //end __construct()


	/**
	 * Generates the error or warning for this sniff.
	 *
	 * @param File   $phpcsFile The file being scanned.
	 * @param int    $stackPtr  The position of the forbidden function in the token array.
	 * @param string $function  The name of the forbidden function.
	 * @param string $pattern   The pattern used for the match.
	 *
	 * @return void
	 *
	 * @untranslatable %s()
	 * @untranslatable Deprecated
	 */

	protected function addError(File $phpcsFile, $stackPtr, $function, $pattern = null)
	    {
		unset($pattern);
		$data  = array($function);
		$error = _("Function") . " %s() " . _("has been deprecated");
		$type  = "Deprecated";

		if ($this->error === true)
		    {
			$phpcsFile->addError($error, $stackPtr, $type, $data);
		    }
		else
		    {
			$phpcsFile->addWarning($error, $stackPtr, $type, $data);
		    }
	    } //end addError()


    } //end class

?>
