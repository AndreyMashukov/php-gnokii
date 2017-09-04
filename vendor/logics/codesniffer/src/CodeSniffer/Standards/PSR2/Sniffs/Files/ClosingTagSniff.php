<?php

/**
 * PHP version 5
 *
 * @package Logics\BuildTools\CodeSniffer\PSR2
 */

namespace Logics\BuildTools\CodeSniffer\PSR2;

use \Logics\BuildTools\CodeSniffer\File;
use \Logics\BuildTools\CodeSniffer\Sniff;

/**
 * PSR2_Sniffs_Files_LineEndingsSniff.
 *
 * Checks that the file does not end with a closing tag.
 *
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2006-2012 Squiz Pty Ltd (ABN 77 084 670 600)
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 * @version   SVN: $Date: 2016-08-16 23:39:01 +0900 (Tue, 16 Aug 2016) $ $Revision: 26 $
 * @link      $HeadURL: https://open.logics.net.au/buildtools/codesniffer/tags/0.1.3/src/CodeSniffer/Standards/PSR2/Sniffs/Files/ClosingTagSniff.php $
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
		return array(T_OPEN_TAG);
	    } //end register()


	/**
	 * Processes this sniff, when one of its tokens is encountered.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $stackPtr  The position of the current token in the stack passed in $tokens.
	 *
	 * @return int
	 *
	 * @untranslatable NotAllowed
	 */

	public function process(File $phpcsFile, $stackPtr)
	    {
		unset($stackPtr);

		$tokens = &$phpcsFile->tokens;

		// Make sure this file only contains PHP code.
		for ($i = 0; $i < $phpcsFile->numTokens; $i++)
		    {
			if ($tokens[$i]["code"] === T_INLINE_HTML && trim($tokens[$i]["content"]) !== "")
			    {
				return $phpcsFile->numTokens;
			    }
		    }

		// Find the last non-empty token.
		for ($last = ($phpcsFile->numTokens - 1); $last > 0; $last--)
		    {
			if (trim($tokens[$last]["content"]) !== "")
			    {
				break;
			    }
		    }

		if ($tokens[$last]["code"] === T_CLOSE_TAG)
		    {
			$error = _("A closing tag is not permitted at the end of a PHP file");
			$phpcsFile->addError($error, $last, "NotAllowed");
		    }

		// Ignore the rest of the file.
		return $phpcsFile->numTokens;
	    } //end process()


    } //end class

?>