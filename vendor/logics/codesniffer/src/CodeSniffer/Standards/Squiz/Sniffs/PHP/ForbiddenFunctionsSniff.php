<?php

/**
 * PHP version 5
 *
 * @package Logics\BuildTools\CodeSniffer\Squiz
 */

namespace Logics\BuildTools\CodeSniffer\Squiz;

use \Logics\BuildTools\CodeSniffer\Generic\ForbiddenFunctionsSniff as Generic_ForbiddenFunctionsSniff;

/**
 * ForbiddenFunctionsSniff
 *
 * Discourages the use of alias functions that are kept in PHP for compatibility
 * with older versions. Can be used to forbid the use of any function.
 *
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @author    Marc McIntyre <mmcintyre@squiz.net>
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2006-2012 Squiz Pty Ltd (ABN 77 084 670 600)
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 * @version   SVN: $Date: 2016-08-16 23:39:01 +0900 (Tue, 16 Aug 2016) $ $Revision: 26 $
 * @link      $HeadURL: https://open.logics.net.au/buildtools/codesniffer/tags/0.1.3/src/CodeSniffer/Standards/Squiz/Sniffs/PHP/ForbiddenFunctionsSniff.php $
 *
 * @untranslatable count
 * @untranslatable unset
 * @untranslatable echo
 */

class ForbiddenFunctionsSniff extends Generic_ForbiddenFunctionsSniff
    {

	/**
	 * A list of forbidden functions with their alternatives.
	 *
	 * The value is NULL if no alternative exists. IE, the function should just not be used.
	 *
	 * @var array(string => string|null)
	 */
	protected $forbiddenFunctions = array(
					 "sizeof"          => "count",
					 "delete"          => "unset",
					 "print"           => "echo",
					 "assert"          => null,
					 "is_null"         => null,
					 "create_function" => null,
					 "extract"         => null,
					);

	/**
	 * Returns an array of tokens this test wants to listen for.
	 *
	 * @return array
	 */

	public function register()
	    {
		$tokens   = parent::register();
		$tokens[] = T_PRINT;
		return $tokens;
	    } //end register()


    } //end class

?>
