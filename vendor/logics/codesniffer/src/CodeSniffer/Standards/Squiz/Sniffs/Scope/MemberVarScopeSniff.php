<?php

/**
 * PHP version 5
 *
 * @package Logics\BuildTools\CodeSniffer\Squiz
 */

namespace Logics\BuildTools\CodeSniffer\Squiz;

use \Logics\BuildTools\CodeSniffer\AbstractMemberSniff;
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
 * @link      $HeadURL: https://open.logics.net.au/buildtools/codesniffer/tags/0.1.3/src/CodeSniffer/Standards/Squiz/Sniffs/Scope/MemberVarScopeSniff.php $
 */

class MemberVarScopeSniff extends AbstractMemberSniff
    {

	/**
	 * Processes the function tokens within the class.
	 *
	 * @param File $phpcsFile The file where this token was found.
	 * @param int  $stackPtr  The position where the token was found.
	 *
	 * @return void
	 *
	 * @untranslatable Missing
	 * @untranslatable \"%s\"
	 */

	protected function processMemberVar(File &$phpcsFile, $stackPtr)
	    {
		$tokens   = &$phpcsFile->tokens;
		$modifier = $phpcsFile->findPrevious(Tokens::$scopeModifiers, $stackPtr);

		if (($modifier === false) || ($tokens[$modifier]["line"] !== $tokens[$stackPtr]["line"]))
		    {
			$error = _("Scope modifier not specified for member variable") . " \"%s\"";
			$data  = array($tokens[$stackPtr]["content"]);
			$phpcsFile->addError($error, $stackPtr, "Missing", $data);
		    }
	    } //end processMemberVar()


    } //end class

?>
