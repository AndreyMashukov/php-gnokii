<?php

/**
 * Ensures that strings are not joined using array.join().
 *
 * PHP version 5
 *
 * @package Logics\BuildTools\CodeSniffer\MySource
 */

namespace Logics\BuildTools\CodeSniffer\MySource;

use \Logics\BuildTools\CodeSniffer\File;
use \Logics\BuildTools\CodeSniffer\Sniff;
use \Logics\BuildTools\CodeSniffer\Tokens;

/**
 * Ensures that strings are not joined using array.join().
 *
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2006-2012 Squiz Pty Ltd (ABN 77 084 670 600)
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 * @version   SVN: $Date: 2016-08-16 23:39:01 +0900 (Tue, 16 Aug 2016) $ $Revision: 26 $
 * @link      $HeadURL: https://open.logics.net.au/buildtools/codesniffer/tags/0.1.3/src/CodeSniffer/Standards/MySource/Sniffs/Strings/JoinStringsSniff.php $
 *
 * @untranslatable JS
 */

class JoinStringsSniff implements Sniff
    {

	/**
	 * A list of tokenizers this sniff supports.
	 *
	 * @var array
	 */
	public $supportedTokenizers = array("JS");

	/**
	 * Returns an array of tokens this test wants to listen for.
	 *
	 * @return array
	 */

	public function register()
	    {
		return array(T_STRING);
	    } //end register()


	/**
	 * Processes this test, when one of its tokens is encountered.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $stackPtr  The position of the current token in the stack passed in $tokens.
	 *
	 * @return void
	 *
	 * @internalconst T_CLOSE_SQUARE_BRACKET T_CLOSE_SQUARE_BRACKET token
	 *
	 * @untranslatable join
	 * @untranslatable ArrayNotAllowed
	 */

	public function process(File $phpcsFile, $stackPtr)
	    {
		$tokens = &$phpcsFile->tokens;

		if ($tokens[$stackPtr]["content"] === "join")
		    {
			$prev = $phpcsFile->findPrevious(Tokens::$emptyTokens, ($stackPtr - 1), null, true);
			if ($tokens[$prev]["code"] === T_OBJECT_OPERATOR)
			    {
				$prev = $phpcsFile->findPrevious(Tokens::$emptyTokens, ($prev - 1), null, true);
				if ($tokens[$prev]["code"] === T_CLOSE_SQUARE_BRACKET)
				    {
					$opener = $tokens[$prev]["bracket_opener"];
					if ($tokens[($opener - 1)]["code"] !== T_STRING)
					    {
						// This means the array is declared inline, like x = [a,b,c].join()
						// and not elsewhere, like x = y[a].join()
						// The first is not allowed while the second is.
						$error = _("Joining strings using inline arrays is not allowed; use the + operator instead");
						$phpcsFile->addError($error, $stackPtr, "ArrayNotAllowed");
					    }
				    }
			    }
		    }
	    } //end process()


    } //end class

?>
