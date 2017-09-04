<?php

/**
 * PHP version 5
 *
 * @package Logics\BuildTools\CodeSniffer\PEAR
 */

namespace Logics\BuildTools\CodeSniffer\PEAR;

use \Logics\BuildTools\CodeSniffer\File;
use \Logics\BuildTools\CodeSniffer\Generic\OpeningFunctionBraceBsdAllmanSniff;
use \Logics\BuildTools\CodeSniffer\Generic\OpeningFunctionBraceKernighanRitchieSniff;
use \Logics\BuildTools\CodeSniffer\Sniff;

/**
 * FunctionDeclarationSniff.
 *
 * Ensure single and multi-line function declarations are defined correctly.
 *
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2006-2012 Squiz Pty Ltd (ABN 77 084 670 600)
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 * @version   SVN: $Date: 2016-08-16 23:39:01 +0900 (Tue, 16 Aug 2016) $ $Revision: 26 $
 * @link      $HeadURL: https://open.logics.net.au/buildtools/codesniffer/tags/0.1.3/src/CodeSniffer/Standards/PEAR/Sniffs/Functions/FunctionDeclarationSniff.php $
 */

class FunctionDeclarationSniff implements Sniff
    {

	/**
	 * Returns an array of tokens this test wants to listen for.
	 *
	 * @return array
	 *
	 * @internalconst T_CLOSURE T_CLOSURE token
	 */

	public function register()
	    {
		return array(
			T_FUNCTION,
			T_CLOSURE,
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
	 * @internalconst T_CLOSURE          T_CLOSURE token
	 * @internalconst T_OPEN_PARENTHESIS T_OPEN_PARENTHESIS token
	 *
	 * @untranslatable %s
	 * @untranslatable SpaceAfterFunction
	 */

	public function process(File $phpcsFile, $stackPtr)
	    {
		$tokens = &$phpcsFile->tokens;

		$spaces = 0;
		if ($tokens[($stackPtr + 1)]["code"] === T_WHITESPACE)
		    {
			$spaces = strlen($tokens[($stackPtr + 1)]["content"]);
		    }

		if ($spaces !== 1)
		    {
			$error = _("Expected 1 space after FUNCTION keyword;") . " %s " . _("found");
			$data  = array($spaces);
			$phpcsFile->addError($error, $stackPtr, "SpaceAfterFunction", $data);
		    }

		$openBracket  = $tokens[$stackPtr]["parenthesis_opener"];
		$closeBracket = $tokens[$stackPtr]["parenthesis_closer"];

		$this->_checkClosure($phpcsFile, $tokens, $stackPtr, $closeBracket, $use);

		// Check if this is a single line or multi-line declaration.
		$singleLine = true;
		if ($tokens[$openBracket]["line"] === $tokens[$closeBracket]["line"])
		    {
			// Closures may use the USE keyword and so be multi-line in this way.
			if ($tokens[$stackPtr]["code"] === T_CLOSURE)
			    {
				if ($use !== false)
				    {
					// If the opening and closing parenthesis of the use statement
					// are also on the same line, this is a single line declaration.
					$open  = $phpcsFile->findNext(T_OPEN_PARENTHESIS, ($use + 1));
					$close = $tokens[$open]["parenthesis_closer"];
					if ($tokens[$open]["line"] !== $tokens[$close]["line"])
					    {
						$singleLine = false;
					    }
				    }
			    }
		    }
		else
		    {
			$singleLine = false;
		    } //end if

		if ($singleLine === true)
		    {
			$this->processSingleLineDeclaration($phpcsFile, $stackPtr, $tokens);
		    }
		else
		    {
			$this->processMultiLineDeclaration($phpcsFile, $stackPtr, $tokens);
		    }
	    } //end process()


	/**
	 * Must be one space before and after USE keyword for closures
	 *
	 * @param File  $phpcsFile    The file being scanned.
	 * @param array $tokens       The stack of tokens that make up the file.
	 * @param int   $stackPtr     Current token position
	 * @param int   $closeBracket Close bracket position
	 * @param int   $use          USE position
	 *
	 * @return void
	 *
	 * @internalconst T_CLOSURE T_CLOSURE token
	 *
	 * @untranslatable SpaceAfterUse
	 * @untranslatable %s
	 * @untranslatable SpaceBeforeUse
	 */

	private function _checkClosure(File &$phpcsFile, array &$tokens, $stackPtr, $closeBracket, &$use)
	    {
		if ($tokens[$stackPtr]["code"] === T_CLOSURE)
		    {
			$use = $phpcsFile->findNext(T_USE, ($closeBracket + 1), $tokens[$stackPtr]["scope_opener"]);
			if ($use !== false)
			    {
				if ($tokens[($use + 1)]["code"] !== T_WHITESPACE)
				    {
					$length = 0;
				    }
				else if ($tokens[($use + 1)]["content"] === "\t")
				    {
					$length = '\t';
				    }
				else
				    {
					$length = strlen($tokens[($use + 1)]["content"]);
				    }

				if ($length !== 1)
				    {
					$error = _("Expected 1 space after USE keyword; found") . " %s";
					$data  = array($length);
					$phpcsFile->addError($error, $use, "SpaceAfterUse", $data);
				    }

				if ($tokens[($use - 1)]["code"] !== T_WHITESPACE)
				    {
					$length = 0;
				    }
				else if ($tokens[($use - 1)]["content"] === "\t")
				    {
					$length = '\t';
				    }
				else
				    {
					$length = strlen($tokens[($use - 1)]["content"]);
				    }

				if ($length !== 1)
				    {
					$error = _("Expected 1 space before USE keyword; found") . " %s";
					$data  = array($length);
					$phpcsFile->addError($error, $use, "SpaceBeforeUse", $data);
				    }
			    } //end if
		    } //end if
	    } //end _checkClosure()


	/**
	 * Processes single-line declarations.
	 *
	 * Just uses the Generic BSD-Allman brace sniff.
	 *
	 * @param File  $phpcsFile The file being scanned.
	 * @param int   $stackPtr  The position of the current token in the stack passed in $tokens.
	 * @param array $tokens    The stack of tokens that make up the file.
	 *
	 * @return void
	 *
	 * @internalconst T_CLOSURE T_CLOSURE token
	 */

	public function processSingleLineDeclaration(File &$phpcsFile, $stackPtr, array $tokens)
	    {
		if ($tokens[$stackPtr]["code"] === T_CLOSURE)
		    {
			$sniff = new OpeningFunctionBraceKernighanRitchieSniff();
		    }
		else
		    {
			$sniff = new OpeningFunctionBraceBsdAllmanSniff();
		    }

		$sniff->process($phpcsFile, $stackPtr);
	    } //end processSingleLineDeclaration()


	/**
	 * Processes mutli-line declarations.
	 *
	 * @param File  $phpcsFile The file being scanned.
	 * @param int   $stackPtr  The position of the current token in the stack passed in $tokens.
	 * @param array $tokens    The stack of tokens that make up the file.
	 *
	 * @return void
	 *
	 * @internalconst T_CLOSURE            T_CLOSURE token
	 * @internalconst T_OPEN_PARENTHESIS   T_OPEN_PARENTHESIS token
	 * @internalconst T_OPEN_CURLY_BRACKET T_OPEN_CURLY_BRACKET token
	 *
	 * @untranslatable CloseBracketLine
	 * @untranslatable %s
	 * @untranslatable NewlineBeforeOpenBrace
	 * @untranslatable SpaceBeforeOpenBrace
	 * @untranslatable NoSpaceBeforeOpenBrace
	 */

	public function processMultiLineDeclaration(File &$phpcsFile, $stackPtr, array $tokens)
	    {
		$functionIndent = $this->_getFunctionIndent($stackPtr, $tokens);

		// The closing parenthesis must be on a new line, even
		// when checking abstract function definitions.
		$closeBracket = $tokens[$stackPtr]["parenthesis_closer"];
		$prev         = $phpcsFile->findPrevious(T_WHITESPACE, ($closeBracket - 1), null, true);

		if ($tokens[$closeBracket]["line"] !== $tokens[$tokens[$closeBracket]["parenthesis_opener"]]["line"] &&
		    $tokens[$prev]["line"] === $tokens[$closeBracket]["line"])
		    {
			$error = _("The closing parenthesis of a multi-line function declaration must be on a new line");
			$phpcsFile->addError($error, $closeBracket, "CloseBracketLine");
		    }

		// If this is a closure and is using a USE statement, the closing
		// parenthesis we need to look at from now on is the closing parenthesis
		// of the USE statement.
		if ($tokens[$stackPtr]["code"] === T_CLOSURE)
		    {
			$use = $phpcsFile->findNext(T_USE, ($closeBracket + 1), $tokens[$stackPtr]["scope_opener"]);
			if ($use !== false)
			    {
				$open         = $phpcsFile->findNext(T_OPEN_PARENTHESIS, ($use + 1));
				$closeBracket = $tokens[$open]["parenthesis_closer"];

				$prev = $phpcsFile->findPrevious(T_WHITESPACE, ($closeBracket - 1), null, true);

				if ($tokens[$closeBracket]["line"] !== $tokens[$tokens[$closeBracket]["parenthesis_opener"]]["line"] &&
				    $tokens[$prev]["line"] === $tokens[$closeBracket]["line"])
				    {
					$error = _("The closing parenthesis of a multi-line use declaration must be on a new line");
					$phpcsFile->addError($error, $closeBracket, "CloseBracketLine");
				    }
			    } //end if
		    } //end if

		$this->_checkIndentation($phpcsFile, $tokens, $stackPtr, $closeBracket, $functionIndent);

		if (isset($tokens[$stackPtr]["scope_opener"]) === true)
		    {
			// The openning brace needs to be one space away
			// from the closing parenthesis.
			$next = $tokens[($closeBracket + 1)];
			if ($next["code"] !== T_WHITESPACE)
			    {
				$length = 0;
			    }
			else if ($next["content"] === $phpcsFile->eolChar)
			    {
				$length = -1;
			    }
			else
			    {
				$length = strlen($next["content"]);
			    }

			if ($length !== 1)
			    {
				$error  = _("There must be a single space between the closing parenthesis and the opening brace of a multi-line function declaration; found ");
				$error .= ($length === -1) ? _("newline") : "%s " . _("spaces");
				$code   = ($length === -1) ? "NewlineBeforeOpenBrace" : "SpaceBeforeOpenBrace";
				$data   = array($length);

				$phpcsFile->addError($error, ($closeBracket + 1), $code, $data);
			    }
			else
			    {
				// And just in case they do something funny before the brace...
				$next = $phpcsFile->findNext(T_WHITESPACE, ($closeBracket + 1), null, true);

				if ($next !== false && $tokens[$next]["code"] !== T_OPEN_CURLY_BRACKET)
				    {
					$error = _("There must be a single space between the closing parenthesis and the opening brace of a multi-line function declaration");
					$phpcsFile->addError($error, $next, "NoSpaceBeforeOpenBrace");
				    }
			    } //end if
		    } //end if
	    } //end processMultiLineDeclaration()


	/**
	 * We need to work out how far indented the function
	 * call itself is, so we can work out how far to
	 * indent the arguments.
	 *
	 * @param int   $stackPtr Position of current token
	 * @param array $tokens   All tokens
	 *
	 * @return int Function indent
	 */

	private function _getFunctionIndent($stackPtr, array &$tokens)
	    {
		$functionIndent = 0;
		for ($i = ($stackPtr - 1); $i >= 0; $i--)
		    {
			if ($tokens[$i]["line"] !== $tokens[$stackPtr]["line"])
			    {
				$i++;
				break;
			    }
		    }

		if ($tokens[$i]["code"] === T_WHITESPACE)
		    {
			$functionIndent = strlen($tokens[$i]["content"]);
		    }

		return $functionIndent;
	    } //end _getFunctionIndent()


	/**
	 * Each line between the parenthesis should be indented 4 spaces
	 *
	 * @param File  $phpcsFile      The file being scanned.
	 * @param array $tokens         The stack of tokens that make up the file.
	 * @param int   $stackPtr       Current token position
	 * @param int   $closeBracket   Close bracket position
	 * @param int   $functionIndent Function indent
	 *
	 * @return void
	 *
	 * @untranslatable %s
	 * @untranslatable Indent
	 */

	private function _checkIndentation(File &$phpcsFile, array &$tokens, $stackPtr, $closeBracket, $functionIndent)
	    {
		$openBracket = $tokens[$stackPtr]["parenthesis_opener"];
		$lastLine    = $tokens[$openBracket]["line"];
		for ($i = ($openBracket + 1); $i < $closeBracket; $i++)
		    {
			if ($tokens[$i]["line"] !== $lastLine)
			    {
				if ($i === $tokens[$stackPtr]["parenthesis_closer"] ||
				    ($tokens[$i]["code"] === T_WHITESPACE && ($i + 1) === $tokens[$stackPtr]["parenthesis_closer"]))
				    {
					// Closing braces need to be indented to the same level
					// as the function.
					$expectedIndent = $functionIndent;
				    }
				else
				    {
					$expectedIndent = ($functionIndent + 4);
				    }

				// We changed lines, so this should be a whitespace indent token.
				$foundIndent = ($tokens[$i]["code"] !== T_WHITESPACE) ? 0 : strlen($tokens[$i]["content"]);

				if ($expectedIndent !== $foundIndent)
				    {
					$error = _("Multi-line function declaration not indented correctly; expected") . " %s " . _("spaces but found") . " %s";
					$data  = array(
						  $expectedIndent,
						  $foundIndent,
						 );
					$phpcsFile->addError($error, $i, "Indent", $data);
				    }

				$lastLine = $tokens[$i]["line"];
			    } //end if

			if ($tokens[$i]["code"] === T_ARRAY)
			    {
				// Skip arrays as they have their own indentation rules.
				$i        = $tokens[$i]["parenthesis_closer"];
				$lastLine = $tokens[$i]["line"];
			    }
		    } //end for
	    } //end _checkIndentation()


    } //end class

?>
