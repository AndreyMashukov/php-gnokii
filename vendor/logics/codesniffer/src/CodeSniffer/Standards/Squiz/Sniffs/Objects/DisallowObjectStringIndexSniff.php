<?php

/**
 * Ensures that object indexes are written in dot notation.
 *
 * PHP version 5
 *
 * @package Logics\BuildTools\CodeSniffer\Squiz
 */

namespace Logics\BuildTools\CodeSniffer\Squiz;

use \Logics\BuildTools\CodeSniffer\File;
use \Logics\BuildTools\CodeSniffer\Sniff;

/**
 * Ensures that object indexes are written in dot notation.
 *
 * @author    Sertan Danis <sdanis@squiz.net>
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2006-2012 Squiz Pty Ltd (ABN 77 084 670 600)
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 * @version   SVN: $Date: 2016-08-16 23:39:01 +0900 (Tue, 16 Aug 2016) $ $Revision: 26 $
 * @link      $HeadURL: https://open.logics.net.au/buildtools/codesniffer/tags/0.1.3/src/CodeSniffer/Standards/Squiz/Sniffs/Objects/DisallowObjectStringIndexSniff.php $
 *
 * @untranslatable JS
 */

class DisallowObjectStringIndexSniff implements Sniff
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
	 *
	 * @internalconst T_OPEN_SQUARE_BRACKET T_OPEN_SQUARE_BRACKET token
	 */

	public function register()
	    {
		return array(T_OPEN_SQUARE_BRACKET);
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
	 * @untranslatable super
	 * @untranslatable Found
	 */

	public function process(File $phpcsFile, $stackPtr)
	    {
		$tokens = &$phpcsFile->tokens;

		// Check if the next non whitespace token is a string.
		$index = $phpcsFile->findNext(T_WHITESPACE, ($stackPtr + 1), null, true);
		if ($tokens[$index]["code"] === T_CONSTANT_ENCAPSED_STRING)
		    {
			// Make sure it is the only thing in the square brackets.
			$next = $phpcsFile->findNext(T_WHITESPACE, ($index + 1), null, true);
			if ($tokens[$next]["code"] === T_CLOSE_SQUARE_BRACKET)
			    {
				// Allow indexes that have dots in them because we can't write
				// them in dot notation.
				$content = trim($tokens[$index]["content"], "\"' ");
				if (strpos($content, ".") === false)
				    {
					// Also ignore reserved words.
					if ($content !== "super")
					    {
						// Token before the opening square bracket cannot be a var name.
						$prev = $phpcsFile->findPrevious(T_WHITESPACE, ($stackPtr - 1), null, true);
						if ($tokens[$prev]["code"] === T_STRING)
						    {
							$error = _("Object indexes must be written in dot notation");
							$phpcsFile->addError($error, $prev, "Found");
						    }
					    }
				    }
			    }
		    } //end if
	    } //end process()


    } //end class

?>
