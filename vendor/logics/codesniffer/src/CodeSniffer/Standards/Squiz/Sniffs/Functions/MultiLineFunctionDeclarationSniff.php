<?php

/**
 * PHP version 5
 *
 * @package Logics\BuildTools\CodeSniffer\Squiz
 */

namespace Logics\BuildTools\CodeSniffer\Squiz;

use \Logics\BuildTools\CodeSniffer\File;
use \Logics\BuildTools\CodeSniffer\PEAR\FunctionDeclarationSniff as PEAR_FunctionDeclarationSniff;

/**
 * MultiLineFunctionDeclarationSniff
 *
 * Ensure single and multi-line function declarations are defined correctly.
 *
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2006-2012 Squiz Pty Ltd (ABN 77 084 670 600)
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 * @version   SVN: $Date: 2016-08-16 23:39:01 +0900 (Tue, 16 Aug 2016) $ $Revision: 26 $
 * @link      $HeadURL: https://open.logics.net.au/buildtools/codesniffer/tags/0.1.3/src/CodeSniffer/Standards/Squiz/Sniffs/Functions/MultiLineFunctionDeclarationSniff.php $
 */

class MultiLineFunctionDeclarationSniff extends PEAR_FunctionDeclarationSniff
    {

	/**
	 * Processes multi-line declarations.
	 *
	 * @param File  $phpcsFile The file being scanned.
	 * @param int   $stackPtr  The position of the current token in the stack passed in $tokens.
	 * @param array $tokens    The stack of tokens that make up the file.
	 *
	 * @return void
	 *
	 * @internalconst T_CLOSURE          T_CLOSURE token
	 * @internalconst T_OPEN_PARENTHESIS T_OPEN_PARENTHESIS token
	 *
	 * @untranslatable function
	 * @untranslatable use
	 */

	public function processMultiLineDeclaration(File &$phpcsFile, $stackPtr, array $tokens)
	    {
		// We do everything the parent sniff does, and a bit more.
		parent::processMultiLineDeclaration($phpcsFile, $stackPtr, $tokens);

		$openBracket = $tokens[$stackPtr]["parenthesis_opener"];
		$this->processBracket($phpcsFile, $openBracket, $tokens, "function");

		if ($tokens[$stackPtr]["code"] === T_CLOSURE)
		    {
			$use = $phpcsFile->findNext(T_USE, ($tokens[$stackPtr]["parenthesis_closer"] + 1), $tokens[$stackPtr]["scope_opener"]);
			if ($use !== false)
			    {
				$openBracket = $phpcsFile->findNext(T_OPEN_PARENTHESIS, ($use + 1), null);
				$this->processBracket($phpcsFile, $openBracket, $tokens, "use");

				// Also check spacing.
				if ($tokens[($use - 1)]["code"] === T_WHITESPACE)
				    {
					$gap = strlen($tokens[($use - 1)]["content"]);
				    }
				else
				    {
					$gap = 0;
				    }
			    }
		    }
	    } //end processMultiLineDeclaration()


	/**
	 * Processes the contents of a single set of brackets.
	 *
	 * @param File   $phpcsFile   The file being scanned.
	 * @param int    $openBracket The position of the open bracket in the stack passed in $tokens.
	 * @param array  $tokens      The stack of tokens that make up the file.
	 * @param string $type        The type of the token the brackets belong to (function or use).
	 *
	 * @return void
	 *
	 * @internalconst T_COMMA T_COMMA token
	 *
	 * @untranslatable function
	 * @untranslatable use
	 * @untranslatable FirstParamSpacing
	 * @untranslatable OneParamPerLine
	 * @untranslatable ContentAfterComma
	 */

	public function processBracket(File &$phpcsFile, $openBracket, array $tokens, $type = "function")
	    {
		$errorPrefix = "";
		if ($type === "use")
		    {
			$errorPrefix = _("Use");
		    }

		$closeBracket = $tokens[$openBracket]["parenthesis_closer"];

		// The open bracket should be the last thing on the line.
		if ($tokens[$openBracket]["line"] !== $tokens[$closeBracket]["line"])
		    {
			$next = $phpcsFile->findNext(T_WHITESPACE, ($openBracket + 1), null, true);
			if ($tokens[$next]["line"] !== ($tokens[$openBracket]["line"] + 1))
			    {
				$error = _("The first parameter of a multi-line") . " " . $type . " " . _("declaration must be on the line after the opening bracket");
				$phpcsFile->addError($error, $next, $errorPrefix . "FirstParamSpacing");
			    }
		    }

		// Each line between the brackets should contain a single parameter.
		$lastCommaLine = null;
		for ($i = ($openBracket + 1); $i < $closeBracket; $i++)
		    {
			// Skip brackets, like arrays, as they can contain commas.
			if (isset($tokens[$i]["parenthesis_opener"]) === true)
			    {
				$i = $tokens[$i]["parenthesis_closer"];
			    }
			else if ($tokens[$i]["code"] === T_COMMA)
			    {
				if ($lastCommaLine !== null && $lastCommaLine === $tokens[$i]["line"])
				    {
					$error = _("Multi-line") . " " . $type . " " . _("declarations must define one parameter per line");
					$phpcsFile->addError($error, $i, $errorPrefix . "OneParamPerLine");
				    }
				else
				    {
					// Comma must be the last thing on the line.
					$next = $phpcsFile->findNext(T_WHITESPACE, ($i + 1), null, true);
					if ($tokens[$next]["line"] !== ($tokens[$i]["line"] + 1))
					    {
						$error = _("Commas in multi-line") . " " . $type . " " . _("declarations must be the last content on a line");
						$phpcsFile->addError($error, $next, $errorPrefix . "ContentAfterComma");
					    }
				    }

				$lastCommaLine = $tokens[$i]["line"];
			    } //end if
		    } //end for
	    } //end processBracket()


    } //end class

?>
