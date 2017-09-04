<?php

/**
 * PHP version 5
 *
 * @package Logics\BuildTools\CodeSniffer\Squiz
 */

namespace Logics\BuildTools\CodeSniffer\Squiz;

use \Logics\BuildTools\CodeSniffer\AbstractPatternSniff;

/**
 * FunctionDeclarationSniff
 *
 * Checks the function declaration is correct.
 *
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @author    Marc McIntyre <mmcintyre@squiz.net>
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2006-2012 Squiz Pty Ltd (ABN 77 084 670 600)
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 * @version   SVN: $Date: 2016-08-16 23:39:01 +0900 (Tue, 16 Aug 2016) $ $Revision: 26 $
 * @link      $HeadURL: https://open.logics.net.au/buildtools/codesniffer/tags/0.1.3/src/CodeSniffer/Standards/Squiz/Sniffs/Functions/FunctionDeclarationSniff.php $
 */

class FunctionDeclarationSniff extends AbstractPatternSniff
    {

	/**
	 * Returns an array of patterns to check are correct.
	 *
	 * @return array
	 *
	 * @untranslatable function abc(...);
	 * @untranslatable abstract function abc(...);
	 */

	protected function getPatterns()
	    {
		return array(
			"function abc(...);",
			"abstract function abc(...);",
		       );
	    } //end getPatterns()


    } //end class

?>
