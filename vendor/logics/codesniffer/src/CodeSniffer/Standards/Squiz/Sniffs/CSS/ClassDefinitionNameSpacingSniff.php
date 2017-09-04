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
 * Squiz_Sniffs_CSS_ClassDefinitionNameSpacingSniff.
 *
 * Ensure there are no blank lines between the names of classes/IDs.
 *
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2006-2012 Squiz Pty Ltd (ABN 77 084 670 600)
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 * @version   SVN: $Date: 2016-08-16 23:39:01 +0900 (Tue, 16 Aug 2016) $ $Revision: 26 $
 * @link      $HeadURL: https://open.logics.net.au/buildtools/codesniffer/tags/0.1.3/src/CodeSniffer/Standards/Squiz/Sniffs/CSS/ClassDefinitionNameSpacingSniff.php $
 *
 * @untranslatable CSS
 */

class ClassDefinitionNameSpacingSniff implements Sniff
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
	 * @internalconst T_OPEN_CURLY_BRACKET  T_OPEN_CURLY_BRACKET token
	 * @internalconst T_CLOSE_CURLY_BRACKET T_CLOSE_CURLY_BRACKET token
	 *
	 * @untranslatable BlankLinesFound
	 */

	public function process(File $phpcsFile, $stackPtr)
	    {
		$tokens = &$phpcsFile->tokens;

		// Do not check nested style definitions as, for example, in @media style rules.
		$nested = $phpcsFile->findNext(T_OPEN_CURLY_BRACKET, ($stackPtr + 1), $tokens[$stackPtr]["bracket_closer"]);
		if ($nested === false)
		    {
			// Find the first blank line before this opening brace, unless we get
			// to another style definition, comment or the start of the file.
			$endTokens  = array(
				       T_OPEN_CURLY_BRACKET  => T_OPEN_CURLY_BRACKET,
				       T_CLOSE_CURLY_BRACKET => T_CLOSE_CURLY_BRACKET,
				       T_OPEN_TAG            => T_OPEN_TAG,
				      );
			$endTokens += Tokens::$commentTokens;

			$foundContent = false;
			$currentLine  = $tokens[$stackPtr]["line"];
			for ($i = ($stackPtr - 1); $i >= 0; $i--)
			    {
				if (in_array($tokens[$i]["code"], $endTokens) === true)
				    {
					break;
				    }

				if ($tokens[$i]["line"] === $currentLine)
				    {
					if ($tokens[$i]["code"] !== T_WHITESPACE)
					    {
						$foundContent = true;
					    }
				    }
				else
				    {
					// We changed lines.
					if ($foundContent === false)
					    {
						// Before we throw an error, make sure we are not looking
						// at a gap before the style definition.
						$prev = $phpcsFile->findPrevious(T_WHITESPACE, $i, null, true);
						if ($prev !== false && in_array($tokens[$prev]["code"], $endTokens) === false)
						    {
							$error = _("Blank lines are not allowed between class names");
							$phpcsFile->addError($error, ($i + 1), "BlankLinesFound");
						    }

						break;
					    }

					$foundContent = false;
					$currentLine  = $tokens[$i]["line"];
				    } //end if
			    } //end for
		    } //end if
	    } //end process()


    } //end class

?>
