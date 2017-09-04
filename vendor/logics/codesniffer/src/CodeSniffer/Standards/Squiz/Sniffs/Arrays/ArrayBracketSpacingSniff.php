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
 * Squiz_Sniffs_Arrays_ArrayBracketSpacingSniff.
 *
 * Ensure that there are no spaces around square brackets.
 *
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @author    Marc McIntyre <mmcintyre@squiz.net>
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2006-2012 Squiz Pty Ltd (ABN 77 084 670 600)
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 * @version   SVN: $Date: 2016-08-16 23:39:01 +0900 (Tue, 16 Aug 2016) $ $Revision: 26 $
 * @link      $HeadURL: https://open.logics.net.au/buildtools/codesniffer/tags/0.1.3/src/CodeSniffer/Standards/Squiz/Sniffs/Arrays/ArrayBracketSpacingSniff.php $
 */

class ArrayBracketSpacingSniff implements Sniff
    {

	/**
	 * Returns an array of tokens this test wants to listen for.
	 *
	 * @return array
	 *
	 * @internalconst T_OPEN_SQUARE_BRACKET  T_OPEN_SQUARE_BRACKET token
	 * @internalconst T_CLOSE_SQUARE_BRACKET T_CLOSE_SQUARE_BRACKET token
	 */

	public function register()
	    {
		return array(
			T_OPEN_SQUARE_BRACKET,
			T_CLOSE_SQUARE_BRACKET,
		       );
	    } //end register()


	/**
	 * Processes this sniff, when one of its tokens is encountered.
	 *
	 * @param File $phpcsFile The current file being checked.
	 * @param int  $stackPtr  The position of the current token in the stack passed in $tokens.
	 *
	 * @return void
	 *
	 * @internalconst T_EQUAL               T_EQUAL token
	 * @internalconst T_OPEN_SQUARE_BRACKET T_OPEN_SQUARE_BRACKET token
	 *
	 * @untranslatable SpaceBeforeBracket
	 * @untranslatable \"%s\"
	 * @untranslatable SpaceAfterBracket
	 */

	public function process(File $phpcsFile, $stackPtr)
	    {
		$tokens = &$phpcsFile->tokens;

		// PHP 5.4 introduced a shorthand array declaration syntax, so we need
		// to ignore the these type of array declarations because this sniff is
		// only dealing with array usage.
		if ($tokens[$stackPtr]["code"] === T_OPEN_SQUARE_BRACKET)
		    {
			$openBracket = $stackPtr;
		    }
		else
		    {
			$openBracket = $tokens[$stackPtr]["bracket_opener"];
		    }

		$prev = $phpcsFile->findPrevious(Tokens::$emptyTokens, ($openBracket - 1), null, true);
		if ($tokens[$prev]["code"] !== T_EQUAL)
		    {
			// Square brackets can not have a space before them.
			$prevType = $tokens[($stackPtr - 1)]["code"];
			if (in_array($prevType, Tokens::$emptyTokens) === true)
			    {
				$nonSpace = $phpcsFile->findPrevious(Tokens::$emptyTokens, ($stackPtr - 2), null, true);
				$expected = $tokens[$nonSpace]["content"] . $tokens[$stackPtr]["content"];
				$found    = $phpcsFile->getTokensAsString($nonSpace, ($stackPtr - $nonSpace)) . $tokens[$stackPtr]["content"];
				$error    = _("Space found before square bracket; expected") . " \"%s\" " . _("but found") . " \"%s\"";
				$data     = array(
					     $expected,
					     $found,
					    );
				$phpcsFile->addError($error, $stackPtr, "SpaceBeforeBracket", $data);
			    }

			// Open square brackets can't ever have spaces after them.
			if ($tokens[$stackPtr]["code"] === T_OPEN_SQUARE_BRACKET)
			    {
				$nextType = $tokens[($stackPtr + 1)]["code"];
				if (in_array($nextType, Tokens::$emptyTokens) === true)
				    {
					$nonSpace = $phpcsFile->findNext(Tokens::$emptyTokens, ($stackPtr + 2), null, true);
					$expected = $tokens[$stackPtr]["content"] . $tokens[$nonSpace]["content"];
					$found    = $phpcsFile->getTokensAsString($stackPtr, ($nonSpace - $stackPtr + 1));
					$error    = _("Space found after square bracket; expected") . " \"%s\" " . _("but found") . " \"%s\"";
					$data     = array(
						     $expected,
						     $found,
						    );
					$phpcsFile->addError($error, $stackPtr, "SpaceAfterBracket", $data);
				    }
			    }
		    } //end if
	    } //end process()


    } //end class

?>
