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
 * Squiz_Sniffs_CSS_ClassDefinitionClosingBraceSpaceSniff.
 *
 * Ensure there is a single blank line after the closing brace of a class definition.
 *
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2006-2012 Squiz Pty Ltd (ABN 77 084 670 600)
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 * @version   SVN: $Date: 2016-08-16 23:39:01 +0900 (Tue, 16 Aug 2016) $ $Revision: 26 $
 * @link      $HeadURL: https://open.logics.net.au/buildtools/codesniffer/tags/0.1.3/src/CodeSniffer/Standards/Squiz/Sniffs/CSS/ClassDefinitionClosingBraceSpaceSniff.php $
 *
 * @untranslatable CSS
 */

class ClassDefinitionClosingBraceSpaceSniff implements Sniff
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
	 * @internalconst T_CLOSE_CURLY_BRACKET T_CLOSE_CURLY_BRACKET token
	 */

	public function register()
	    {
		return array(T_CLOSE_CURLY_BRACKET);
	    } //end register()


	/**
	 * Processes the tokens that this sniff is interested in.
	 *
	 * @param File $phpcsFile The file where the token was found.
	 * @param int  $stackPtr  The position in the stack where the token was found.
	 *
	 * @return void
	 *
	 * @internalconst T_CLOSE_CURLY_BRACKET T_CLOSE_CURLY_BRACKET token
	 *
	 * @untranslatable SpacingAfterClose
	 * @untranslatable SpacingBeforeClose
	 * @untranslatable %s
	 */

	public function process(File $phpcsFile, $stackPtr)
	    {
		$tokens = &$phpcsFile->tokens;

		$next = $phpcsFile->findNext(T_WHITESPACE, ($stackPtr + 1), null, true);
		if ($next !== false)
		    {
			if ($tokens[$next]["code"] !== T_CLOSE_TAG)
			    {
				$found = (($tokens[$next]["line"] - $tokens[$stackPtr]["line"]) - 1);
				if ($found !== 1)
				    {
					$error = _("Expected one blank line after closing brace of class definition;") . " %s " . _("found");
					$data  = array($found);
					$phpcsFile->addError($error, $stackPtr, "SpacingAfterClose", $data);
				    }
			    }

			// Ignore nested style definitions from here on. The spacing before the closing brace
			// (a single blank line) will be enforced by the above check, which ensures there is a
			// blank line after the last nested class.
			$found = $phpcsFile->findPrevious(
				    T_CLOSE_CURLY_BRACKET,
				    ($stackPtr - 1),
				    $tokens[$stackPtr]["bracket_opener"]
				 );
			if ($found === false)
			    {
				$prev = $phpcsFile->findPrevious(Tokens::$emptyTokens, ($stackPtr - 1), null, true);
				if ($prev !== false && $tokens[$prev]["line"] !== ($tokens[$stackPtr]["line"] - 1))
				    {
					$num   = ($tokens[$stackPtr]["line"] - $tokens[$prev]["line"] - 1);
					$error = _("Expected 0 blank lines before closing brace of class definition;") . " %s " . _("found");
					$data  = array($num);
					$phpcsFile->addError($error, $stackPtr, "SpacingBeforeClose", $data);
				    }
			    }
		    } //end if
	    } //end process()


    } //end class

?>
