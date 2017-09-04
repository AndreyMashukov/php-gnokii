<?php

/**
 * PHP version 5
 *
 * @package Logics\BuildTools\CodeSniffer\Squiz
 */

namespace Logics\BuildTools\CodeSniffer\Squiz;

use \Logics\BuildTools\CodeSniffer\File;
use \Logics\BuildTools\CodeSniffer\Sniff;

/**
 * Squiz_Sniffs_WhiteSpace_FunctionOpeningBraceSpaceSniff.
 *
 * Checks that there is no empty line after the opening brace of a function.
 *
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @author    Marc McIntyre <mmcintyre@squiz.net>
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2006-2012 Squiz Pty Ltd (ABN 77 084 670 600)
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 * @version   SVN: $Date: 2016-08-16 23:39:01 +0900 (Tue, 16 Aug 2016) $ $Revision: 26 $
 * @link      $HeadURL: https://open.logics.net.au/buildtools/codesniffer/tags/0.1.3/src/CodeSniffer/Standards/Squiz/Sniffs/WhiteSpace/FunctionOpeningBraceSpaceSniff.php $
 *
 * @untranslatable PHP
 * @untranslatable JS
 */

class FunctionOpeningBraceSpaceSniff implements Sniff
    {

	/**
	 * A list of tokenizers this sniff supports.
	 *
	 * @var array
	 */
	public $supportedTokenizers = array(
				       "PHP",
				       "JS",
				      );

	/**
	 * Returns an array of tokens this test wants to listen for.
	 *
	 * @return array
	 *
	 * @internalconst T_CLOSURE T_CLOSURE token
	 */

	public function register()
	    {
		return array(
			T_FUNCTION,
			T_CLOSURE,
		       );
	    } //end register()


	/**
	 * Processes this test, when one of its tokens is encountered.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $stackPtr  The position of the current token in the stack passed in $tokens.
	 *
	 * @return void
	 *
	 * @untranslatable SpacingAfter
	 * @untranslatable %s
	 */

	public function process(File $phpcsFile, $stackPtr)
	    {
		$tokens = &$phpcsFile->tokens;

		if (isset($tokens[$stackPtr]["scope_opener"]) === false)
		    {
			// Probably an interface method.
			return;
		    }

		$openBrace   = $tokens[$stackPtr]["scope_opener"];
		$nextContent = $phpcsFile->findNext(T_WHITESPACE, ($openBrace + 1), null, true);

		if ($nextContent === $tokens[$stackPtr]["scope_closer"])
		    {
			 // The next bit of content is the closing brace, so this is an empty function and should have a blank line between the opening and closing braces.
			return;
		    }
		else
		    {
			$braceLine = $tokens[$openBrace]["line"];
			$nextLine  = $tokens[$nextContent]["line"];

			$found = ($nextLine - $braceLine - 1);
			if ($found > 0)
			    {
				$error = _("Expected 0 blank lines after opening function brace;") . " %s " . _("found");
				$data  = array($found);
				$phpcsFile->addError($error, $openBrace, "SpacingAfter", $data);
			    }
		    }
	    } //end process()


    } //end class

?>