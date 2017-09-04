<?php

/**
 * PHP version 5
 *
 * @package Logics\BuildTools\CodeSniffer\Generic
 */

namespace Logics\BuildTools\CodeSniffer\Generic;

use \Logics\BuildTools\CodeSniffer\File;
use \Logics\BuildTools\CodeSniffer\Sniff;

/**
 * Bans the use of the PHP short array syntax.
 *
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2006-2012 Squiz Pty Ltd (ABN 77 084 670 600)
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 * @version   SVN: $Date: 2016-08-16 23:39:01 +0900 (Tue, 16 Aug 2016) $ $Revision: 26 $
 * @link      $HeadURL: https://open.logics.net.au/buildtools/codesniffer/tags/0.1.3/src/CodeSniffer/Standards/Generic/Sniffs/Arrays/DisallowShortArraySyntaxSniff.php $
 */

class DisallowShortArraySyntaxSniff implements Sniff
    {

	/**
	 * Registers the tokens that this sniff wants to listen for.
	 *
	 * @return int[]
	 *
	 * @internalconst T_OPEN_SHORT_ARRAY T_OPEN_SHORT_ARRAY token
	 */

	public function register()
	    {
		return array(T_OPEN_SHORT_ARRAY);
	    } //end register()


	/**
	 * Processes this test, when one of its tokens is encountered.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $stackPtr  The position of the current token in the stack passed in $tokens.
	 *
	 * @return void
	 *
	 * @untranslatable Found
	 */

	public function process(File $phpcsFile, $stackPtr)
	    {
		$error = _("Short array syntax is not allowed");
		$phpcsFile->addError($error, $stackPtr, "Found");
	    } //end process()


    } //end class

?>
