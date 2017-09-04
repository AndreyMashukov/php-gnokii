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
 * MultiLineConditionSniff.
 *
 * Ensure multi-line IF conditions are defined correctly.
 *
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2006-2012 Squiz Pty Ltd (ABN 77 084 670 600)
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 * @version   SVN: $Date: 2016-08-16 23:39:01 +0900 (Tue, 16 Aug 2016) $ $Revision: 26 $
 * @link      $HeadURL: https://open.logics.net.au/buildtools/codesniffer/tags/0.1.3/src/CodeSniffer/Standards/PEAR/Sniffs/ControlStructures/MultiLineConditionSniff.php $
 */

class MultiLineConditionSniff implements Sniff
    {

	/**
	 * Returns an array of tokens this test wants to listen for.
	 *
	 * @return array
	 */

	public function register()
	    {
		return array(T_IF);
	    } //end register()


	/**
	 * Processes this test, when one of its tokens is encountered.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $stackPtr  The position of the current token in the stack passed in $tokens.
	 *
	 * @return void
	 *
	 * @internalconst T_OPEN_CURLY_BRACKET T_OPEN_CURLY_BRACKET token
	 * @internalconst T_COLON              T_COLON token
	 *
	 * @untranslatable SpaceBeforeOpenBrace
	 * @untranslatable NewlineBeforeOpenBrace
	 * @untranslatable %s
	 * @untranslatable NoSpaceBeforeOpenBrace
	 */

	public function process(File $phpcsFile, $stackPtr)
	    {
		$tokens = &$phpcsFile->tokens;

		// We need to work out how far indented the if statement
		// itself is, so we can work out how far to indent conditions.
		$statementIndent = 0;
		for ($i = ($stackPtr - 1); $i >= 0; $i--)
		    {
			if ($tokens[$i]["line"] !== $tokens[$stackPtr]["line"])
			    {
				$i++;
				break;
			    }
		    }

		if ($i >= 0 && $tokens[$i]["code"] === T_WHITESPACE)
		    {
			$statementIndent = strlen($tokens[$i]["content"]);
		    }

		$this->_checkIndentation($phpcsFile, $stackPtr, $tokens, $statementIndent, $closeBracket);

		// From here on, we are checking the spacing of the opening and closing
		// braces. If this IF statement does not use braces, we end here.
		if (isset($tokens[$stackPtr]["scope_opener"]) === true)
		    {
			// The opening brace needs to be one space away from the closing parenthesis.
			if ($tokens[($closeBracket + 1)]["code"] !== T_WHITESPACE)
			    {
				$length = 0;
			    }
			else if ($tokens[($closeBracket + 1)]["content"] === $phpcsFile->eolChar)
			    {
				$length = -1;
			    }
			else
			    {
				$length = strlen($tokens[($closeBracket + 1)]["content"]);
			    }

			if ($length !== 1)
			    {
				$data = array($length);
				$code = "SpaceBeforeOpenBrace";

				$error = _("There must be a single space between the closing parenthesis and the opening brace of a multi-line IF statement; found ");
				if ($length === -1)
				    {
					$error .= _("newline");
					$code   = "NewlineBeforeOpenBrace";
				    }
				else
				    {
					$error .= "%s " . _("spaces");
				    }

				$phpcsFile->addError($error, ($closeBracket + 1), $code, $data);
			    }

			// And just in case they do something funny before the brace...
			$next = $phpcsFile->findNext(T_WHITESPACE, ($closeBracket + 1), null, true);
			if ($next !== false && $tokens[$next]["code"] !== T_OPEN_CURLY_BRACKET && $tokens[$next]["code"] !== T_COLON)
			    {
				$error = _("There must be a single space between the closing parenthesis and the opening brace of a multi-line IF statement");
				$phpcsFile->addError($error, $next, "NoSpaceBeforeOpenBrace");
			    }
		    } //end if
	    } //end process()


	/**
	 * Check indentation
	 *
	 * @param File  $phpcsFile       The file being scanned.
	 * @param int   $stackPtr        The position of the current token in the stack passed in $tokens.
	 * @param array $tokens          All tokens
	 * @param int   $statementIndent Statement indent
	 * @param int   $closeBracket    Close bracket
	 *
	 * @return void
	 *
	 * @internalconst T_OPEN_PARENTHESIS T_OPEN_PARENTHESIS token
	 *
	 * @untranslatable CloseBracketNewLine
	 * @untranslatable %s
	 * @untranslatable Alignment
	 * @untranslatable StartWithBoolean
	 */

	private function _checkIndentation(File &$phpcsFile, $stackPtr, array &$tokens, $statementIndent, &$closeBracket)
	    {
		// Each line between the parenthesis should be indented 4 spaces
		// and start with an operator, unless the line is inside a
		// function call, in which case it is ignored.
		$openBracket  = $tokens[$stackPtr]["parenthesis_opener"];
		$closeBracket = $tokens[$stackPtr]["parenthesis_closer"];
		$lastLine     = $tokens[$openBracket]["line"];
		for ($i = ($openBracket + 1); $i < $closeBracket; $i++)
		    {
			if ($tokens[$i]["line"] !== $lastLine)
			    {
				if ($tokens[$i]["line"] === $tokens[$closeBracket]["line"])
				    {
					$next = $phpcsFile->findNext(T_WHITESPACE, $i, null, true);
					if ($next !== $closeBracket)
					    {
						// Closing bracket is on the same line as a condition.
						$error = _("Closing parenthesis of a multi-line IF statement must be on a new line");
						$phpcsFile->addError($error, $i, "CloseBracketNewLine");
						$expectedIndent = ($statementIndent + 4);
					    }
					else
					    {
						// Closing brace needs to be indented to the same level
						// as the function.
						$expectedIndent = $statementIndent;
					    }
				    }
				else
				    {
					$expectedIndent = ($statementIndent + 4);
				    }

				// We changed lines, so this should be a whitespace indent token.
				$foundIndent = ($tokens[$i]["code"] !== T_WHITESPACE) ? 0 : strlen($tokens[$i]["content"]);

				if ($expectedIndent !== $foundIndent)
				    {
					$error = _("Multi-line IF statement not indented correctly; expected") . " %s " . _("spaces but found") . " %s";
					$data  = array(
						  $expectedIndent,
						  $foundIndent,
						 );
					$phpcsFile->addError($error, $i, "Alignment", $data);
				    }

				if ($tokens[$i]["line"] !== $tokens[$closeBracket]["line"])
				    {
					$next = $phpcsFile->findNext(T_WHITESPACE, $i, null, true);
					if (in_array($tokens[$next]["code"], Tokens::$booleanOperators) === false)
					    {
						$error = _("Each line in a multi-line IF statement must begin with a boolean operator");
						$phpcsFile->addError($error, $i, "StartWithBoolean");
					    }
				    }

				$lastLine = $tokens[$i]["line"];
			    } //end if

			if ($tokens[$i]["code"] === T_STRING)
			    {
				$next = $phpcsFile->findNext(T_WHITESPACE, ($i + 1), null, true);
				if ($tokens[$next]["code"] === T_OPEN_PARENTHESIS)
				    {
					// This is a function call, so skip to the end as they
					// have their own indentation rules.
					$i        = $tokens[$next]["parenthesis_closer"];
					$lastLine = $tokens[$i]["line"];
				    }
			    }
		    } //end for
	    } //end _checkIndentation()


    } //end class

?>
