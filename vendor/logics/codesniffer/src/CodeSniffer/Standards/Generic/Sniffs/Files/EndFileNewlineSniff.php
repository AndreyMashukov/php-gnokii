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
 * EndFileNewlineSniff.
 *
 * Ensures the file ends with a newline character.
 *
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2006-2012 Squiz Pty Ltd (ABN 77 084 670 600)
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 * @version   SVN: $Date: 2016-08-16 23:39:01 +0900 (Tue, 16 Aug 2016) $ $Revision: 26 $
 * @link      $HeadURL: https://open.logics.net.au/buildtools/codesniffer/tags/0.1.3/src/CodeSniffer/Standards/Generic/Sniffs/Files/EndFileNewlineSniff.php $
 *
 * @untranslatable PHP
 * @untranslatable JS
 * @untranslatable CSS
 */

class EndFileNewlineSniff implements Sniff
    {

	/**
	 * A list of tokenizers this sniff supports.
	 *
	 * @var array
	 */
	public $supportedTokenizers = array(
				       "PHP",
				       "JS",
				       "CSS",
				      );

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
	 * @untranslatable PHP
	 * @untranslatable NotFound
	 */

	public function process(File $phpcsFile, $stackPtr)
	    {
		// Skip to the end of the file.
		$tokens   = &$phpcsFile->tokens;
		$stackPtr = ($phpcsFile->numTokens - 1);

		if ($phpcsFile->tokenizerType !== "PHP")
		    {
			$stackPtr--;
		    }

		$eolCharLen = strlen($phpcsFile->eolChar);
		$lastChars  = substr($tokens[$stackPtr]["content"], ($eolCharLen * -1));
		if ($lastChars !== $phpcsFile->eolChar)
		    {
			$error = _("File must end with a newline character");
			$phpcsFile->addError($error, $stackPtr, "NotFound");
		    }

		// Ignore the rest of the file.
		return ($phpcsFile->numTokens + 1);
	    } //end process()


    } //end class

?>
