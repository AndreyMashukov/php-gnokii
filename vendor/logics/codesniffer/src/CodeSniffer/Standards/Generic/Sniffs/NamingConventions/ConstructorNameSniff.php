<?php

/**
 * PHP version 5
 *
 * @package Logics\BuildTools\CodeSniffer\Generic
 */

namespace Logics\BuildTools\CodeSniffer\Generic;

use \Logics\BuildTools\CodeSniffer\AbstractScopeSniff;
use \Logics\BuildTools\CodeSniffer\File;

/**
 * ConstructorNameSniff.
 *
 * Favor PHP 5 constructor syntax, which uses "function __construct()".
 * Avoid PHP 4 constructor syntax, which uses "function ClassName()".
 *
 * @author    Leif Wickland <lwickland@rightnow.com>
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 * @version   SVN: $Date: 2016-08-16 23:39:01 +0900 (Tue, 16 Aug 2016) $ $Revision: 26 $
 * @link      $HeadURL: https://open.logics.net.au/buildtools/codesniffer/tags/0.1.3/src/CodeSniffer/Standards/Generic/Sniffs/NamingConventions/ConstructorNameSniff.php $
 */

class ConstructorNameSniff extends AbstractScopeSniff
    {

	/**
	 * Constructs the test with the tokens it wishes to listen for.
	 *
	 * @return void
	 */

	public function __construct()
	    {
		parent::__construct(array(T_CLASS, T_INTERFACE), array(T_FUNCTION), true);
	    } //end __construct()


	/**
	 * Processes this test when one of its tokens is encountered.
	 *
	 * @param File $phpcsFile The current file being scanned.
	 * @param int  $stackPtr  The position of the current token in the stack passed in $tokens.
	 * @param int  $currScope A pointer to the start of the scope.
	 *
	 * @return void
	 *
	 * @untranslatable __construct
	 * @untranslatable OldStyle
	 * @untranslatable OldStyleCall
	 */

	protected function processTokenWithinScope(File &$phpcsFile, $stackPtr, $currScope)
	    {
		$className  = $phpcsFile->getDeclarationName($currScope);
		$methodName = $phpcsFile->getDeclarationName($stackPtr);

		if (strcasecmp($methodName, $className) === 0 || strcasecmp($methodName, "__construct") === 0)
		    {
			if (strcasecmp($methodName, $className) === 0)
			    {
				$error = _("PHP4 style constructors are not allowed; use \"__construct()\" instead");
				$phpcsFile->addError($error, $stackPtr, "OldStyle");
			    }

			$tokens = &$phpcsFile->tokens;

			$parentClassName = $phpcsFile->findExtendedClassName($currScope);
			if ($parentClassName !== false)
			    {
				$endFunctionIndex = $tokens[$stackPtr]["scope_closer"];
				$startIndex       = $stackPtr;
				while ($doubleColonIndex = $phpcsFile->findNext(array(T_DOUBLE_COLON), $startIndex, $endFunctionIndex))
				    {
					if ($tokens[($doubleColonIndex + 1)]["code"] === T_STRING && $tokens[($doubleColonIndex + 1)]["content"] === $parentClassName)
					    {
						$error = _("PHP4 style calls to parent constructors are not allowed; use \"parent::__construct()\" instead");
						$phpcsFile->addError($error, ($doubleColonIndex + 1), "OldStyleCall");
					    }

					$startIndex = ($doubleColonIndex + 1);
				    }
			    }
		    } //end if
	    } //end processTokenWithinScope()


    } //end class

?>
