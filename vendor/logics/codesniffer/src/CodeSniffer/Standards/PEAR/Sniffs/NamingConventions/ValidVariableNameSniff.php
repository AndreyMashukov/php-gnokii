<?php

/**
 * PHP version 5
 *
 * @package Logics\BuildTools\CodeSniffer\PEAR
 */

namespace Logics\BuildTools\CodeSniffer\PEAR;

use \Logics\BuildTools\CodeSniffer\AbstractMemberSniff;
use \Logics\BuildTools\CodeSniffer\File;

/**
 * ValidVariableNameSniff
 *
 * Checks the naming of member variables.
 *
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2006-2012 Squiz Pty Ltd (ABN 77 084 670 600)
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 * @version   SVN: $Date: 2016-08-16 23:39:01 +0900 (Tue, 16 Aug 2016) $ $Revision: 26 $
 * @link      $HeadURL: https://open.logics.net.au/buildtools/codesniffer/tags/0.1.3/src/CodeSniffer/Standards/PEAR/Sniffs/NamingConventions/ValidVariableNameSniff.php $
 */

class ValidVariableNameSniff extends AbstractMemberSniff
    {

	/**
	 * Processes class member variables.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $stackPtr  The position of the current token in the stack passed in $tokens.
	 *
	 * @return void
	 *
	 * @untranslatable private
	 * @untranslatable PrivateNoUnderscore
	 * @untranslatable %s
	 * @untranslatable \"%s\"
	 * @untranslatable PublicUnderscore
	 */

	protected function processMemberVar(File &$phpcsFile, $stackPtr)
	    {
		$tokens = &$phpcsFile->tokens;

		$memberProps = $phpcsFile->getMemberProperties($stackPtr);
		if (empty($memberProps) === false)
		    {
			$memberName     = ltrim($tokens[$stackPtr]["content"], "$");
			$isPublic       = ($memberProps["scope"] === "private") ? false : true;
			$scope          = $memberProps["scope"];
			$scopeSpecified = $memberProps["scope_specified"];

			if ($isPublic === false && $memberName{0} !== "_")
			    {
				// If it's a private member, it must have an underscore on the front.
				$error = _("Private member variable") . " \"%s\" " . _("must be prefixed with an underscore");
				$data  = array($memberName);
				$phpcsFile->addError($error, $stackPtr, "PrivateNoUnderscore", $data);
			    }
			else if ($isPublic === true && $scopeSpecified === true && $memberName{0} === "_")
			    {
				// If it's not a private member, it must not have an underscore on the front.
				$error = "%s " . _("member variable") . " \"%s\" " . _("must not be prefixed with an underscore");
				$data  = array(
					  ucfirst($scope),
					  $memberName,
					 );
				$phpcsFile->addError($error, $stackPtr, "PublicUnderscore", $data);
			    }
		    } //end if
	    } //end processMemberVar()


    } //end class

?>
