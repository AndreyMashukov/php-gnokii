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
 * DisallowMultipleStatementsSniff.
 *
 * Ensures each statement is on a line by itself.
 *
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2006-2012 Squiz Pty Ltd (ABN 77 084 670 600)
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 * @version   SVN: $Date: 2016-08-16 23:39:01 +0900 (Tue, 16 Aug 2016) $ $Revision: 26 $
 * @link      $HeadURL: https://open.logics.net.au/buildtools/codesniffer/tags/0.1.3/src/CodeSniffer/Standards/Generic/Sniffs/Formatting/DisallowMultipleStatementsSniff.php $
 */

class DisallowMultipleStatementsSniff implements Sniff
    {

	/**
	 * Returns an array of tokens this test wants to listen for.
	 *
	 * @return array
	 *
	 * @internalconst T_SEMICOLON Semicolon token
	 */

	public function register()
	    {
		return array(T_SEMICOLON);
	    } //end register()


	/**
	 * Processes this test, when one of its tokens is encountered.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $stackPtr  The position of the current token in the stack passed in $tokens.
	 *
	 * @return void
	 *
	 * @internalconst T_SEMICOLON Semicolon token
	 *
	 * @untranslatable SameLine
	 */

	public function process(File $phpcsFile, $stackPtr)
	    {
		$tokens = &$phpcsFile->tokens;

		$prev = $phpcsFile->findPrevious(T_SEMICOLON, ($stackPtr - 1));
		if ($prev !== false)
		    {
			$for = false;

			// Ignore multiple statements in a FOR condition.
			if (isset($tokens[$stackPtr]["nested_parenthesis"]) === true)
			    {
				foreach ($tokens[$stackPtr]["nested_parenthesis"] as $bracket)
				    {
					if (isset($tokens[$bracket]["parenthesis_owner"]) === true)
					    {
						// Probably a closure sitting inside a function call.
						$owner = $tokens[$bracket]["parenthesis_owner"];
						if ($tokens[$owner]["code"] === T_FOR)
						    {
							$for = true;
							break;
						    }
					    }
				    }
			    }

			if ($for === false && $tokens[$prev]["line"] === $tokens[$stackPtr]["line"])
			    {
				$phpcsFile->addError(_("Each PHP statement must be on a line by itself"), $stackPtr, "SameLine");
			    }
		    } //end if
	    } //end process()


    } //end class

?>
