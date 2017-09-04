<?php

/**
 * PHP version 5
 *
 * @package Logics\BuildTools\CodeSniffer\Beauty
 */

namespace Logics\BuildTools\CodeSniffer\Beauty;

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
 * @license   http://matrix.squiz.net/developer/tools/php_cs/licence BSD Licence
 * @version   SVN: $Date: 2016-08-16 23:39:01 +0900 (Tue, 16 Aug 2016) $ $Revision: 26 $
 * @link      $HeadURL: https://open.logics.net.au/buildtools/codesniffer/tags/0.1.3/src/CodeSniffer/Standards/Beauty/Sniffs/NamingConventions/ValidVariableNameSniff.php $
 *
 * @untranslatable _GET
 * @untranslatable _POST
 * @untranslatable _REQUEST
 * @untranslatable _COOKIE
 * @untranslatable _FILES
 * @untranslatable _SERVER
 * @untranslatable _SESSION
 * @untranslatable _ENV
 * @untranslatable GLOBALS
 */

class ValidVariableNameSniff extends Squiz_ValidVariableNameSniff
    {

	/**
	 * Files with special treatment
	 *
	 * @var array
	 */
	private static $_specialFiles = array(
					 "RequestValidator.php",
					 "ScriptExecutor.php",
					);

	/**
	 * PHP reserved var names for special files
	 *
	 * @var array
	 */
	private static $_phpReservedVarsSpecial = array(
						   "_GET",
						   "_POST",
						   "_REQUEST",
						   "_COOKIE",
						   "_FILES",
						   "_SERVER",
						   "_SESSION",
						   "_ENV",
						   "GLOBALS",
						  );

	/**
	 * Generally prohibited reserved PHP variables
	 *
	 * @var array
	 */
	private static $_prohibitedReservedVars = array(
						   "_GET",
						   "_POST",
						   "_REQUEST",
						   "_COOKIE",
						   "_FILES",
						  );

	/**
	 * Variables reserved for PHP use
	 *
	 * @var array
	 */
	private static $_phpReservedVars = array(
					    "_SERVER",
					    "_SESSION",
					    "_ENV",
					    "GLOBALS",
					   );

	/**
	 * Processes this test, when one of its tokens is encountered.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $stackPtr  The position of the current token in the stack passed in $tokens.
	 *
	 * @return void
	 */

	protected function processVariable(File &$phpcsFile, $stackPtr)
	    {
		$tokens   = &$phpcsFile->tokens;
		$varName  = ltrim($tokens[$stackPtr]["content"], "$");
		$filename = basename($phpcsFile->getFilename());

		if (in_array($filename, self::$_specialFiles) === true)
		    {
			if (in_array($varName, self::$_phpReservedVarsSpecial) === false)
			    {
				// If it's a php reserved var, then its ok.
				$this->checkVariable($phpcsFile, $stackPtr, $tokens, $varName);
			    }
		    }
		else
		    {
			if (in_array($varName, self::$_prohibitedReservedVars) === true)
			    {
				// If it's a php reserved var, then its ok.
				$error = _("Variable") . " \"" . $varName . "\" " . _("is not allowed, use RequestValidator class instead");
				$phpcsFile->addError($error, $stackPtr);
			    }
			else if (in_array($varName, self::$_phpReservedVars) === false)
			    {
				// If it's a php reserved var, then its ok.
				$this->checkVariable($phpcsFile, $stackPtr, $tokens, $varName);
			    }
		    } //end if
	    } //end processVariable()


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
	 * @untranslatable \"%s\"
	 */

	protected function validateVariable(File &$phpcsFile, $stackPtr, $varName, $originalVarName, $prefix = "", $public = true)
	    {
		if (CodeSniffer::isCamelCaps($varName, false, $public, false, true) === false)
		    {
			$error = _("Variable") . " \"%s\" " . _("is not in valid camel caps format");
			$data  = array($originalVarName);
			$phpcsFile->addError($error, $stackPtr, $prefix . "NotCamelCaps", $data);
		    }
	    } //end validateVariable()


    } //end class

?>
