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
 * Squiz_Sniffs_CSS_IndentationSniff.
 *
 * Ensures styles are indented 4 spaces.
 *
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2006-2012 Squiz Pty Ltd (ABN 77 084 670 600)
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 * @version   SVN: $Date: 2016-08-16 23:39:01 +0900 (Tue, 16 Aug 2016) $ $Revision: 26 $
 * @link      $HeadURL: https://open.logics.net.au/buildtools/codesniffer/tags/0.1.3/src/CodeSniffer/Standards/Squiz/Sniffs/CSS/IndentationSniff.php $
 *
 * @untranslatable CSS
 */

class IndentationSniff implements Sniff
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
	 */

	public function register()
	    {
		return array(T_OPEN_TAG);
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
	 * @untranslatable BlankLine
	 * @untranslatable Incorrect
	 * @untranslatable %s
	 */

	public function process(File $phpcsFile, $stackPtr)
	    {
		unset($stackPtr);
		$tokens = &$phpcsFile->tokens;

		$numTokens    = (count($tokens) - 2);
		$currentLine  = 0;
		$indentLevel  = 0;
		$nestingLevel = 0;
		for ($i = 1; $i < $numTokens; $i++)
		    {
			// Don't check the indent of comments.
			if ($tokens[$i]["code"] !== T_COMMENT)
			    {
				if ($tokens[$i]["code"] === T_OPEN_CURLY_BRACKET)
				    {
					$indentLevel++;

					// Check for nested class definitions.
					$found        = $phpcsFile->findNext(
							 T_OPEN_CURLY_BRACKET,
							 ($i + 1),
							 $tokens[$i]["bracket_closer"]
							);
					$nestingLevel = (($found === false) ? $nestingLevel : $indentLevel);
				    }
				else if ($tokens[($i + 1)]["code"] === T_CLOSE_CURLY_BRACKET)
				    {
					$indentLevel--;
				    }

				if ($tokens[$i]["line"] !== $currentLine)
				    {
					// We started a new line, so check indent.
					if ($tokens[$i]["code"] === T_WHITESPACE)
					    {
						$content     = str_replace($phpcsFile->eolChar, "", $tokens[$i]["content"]);
						$foundIndent = strlen($content);
					    }
					else
					    {
						$foundIndent = 0;
					    }

					$expectedIndent = ($indentLevel * 4);
					if ($expectedIndent > 0 && strpos($tokens[$i]["content"], $phpcsFile->eolChar) !== false)
					    {
						if ($nestingLevel !== $indentLevel)
						    {
							$error = _("Blank lines are not allowed in class definitions");
							$phpcsFile->addError($error, $i, "BlankLine");
						    }
					    }
					else if ($foundIndent !== $expectedIndent)
					    {
						$error = _("Line indented incorrectly; expected") . " %s " . _("spaces, found") . " %s";
						$data  = array(
							  $expectedIndent,
							  $foundIndent,
							 );
						$phpcsFile->addError($error, $i, "Incorrect", $data);
					    }

					$currentLine = $tokens[$i]["line"];
				    } //end if
			    } //end if
		    } //end for
	    } //end process()


    } //end class

?>