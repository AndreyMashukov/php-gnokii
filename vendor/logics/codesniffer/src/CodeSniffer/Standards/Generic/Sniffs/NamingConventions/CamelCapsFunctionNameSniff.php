<?php

/**
 * PHP version 5
 *
 * @package Logics\BuildTools\CodeSniffer\Generic
 */

namespace Logics\BuildTools\CodeSniffer\Generic;

use \Logics\BuildTools\CodeSniffer\AbstractScopeSniff;
use \Logics\BuildTools\CodeSniffer\CodeSniffer;
use \Logics\BuildTools\CodeSniffer\File;

/**
 * Generic_Sniffs_NamingConventions_CamelCapsFunctionNameSniff.
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
 * @link      $HeadURL: https://open.logics.net.au/buildtools/codesniffer/tags/0.1.3/src/CodeSniffer/Standards/Generic/Sniffs/NamingConventions/CamelCapsFunctionNameSniff.php $
 */

class CamelCapsFunctionNameSniff extends AbstractScopeSniff
    {

	/**
	 * A list of all PHP magic methods.
	 *
	 * @var array
	 */
	protected $magicMethods = array(
				   "construct"  => true,
				   "destruct"   => true,
				   "call"       => true,
				   "callstatic" => true,
				   "get"        => true,
				   "set"        => true,
				   "isset"      => true,
				   "unset"      => true,
				   "sleep"      => true,
				   "wakeup"     => true,
				   "tostring"   => true,
				   "set_state"  => true,
				   "clone"      => true,
				   "invoke"     => true,
				  );

	/**
	 * A list of all PHP non-magic methods starting with a double underscore.
	 *
	 * These come from PHP modules such as SOAPClient.
	 *
	 * @var array
	 */
	protected $methodsDoubleUnderscore = array(
					      "soapcall"               => true,
					      "getlastrequest"         => true,
					      "getlastresponse"        => true,
					      "getlastrequestheaders"  => true,
					      "getlastresponseheaders" => true,
					      "getfunctions"           => true,
					      "gettypes"               => true,
					      "dorequest"              => true,
					      "setcookie"              => true,
					      "setlocation"            => true,
					      "setsoapheaders"         => true,
					     );

	/**
	 * A list of all PHP magic functions.
	 *
	 * @var array
	 */
	protected $magicFunctions = array("autoload" => true);

	/**
	 * If TRUE, the string must not have two capital letters next to each other.
	 *
	 * @var bool
	 */
	public $strict = false;

	/**
	 * Constructs a Generic_Sniffs_NamingConventions_CamelCapsFunctionNameSniff.
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
	 * @untranslatable %s
	 * @untranslatable ScopeNotCamelCaps
	 * @untranslatable \"%s\"
	 * @untranslatable NotCamelCaps
	 */

	protected function processTokenWithinScope(File &$phpcsFile, $stackPtr, $currScope)
	    {
		$methodName = $phpcsFile->getDeclarationName($stackPtr);
		if ($methodName !== null)
		    {
			$className = $phpcsFile->getDeclarationName($currScope);
			$errorData = array($className . "::" . $methodName);

			// Is this a magic method. i.e., is prefixed with "__" ?
			if (preg_match("|^__|", $methodName) !== 0)
			    {
				$magicPart = strtolower(substr($methodName, 2));
				if (isset($this->magicMethods[$magicPart]) === false && isset($this->methodsDoubleUnderscore[$magicPart]) === false)
				    {
					$error = _("Method name") . " \"%s\" " . _("is invalid; only PHP magic methods should be prefixed with a double underscore");
					$phpcsFile->addError($error, $stackPtr, "MethodDoubleUnderscore", $errorData);
				    }
			    }
			else
			    {
				// PHP4 constructors are allowed to break our rules.
				if ($methodName !== $className && $methodName !== "_" . $className)
				    {
					// Ignore first underscore in methods prefixed with "_".
					$methodName = ltrim($methodName, "_");

					$methodProps = $phpcsFile->getMethodProperties($stackPtr);
					if (CodeSniffer::isCamelCaps($methodName, false, $methodProps["scope"] !== "private", $this->strict) === false)
					    {
						if ($methodProps["scope_specified"] === true)
						    {
							$error = "%s " . _("method name") . " \"%s\" " . _("is not in camel caps format");
							$data  = array(
								  ucfirst($methodProps["scope"]),
								  $errorData[0],
								 );
							$phpcsFile->addError($error, $stackPtr, "ScopeNotCamelCaps", $data);
						    }
						else
						    {
							$error = _("Method name") . " \"%s\" " . _("is not in camel caps format");
							$phpcsFile->addError($error, $stackPtr, "NotCamelCaps", $errorData);
						    } //end if
					    } //end if
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
	 * @untranslatable \"%s\"
	 * @untranslatable NotCamelCaps
	 */

	protected function processTokenOutsideScope(File &$phpcsFile, $stackPtr)
	    {
		$functionName = $phpcsFile->getDeclarationName($stackPtr);
		if ($functionName !== null)
		    {
			$errorData = array($functionName);

			// Is this a magic function. i.e., it is prefixed with "__".
			if (preg_match("|^__|", $functionName) !== 0)
			    {
				$magicPart = strtolower(substr($functionName, 2));
				if (isset($this->magicFunctions[$magicPart]) === false)
				    {
					$error = _("Function name") . " \"%s\" " . _("is invalid; only PHP magic methods should be prefixed with a double underscore");
					$phpcsFile->addError($error, $stackPtr, "FunctionDoubleUnderscore", $errorData);
				    }
			    }
			else
			    {
				// Ignore first underscore in functions prefixed with "_".
				$functionName = ltrim($functionName, "_");

				if (CodeSniffer::isCamelCaps($functionName, false, true, $this->strict) === false)
				    {
					$error = _("Function name") . " \"%s\" " . _("is not in camel caps format");
					$phpcsFile->addError($error, $stackPtr, "NotCamelCaps", $errorData);
				    }
			    } //end if
		    } //end if
	    } //end processTokenOutsideScope()


    } //end class

?>
