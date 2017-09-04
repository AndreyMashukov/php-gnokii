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
 * OpeningFunctionBraceSniff
 *
 * Checks that the opening brace of a function is on the line after the
 * function declaration.
 *
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @author    Marc McIntyre <mmcintyre@squiz.net>
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2006-2012 Squiz Pty Ltd (ABN 77 084 670 600)
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   http://matrix.squiz.net/developer/tools/php_cs/licence BSD Licence
 * @version   SVN: $Date: 2016-08-16 23:39:01 +0900 (Tue, 16 Aug 2016) $ $Revision: 26 $
 * @link      $HeadURL: https://open.logics.net.au/buildtools/codesniffer/tags/0.1.3/src/CodeSniffer/Standards/Beauty/Sniffs/Functions/OpeningFunctionBraceSniff.php $
 */

class OpeningFunctionBraceSniff implements Sniff
    {

	/**
	 * Registers the tokens that this sniff wants to listen for.
	 *
	 * @return array
	 */

	public function register()
	    {
		return array(T_FUNCTION);
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

		if (isset($tokens[$stackPtr]["scope_opener"]) === true)
		    {
			$openingBrace = $tokens[$stackPtr]["scope_opener"];

			// The end of the function occurs at the end of the argument list. Its
			// like this because some people like to break long function declarations
			// over multiple lines.
			$functionLine = $tokens[$tokens[$stackPtr]["parenthesis_closer"]]["line"];
			$braceLine    = $tokens[$openingBrace]["line"];

			$lineDifference = ($braceLine - $functionLine);

			if ($lineDifference === 0)
			    {
				$error = _("Opening brace should be on a new line");
				$phpcsFile->addError($error, $openingBrace);
			    }
			else if ($lineDifference > 1)
			    {
				$ender = (($lineDifference - 1) !== 1) ? _("lines") : _("line");

				$error = _("Opening brace should be on the line after the declaration; found") . " " . ($lineDifference - 1) . " " . _("blank") . " " . $ender;
				$phpcsFile->addError($error, $openingBrace);
			    }
			else
			    {
				// We need to actually find the first piece of content on this line,
				// as if this is a method with tokens before it (public, static etc)
				// or an if with an else before it, then we need to start the scope
				// checking from there, rather than the current token.
				$lineStart = $phpcsFile->findPrevious(array(T_WHITESPACE), ($stackPtr - 1), null, false);
				while ($lineStart !== false)
				    {
					if (strpos($tokens[$lineStart]["content"], $phpcsFile->eolChar) !== false)
					    {
						break;
					    }

					$lineStart = $phpcsFile->findPrevious(array(T_WHITESPACE), ($lineStart - 1), null, false);
				    }

				// We found a new line, now go forward and find the first non-whitespace
				// token.
				$lineStart = $phpcsFile->findNext(array(T_WHITESPACE), $lineStart, null, true);

				// The opening brace is on the correct line, now it needs to be
				// checked to be correctly indented.
				$startColumn = $tokens[$lineStart]["column"];
				$braceIndent = $tokens[$openingBrace]["column"];

				if ($braceIndent !== ($startColumn + 4))
				    {
					$error = _("Opening brace indented incorrectly; expected") . " " . ($startColumn + 3) . " " .
						 _("spaces, found") . " " . ($braceIndent - 1);
					$phpcsFile->addError($error, $openingBrace);
				    }
			    } //end if
		    } //end if
	    } //end process()


    } //end class

?>
