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
 * NewLineAfterOpenTagAndBeforeCloseTagSniff.
 *
 * Makes sure that open and close php tags are properly used.
 *
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @author    Marc McIntyre <mmcintyre@squiz.net>
 * @author    Alex Shumilov <alex@logics.net.au>
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2006-2012 Squiz Pty Ltd (ABN 77 084 670 600)
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   http://matrix.squiz.net/developer/tools/php_cs/licence BSD Licence
 * @version   SVN: $Date: 2016-08-16 23:39:01 +0900 (Tue, 16 Aug 2016) $ $Revision: 26 $
 * @link      $HeadURL: https://open.logics.net.au/buildtools/codesniffer/tags/0.1.3/src/CodeSniffer/Standards/Beauty/Sniffs/PHP/NewLineAfterOpenTagAndBeforeCloseTagSniff.php $
 */

class NewLineAfterOpenTagAndBeforeCloseTagSniff implements Sniff
    {

	/**
	 * Returns an array of tokens this test wants to listen for.
	 *
	 * @return array
	 */

	public function register()
	    {
		return array(
			T_OPEN_TAG,
			T_CLOSE_TAG,
		       );
	    } //end register()


	/**
	 * Processes this test, when one of its tokens is encountered.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $stackPtr  The position of the current token in the stack passed in $tokens.
	 *
	 * @return void
	 */

	public function process(File $phpcsFile, $stackPtr)
	    {
		$tokens = &$phpcsFile->tokens;
		if ($tokens[$stackPtr]["code"] === T_OPEN_TAG)
		    {
			if ((preg_match("/[ \t]/", $tokens[$stackPtr]["content"]) === 0 &&
			     $tokens[($stackPtr + 1)]["content"] === "\n" && $tokens[($stackPtr + 2)]["content"] !== "\n") === false)
			    {
				$error = _("PHP open tag has to be followed by one empty line");
				$phpcsFile->addError($error, $stackPtr);
			    }
		    }
		else if ($tokens[$stackPtr]["code"] === T_CLOSE_TAG)
		    {
			if (($tokens[($stackPtr - 1)]["content"] === "\n" && $tokens[($stackPtr - 2)]["column"] !== 1) === false)
			    {
				$error = _("PHP close tag has to be prepended with one empty line");
				$phpcsFile->addError($error, $stackPtr);
			    }
		    } //end if
	    } //end process()


    } //end class

?>
