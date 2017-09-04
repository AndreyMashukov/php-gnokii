<?php

/**
 * PHP version 5
 *
 * @package Logics\BuildTools\CodeSniffer\Beauty
 */

namespace Logics\BuildTools\CodeSniffer\Beauty;

use \Logics\BuildTools\CodeSniffer\File;
use \Logics\BuildTools\CodeSniffer\Sniff;

/**
 * Beauty_Sniffs_PHP_NamespaceRequired
 *
 * All classes, traits and interfaces should have namespace declared
 *
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://opensource.org/licenses/MIT MIT License
 * @version   SVN: $Date: 2016-08-16 23:39:01 +0900 (Tue, 16 Aug 2016) $ $Revision: 26 $
 * @link      $HeadURL: https://open.logics.net.au/buildtools/codesniffer/tags/0.1.3/src/CodeSniffer/Standards/Beauty/Sniffs/PHP/NamespaceRequiredSniff.php $
 */

class NamespaceRequiredSniff implements Sniff
    {

	/**
	 * Returns an array of tokens this test wants to listen for.
	 *
	 * @return array
	 */

	public function register()
	    {
		return array(
			T_CLASS,
			T_INTERFACE,
			T_TRAIT,
		       );
	    } //end register()


	/**
	 * Processes this test, when used continue
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $stackPtr  The position of the current token in the stack passed in $tokens.
	 *
	 * @return void
	 *
	 * @untranslatable Namespace
	 */

	public function process(File $phpcsFile, $stackPtr)
	    {
		if ($phpcsFile->findPrevious(array(T_NAMESPACE), $stackPtr, 0) === false)
		    {
			$phpcsFile->addError(_("Namespace not declared"), $stackPtr, "Namespace");
		    }
	    } //end process()


    } //end class

?>
