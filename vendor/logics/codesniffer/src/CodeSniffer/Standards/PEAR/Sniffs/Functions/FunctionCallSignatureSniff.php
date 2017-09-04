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
 * FunctionCallSignatureSniff
 *
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @author    Marc McIntyre <mmcintyre@squiz.net>
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2006-2012 Squiz Pty Ltd (ABN 77 084 670 600)
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 * @version   SVN: $Date: 2016-08-16 23:39:01 +0900 (Tue, 16 Aug 2016) $ $Revision: 26 $
 * @link      $HeadURL: https://open.logics.net.au/buildtools/codesniffer/tags/0.1.3/src/CodeSniffer/Standards/PEAR/Sniffs/Functions/FunctionCallSignatureSniff.php $
 *
 * @untranslatable PHP
 * @untranslatable JS
 */

class FunctionCallSignatureSniff implements Sniff
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
	 * The number of spaces code should be indented.
	 *
	 * @var int
	 */
	public $indent = 4;

	/**
	 * If TRUE, multiple arguments can be defined per line in a multi-line call.
	 *
	 * @var bool
	 */
	public $allowMultipleArguments = true;

	/**
	 * How many spaces should follow the opening bracket.
	 *
	 * @var int
	 */
	public $requiredSpacesAfterOpen = 0;

	/**
	 * How many spaces should precede the closing bracket.
	 *
	 * @var int
	 */
	public $requiredSpacesBeforeClose = 0;

	/**
	 * Returns an array of tokens this test wants to listen for.
	 *
	 * @return array
	 */

	public function register()
	    {
		return Tokens::$functionNameTokens;
	    } //end register()


	/**
	 * Processes this test, when one of its tokens is encountered.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $stackPtr  The position of the current token in the stack passed in $tokens.
	 *
	 * @return void
	 *
	 * @internalconst T_OPEN_PARENTHESIS T_OPEN_PARENTHESIS
	 * @internalconst T_BITWISE_AND      T_BITWISE_AND token
	 * @internalconst T_SEMICOLON        T_SEMICOLON token
	 *
	 * @untranslatable SpaceBeforeOpenBracket
	 * @untranslatable SpaceAfterCloseBracket
	 */

	public function process(File $phpcsFile, $stackPtr)
	    {
		$this->requiredSpacesAfterOpen   = (int) $this->requiredSpacesAfterOpen;
		$this->requiredSpacesBeforeClose = (int) $this->requiredSpacesBeforeClose;
		$tokens = &$phpcsFile->tokens;

		// Find the next non-empty token.
		$openBracket = $phpcsFile->findNext(Tokens::$emptyTokens, ($stackPtr + 1), null, true);

		if ($tokens[$openBracket]["code"] !== T_OPEN_PARENTHESIS)
		    {
			// Not a function call.
			return;
		    }

		if (isset($tokens[$openBracket]["parenthesis_closer"]) === false)
		    {
			// Not a function call.
			return;
		    }

		// Find the previous non-empty token.
		$search   = Tokens::$emptyTokens;
		$search[] = T_BITWISE_AND;
		$previous = $phpcsFile->findPrevious($search, ($stackPtr - 1), null, true);
		if ($tokens[$previous]["code"] === T_FUNCTION)
		    {
			// It's a function definition, not a function call.
			return;
		    }

		$closeBracket = $tokens[$openBracket]["parenthesis_closer"];

		if (($stackPtr + 1) !== $openBracket)
		    {
			// Checking following construct: $value = my_function[*](...).
			$error = _("Space before opening parenthesis of function call prohibited");
			$phpcsFile->addError($error, $stackPtr, "SpaceBeforeOpenBracket");
		    }

		$next = $phpcsFile->findNext(T_WHITESPACE, ($closeBracket + 1), null, true);
		if ($tokens[$next]["code"] === T_SEMICOLON)
		    {
			if (isset(Tokens::$emptyTokens[$tokens[($closeBracket + 1)]["code"]]) === true)
			    {
				$error = _("Space after closing parenthesis of function call prohibited");
				$phpcsFile->addError($error, $closeBracket, "SpaceAfterCloseBracket");
			    }
		    }

		// Check if this is a single line or multi-line function call.
		if ($this->isMultiLineCall($phpcsFile, $stackPtr, $openBracket, $tokens) === true)
		    {
			$this->processMultiLineCall($phpcsFile, $stackPtr, $openBracket, $tokens);
		    }
		else
		    {
			$this->processSingleLineCall($phpcsFile, $stackPtr, $openBracket, $tokens);
		    }
	    } //end process()


	/**
	 * Processes single-line calls.
	 *
	 * @param File  $phpcsFile   The file being scanned.
	 * @param int   $stackPtr    The position of the current token in the stack passed in $tokens.
	 * @param int   $openBracket The position of the opening bracket in the stack passed in $tokens.
	 * @param array $tokens      The stack of tokens that make up the file.
	 *
	 * @return bool
	 */

	public function isMultiLineCall(File $phpcsFile, $stackPtr, $openBracket, array $tokens)
	    {
		unset($phpcsFile);
		unset($stackPtr);

		$closeBracket = $tokens[$openBracket]["parenthesis_closer"];
		if ($tokens[$openBracket]["line"] !== $tokens[$closeBracket]["line"])
		    {
			return true;
		    }

		return false;
	    } //end isMultiLineCall()


	/**
	 * Processes single-line calls.
	 *
	 * @param File  $phpcsFile   The file being scanned.
	 * @param int   $stackPtr    The position of the current token in the stack passed in $tokens.
	 * @param int   $openBracket The position of the opening bracket in the stack passed in $tokens.
	 * @param array $tokens      The stack of tokens that make up the file.
	 *
	 * @return void
	 *
	 * @internalconst T_END_NOWDOC T_END_NOWDOC token
	 *
	 * @untranslatable SpaceAfterOpenBracket
	 * @untranslatable newline
	 * @untranslatable %s
	 * @untranslatable ; %s
	 * @untranslatable SpaceBeforeCloseBracket
	 */

	public function processSingleLineCall(File $phpcsFile, $stackPtr, $openBracket, array $tokens)
	    {
		$closer = $tokens[$openBracket]["parenthesis_closer"];
		if ($openBracket === ($closer - 1))
		    {
			return;
		    }

		if ($this->requiredSpacesAfterOpen === 0 && $tokens[($openBracket + 1)]["code"] === T_WHITESPACE)
		    {
			// Checking following construct: $value = my_function([*]...).
			$error = _("Space after opening parenthesis of function call prohibited");
			$phpcsFile->addError($error, $stackPtr, "SpaceAfterOpenBracket");
		    }
		else if ($this->requiredSpacesAfterOpen > 0)
		    {
			$spaceAfterOpen = 0;
			if ($tokens[($openBracket + 1)]["code"] === T_WHITESPACE)
			    {
				$spaceAfterOpen = strlen($tokens[($openBracket + 1)]["content"]);
			    }

			if ($spaceAfterOpen !== $this->requiredSpacesAfterOpen)
			    {
				$error = _("Expected") . " %s " . _("spaces after opening bracket") . "; %s " . _("found");
				$data  = array(
					  $this->requiredSpacesAfterOpen,
					  $spaceAfterOpen,
					 );
				$phpcsFile->addError($error, $stackPtr, "SpaceAfterOpenBracket", $data);
			    }
		    } //end if

		// Checking following construct: $value = my_function(...[*]).
		$spaceBeforeClose = 0;
		$prev             = $phpcsFile->findPrevious(Tokens::$emptyTokens, ($closer - 1), $openBracket, true);
		if ($tokens[$prev]["code"] === T_END_HEREDOC || $tokens[$prev]["code"] === T_END_NOWDOC)
		    {
			// Need a newline after these tokens, so ignore this rule.
			return;
		    }

		if ($tokens[$prev]["line"] !== $tokens[$closer]["line"])
		    {
			$spaceBeforeClose = "newline";
		    }
		else if ($tokens[($closer - 1)]["code"] === T_WHITESPACE)
		    {
			$spaceBeforeClose = strlen($tokens[($closer - 1)]["content"]);
		    }

		if ($spaceBeforeClose !== $this->requiredSpacesBeforeClose)
		    {
			$error = _("Expected") . " %s " . _("spaces before closing bracket") . "; %s " . _("found");
			$data  = array(
				  $this->requiredSpacesBeforeClose,
				  $spaceBeforeClose,
				 );
			$phpcsFile->addError($error, $stackPtr, "SpaceBeforeCloseBracket", $data);
		    }
	    } //end processSingleLineCall()


	/**
	 * Processes multi-line calls.
	 *
	 * @param File  $phpcsFile   The file being scanned.
	 * @param int   $stackPtr    The position of the current token in the stack passed in $tokens.
	 * @param int   $openBracket The position of the opening bracket in the stack passed in $tokens.
	 * @param array $tokens      The stack of tokens that make up the file.
	 *
	 * @return void
	 *
	 * @internalconst T_DOC_COMMENT_WHITESPACE T_DOC_COMMENT_WHITESPACE token
	 * @internalconst T_CLOSURE                T_CLOSURE token
	 * @internalconst T_OPEN_SHORT_ARRAY       T_OPEN_SHORT_ARRAY token
	 * @internalconst T_DOC_COMMENT_OPEN_TAG   T_DOC_COMMENT_OPEN_TAG token
	 * @internalconst T_OPEN_PARENTHESIS       T_OPEN_PARENTHESIS token
	 * @internalconst T_OBJECT                 T_OBJECT token
	 * @internalconst T_COMMA                  T_COMMA token
	 *
	 * @untranslatable ContentAfterOpenBracket
	 * @untranslatable CloseBracketLine
	 * @untranslatable EmptyLine
	 * @untranslatable T_OBJECT_OPERATOR
	 * @untranslatable %s
	 * @untranslatable Indent
	 * @untranslatable JS
	 * @untranslatable MultipleArguments
	 */

	public function processMultiLineCall(File $phpcsFile, $stackPtr, $openBracket, array $tokens)
	    {
		// We need to work out how far indented the function
		// call itself is, so we can work out how far to
		// indent the arguments.
		for ($i = ($stackPtr - 1); $i >= 0; $i--)
		    {
			if ($tokens[$i]["line"] !== $tokens[$stackPtr]["line"])
			    {
				$i++;
				break;
			    }
		    }

		if ($i <= 0)
		    {
			$functionIndent = 0;
		    }
		else if ($tokens[$i]["code"] === T_WHITESPACE)
		    {
			$functionIndent = strlen($tokens[$i]["content"]);
		    }
		else
		    {
			$trimmed = ltrim($tokens[$i]["content"]);
			if ($trimmed === "")
			    {
				$functionIndent = ($tokens[$i]["column"] - 1);
			    }
			else
			    {
				$functionIndent = (strlen($tokens[$i]["content"]) - strlen($trimmed));
			    }
		    }

		if ($tokens[($openBracket + 1)]["content"] !== $phpcsFile->eolChar)
		    {
			$error = _("Opening parenthesis of a multi-line function call must be the last content on the line");
			$phpcsFile->addError($error, $stackPtr, "ContentAfterOpenBracket");
		    }

		$closeBracket = $tokens[$openBracket]["parenthesis_closer"];
		$prev         = $phpcsFile->findPrevious(T_WHITESPACE, ($closeBracket - 1), null, true);
		if ($tokens[$prev]["line"] === $tokens[$closeBracket]["line"])
		    {
			$error = _("Closing parenthesis of a multi-line function call must be on a line by itself");
			$phpcsFile->addError($error, $closeBracket, "CloseBracketLine");
		    }

		// Each line between the parenthesis should be indented n spaces.
		$lastLine = $tokens[$openBracket]["line"];
		$exact    = true;
		$exactEnd = null;
		for ($i = ($openBracket + 1); $i < $closeBracket; $i++)
		    {
			if ($i === $exactEnd)
			    {
				$exact = true;
			    }

			if ($tokens[$i]["line"] !== $lastLine)
			    {
				$lastLine = $tokens[$i]["line"];

				// Ignore heredoc indentation.
				if (isset(Tokens::$heredocTokens[$tokens[$i]["code"]]) === true)
				    {
					continue;
				    }

				// Ignore multi-line string indentation.
				if (isset(Tokens::$stringTokens[$tokens[$i]["code"]]) === true && $tokens[$i]["code"] === $tokens[($i - 1)]["code"])
				    {
					continue;
				    }

				// Ignore inline HTML.
				if ($tokens[$i]["code"] === T_INLINE_HTML)
				    {
					continue;
				    }

				// We changed lines, so this should be a whitespace indent token, but first make
				// sure it isn't a blank line because we don't need to check indent unless there
				// is actually some code to indent.
				if ($tokens[$i]["code"] === T_WHITESPACE)
				    {
					$nextCode = $phpcsFile->findNext(T_WHITESPACE, ($i + 1), ($closeBracket + 1), true);
					if ($tokens[$nextCode]["line"] !== $lastLine)
					    {
						if ($exact === true)
						    {
							$error = _("Empty lines are not allowed in multi-line function calls");
							$phpcsFile->addError($error, $i, "EmptyLine");
						    }

						continue;
					    }
				    }
				else
				    {
					$nextCode = $i;
				    }

				// Check if the next line contains an object operator, if so rely on
				// the ObjectOperatorIndentSniff to test the indent.
				if ($tokens[$nextCode]["type"] === "T_OBJECT_OPERATOR")
				    {
					continue;
				    }

				if ($tokens[$nextCode]["line"] === $tokens[$closeBracket]["line"])
				    {
					// Closing brace needs to be indented to the same level
					// as the function call.
					$expectedIndent = $functionIndent;
				    }
				else
				    {
					$expectedIndent = ($functionIndent + $this->indent);
				    }

				if ($tokens[$i]["code"] !== T_WHITESPACE && $tokens[$i]["code"] !== T_DOC_COMMENT_WHITESPACE)
				    {
					// Just check if it is a multi-line block comment. If so, we can
					// calculate the indent from the whitespace before the content.
					if ($tokens[$i]["code"] === T_COMMENT && $tokens[($i - 1)]["code"] === T_COMMENT)
					    {
						$trimmed     = ltrim($tokens[$i]["content"]);
						$foundIndent = (strlen($tokens[$i]["content"]) - strlen($trimmed));
					    }
					else
					    {
						$foundIndent = 0;
					    }
				    }
				else
				    {
					$foundIndent = strlen($tokens[$i]["content"]);
				    }

				if ($foundIndent < $expectedIndent || ($exact === true && $expectedIndent !== $foundIndent))
				    {
					$error = _("Multi-line function call not indented correctly; expected") . " %s " . _("spaces but found") . " %s";
					$data  = array(
						  $expectedIndent,
						  $foundIndent,
						 );

					$phpcsFile->addError($error, $i, "Indent", $data);
				    } //end if
			    } //end if

			// Turn off exact indent matching for some structures that typically
			// define their own indentation rules.
			if ($exact === true)
			    {
				if ($tokens[$i]["code"] === T_CLOSURE)
				    {
					$exact    = false;
					$exactEnd = $tokens[$i]["scope_closer"];
				    }
				else if ($tokens[$i]["code"] === T_OPEN_SHORT_ARRAY)
				    {
					$exact    = false;
					$exactEnd = $tokens[$i]["bracket_closer"];
				    }
				else if ($tokens[$i]["code"] === T_DOC_COMMENT_OPEN_TAG)
				    {
					$exact    = false;
					$exactEnd = $tokens[$i]["comment_closer"];
				    }
				else if ($tokens[$i]["code"] === T_OPEN_PARENTHESIS)
				    {
					$exact    = false;
					$exactEnd = $tokens[$i]["parenthesis_closer"];
				    }
				else if ($phpcsFile->tokenizerType === "JS" && $tokens[$i]["code"] === T_OBJECT)
				    {
					$exact    = false;
					$exactEnd = $tokens[$i]["bracket_closer"];
				    } //end if
			    }
			else
			    {
				continue;
			    } //end if

			if ($this->allowMultipleArguments === false && $tokens[$i]["code"] === T_COMMA)
			    {
				// Comma has to be the last token on the line.
				$next = $phpcsFile->findNext(array(T_WHITESPACE, T_COMMENT), ($i + 1), $closeBracket, true);
				if ($next !== false && $tokens[$i]["line"] === $tokens[$next]["line"])
				    {
					$error = _("Only one argument is allowed per line in a multi-line function call");
					$phpcsFile->addError($error, $next, "MultipleArguments");
				    }
			    } //end if
		    } //end for
	    } //end processMultiLineCall()


    } //end class

?>
