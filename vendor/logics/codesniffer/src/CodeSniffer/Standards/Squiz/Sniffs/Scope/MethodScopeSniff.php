<?php

/**
 * PHP version 5
 *
 * @package Logics\BuildTools\CodeSniffer\Squiz
 */

namespace Logics\BuildTools\CodeSniffer\Squiz;

use \Logics\BuildTools\CodeSniffer\AbstractScopeSniff;
use \Logics\BuildTools\CodeSniffer\File;
use \Logics\BuildTools\CodeSniffer\Tokens;

/**
 * Verifies that class members have scope modifiers.
 *
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @author    Marc McIntyre <mmcintyre@squiz.net>
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2006-2012 Squiz Pty Ltd (ABN 77 084 670 600)
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 * @version   SVN: $Date: 2016-08-16 23:39:01 +0900 (Tue, 16 Aug 2016) $ $Revision: 26 $
 * @link      $HeadURL: https://open.logics.net.au/buildtools/codesniffer/tags/0.1.3/src/CodeSniffer/Standards/Squiz/Sniffs/Scope/MethodScopeSniff.php $
 */

class MethodScopeSniff extends AbstractScopeSniff
    {

	/**
	 * Constructs a Squiz_Sniffs_Scope_MethodScopeSniff.
	 *
	 * @return void
	 */

	public function __construct()
	    {
		parent::__construct(array(T_CLASS, T_INTERFACE), array(T_FUNCTION));
	    } //end __construct()


	/**
	 * Processes the function tokens within the class.
	 *
	 * @param File $phpcsFile The file where this token was found.
	 * @param int  $stackPtr  The position where the token was found.
	 * @param int  $currScope The current scope opener token.
	 *
	 * @return void
	 *
	 * @untranslatable Missing
	 * @untranslatable \"%s\"
	 */

	protected function processTokenWithinScope(File &$phpcsFile, $stackPtr, $currScope)
	    {
		unset($currScope);
		$tokens = &$phpcsFile->tokens;

		$methodName = $phpcsFile->getDeclarationName($stackPtr);
		// Ignore closures.
		if ($methodName !== null)
		    {
			$modifier = $phpcsFile->findPrevious(Tokens::$scopeModifiers, $stackPtr);
			if (($modifier === false) || ($tokens[$modifier]["line"] !== $tokens[$stackPtr]["line"]))
			    {
				$error = _("No scope modifier specified for function") . " \"%s\"";
				$data  = array($methodName);
				$phpcsFile->addError($error, $stackPtr, "Missing", $data);
			    }
		    }
	    } //end processTokenWithinScope()


    } //end class

?>
