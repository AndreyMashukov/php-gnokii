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
 * Generic_Sniffs_Files_EndFileNewlineSniff.
 *
 * Ensures the file ends with a newline character.
 *
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2006-2012 Squiz Pty Ltd (ABN 77 084 670 600)
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 * @version   SVN: $Date: 2016-08-16 23:39:01 +0900 (Tue, 16 Aug 2016) $ $Revision: 26 $
 * @link      $HeadURL: https://open.logics.net.au/buildtools/codesniffer/tags/0.1.3/src/CodeSniffer/Standards/PSR2/Sniffs/Files/EndFileNewlineSniff.php $
 */

class EndFileNewlineSniff implements Sniff
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
	 * @untranslatable NoneFound
	 * @untranslatable %s
	 * @untranslatable TooMany
	 */

	public function process(File $phpcsFile, $stackPtr)
	    {
		// We are only interested if this is the first open tag and in a file
		// that only contains PHP code.
		if ($stackPtr === 0 || $phpcsFile->findPrevious(array(T_OPEN_TAG, T_INLINE_HTML), ($stackPtr - 1)) === false)
		    {
			if ($phpcsFile->findNext(T_INLINE_HTML, ($stackPtr + 1)) === false)
			    {
				// Skip to the end of the file.
				$tokens   = &$phpcsFile->tokens;
				$stackPtr = ($phpcsFile->numTokens - 1);

				// Hard-coding the expected \n in this sniff as it is PSR-2 specific and
				// PSR-2 enforces the use of unix style newlines.
				if (substr($tokens[$stackPtr]["content"], -1) !== "\n")
				    {
					$error = _("Expected 1 newline at end of file; 0 found");
					$phpcsFile->addError($error, $stackPtr, "NoneFound");
				    }
				else
				    {
					// Go looking for the last non-empty line.
					$lastLine = $tokens[$stackPtr]["line"];
					while ($tokens[$stackPtr]["code"] === T_WHITESPACE)
					    {
						$stackPtr--;
					    }

					$lastCodeLine = $tokens[$stackPtr]["line"];
					$blankLines   = ($lastLine - $lastCodeLine);
					if ($blankLines > 0)
					    {
						$error = _("Expected 1 blank line at end of file;") . " %s " . _("found");
						$data  = array($blankLines + 1);
						$phpcsFile->addError($error, $stackPtr, "TooMany", $data);
					    }
				    } //end if
			    } //end if
		    } //end if
	    } //end process()


    } //end class

?>
