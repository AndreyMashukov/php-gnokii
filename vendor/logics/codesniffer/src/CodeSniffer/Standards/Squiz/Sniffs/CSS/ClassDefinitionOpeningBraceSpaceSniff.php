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
 * Squiz_Sniffs_CSS_ClassDefinitionOpeningBraceSpaceSniff.
 *
 * Ensure there is a single space before the opening brace in a class definition
 * and the content starts on the next line.
 *
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2006-2012 Squiz Pty Ltd (ABN 77 084 670 600)
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 * @version   SVN: $Date: 2016-08-16 23:39:01 +0900 (Tue, 16 Aug 2016) $ $Revision: 26 $
 * @link      $HeadURL: https://open.logics.net.au/buildtools/codesniffer/tags/0.1.3/src/CodeSniffer/Standards/Squiz/Sniffs/CSS/ClassDefinitionOpeningBraceSpaceSniff.php $
 *
 * @untranslatable CSS
 */

class ClassDefinitionOpeningBraceSpaceSniff implements Sniff
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
	 *
	 * @untranslatable NoneBefore
	 * @untranslatable tab
	 * @untranslatable Before
	 * @untranslatable AfterNesting
	 * @untranslatable After
	 * @untranslatable %s
	 */

	public function process(File $phpcsFile, $stackPtr)
	    {
		$tokens = &$phpcsFile->tokens;

		if ($tokens[($stackPtr - 1)]["code"] !== T_WHITESPACE)
		    {
			$error = _("Expected 1 space before opening brace of class definition; 0 found");
			$phpcsFile->addError($error, $stackPtr, "NoneBefore");
		    }
		else
		    {
			$content = $tokens[($stackPtr - 1)]["content"];
			if ($content !== " ")
			    {
				$length = strlen($content);
				if ($length === 1)
				    {
					$length = "tab";
				    }

				$error = _("Expected 1 space before opening brace of class definition;") . " %s " . _("found");
				$data  = array($length);
				$phpcsFile->addError($error, $stackPtr, "Before", $data);
			    }
		    } //end if

		$next = $phpcsFile->findNext(Tokens::$emptyTokens, ($stackPtr + 1), null, true);
		if ($next !== false)
		    {
			// Check for nested class definitions.
			$nested = false;
			$found  = $phpcsFile->findNext(
				   T_OPEN_CURLY_BRACKET,
				   ($stackPtr + 1),
				   $tokens[$stackPtr]["bracket_closer"]
				  );
			if ($found !== false)
			    {
				$nested = true;
			    }

			$foundLines = ($tokens[$next]["line"] - $tokens[$stackPtr]["line"] - 1);
			if ($nested === true)
			    {
				if ($foundLines !== 1)
				    {
					$error = _("Expected 1 blank line after opening brace of nesting class definition;") . " %s " . _("found");
					$data  = array($foundLines);
					$phpcsFile->addError($error, $stackPtr, "AfterNesting", $data);
				    }
			    }
			else
			    {
				if ($foundLines !== 0)
				    {
					$error = _("Expected 0 blank lines after opening brace of class definition;") . " %s " . _("found");
					$data  = array($foundLines);
					$phpcsFile->addError($error, $stackPtr, "After", $data);
				    }
			    }
		    } //end if
	    } //end process()


    } //end class

?>
