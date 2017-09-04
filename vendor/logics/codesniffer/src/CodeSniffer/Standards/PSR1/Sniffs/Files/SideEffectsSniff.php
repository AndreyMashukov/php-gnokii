<?php

/**
 * SideEffectsSniff
 *
 * PHP version 5
 *
 * @package Logics\BuildTools\CodeSniffer\PSR1
 */

namespace Logics\BuildTools\CodeSniffer\PSR1;

use \Logics\BuildTools\CodeSniffer\File;
use \Logics\BuildTools\CodeSniffer\Sniff;
use \Logics\BuildTools\CodeSniffer\Tokens;

/**
 * SideEffectsSniff
 *
 * Ensures a file declare new symbols and causes no other side effects, or executes
 * logic with side effects, but not both.
 *
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2006-2012 Squiz Pty Ltd (ABN 77 084 670 600)
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 * @version   SVN: $Date: 2016-08-16 23:39:01 +0900 (Tue, 16 Aug 2016) $ $Revision: 26 $
 * @link      $HeadURL: https://open.logics.net.au/buildtools/codesniffer/tags/0.1.3/src/CodeSniffer/Standards/PSR1/Sniffs/Files/SideEffectsSniff.php $
 */

class SideEffectsSniff implements Sniff
    {

	/**
	 * Returns an array of tokens this test wants to listen for.
	 *
	 * @return array
	 */

	public function register()
	    {
		return array(T_OPEN_TAG);
	    } //end register()


	/**
	 * Processes this sniff, when one of its tokens is encountered.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $stackPtr  The position of the current token in the token stack.
	 *
	 * @return void
	 *
	 * @untranslatable FoundWithSymbols
	 * @untranslatable %s
	 * @untranslatable %s.
	 */

	public function process(File $phpcsFile, $stackPtr)
	    {
		// We are only interested if this is the first open tag.
		if ($stackPtr === 0 || $phpcsFile->findPrevious(T_OPEN_TAG, ($stackPtr - 1)) === false)
		    {
			$tokens = &$phpcsFile->tokens;
			$result = $this->_searchForConflict($phpcsFile, 0, ($phpcsFile->numTokens - 1), $tokens);

			if ($result["symbol"] !== null && $result["effect"] !== null)
			    {
				$error = _("A file should declare new symbols (classes, functions, constants, etc.) and cause no other side effects, " .
					   "or it should execute logic with side effects, but should not do both. The first symbol is defined on line") .
					 " %s " . _("and the first side effect is on line") . " %s.";
				$data  = array(
					  $tokens[$result["symbol"]]["line"],
					  $tokens[$result["effect"]]["line"],
					 );
				$phpcsFile->addWarning($error, 0, "FoundWithSymbols", $data);
			    }
		    }
	    } //end process()


	/**
	 * Searches for symbol declarations and side effects.
	 *
	 * Returns the positions of both the first symbol declared and the first
	 * side effect in the file. A NULL value for either indicates nothing was
	 * found.
	 *
	 * @param File  $phpcsFile The file being scanned.
	 * @param int   $start     The token to start searching from.
	 * @param int   $end       The token to search to.
	 * @param array $tokens    The stack of tokens that make up the file.
	 *
	 * @return array
	 *
	 * @internalconst T_OPEN_CURLY_BRACKET T_OPEN_CURLY_BRACKET token
	 * @internalconst T_SEMICOLON          T_SEMICOLON token
	 *
	 * @untranslatable define
	 */

	private function _searchForConflict(File &$phpcsFile, $start, $end, array $tokens)
	    {
		$symbols = array(
			    T_CLASS,
			    T_INTERFACE,
			    T_TRAIT,
			    T_FUNCTION,
			   );

		$conditions = array(
			       T_IF,
			       T_ELSE,
			       T_ELSEIF,
			      );

		$firstSymbol = null;
		$firstEffect = null;
		for ($i = $start; $i <= $end; $i++)
		    {
			// Ignore whitespace and comments.
			// Ignore PHP tags.
			if (in_array($tokens[$i]["code"], Tokens::$emptyTokens) === false &&
			    $tokens[$i]["code"] !== T_OPEN_TAG && $tokens[$i]["code"] !== T_CLOSE_TAG)
			    {
				// Ignore entire namespace, const and use statements.
				if ($tokens[$i]["code"] === T_NAMESPACE)
				    {
					$next = $phpcsFile->findNext(array(T_SEMICOLON, T_OPEN_CURLY_BRACKET), ($i + 1));
					$next = ($next === false) ? $i++ : (($tokens[$next]["code"] === T_OPEN_CURLY_BRACKET) ? $tokens[$next]["bracket_closer"] : $next);

					$i = $next;
				    }
				else if ($tokens[$i]["code"] === T_USE || $tokens[$i]["code"] === T_CONST)
				    {
					$semicolon = $phpcsFile->findNext(T_SEMICOLON, ($i + 1));
					$i         = ($semicolon !== false) ? $semicolon : $i;
				    }
				else if (in_array($tokens[$i]["code"], Tokens::$methodPrefixes) === false)
				    {
					// Ignore function/class prefixes.
					if (in_array($tokens[$i]["code"], $symbols) === true && isset($tokens[$i]["scope_closer"]) === true)
					    {
						// Detect and skip over symbols.
						$firstSymbol = ($firstSymbol === null) ? $i : $firstSymbol;

						$i = $tokens[$i]["scope_closer"];
					    }
					else if ($tokens[$i]["code"] === T_STRING && strtolower($tokens[$i]["content"]) === "define")
					    {
						$firstSymbol = ($firstSymbol === null) ? $i : $firstSymbol;

						$i = $phpcsFile->findNext(T_SEMICOLON, ($i + 1));
					    }
					else if (in_array($tokens[$i]["code"], $conditions) === true)
					    {
						$this->_checkConditional($phpcsFile, $tokens, $i, $firstSymbol, $firstEffect);
					    }
					else
					    {
						$firstEffect = ($firstEffect === null) ? $i : $firstEffect;

						if ($firstSymbol !== null)
						    {
							// We have a conflict we have to report, so no point continuing.
							break;
						    }
					    } //end if
				    } //end if
			    } //end if
		    } //end for

		return array(
			"symbol" => $firstSymbol,
			"effect" => $firstEffect,
		       );
	    } //end _searchForConflict()


	/**
	 * Conditional statements are allowed in symbol files as long as the
	 * contents is only a symbol definition. So don't count these as effects
	 * in this case.
	 *
	 * @param File  $phpcsFile   The file being scanned
	 * @param array $tokens      The stack of tokens that make up the file
	 * @param int   $i           Current token
	 * @param int   $firstSymbol First symbol
	 * @param int   $firstEffect First effect
	 *
	 * @return void
	 */

	private function _checkConditional(File &$phpcsFile, array &$tokens, &$i, &$firstSymbol, &$firstEffect)
	    {
		// Probably an "else if", so just ignore.
		if (isset($tokens[$i]["scope_opener"]) === true)
		    {
			$result = $this->_searchForConflict($phpcsFile, ($tokens[$i]["scope_opener"] + 1), ($tokens[$i]["scope_closer"] - 1), $tokens);

			if ($result["symbol"] !== null)
			    {
				if ($firstSymbol === null)
				    {
					$firstSymbol = $result["symbol"];
				    }

				if ($result["effect"] !== null)
				    {
					// Found a conflict.
					$firstEffect = $result["effect"];
					break;
				    }
			    }

			if ($firstEffect === null)
			    {
				$firstEffect = $result["effect"];
			    }

			$i = $tokens[$i]["scope_closer"];
		    } //end if
	    } //end _checkConditional()


    } //end class

?>
