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
 * LongConditionClosingCommentSniff
 *
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @author    Marc McIntyre <mmcintyre@squiz.net>
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2006-2012 Squiz Pty Ltd (ABN 77 084 670 600)
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 * @version   SVN: $Date: 2016-08-16 23:39:01 +0900 (Tue, 16 Aug 2016) $ $Revision: 26 $
 * @link      $HeadURL: https://open.logics.net.au/buildtools/codesniffer/tags/0.1.3/src/CodeSniffer/Standards/Squiz/Sniffs/Commenting/LongConditionClosingCommentSniff.php $
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
	 */

	public function process(File $phpcsFile, $stackPtr)
	    {
		$tokens = &$phpcsFile->tokens;

		// No scope condition. It is a function closer.
		if (isset($tokens[$stackPtr]["scope_condition"]) === true)
		    {
			$startCondition = $tokens[$tokens[$stackPtr]["scope_condition"]];
			$startBrace     = $tokens[$tokens[$stackPtr]["scope_opener"]];
			$endBrace       = $tokens[$stackPtr];

			$previous = $phpcsFile->findPrevious(T_WHITESPACE, ($tokens[$stackPtr]["scope_condition"] - 1), null, true);

			// We are only interested in some code blocks.
			// If this is actually and ELSE IF, skip it as the brace
			// will be checked by the original IF.
			if (in_array($startCondition["code"], self::$_openers) === true &&
			    ($tokens[$previous]["code"] !== T_ELSE || $startCondition["code"] !== T_IF) &&
			    $startCondition["code"] === T_IF)
			    {
				// IF statements that have an ELSE block need to use
				// "end if" rather than "end else" or "end elseif".
				do
				    {
					$nextToken = $phpcsFile->findNext(T_WHITESPACE, ($stackPtr + 1), null, true);
					if ($tokens[$nextToken]["code"] === T_ELSE || $tokens[$nextToken]["code"] === T_ELSEIF)
					    {
						if ($this->_isElseIf($phpcsFile, $tokens, $nextToken) === false)
						    {
							break;
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

				$this->_checkClosingComment($phpcsFile, $tokens, $stackPtr, $startCondition, $startBrace, $endBrace);
			    }
			else if (in_array($startCondition["code"], self::$_openers) === true && $startCondition["code"] === T_TRY)
			    {
				// TRY statements need to check until the end of all CATCH statements.
				$nextToken = $phpcsFile->findNext(T_WHITESPACE, ($stackPtr + 1), null, true);
				while ($tokens[$nextToken]["code"] === T_CATCH && isset($tokens[$nextToken]["scope_closer"]) === true)
				    {
					// The end brace becomes the CATCH's end brace.
					$stackPtr  = $tokens[$nextToken]["scope_closer"];
					$endBrace  = $tokens[$stackPtr];
					$nextToken = $phpcsFile->findNext(T_WHITESPACE, ($stackPtr + 1), null, true);
				    }

				$this->_checkClosingComment($phpcsFile, $tokens, $stackPtr, $startCondition, $startBrace, $endBrace);
			    }
			else if (in_array($startCondition["code"], self::$_openers) === true &&
				 ($tokens[$previous]["code"] !== T_ELSE || $startCondition["code"] !== T_IF))
			    {
				$this->_checkClosingComment($phpcsFile, $tokens, $stackPtr, $startCondition, $startBrace, $endBrace);
			    } //end if
		    } //end if
	    } //end process()


	/**
	 * Check if token is ELSE IF
	 *
	 * @param File  $phpcsFile The file being scanned.
	 * @param array $tokens    All tokens
	 * @param int   $nextToken The position of the current token in the stack passed in $tokens.
	 *
	 * @return boolean True if token is ELSE IF
	 */

	private function _isElseIf(File &$phpcsFile, array &$tokens, &$nextToken)
	    {
		$result = true;
		if ($tokens[$nextToken]["code"] === T_ELSE && isset($tokens[$nextToken]["scope_closer"]) === false)
		    {
			$nextToken = $phpcsFile->findNext(T_WHITESPACE, ($nextToken + 1), null, true);
			if ($tokens[$nextToken]["code"] !== T_IF || isset($tokens[$nextToken]["scope_closer"]) === false)
			    {
				// Not an ELSE IF or is an inline ELSE IF.
				$result = false;
			    }
		    }

		return $result;
	    } //end _isElseIf()


	/**
	 * Check closing comment
	 *
	 * @param File  $phpcsFile      The file being scanned.
	 * @param array $tokens         All tokens
	 * @param int   $stackPtr       The position of the current token in the stack passed in $tokens.
	 * @param array $startCondition Start condition
	 * @param array $startBrace     Start brace
	 * @param array $endBrace       End brace
	 *
	 * @return void
	 *
	 * @untranslatable //end
	 * @untranslatable \"%s\"
	 * @untranslatable Missing
	 * @untranslatable SpacingBefore
	 * @untranslatable Invalid
	 */

	private function _checkClosingComment(File &$phpcsFile, array &$tokens, $stackPtr, array $startCondition, array $startBrace, array $endBrace)
	    {
		$lineDifference = ($endBrace["line"] - $startBrace["line"]);

		$expected = "//end " . $startCondition["content"];
		$comment  = $phpcsFile->findNext(array(T_COMMENT), $stackPtr, null, false);

		if (($comment === false) || ($tokens[$comment]["line"] !== $endBrace["line"]))
		    {
			if ($lineDifference >= $this->lineLimit)
			    {
				$error = _("End comment for long condition not found; expected") . " \"%s\"";
				$data  = array($expected);
				$phpcsFile->addError($error, $stackPtr, "Missing", $data);
			    }
		    }
		else
		    {
			if (($comment - $stackPtr) !== 1)
			    {
				$error = _("Space found before closing comment; expected") . " \"%s\"";
				$data  = array($expected);
				$phpcsFile->addError($error, $stackPtr, "SpacingBefore", $data);
			    }

			if (trim($tokens[$comment]["content"]) !== $expected)
			    {
				$found = trim($tokens[$comment]["content"]);
				$error = _("Incorrect closing comment; expected") . " \"%s\" " . _("but found") . " \"%s\"";
				$data  = array(
					  $expected,
					  $found,
					 );
				$phpcsFile->addError($error, $stackPtr, "Invalid", $data);
			    }
		    } //end if
	    } //end _checkClosingComment()


    } //end class

?>
