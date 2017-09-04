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
 * ControlStructureSpacingSniff
 *
 * Checks that control structures have the correct spacing around brackets.
 *
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @author    Marc McIntyre <mmcintyre@squiz.net>
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2006-2012 Squiz Pty Ltd (ABN 77 084 670 600)
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 * @version   SVN: $Date: 2016-08-16 23:39:01 +0900 (Tue, 16 Aug 2016) $ $Revision: 26 $
 * @link      $HeadURL: https://open.logics.net.au/buildtools/codesniffer/tags/0.1.3/src/CodeSniffer/Standards/Squiz/Sniffs/WhiteSpace/ControlStructureSpacingSniff.php $
 *
 * @untranslatable PHP
 * @untranslatable JS
 */

class ControlStructureSpacingSniff implements Sniff
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
	 */

	public function register()
	    {
		return array(
			T_IF,
			T_WHILE,
			T_FOREACH,
			T_FOR,
			T_SWITCH,
			T_DO,
			T_ELSE,
			T_ELSEIF,
			T_TRY,
			T_CATCH,
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
	 * @internalconst T_CLOSURE T_CLOSURE token
	 *
	 * @untranslatable SpacingAfterOpen
	 * @untranslatable SpacingBeforeClose
	 * @untranslatable SpacingAfterOpenBrace
	 * @untranslatable SpaceBeforeCloseBrace
	 * @untranslatable LineAfterClose
	 * @untranslatable NoLineAfterClose
	 */

	public function process(File $phpcsFile, $stackPtr)
	    {
		$tokens = &$phpcsFile->tokens;

		if (isset($tokens[$stackPtr]["parenthesis_opener"]) === true && isset($tokens[$stackPtr]["parenthesis_closer"]) === true)
		    {
			$parenOpener = $tokens[$stackPtr]["parenthesis_opener"];
			$parenCloser = $tokens[$stackPtr]["parenthesis_closer"];
			if ($tokens[($parenOpener + 1)]["code"] === T_WHITESPACE)
			    {
				$gap   = $tokens[($parenOpener + 1)]["length"];
				$error = _("Expected 0 spaces after opening bracket; %s found");
				$data  = array($gap);
				$phpcsFile->addError($error, ($parenOpener + 1), "SpacingAfterOpenBrace", $data);
			    }

			if ($tokens[$parenOpener]["line"] === $tokens[$parenCloser]["line"] && $tokens[($parenCloser - 1)]["code"] === T_WHITESPACE)
			    {
				$gap   = $tokens[($parenCloser - 1)]["length"];
				$error = _("Expected 0 spaces before closing bracket; %s found");
				$data  = array($gap);
				$phpcsFile->addError($error, ($parenCloser - 1), "SpaceBeforeCloseBrace", $data);
			    }
		    } //end if

		if (isset($tokens[$stackPtr]["scope_closer"]) === false)
		    {
			return;
		    }

		$scopeOpener = $tokens[$stackPtr]["scope_opener"];
		$scopeCloser = $tokens[$stackPtr]["scope_closer"];

		for ($firstContent = ($scopeOpener + 1); $firstContent < $phpcsFile->numTokens; $firstContent++)
		    {
			if ($tokens[$firstContent]["code"] !== T_WHITESPACE)
			    {
				break;
			    }
		    } //end for

		if ($tokens[$firstContent]["line"] >= ($tokens[$scopeOpener]["line"] + 2))
		    {
			$error = _("Blank line found at start of control structure");
			$phpcsFile->addError($error, $scopeOpener, "SpacingAfterOpen");
		    }

		if ($firstContent !== $scopeCloser)
		    {
			$lastContent = $phpcsFile->findPrevious(T_WHITESPACE, ($scopeCloser - 1), null, true);

			if ($tokens[$lastContent]["line"] <= ($tokens[$scopeCloser]["line"] - 2))
			    {
				$errorToken = $scopeCloser;
				for ($i = ($scopeCloser - 1); $i > $lastContent; $i--)
				    {
					if ($tokens[$i]["line"] < $tokens[$scopeCloser]["line"])
					    {
						$errorToken = $i;
						break;
					    }
				    }

				$error = _("Blank line found at end of control structure");
				$phpcsFile->addError($error, $errorToken, "SpacingBeforeClose");
			    } //end if
		    } //end if

		$trailingContent = $phpcsFile->findNext(T_WHITESPACE, ($scopeCloser + 1), null, true);

		if ($tokens[$trailingContent]["code"] === T_COMMENT)
		    {
			// Special exception for code where the comment about an ELSE or ELSEIF is written between the control structures.
			$nextCode = $phpcsFile->findNext(Tokens::$emptyTokens, ($scopeCloser + 1), null, true);

			if ($tokens[$nextCode]["code"] === T_ELSE || $tokens[$nextCode]["code"] === T_ELSEIF)
			    {
				$trailingContent = $nextCode;
			    }
		    } //end if

		if ($tokens[$trailingContent]["code"] === T_ELSE)
		    {
			if ($tokens[$stackPtr]["code"] === T_IF)
			    {
				// IF with ELSE.
				return;
			    }
		    }

		if ($tokens[$trailingContent]["code"] === T_WHILE && $tokens[$stackPtr]["code"] === T_DO)
		    {
			// DO with WHILE.
			return;
		    }

		if ($tokens[$trailingContent]["code"] === T_CLOSE_TAG)
		    {
			// At the end of the script or embedded code.
			return;
		    }

		if (isset($tokens[$trailingContent]["scope_condition"]) === true && $tokens[$trailingContent]["scope_condition"] !== $trailingContent
		&& isset($tokens[$trailingContent]["scope_opener"]) === true && $tokens[$trailingContent]["scope_opener"] !== $trailingContent)
		    {
			// Another control structure"s closing brace.
			$owner = $tokens[$trailingContent]["scope_condition"];
			if ($tokens[$owner]["code"] === T_FUNCTION)
			    {
				// The next content is the closing brace of a function so normal function rules apply and we can ignore it.
				return;
			    }

			if ($tokens[$owner]["code"] === T_CLOSURE && ($phpcsFile->hasCondition($stackPtr, T_FUNCTION) === true
			|| $phpcsFile->hasCondition($stackPtr, T_CLOSURE) === true || isset($tokens[$stackPtr]["nested_parenthesis"]) === true))
			    {
				return;
			    }

			if ($tokens[$trailingContent]["line"] !== ($tokens[$scopeCloser]["line"] + 1))
			    {
				$error = _("Blank line found after control structure");
				$phpcsFile->addError($error, $scopeCloser, "LineAfterClose");
			    }
		    }
		else if ($tokens[$trailingContent]["code"] !== T_ELSE && $tokens[$trailingContent]["code"] !== T_ELSEIF
		&& $tokens[$trailingContent]["code"] !== T_CATCH && $tokens[$trailingContent]["code"] !== T_FINALLY
		&& $tokens[$trailingContent]["line"] === ($tokens[$scopeCloser]["line"] + 1))
		    {
			$error = _("No blank line found after control structure");
			$phpcsFile->addError($error, $scopeCloser, "NoLineAfterClose");
		    } //end if
	    } //end process()


	/**
	 * Check trailing content
	 *
	 * @param File  $phpcsFile   The file being scanned.
	 * @param int   $stackPtr    The position of the current token in the stack passed in $tokens.
	 * @param array $tokens      All tokens
	 * @param int   $scopeCloser Scope closer position
	 *
	 * @return void
	 *
	 * @internalconst T_CLOSE_CURLY_BRACKET T_CLOSE_CURLY_BRACKET token
	 *
	 * @untranslatable //end
	 * @untranslatable LineAfterClose
	 * @untranslatable NoLineAfterClose
	 */

	private function _checkTrailingContent(File &$phpcsFile, $stackPtr, array &$tokens, $scopeCloser)
	    {
		$trailingContent = $phpcsFile->findNext(T_WHITESPACE, ($scopeCloser + 1), null, true);

		// IF with ELSE.
		if ($tokens[$trailingContent]["code"] !== T_ELSE || $tokens[$stackPtr]["code"] !== T_IF)
		    {
			if ($tokens[$trailingContent]["code"] === T_COMMENT &&
			    $tokens[$trailingContent]["line"] === $tokens[$scopeCloser]["line"] &&
			    substr($tokens[$trailingContent]["content"], 0, 5) === "//end")
			    {
				// There is an end comment, so we have to get the next piece of content.
				$trailingContent = $phpcsFile->findNext(T_WHITESPACE, ($trailingContent + 1), null, true);
			    }

			$break = false;
			if ($tokens[$trailingContent]["code"] === T_BREAK)
			    {
				// If this BREAK is closing a CASE, we don't need the
				// blank line after this control structure.
				if (isset($tokens[$trailingContent]["scope_condition"]) === true)
				    {
					$condition = $tokens[$trailingContent]["scope_condition"];
					if ($tokens[$condition]["code"] === T_CASE || $tokens[$condition]["code"] === T_DEFAULT)
					    {
						$break = true;
					    }
				    }
			    }

			// At the end of the script or embedded code.
			if ($break === false && $tokens[$trailingContent]["code"] !== T_CLOSE_TAG)
			    {
				if ($tokens[$trailingContent]["code"] === T_CLOSE_CURLY_BRACKET)
				    {
					// Another control structure's closing brace.
					if ((isset($tokens[$trailingContent]["scope_condition"]) === false ||
					     $tokens[$tokens[$trailingContent]["scope_condition"]]["code"] !== T_FUNCTION) &&
					    ($tokens[$trailingContent]["line"] !== ($tokens[$scopeCloser]["line"] + 1)))
					    {
						$error = _("Blank line found after control structure");
						$phpcsFile->addError($error, $scopeCloser, "LineAfterClose");
					    }
				    }
				else
				    {
					if ($tokens[$trailingContent]["line"] === ($tokens[$scopeCloser]["line"] + 1))
					    {
						$error = _("No blank line found after control structure");
						$phpcsFile->addError($error, $scopeCloser, "NoLineAfterClose");
					    }
				    } //end if
			    } //end if
		    } //end if
	    } //end _checkTrailingContent()


    } //end class

?>
