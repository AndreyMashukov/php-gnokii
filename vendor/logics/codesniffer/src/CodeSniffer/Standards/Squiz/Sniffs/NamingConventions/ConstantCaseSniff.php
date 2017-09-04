<?php

/**
 * PHP version 5
 *
 * @package Logics\BuildTools\CodeSniffer\Squiz
 */

namespace Logics\BuildTools\CodeSniffer\Squiz;

use \Logics\BuildTools\CodeSniffer\File;
use \Logics\BuildTools\CodeSniffer\Generic\LowerCaseConstantSniff;
use \Logics\BuildTools\CodeSniffer\Generic\UpperCaseConstantSniff;

/**
 * ConstantCaseSniff.
 *
 * Ensures TRUE, FALSE and NULL are uppercase for PHP and lowercase for JS.
 *
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2006-2012 Squiz Pty Ltd (ABN 77 084 670 600)
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 * @version   SVN: $Date: 2016-08-16 23:39:01 +0900 (Tue, 16 Aug 2016) $ $Revision: 26 $
 * @link      $HeadURL: https://open.logics.net.au/buildtools/codesniffer/tags/0.1.3/src/CodeSniffer/Standards/Squiz/Sniffs/NamingConventions/ConstantCaseSniff.php $
 */

class ConstantCaseSniff extends LowerCaseConstantSniff
    {

	/**
	 * Processes this sniff, when one of its tokens is encountered.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $stackPtr  The position of the current token in the stack passed in $tokens.
	 *
	 * @return void
	 *
	 * @untranslatable JS
	 */

	public function process(File $phpcsFile, $stackPtr)
	    {
		if ($phpcsFile->tokenizerType === "JS")
		    {
			parent::process($phpcsFile, $stackPtr);
		    }
		else
		    {
			$sniff = new UpperCaseConstantSniff;
			$sniff->process($phpcsFile, $stackPtr);
		    }
	    } //end process()


    } //end class

?>
