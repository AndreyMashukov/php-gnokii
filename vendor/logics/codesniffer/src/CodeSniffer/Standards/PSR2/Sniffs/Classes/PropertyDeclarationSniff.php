<?php

/**
 * PHP version 5
 *
 * @package Logics\BuildTools\CodeSniffer\PSR2
 */

namespace Logics\BuildTools\CodeSniffer\PSR2;

use \Logics\BuildTools\CodeSniffer\AbstractMemberSniff;
use \Logics\BuildTools\CodeSniffer\File;
use \Logics\BuildTools\CodeSniffer\Tokens;

/**
 * Verifies that properties are declared correctly.
 *
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2006-2012 Squiz Pty Ltd (ABN 77 084 670 600)
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 * @version   SVN: $Date: 2016-08-16 23:39:01 +0900 (Tue, 16 Aug 2016) $ $Revision: 26 $
 * @link      $HeadURL: https://open.logics.net.au/buildtools/codesniffer/tags/0.1.3/src/CodeSniffer/Standards/PSR2/Sniffs/Classes/PropertyDeclarationSniff.php $
 */

class PropertyDeclarationSniff extends AbstractMemberSniff
    {

	/**
	 * Processes the function tokens within the class.
	 *
	 * @param File $phpcsFile The file where this token was found.
	 * @param int  $stackPtr  The position where the token was found.
	 *
	 * @return void
	 *
	 * @internalconst T_SEMICOLON T_SEMICOLON token
	 *
	 * @untranslatable Underscore
	 * @untranslatable VarUsed
	 * @untranslatable Multiple
	 * @untranslatable \"%s\"
	 * @untranslatable ScopeMissing
	 */

	protected function processMemberVar(File &$phpcsFile, $stackPtr)
	    {
		$tokens = &$phpcsFile->tokens;

		if ($tokens[$stackPtr]["content"][1] === "_")
		    {
			$error = _("Property name") . " \"%s\" " . _("should not be prefixed with an underscore to indicate visibility");
			$data  = array($tokens[$stackPtr]["content"]);
			$phpcsFile->addWarning($error, $stackPtr, "Underscore", $data);
		    }

		// Detect multiple properties defined at the same time. Throw an error
		// for this, but also only process the first property in the list so we don't
		// repeat errors.
		$find = Tokens::$scopeModifiers;
		$find = array_merge($find, array(T_VARIABLE, T_VAR, T_SEMICOLON));
		$prev = $phpcsFile->findPrevious($find, ($stackPtr - 1));
		if ($tokens[$prev]["code"] !== T_VARIABLE)
		    {
			if ($tokens[$prev]["code"] === T_VAR)
			    {
				$error = _("The var keyword must not be used to declare a property");
				$phpcsFile->addError($error, $stackPtr, "VarUsed");
			    }

			$next = $phpcsFile->findNext(array(T_VARIABLE, T_SEMICOLON), ($stackPtr + 1));
			if ($tokens[$next]["code"] === T_VARIABLE)
			    {
				$error = _("There must not be more than one property declared per statement");
				$phpcsFile->addError($error, $stackPtr, "Multiple");
			    }

			$modifier = $phpcsFile->findPrevious(Tokens::$scopeModifiers, $stackPtr);
			if (($modifier === false) || ($tokens[$modifier]["line"] !== $tokens[$stackPtr]["line"]))
			    {
				$error = _("Visibility must be declared on property") . " \"%s\"";
				$data  = array($tokens[$stackPtr]["content"]);
				$phpcsFile->addError($error, $stackPtr, "ScopeMissing", $data);
			    }
		    } //end if
	    } //end processMemberVar()


    } //end class

?>
