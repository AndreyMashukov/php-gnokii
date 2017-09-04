<?php

/**
 * PHP version 5
 *
 * @package Logics\BuildTools\CodeSniffer\PEAR
 */

namespace Logics\BuildTools\CodeSniffer\PEAR;

use \Logics\BuildTools\CodeSniffer\AbstractScopeSniff;
use \Logics\BuildTools\CodeSniffer\CodeSniffer;
use \Logics\BuildTools\CodeSniffer\File;

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
 * @link      $HeadURL: https://open.logics.net.au/buildtools/codesniffer/tags/0.1.3/src/CodeSniffer/Standards/PEAR/Sniffs/NamingConventions/ValidFunctionNameSniff.php $
 *
 * @untranslatable autoload
 * @untranslatable construct
 * @untranslatable destruct
 * @untranslatable callstatic
 * @untranslatable get
 * @untranslatable set
 * @untranslatable isset
 * @untranslatable unset
 * @untranslatable sleep
 * @untranslatable wakeup
 * @untranslatable tostring
 * @untranslatable set_state
 * @untranslatable clone
 * @untranslatable invoke
 * @untranslatable call
 */

class ValidFunctionNameSniff extends AbstractScopeSniff
    {

	/**
	 * A list of all PHP magic methods.
	 *
	 * @var array
	 */
	protected $magicMethods = array(
				   "construct",
				   "destruct",
				   "call",
				   "callstatic",
				   "get",
				   "set",
				   "isset",
				   "unset",
				   "sleep",
				   "wakeup",
				   "tostring",
				   "set_state",
				   "clone",
				   "invoke",
				   "call",
				  );

	/**
	 * A list of all PHP magic functions.
	 *
	 * @var array
	 */
	protected $magicFunctions = array("autoload");

	/**
	 * Constructs a ValidFunctionNameSniff.
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
	 * @untranslatable MethodDoubleUnderscore
	 * @untranslatable private
	 * @untranslatable PrivateNoUnderscore
	 * @untranslatable PublicUnderscore
	 * @untranslatable %s
	 * @untranslatable ScopeNotCamelCaps
	 * @untranslatable \"%s\"
	 * @untranslatable NotCamelCaps
	 */

	protected function processTokenWithinScope(File &$phpcsFile, $stackPtr, $currScope)
	    {
		$methodName = $phpcsFile->getDeclarationName($stackPtr);
		// Ignore closures.
		if ($methodName !== null)
		    {
			$className = $phpcsFile->getDeclarationName($currScope);
			$errorData = array($className . "::" . $methodName);

			// Is this a magic method. i.e., is prefixed with "__" ?
			if (preg_match("/^__/", $methodName) !== 0)
			    {
				if (in_array(strtolower(substr($methodName, 2)), $this->magicMethods) === false)
				    {
					$error = _("Method name") . " \"%s\" " . _("is invalid; only PHP magic methods should be prefixed with a double underscore");
					$phpcsFile->addError($error, $stackPtr, "MethodDoubleUnderscore", $errorData);
				    }
			    }
			else if ($methodName !== $className && $methodName !== "_" . $className)
			    {
				// PHP4 constructors/destructors are allowed to break our rules.
				$methodProps    = $phpcsFile->getMethodProperties($stackPtr);
				$isPublic       = ($methodProps["scope"] === "private") ? false : true;
				$scope          = $methodProps["scope"];
				$scopeSpecified = $methodProps["scope_specified"];

				if ($isPublic === false && $methodName{0} !== "_")
				    {
					// If it's a private method, it must have an underscore on the front.
					$error = _("Private method name") . " \"%s\" " . _("must be prefixed with an underscore");
					$phpcsFile->addError($error, $stackPtr, "PrivateNoUnderscore", $errorData);
				    }
				else if ($isPublic === true && $scopeSpecified === true && $methodName{0} === "_")
				    {
					// If it's not a private method, it must not have an underscore on the front.
					$error = "%s " . _("method name") . " \"%s\" " . _("must not be prefixed with an underscore");
					$data  = array(
						  ucfirst($scope),
						  $errorData[0],
						 );
					$phpcsFile->addError($error, $stackPtr, "PublicUnderscore", $data);
				    }
				else
				    {
					// If the scope was specified on the method, then the method must be
					// camel caps and an underscore should be checked for. If it wasn't
					// specified, treat it like a public method and remove the underscore
					// prefix if there is one because we cant determine if it is private or
					// public.
					$testMethodName = $methodName;
					if ($scopeSpecified === false && $methodName{0} === "_")
					    {
						$testMethodName = substr($methodName, 1);
					    }

					if (CodeSniffer::isCamelCaps($testMethodName, false, $isPublic, false) === false)
					    {
						if ($scopeSpecified === true)
						    {
							$error = "%s " . _("method name") . " \"%s\" " . _("is not in camel caps format");
							$data  = array(
								  ucfirst($scope),
								  $errorData[0],
								 );
							$phpcsFile->addError($error, $stackPtr, "ScopeNotCamelCaps", $data);
						    }
						else
						    {
							$error = _("Method name") . " \"%s\" " . _("is not in camel caps format");
							$phpcsFile->addError($error, $stackPtr, "NotCamelCaps", $errorData);
						    }
					    }
				    } //end if
			    } //end if
		    } //end if
	    } //end processTokenWithinScope()


	/**
	 * Processes the tokens outside the scope.
	 *
	 * @param File $phpcsFile The file being processed.
	 * @param int  $stackPtr  The position where this token was found.
	 *
	 * @return void
	 *
	 * @untranslatable FunctionDoubleUnderscore
	 * @untranslatable FunctionUnderscore
	 * @untranslatable FunctionNoCapital
	 * @untranslatable FunctionInvalid
	 * @untranslatable \"%s\"
	 * @untranslatable FunctionNameInvalid
	 */

	protected function processTokenOutsideScope(File &$phpcsFile, $stackPtr)
	    {
		$functionName = $phpcsFile->getDeclarationName($stackPtr);
		// Ignore closures.
		if ($functionName !== null)
		    {
			$errorData = array($functionName);

			// Is this a magic function. i.e., it is prefixed with "__".
			if (preg_match("/^__/", $functionName) !== 0)
			    {
				if (in_array(strtolower(substr($functionName, 2)), $this->magicFunctions) === false)
				    {
					$error = _("Function name") . " \"%s\" " . _("is invalid; only PHP magic methods should be prefixed with a double underscore");
					$phpcsFile->addError($error, $stackPtr, "FunctionDoubleUnderscore", $errorData);
				    }
			    }
			else
			    {
				// Function names can be in two parts; the package name and
				// the function name.
				$packagePart   = "";
				$camelCapsPart = "";
				$underscorePos = strrpos($functionName, "_");
				if ($underscorePos === false)
				    {
					$camelCapsPart = $functionName;
				    }
				else
				    {
					$packagePart   = substr($functionName, 0, $underscorePos);
					$camelCapsPart = substr($functionName, ($underscorePos + 1));

					// We don't care about _'s on the front.
					$packagePart = ltrim($packagePart, "_");
				    }

				// If it has a package part, make sure the first letter is a capital.
				if ($packagePart !== "" && $functionName{0} === "_")
				    {
					$error = _("Function name") . " \"%s\" " . _("is invalid; only private methods should be prefixed with an underscore");
					$phpcsFile->addError($error, $stackPtr, "FunctionUnderscore", $errorData);
				    }
				else if ($packagePart !== "" && $functionName{0} !== strtoupper($functionName{0}))
				    {
					$error = _("Function name") . " \"%s\" " . _("is prefixed with a package name but does not begin with a capital letter");
					$phpcsFile->addError($error, $stackPtr, "FunctionNoCapital", $errorData);
				    }
				else if (trim($camelCapsPart) === "")
				    {
					// If it doesn't have a camel caps part, it's not valid.
					$error = _("Function name") . " \"%s\" " . _("is not valid; name appears incomplete");
					$phpcsFile->addError($error, $stackPtr, "FunctionInvalid", $errorData);
				    }
				else
				    {
					$validName        = true;
					$newPackagePart   = $packagePart;
					$newCamelCapsPart = $camelCapsPart;

					// Every function must have a camel caps part, so check that first.
					if (CodeSniffer::isCamelCaps($camelCapsPart, false, true, false) === false)
					    {
						$validName        = false;
						$newCamelCapsPart = strtolower($camelCapsPart{0}) . substr($camelCapsPart, 1);
					    }

					$this->_checkPackage($packagePart, $newPackagePart, $validName);

					if ($validName === false)
					    {
						$error  = _("Function name") . " \"%s\" " . _("is invalid; consider") . " \"%s\" " . _("instead");
						$data   = $errorData;
						$data[] = ($newPackagePart === "") ? $newCamelCapsPart : rtrim($newPackagePart, "_") . "_" . $newCamelCapsPart;
						$phpcsFile->addError($error, $stackPtr, "FunctionNameInvalid", $data);
					    }
				    } //end if
			    } //end if
		    } //end if
	    } //end processTokenOutsideScope()


	/**
	 * Check package part
	 *
	 * @param string $packagePart    Package part
	 * @param string $newPackagePart New package part
	 * @param bool   $validName      True if name is valid
	 *
	 * @return void
	 */

	private function _checkPackage($packagePart, &$newPackagePart, &$validName)
	    {
		if ($packagePart !== "")
		    {
			// Check that each new word starts with a capital.
			$nameBits = explode("_", $packagePart);
			foreach ($nameBits as $bit)
			    {
				if ($bit{0} !== strtoupper($bit{0}))
				    {
					$newPackagePart = "";
					foreach ($nameBits as $bit)
					    {
						$newPackagePart .= strtoupper($bit{0}) . substr($bit, 1) . "_";
					    }

					$validName = false;
					break;
				    }
			    }
		    }
	    } //end _checkPackage()


    } //end class

?>
