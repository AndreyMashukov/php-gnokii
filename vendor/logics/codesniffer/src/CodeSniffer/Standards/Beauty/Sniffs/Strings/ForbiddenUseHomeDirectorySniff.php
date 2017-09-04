<?php

/**
 * PHP version 5.6
 *
 * @package Logics\BuildTools\CodeSniffer\Beauty
 */

namespace Logics\BuildTools\CodeSniffer\Beauty;

use \Logics\BuildTools\CodeSniffer\File;
use \Logics\BuildTools\CodeSniffer\Sniff;

/**
 * ForbiddenUseHomeDirectory
 *
 * @author    Anastasia Bashkirtseva <anastasia@logics.net.au>
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2006-2012 Squiz Pty Ltd (ABN 77 084 670 600)
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   http://matrix.squiz.net/developer/tools/php_cs/licence BSD Licence
 * @version   SVN: $Date: 2016-08-16 23:39:01 +0900 (Tue, 16 Aug 2016) $ $Revision: 26 $
 * @link      $HeadURL: https://open.logics.net.au/buildtools/codesniffer/tags/0.1.3/src/CodeSniffer/Standards/Beauty/Sniffs/Strings/ForbiddenUseHomeDirectorySniff.php $
 */

class ForbiddenUseHomeDirectorySniff implements Sniff
    {

	/**
	 * Returns an array of tokens this test wants to listen for.
	 *
	 * @return array
	 *
	 * @internalconst T_DOUBLE_QUOTED_STRING T_DOUBLE_QUOTED_STRING token
	 */

	public function register()
	    {
		return array(
			T_CONSTANT_ENCAPSED_STRING,
			T_DOUBLE_QUOTED_STRING,
		       );
	    } //end register()


	/**
	 * Processes this test, when one of its tokens is encountered.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $stackPtr  The position of the current token in the stack passed in $tokens.
	 *
	 * @return void
	 *
	 * @untranslatable ForbiddenUseHomeDirectory
	 */

	public function process(File $phpcsFile, $stackPtr)
	    {
		$tokens  = &$phpcsFile->tokens;
		$content = $tokens[$stackPtr]["content"];
		if (preg_match("/\/home\/.+\//", $content) > 0)
		    {
			$phpcsFile->addError(_("Forbidden use home directory"), $stackPtr, "ForbiddenUseHomeDirectory");
		    }
	    } //end process()


    } //end class

?>
