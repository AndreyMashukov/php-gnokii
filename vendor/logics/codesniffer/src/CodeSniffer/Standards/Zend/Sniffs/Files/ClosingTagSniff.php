<?php

/**
 * PHP version 5
 *
 * @package Logics\BuildTools\CodeSniffer\Zend
 */

namespace Logics\BuildTools\CodeSniffer\Zend;

use \Logics\BuildTools\CodeSniffer\File;
use \Logics\BuildTools\CodeSniffer\Sniff;

/**
 * Zend_Sniffs_Files_LineEndingsSniff.
 *
 * Checks that the file does not end with a closing tag.
 *
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @author    Marc McIntyre <mmcintyre@squiz.net>
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2006-2012 Squiz Pty Ltd (ABN 77 084 670 600)
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 * @version   SVN: $Date: 2016-08-16 23:39:01 +0900 (Tue, 16 Aug 2016) $ $Revision: 26 $
 * @link      $HeadURL: https://open.logics.net.au/buildtools/codesniffer/tags/0.1.3/src/CodeSniffer/Standards/Zend/Sniffs/Files/ClosingTagSniff.php $
 */

class ClosingTagSniff implements Sniff
    {

	/**
	 * Returns an array of tokens this test wants to listen for.
	 *
	 * @return array
	 */

	public function register()
	    {
		return array(T_CLOSE_TAG);
	    } //end register()


	/**
	 * Processes this sniff, when one of its tokens is encountered.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $stackPtr  The position of the current token in the stack passed in $tokens.
	 *
	 * @return void
	 *
	 * @untranslatable NotAllowed
	 */

	public function process(File $phpcsFile, $stackPtr)
	    {
		$tokens = &$phpcsFile->tokens;
		$next   = $phpcsFile->findNext(T_INLINE_HTML, ($stackPtr + 1), null, true);
		if ($next !== false)
		    {
			return;
		    }
		else
		    {
			// We've found the last closing tag in the file so the only thing potentially remaining is inline HTML.
			// Now we need to figure out whether or not it's just a bunch of whitespace.
			$content = "";
			for ($i = ($stackPtr + 1); $i < $phpcsFile->numTokens; $i++)
			    {
				$content .= $tokens[$i]["content"];
			    }

			// Check if the remaining inline HTML is just whitespace.
			$content = trim($content);
			if (empty($content) === true)
			    {
				$phpcsFile->addError(_("A closing tag is not permitted at the end of a PHP file"), $stackPtr, "NotAllowed");
			    }
		    }
	    } //end process()


    } //end class

?>
