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
 * ClosingPHPTagSniff.
 *
 * Checks that the file does not end with a closing tag.
 *
 * @author    Stefano Kowalke <blueduck@gmx.net>
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2010 Stefano Kowalke
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 * @version   SVN: $Date: 2016-08-16 23:39:01 +0900 (Tue, 16 Aug 2016) $ $Revision: 26 $
 * @link      $HeadURL: https://open.logics.net.au/buildtools/codesniffer/tags/0.1.3/src/CodeSniffer/Standards/Generic/Sniffs/PHP/ClosingPHPTagSniff.php $
 */

class ClosingPHPTagSniff implements Sniff
    {

	/**
	 * Returns an array of tokens this test wants to listen for.
	 *
	 * @return array
	 */

	public function register()
	    {
		return array(T_OPEN_TAG);
	    } //end register()


	/**
	 * Processes this sniff, when one of its tokens is encountered.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $stackPtr  The position of the current token in the stack passed in $tokens.
	 *
	 * @return void
	 *
	 * @untranslatable NotFound
	 */

	public function process(File $phpcsFile, $stackPtr)
	    {
		$closeTag = $phpcsFile->findNext(T_CLOSE_TAG, $stackPtr);
		if ($closeTag === false)
		    {
			$phpcsFile->addError(_("The PHP open tag does not have a corresponding PHP close tag"), $stackPtr, "NotFound");
		    }
	    } //end process()


    } //end class

?>
