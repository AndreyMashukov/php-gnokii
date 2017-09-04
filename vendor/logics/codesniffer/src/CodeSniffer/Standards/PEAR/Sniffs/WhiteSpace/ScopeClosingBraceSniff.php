<?php

/**
 * PHP version 5
 *
 * @package Logics\BuildTools\CodeSniffer\PEAR
 */

namespace Logics\BuildTools\CodeSniffer\PEAR;

use \Logics\BuildTools\CodeSniffer\File;
use \Logics\BuildTools\CodeSniffer\Sniff;
use \Logics\BuildTools\CodeSniffer\Tokens;

/**
 * ScopeClosingBraceSniff
 *
 * Checks that the closing braces of scopes are aligned correctly.
 *
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @author    Marc McIntyre <mmcintyre@squiz.net>
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2006-2012 Squiz Pty Ltd (ABN 77 084 670 600)
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 * @version   SVN: $Date: 2016-08-16 23:39:01 +0900 (Tue, 16 Aug 2016) $ $Revision: 26 $
 * @link      $HeadURL: https://open.logics.net.au/buildtools/codesniffer/tags/0.1.3/src/CodeSniffer/Standards/PEAR/Sniffs/WhiteSpace/ScopeClosingBraceSniff.php $
 */

class ScopeClosingBraceSniff implements Sniff
    {

	/**
	 * The number of spaces code should be indented.
	 *
	 * @var int
	 */
	public $indent = 4;

	/**
	 * Returns an array of tokens this test wants to listen for.
	 *
	 * @return array
	 */

	public function register()
	    {
		return Tokens::$scopeOpeners;
	    } //end register()


	/**
	 * Processes this test, when one of its tokens is encountered.
	 *
	 * @param File $phpcsFile All the tokens found in the document.
	 * @param int  $stackPtr  The position of the current token in the stack passed in $tokens.
	 *
	 * @return void
	 *
	 * @untranslatable ContentBefore
	 */

	public function process(File $phpcsFile, $stackPtr)
	    {
		$tokens = &$phpcsFile->tokens;

		// If this is an inline condition (ie. there is no scope opener), then
		// return, as this is not a new scope.
		if (isset($tokens[$stackPtr]["scope_closer"]) === true)
		    {
			$scopeStart = $tokens[$stackPtr]["scope_opener"];
			$scopeEnd   = $tokens[$stackPtr]["scope_closer"];

			// If the scope closer doesn't think it belongs to this scope opener
			// then the opener is sharing its closer ith other tokens. We only
			// want to process the closer once, so skip this one.
			if ($tokens[$scopeEnd]["scope_condition"] === $stackPtr)
			    {
				// We need to actually find the first piece of content on this line,
				// because if this is a method with tokens before it (public, static etc)
				// or an if with an else before it, then we need to start the scope
				// checking from there, rather than the current token.
				$lineStart = ($stackPtr - 1);
				for ($lineStart; $lineStart > 0; $lineStart--)
				    {
					if (strpos($tokens[$lineStart]["content"], $phpcsFile->eolChar) !== false)
					    {
						break;
					    }
				    }

				// We found a new line, now go forward and find the first non-whitespace
				// token.
				$lineStart = $phpcsFile->findNext(array(T_WHITESPACE), ($lineStart + 1), null, true);

				$startColumn = $tokens[$lineStart]["column"];

				// Check that the closing brace is on it's own line.
				$lastContent = $phpcsFile->findPrevious(array(T_WHITESPACE), ($scopeEnd - 1), $scopeStart, true);

				if ($tokens[$lastContent]["line"] === $tokens[$scopeEnd]["line"])
				    {
					$error = _("Closing brace must be on a line by itself");
					$phpcsFile->addError($error, $scopeEnd, "ContentBefore");
				    }
				else
				    {
					$this->checkCloser($phpcsFile, $stackPtr, $tokens, $startColumn, $scopeEnd);
				    } //end if
			    } //end if
		    } //end if
	    } //end process()


	/**
	 * Check now that the closing brace is lined up correctly.
	 *
	 * @param File  $phpcsFile   All the tokens found in the document.
	 * @param int   $stackPtr    The position of the current token in the stack passed in $tokens.
	 * @param array $tokens      All tokens
	 * @param int   $startColumn Start column
	 * @param int   $scopeEnd    Scope end
	 *
	 * @return void
	 *
	 * @untranslatable BreakIndent
	 * @untranslatable %s
	 * @untranslatable Indent
	 */

	protected function checkCloser(File &$phpcsFile, $stackPtr, array &$tokens, $startColumn, $scopeEnd)
	    {
		$braceIndent = $tokens[$scopeEnd]["column"];
		if (in_array($tokens[$stackPtr]["code"], array(T_CASE, T_DEFAULT)) === true)
		    {
			// BREAK statements should be indented n spaces from the
			// CASE or DEFAULT statement.
			if ($braceIndent !== ($startColumn + $this->indent))
			    {
				$error = _("Case breaking statement indented incorrectly; expected") . " %s " . _("spaces, found") . " %s";
				$data  = array(
					  ($startColumn + $this->indent - 1),
					  ($braceIndent - 1),
					 );
				$phpcsFile->addError($error, $scopeEnd, "BreakIndent", $data);
			    }
		    }
		else
		    {
			if ($braceIndent !== $startColumn)
			    {
				$error = _("Closing brace indented incorrectly; expected") . " %s " . _("spaces, found") . " %s";
				$data  = array(
					  ($startColumn - 1),
					  ($braceIndent - 1),
					 );
				$phpcsFile->addError($error, $scopeEnd, "Indent", $data);
			    }
		    } //end if
	    } //end checkCloser()


    } //end class

?>
