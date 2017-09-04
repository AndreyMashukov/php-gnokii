<?php

/**
 * PHP version 5
 *
 * @package Logics\BuildTools\CodeSniffer\Beauty
 */

namespace Logics\BuildTools\CodeSniffer\Beauty;

use \Logics\BuildTools\CodeSniffer\File;
use \Logics\BuildTools\CodeSniffer\Sniff;

/**
 * Sniffs_ControlStructures_LongConditionClosingCommentSniff.
 *
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @author    Marc McIntyre <mmcintyre@squiz.net>
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2006-2012 Squiz Pty Ltd (ABN 77 084 670 600)
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 * @version   SVN: $Date: 2016-08-16 23:39:01 +0900 (Tue, 16 Aug 2016) $ $Revision: 26 $
 * @link      $HeadURL: https://open.logics.net.au/buildtools/codesniffer/tags/0.1.3/src/CodeSniffer/Standards/Beauty/Sniffs/Commenting/LongConditionClosingCommentSniff.php $
 *
 * @untranslatable PHP
 * @untranslatable JS
 */

class LongConditionClosingCommentSniff implements Sniff
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
	 * The openers that we are interested in.
	 *
	 * @var array(int)
	 */
	private static $_openers = array(
				    T_SWITCH,
				    T_IF,
				    T_FOR,
				    T_FOREACH,
				    T_WHILE,
				    T_TRY,
				    T_CASE,
				   );

	/**
	 * The length that a code block must be before requiring a closing comment.
	 *
	 * @var int
	 */
	protected $lineLimit = 20;

	/**
	 * Returns an array of tokens this test wants to listen for.
	 *
	 * @return array
	 *
	 * @internalconst T_CLOSE_CURLY_BRACKET T_CLOSE_CURLY_BRACKET token
	 */

	public function register()
	    {
		return array(T_CLOSE_CURLY_BRACKET);
	    } //end register()


	/**
	 * Processes this test, when one of its tokens is encountered.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $stackPtr  The position of the current token in the stack passed in $tokens.
	 *
	 * @return void
	 *
	 * @untranslatable //end
	 * @untranslatable Missing
	 * @untranslatable SpacingBefore
	 * @untranslatable \"%s\"
	 * @untranslatable Invalid
	 */

	public function process(File $phpcsFile, $stackPtr)
	    {
		$tokens = &$phpcsFile->tokens;

		if (isset($tokens[$stackPtr]["scope_condition"]) === true)
		    {
			$startCondition = $tokens[$tokens[$stackPtr]["scope_condition"]];
			$startBrace     = $tokens[$tokens[$stackPtr]["scope_opener"]];
			$endBrace       = $tokens[$stackPtr];

			// We are only interested in some code blocks.
			if (in_array($startCondition["code"], self::$_openers) === true)
			    {
				$skip = false;

				if ($startCondition["code"] === T_IF)
				    {
					// If this is actually and ELSE IF, skip it as the brace
					// will be checked by the original IF.
					$else = $phpcsFile->findPrevious(T_WHITESPACE, ($tokens[$stackPtr]["scope_condition"] - 1), null, true);
					if ($tokens[$else]["code"] === T_ELSE)
					    {
						$skip = true;
					    }
					else
					    {
						$this->_processIfToken($tokens, $phpcsFile, $stackPtr, $endBrace);
					    } //end if
				    }
				else if ($startCondition["code"] === T_TRY)
				    {
					$this->_processTryToken($tokens, $phpcsFile, $stackPtr, $endBrace);
				    } //end if

				if ($skip === false)
				    {
					$lineDifference = ($endBrace["line"] - $startBrace["line"]);

					$expected = "//end " . $startCondition["content"];
					$comment  = $phpcsFile->findNext(array(T_COMMENT), $stackPtr, null, false);

					if (($comment === false || $tokens[$comment]["line"] !== $endBrace["line"]) && $lineDifference >= $this->lineLimit)
					    {
						$data = array($expected);
						$phpcsFile->addError(_("End comment for long condition not found; expected") . " \"%s\"", $stackPtr, "Missing", $data);
					    }

					if ($comment !== false && $tokens[$comment]["line"] === $endBrace["line"] && ($comment - $stackPtr) !== 2)
					    {
						$data = array($expected);
						$phpcsFile->addError(_("Space not found before closing comment; expected") . " \"%s\"", $stackPtr, "SpacingBefore", $data);
					    }

					if ($comment !== false && $tokens[$comment]["line"] === $endBrace["line"] && trim($tokens[$comment]["content"]) !== $expected)
					    {
						$data = array(
							 $expected,
							 trim($tokens[$comment]["content"]),
							);
						$phpcsFile->addError(
						    _("Incorrect closing comment; expected") . " \"%s\" " . _("but found") . " \"%s\"", $stackPtr, "Invalid", $data
						);
					    }
				    } //end if
			    } //end if
		    } //end if
	    } //end process()


	/**
	 * IF statements that have an ELSE block need to use "end if" rather than "end else" or "end elseif".
	 *
	 * @param array $tokens    Tokens array
	 * @param File  $phpcsFile The file being scanned.
	 * @param int   $stackPtr  The position of the current token in the stack passed in $tokens.
	 * @param int   $endBrace  The position of closing brace
	 *
	 * @return void
	 */

	private function _processIfToken(array &$tokens, File &$phpcsFile, &$stackPtr, &$endBrace)
	    {
		do
		    {
			$nextToken = $phpcsFile->findNext(T_WHITESPACE, ($stackPtr + 1), null, true);
			if ($tokens[$nextToken]["code"] === T_ELSE || $tokens[$nextToken]["code"] === T_ELSEIF)
			    {
				// Check for ELSE IF which is 2 tokens as opposed to ELSEIF which is 1 token.
				if ($tokens[$nextToken]["code"] === T_ELSE && isset($tokens[$nextToken]["scope_closer"]) === false)
				    {
					$nextToken = $phpcsFile->findNext(T_WHITESPACE, ($nextToken + 1), null, true);
					if ($tokens[$nextToken]["code"] !== T_IF || isset($tokens[$nextToken]["scope_closer"]) === false)
					    {
						// Not an ELSE IF or is an inline ELSE IF.
						break;
					    }
				    }

				// The end brace becomes the ELSE's end brace.
				$stackPtr = $tokens[$nextToken]["scope_closer"];
				$endBrace = $tokens[$stackPtr];
			    }
			else
			    {
				break;
			    }
		    } while (isset($tokens[$nextToken]["scope_closer"]) === true);
	    } //end _processIfToken()


	/**
	 * TRY statements need to check until the end of all CATCH statements.
	 *
	 * @param array $tokens    Tokens array
	 * @param File  $phpcsFile The file being scanned.
	 * @param int   $stackPtr  The position of the current token in the stack passed in $tokens.
	 * @param int   $endBrace  The position of closing brace
	 *
	 * @return void
	 */

	private function _processTryToken(array &$tokens, File &$phpcsFile, &$stackPtr, &$endBrace)
	    {
		do
		    {
			$nextToken = $phpcsFile->findNext(T_WHITESPACE, ($stackPtr + 1), null, true);
			if ($tokens[$nextToken]["code"] === T_CATCH)
			    {
				// The end brace becomes the CATCH's end brace.
				$stackPtr = $tokens[$nextToken]["scope_closer"];
				$endBrace = $tokens[$stackPtr];
			    }
			else
			    {
				break;
			    }
		    } while (isset($tokens[$nextToken]["scope_closer"]) === true);
	    } //end _processTryToken()


    } //end class

?>
