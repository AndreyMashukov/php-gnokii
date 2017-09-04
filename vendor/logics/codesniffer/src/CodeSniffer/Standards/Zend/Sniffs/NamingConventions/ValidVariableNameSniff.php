<?php

/**
 * PHP version 5
 *
 * @package Logics\BuildTools\CodeSniffer\Zend
 */

namespace Logics\BuildTools\CodeSniffer\Zend;

use \Logics\BuildTools\CodeSniffer\CodeSniffer;
use \Logics\BuildTools\CodeSniffer\File;
use \Logics\BuildTools\CodeSniffer\Squiz\ValidVariableNameSniff as Squiz_ValidVariableNameSniff;

/**
 * ValidVariableNameSniff
 *
 * Checks the naming of variables and member variables.
 *
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @author    Marc McIntyre <mmcintyre@squiz.net>
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2006-2012 Squiz Pty Ltd (ABN 77 084 670 600)
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 * @version   SVN: $Date: 2016-08-16 23:39:01 +0900 (Tue, 16 Aug 2016) $ $Revision: 26 $
 * @link      $HeadURL: https://open.logics.net.au/buildtools/codesniffer/tags/0.1.3/src/CodeSniffer/Standards/Zend/Sniffs/NamingConventions/ValidVariableNameSniff.php $
 */

class ValidVariableNameSniff extends Squiz_ValidVariableNameSniff
    {

	/**
	 * Variables may have or not leading underscore depending on scope. True means "no leading underscore".
	 *
	 * @var array
	 */
	protected $underscores = array(
				  "public"    => true,
				  "protected" => false,
				  "private"   => false,
				 );

	/**
	 * Check validity of variable name
	 *
	 * @param File   $phpcsFile       The file being scanned.
	 * @param int    $stackPtr        The position of the current token in the stack passed in $tokens.
	 * @param string $varName         Stripped down variable name
	 * @param string $originalVarName Original variable name
	 * @param string $prefix          Prefix to be used for error category
	 * @param bool   $public          Whether this variable is public or not
	 *
	 * @return void
	 *
	 * @untranslatable NotCamelCaps
	 * @untranslatable ContainsNumbers
	 * @untranslatable \"%s\"
	 */

	protected function validateVariable(File &$phpcsFile, $stackPtr, $varName, $originalVarName, $prefix = "", $public = true)
	    {
		if (CodeSniffer::isCamelCaps($varName, false, $public, false) === false)
		    {
			$error = _("Variable") . " \"%s\" " . _("is not in valid camel caps format");
			$data  = array($originalVarName);
			$phpcsFile->addError($error, $stackPtr, $prefix . "NotCamelCaps", $data);
		    }
		else if (preg_match("/\d/", $varName) > 0)
		    {
			$warning = _("Variable") . " \"%s\" " . _("contains numbers but this is discouraged");
			$data    = array($originalVarName);
			$phpcsFile->addWarning($warning, $stackPtr, $prefix . "ContainsNumbers", $data);
		    }
	    } //end validateVariable()


    } //end class

?>