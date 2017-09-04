<?php

/**
 * PHP version 5
 *
 * @package Logics\BuildTools\CodeSniffer\Squiz
 */

namespace Logics\BuildTools\CodeSniffer\Squiz;

use \Logics\BuildTools\CodeSniffer\AbstractVariableSniff;
use \Logics\BuildTools\CodeSniffer\CodeSniffer;
use \Logics\BuildTools\CodeSniffer\File;

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
 * @link      $HeadURL: https://open.logics.net.au/buildtools/codesniffer/tags/0.1.3/src/CodeSniffer/Standards/Squiz/Sniffs/NamingConventions/ValidVariableNameSniff.php $
 */

class ValidVariableNameSniff extends AbstractVariableSniff
    {

	/**
	 * Variables may have or not leading underscore depending on scope. True means "no leading underscore".
	 *
	 * @var array
	 */
	protected $underscores = array(
				  "public"    => true,
				  "protected" => true,
				  "private"   => false,
				 );

	/**
	 * Processes this test, when one of its tokens is encountered.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $stackPtr  The position of the current token in the stack passed in $tokens.
	 *
	 * @return void
	 *
	 * @untranslatable _SERVER
	 * @untranslatable _GET
	 * @untranslatable _POST
	 * @untranslatable _REQUEST
	 * @untranslatable _SESSION
	 * @untranslatable _ENV
	 * @untranslatable _COOKIE
	 * @untranslatable _FILES
	 * @untranslatable GLOBALS
	 */

	protected function processVariable(File &$phpcsFile, $stackPtr)
	    {
		$tokens  = &$phpcsFile->tokens;
		$varName = ltrim($tokens[$stackPtr]["content"], "$");

		$phpReservedVars = array(
				    "_SERVER",
				    "_GET",
				    "_POST",
				    "_REQUEST",
				    "_SESSION",
				    "_ENV",
				    "_COOKIE",
				    "_FILES",
				    "GLOBALS",
				   );

		// If it's a php reserved var, then its ok.
		if (in_array($varName, $phpReservedVars) === false)
		    {
			$this->checkVariable($phpcsFile, $stackPtr, $tokens, $varName);
		    } //end if
	    } //end processVariable()


	/**
	 * Check variable validity
	 *
	 * @param File   $phpcsFile The file being scanned.
	 * @param int    $stackPtr  The position of the current token in the stack passed in $tokens.
	 * @param array  $tokens    All tokens
	 * @param string $varName   Variable name
	 *
	 * @return void
	 *
	 * @internalconst T_OPEN_PARENTHESIS T_OPEN_PARENTHESIS token
	 */

	protected function checkVariable(File &$phpcsFile, $stackPtr, array &$tokens, $varName)
	    {
		$objOperator = $phpcsFile->findNext(array(T_WHITESPACE), ($stackPtr + 1), null, true);
		if ($tokens[$objOperator]["code"] === T_OBJECT_OPERATOR)
		    {
			// Check to see if we are using a variable from an object.
			$var = $phpcsFile->findNext(array(T_WHITESPACE), ($objOperator + 1), null, true);
			if ($tokens[$var]["code"] === T_STRING)
			    {
				// Either a var name or a function call, so check for bracket.
				$bracket = $phpcsFile->findNext(array(T_WHITESPACE), ($var + 1), null, true);

				if ($tokens[$bracket]["code"] !== T_OPEN_PARENTHESIS)
				    {
					// There is no way for us to know if the var is public or private,
					// so we have to ignore a leading underscore if there is one and just
					// check the main part of the variable name.
					$objVarName = preg_replace("/^_/", "", $tokens[$var]["content"]);

					$this->validateVariable($phpcsFile, $stackPtr, $objVarName, $tokens[$var]["content"]);
				    } //end if
			    } //end if
		    } //end if

		// There is no way for us to know if the var is public or private,
		// so we have to ignore a leading underscore if there is one and just
		// check the main part of the variable name.
		$originalVarName = $varName;
		if (substr($varName, 0, 1) === "_")
		    {
			$objOperator = $phpcsFile->findPrevious(array(T_WHITESPACE), ($stackPtr - 1), null, true);
			if ($tokens[$objOperator]["code"] === T_DOUBLE_COLON)
			    {
				// The variable lives within a class, and is referenced like
				// this: MyClass::$_variable, so we don't know its scope.
				$inClass = true;
			    }
			else
			    {
				$inClass = $phpcsFile->hasCondition($stackPtr, array(T_CLASS, T_INTERFACE));
			    }

			if ($inClass === true)
			    {
				$varName = substr($varName, 1);
			    }
		    }

		$this->validateVariable($phpcsFile, $stackPtr, $varName, $originalVarName);
	    } //end checkVariable()


	/**
	 * Processes class member variables.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $stackPtr  The position of the current token in the stack passed in $tokens.
	 *
	 * @return void
	 *
	 * @untranslatable HasUnderscore
	 * @untranslatable NoUnderscore
	 * @untranslatable Member
	 * @untranslatable \"%s\"
	 */

	protected function processMemberVar(File &$phpcsFile, $stackPtr)
	    {
		$tokens = &$phpcsFile->tokens;

		$varName     = ltrim($tokens[$stackPtr]["content"], "$");
		$memberProps = $phpcsFile->getMemberProperties($stackPtr);

		// Couldn't get any info about this variable, which
		// generally means it is invalid or possibly has a parse
		// error. Any errors will be reported by the core, so
		// we can ignore it.
		if (empty($memberProps) === false)
		    {
			$scope   = ucfirst($memberProps["scope"]);
			$data    = array($varName);
			$pattern = (($this->underscores[$memberProps["scope"]] === true) ? "/^_/" : "/^[^_]/");

			if ($this->underscores[$memberProps["scope"]] === true && preg_match($pattern, $varName) > 0)
			    {
				$error = $scope . " " . _("member variable") . " \"%s\" " . _("must not contain a leading underscore");
				$phpcsFile->addError($error, $stackPtr, $scope . "HasUnderscore", $data);
			    }
			else if ($this->underscores[$memberProps["scope"]] === false && preg_match($pattern, $varName) > 0)
			    {
				$error = $scope . " " . _("member variable") . " \"%s\" " . _("must contain a leading underscore");
				$phpcsFile->addError($error, $stackPtr, $scope . "NoUnderscore", $data);
			    }
			else
			    {
				$this->validateVariable($phpcsFile, $stackPtr, $varName, $varName, "Member", $this->underscores[$memberProps["scope"]]);
			    }
		    } //end if
	    } //end processMemberVar()


	/**
	 * Processes the variable found within a double quoted string.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $stackPtr  The position of the double quoted string.
	 *
	 * @return void
	 *
	 * @untranslatable _SERVER
	 * @untranslatable _GET
	 * @untranslatable _POST
	 * @untranslatable _REQUEST
	 * @untranslatable _SESSION
	 * @untranslatable _ENV
	 * @untranslatable _COOKIE
	 * @untranslatable _FILES
	 * @untranslatable GLOBALS
	 * @untranslatable String
	 */

	protected function processVariableInString(File &$phpcsFile, $stackPtr)
	    {
		$tokens = &$phpcsFile->tokens;

		$phpReservedVars = array(
				    "_SERVER",
				    "_GET",
				    "_POST",
				    "_REQUEST",
				    "_SESSION",
				    "_ENV",
				    "_COOKIE",
				    "_FILES",
				    "GLOBALS",
				   );

		if (preg_match_all('/[^\\\]\${?([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)/', $tokens[$stackPtr]["content"], $matches) !== 0)
		    {
			foreach ($matches[1] as $varName)
			    {
				// If it's a php reserved var, then its ok.
				if (in_array($varName, $phpReservedVars) === false)
				    {
					// There is no way for us to know if the var is public or private,
					// so we have to ignore a leading underscore if there is one and just
					// check the main part of the variable name.
					$originalVarName = $varName;
					if (substr($varName, 0, 1) === "_")
					    {
						if ($phpcsFile->hasCondition($stackPtr, array(T_CLASS, T_INTERFACE)) === true)
						    {
							$varName = substr($varName, 1);
						    }
					    }

					$this->validateVariable($phpcsFile, $stackPtr, $varName, $originalVarName, "String");
				    } //end if
			    } //end foreach
		    } //end if
	    } //end processVariableInString()


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
		if (CodeSniffer::isCamelCaps($varName, false, $public, false) === false)
		    {
			$error = _("Variable") . " \"%s\" " . _("is not in valid camel caps format");
			$data  = array($originalVarName);
			$phpcsFile->addError($error, $stackPtr, $prefix . "NotCamelCaps", $data);
		    }
	    } //end validateVariable()


    } //end class

?>
