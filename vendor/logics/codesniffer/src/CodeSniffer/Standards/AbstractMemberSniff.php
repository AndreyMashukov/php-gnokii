<?php

/**
 * A class to find T_VARIABLE tokens in classes.
 *
 * PHP version 5.6
 *
 * @package Logics\BuildTools\CodeSniffer
 */

namespace Logics\BuildTools\CodeSniffer;

/**
 * A class to find T_VARIABLE tokens.
 *
 * If a class member is encountered, then then processMemberVar method is called so the extending class can process it.
 *
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @author    Marc McIntyre <mmcintyre@squiz.net>
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2006-2012 Squiz Pty Ltd (ABN 77 084 670 600)
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 * @version   SVN: $Date: 2016-08-16 23:39:01 +0900 (Tue, 16 Aug 2016) $ $Revision: 26 $
 * @link      $HeadURL: https://open.logics.net.au/buildtools/codesniffer/tags/0.1.3/src/CodeSniffer/Standards/AbstractMemberSniff.php $
 */

abstract class AbstractMemberSniff extends AbstractVariableSniff
    {

	/**
	 * Processes normal variables.
	 *
	 * @param File $phpcsFile The file where this token was found.
	 * @param int  $stackPtr  The position where the token was found.
	 *
	 * @return void
	 */

	protected function processVariable(File &$phpcsFile, $stackPtr)
	    {
		// We don't care about normal variables.
		unset($phpcsFile);
		unset($stackPtr);
	    } //end processVariable()


	/**
	 * Processes variables in double quoted strings.
	 *
	 * @param File $phpcsFile The file where this token was found.
	 * @param int  $stackPtr  The position where the token was found.
	 *
	 * @return void
	 */

	protected function processVariableInString(File &$phpcsFile, $stackPtr)
	    {
		// We don't care about normal variables.
		unset($phpcsFile);
		unset($stackPtr);
	    } //end processVariableInString()


    } //end class

?>
