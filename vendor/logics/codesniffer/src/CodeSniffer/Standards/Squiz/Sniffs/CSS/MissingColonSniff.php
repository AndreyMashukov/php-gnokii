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
 * Squiz_Sniffs_CSS_MissingColonSniff.
 *
 * Ensure that all style definitions have a colon.
 *
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2006-2012 Squiz Pty Ltd (ABN 77 084 670 600)
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 * @version   SVN: $Date: 2016-08-16 23:39:01 +0900 (Tue, 16 Aug 2016) $ $Revision: 26 $
 * @link      $HeadURL: https://open.logics.net.au/buildtools/codesniffer/tags/0.1.3/src/CodeSniffer/Standards/Squiz/Sniffs/CSS/MissingColonSniff.php $
 *
 * @untranslatable CSS
 */

class MissingColonSniff implements Sniff
    {

	/**
	 * A list of tokenizers this sniff supports.
	 *
	 * @var array
	 */
	public $supportedTokenizers = array("CSS");

	/**
	 * Returns the token types that this sniff is interested in.
	 *
	 * @return array(int)
	 *
	 * @internalconst T_OPEN_CURLY_BRACKET T_OPEN_CURLY_BRACKET token
	 */

	public function register()
	    {
		return array(T_OPEN_CURLY_BRACKET);
	    } //end register()


	/**
	 * Processes the tokens that this sniff is interested in.
	 *
	 * @param File $phpcsFile The file where the token was found.
	 * @param int  $stackPtr  The position in the stack where the token was found.
	 *
	 * @return void
	 *
	 * @internalconst T_OPEN_CURLY_BRACKET T_OPEN_CURLY_BRACKET token
	 * @internalconst T_COLON              T_COLON token
	 *
	 * @untranslatable Found
	 */

	public function process(File $phpcsFile, $stackPtr)
	    {
		$tokens   = &$phpcsFile->tokens;
		$lastLine = $tokens[$stackPtr]["line"];
		$end      = $tokens[$stackPtr]["bracket_closer"];
		$endLine  = $tokens[$end]["line"];

		// Do not check nested style definitions as, for example, in @media style rules.
		$nested = $phpcsFile->findNext(T_OPEN_CURLY_BRACKET, ($stackPtr + 1), $end);
		if ($nested === false)
		    {
			$foundColon  = false;
			$foundString = false;
			for ($i = ($stackPtr + 1); $i <= $end; $i++)
			    {
				if ($tokens[$i]["line"] !== $lastLine)
				    {
					// We changed lines.
					if ($foundColon === false && $foundString !== false)
					    {
						// We didn't find a colon on the previous line.
						$error = _("No style definition found on line; check for missing colon");
						$phpcsFile->addError($error, $foundString, "Found");
					    }

					$foundColon  = false;
					$foundString = false;
					$lastLine    = $tokens[$i]["line"];
				    }

				if ($tokens[$i]["code"] === T_STRING)
				    {
					$foundString = $i;
				    }
				else if ($tokens[$i]["code"] === T_COLON)
				    {
					$foundColon = $i;
				    }
			    } //end for
		    } //end if
	    } //end process()


    } //end class

?>
