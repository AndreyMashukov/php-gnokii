<?php

/**
 * Warns about the use of debug code.
 *
 * PHP version 5
 *
 * @package Logics\BuildTools\CodeSniffer\MySource
 */

namespace Logics\BuildTools\CodeSniffer\MySource;

use \Logics\BuildTools\CodeSniffer\File;
use \Logics\BuildTools\CodeSniffer\Sniff;

/**
 * Warns about the use of debug code.
 *
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2006-2012 Squiz Pty Ltd (ABN 77 084 670 600)
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 * @version   SVN: $Date: 2016-08-16 23:39:01 +0900 (Tue, 16 Aug 2016) $ $Revision: 26 $
 * @link      $HeadURL: https://open.logics.net.au/buildtools/codesniffer/tags/0.1.3/src/CodeSniffer/Standards/MySource/Sniffs/Debug/DebugCodeSniff.php $
 */

class DebugCodeSniff implements Sniff
    {

	/**
	 * Returns an array of tokens this test wants to listen for.
	 *
	 * @return array
	 */

	public function register()
	    {
		return array(T_DOUBLE_COLON);
	    } //end register()


	/**
	 * Processes this sniff, when one of its tokens is encountered.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $stackPtr  The position of the current token in the stack passed in $tokens.
	 *
	 * @return void
	 *
	 * @untranslatable debug
	 * @untranslatable %s()
	 * @untranslatable Found
	 */

	public function process(File $phpcsFile, $stackPtr)
	    {
		$tokens = &$phpcsFile->tokens;

		$className = $phpcsFile->findPrevious(T_WHITESPACE, ($stackPtr - 1), null, true);
		if (strtolower($tokens[$className]["content"]) === "debug")
		    {
			$method = $phpcsFile->findNext(T_WHITESPACE, ($stackPtr + 1), null, true);
			$error  = _("Call to debug function Debug::") . "%s() " . _("must be removed");
			$data   = array($tokens[$method]["content"]);
			$phpcsFile->addError($error, $stackPtr, "Found", $data);
		    }
	    } //end process()


    } //end class

?>