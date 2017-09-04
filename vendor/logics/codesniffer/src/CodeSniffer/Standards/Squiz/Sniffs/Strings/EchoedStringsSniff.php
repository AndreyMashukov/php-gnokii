<?php

/**
 * PHP version 5
 *
 * @package Logics\BuildTools\CodeSniffer\Squiz
 */

namespace Logics\BuildTools\CodeSniffer\Squiz;

use \Logics\BuildTools\CodeSniffer\File;
use \Logics\BuildTools\CodeSniffer\Sniff;
use \Logics\BuildTools\CodeSniffer\Tokens;

/**
 * Squiz_Sniffs_Strings_EchoedStringsSniff.
 *
 * Makes sure that any strings that are "echoed" are not enclosed in brackets
 * like a function call.
 *
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @author    Marc McIntyre <mmcintyre@squiz.net>
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2006-2012 Squiz Pty Ltd (ABN 77 084 670 600)
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 * @version   SVN: $Date: 2016-08-16 23:39:01 +0900 (Tue, 16 Aug 2016) $ $Revision: 26 $
 * @link      $HeadURL: https://open.logics.net.au/buildtools/codesniffer/tags/0.1.3/src/CodeSniffer/Standards/Squiz/Sniffs/Strings/EchoedStringsSniff.php $
 */

class EchoedStringsSniff implements Sniff
    {

	/**
	 * Returns an array of tokens this test wants to listen for.
	 *
	 * @return array
	 */

	public function register()
	    {
		return array(T_ECHO);
	    } //end register()


	/**
	 * Processes this test, when one of its tokens is encountered.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $stackPtr  The position of the current token in the stack passed in $tokens.
	 *
	 * @return void
	 *
	 * @internalconst T_OPEN_PARENTHESIS  T_OPEN_PARENTHESIS token
	 * @internalconst T_SEMICOLON         T_SEMICOLON token
	 * @internalconst T_CLOSE_PARENTHESIS T_CLOSE_PARENTHESIS token
	 *
	 * @untranslatable HasBracket
	 */

	public function process(File $phpcsFile, $stackPtr)
	    {
		$tokens = &$phpcsFile->tokens;

		$firstContent = $phpcsFile->findNext(array(T_WHITESPACE), ($stackPtr + 1), null, true);
		// If the first non-whitespace token is not an opening parenthesis, then we are not concerned.
		if ($tokens[$firstContent]["code"] === T_OPEN_PARENTHESIS)
		    {
			$endOfStatement = $phpcsFile->findNext(array(T_SEMICOLON), $stackPtr, null, false);

			// If the token before the semi-colon is not a closing parenthesis, then we are not concerned.
			if ($tokens[($endOfStatement - 1)]["code"] === T_CLOSE_PARENTHESIS)
			    {
				if (($phpcsFile->findNext(Tokens::$operators, $stackPtr, $endOfStatement, false)) === false)
				    {
					// There are no arithmetic operators in this.
					$error = _("Echoed strings should not be bracketed");
					$phpcsFile->addError($error, $stackPtr, "HasBracket");
				    }
			    }
		    }
	    } //end process()


    } //end class

?>
