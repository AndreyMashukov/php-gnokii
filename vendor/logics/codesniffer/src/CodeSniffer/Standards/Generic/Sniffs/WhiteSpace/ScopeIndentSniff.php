<?php

/**
 * PHP version 5
 *
 * @package Logics\BuildTools\CodeSniffer\Generic
 */

namespace Logics\BuildTools\CodeSniffer\Generic;

use \Logics\BuildTools\CodeSniffer\File;
use \Logics\BuildTools\CodeSniffer\Sniff;
use \Logics\BuildTools\CodeSniffer\Tokens;

/**
 * ScopeIndentSniff.
 *
 * Checks that control structures are structured correctly, and their content
 * is indented correctly. This sniff will throw errors if tabs are used
 * for indentation rather than spaces.
 *
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @author    Marc McIntyre <mmcintyre@squiz.net>
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2006-2012 Squiz Pty Ltd (ABN 77 084 670 600)
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 * @version   SVN: $Date: 2016-08-16 23:39:01 +0900 (Tue, 16 Aug 2016) $ $Revision: 26 $
 * @link      $HeadURL: https://open.logics.net.au/buildtools/codesniffer/tags/0.1.3/src/CodeSniffer/Standards/Generic/Sniffs/WhiteSpace/ScopeIndentSniff.php $
 *
 * @untranslatable PHP
 * @untranslatable JS
 */

class ScopeIndentSniff implements Sniff
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
	 * Does the indent need to be exactly right?
	 *
	 * If TRUE, indent needs to be exactly $indent spaces. If FALSE,
	 * indent needs to be at least $indent spaces (but can be more).
	 *
	 * @var bool
	 */
	public $exact = false;

	/**
	 * Should tabs be used for indenting?
	 *
	 * If TRUE, fixes will be made using tabs instead of spaces.
	 * The size of each tab is important, so it should be specified
	 * using the --tab-width CLI argument.
	 *
	 * @var bool
	 */
	public $tabIndent = false;

	/**
	 * The --tab-width CLI value that is being used.
	 *
	 * @var int
	 */
	private $_tabWidth = null;

	/**
	 * List of tokens not needing to be checked for indentation.
	 *
	 * Useful to allow Sniffs based on this to easily ignore/skip some
	 * tokens from verification. For example, inline HTML sections
	 * or PHP open/close tags can escape from here and have their own
	 * rules elsewhere.
	 *
	 * @var int[]
	 */
	public $ignoreIndentationTokens = array();

	/**
	 * List of tokens not needing to be checked for indentation.
	 *
	 * This is a cached copy of the public version of this var, which
	 * can be set in a ruleset file, and some core ignored tokens.
	 *
	 * @var int[]
	 */
	private $_ignoreIndentationTokens = array();

	/**
	 * Any scope openers that should not cause an indent.
	 *
	 * @var int[]
	 */
	protected $nonIndentingScopes = array();

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
	 * Processes this test, when one of its tokens is encountered.
	 *
	 * @param File $phpcsFile All the tokens found in the document.
	 * @param int  $stackPtr  The position of the current token in the stack passed in $tokens.
	 *
	 * @return int
	 *
	 * @internalconst T_CLOSE_PARENTHESIS    T_CLOSE_PARENTHESIS token
	 * @internalconst T_CLOSE_SHORT_ARRAY    T_CLOSE_SHORT_ARRAY token
	 * @internalconst T_PROPERTY             T_PROPERTY token
	 * @internalconst T_START_NOWDOC         T_START_NOWDOC token
	 * @internalconst T_END_NOWDOC           T_END_NOWDOC token
	 * @internalconst T_DOUBLE_QUOTED_STRING T_DOUBLE_QUOTED_STRING token
	 * @internalconst T_CLOSURE              T_CLOSURE token
	 * @internalconst T_OBJECT               T_OBJECT token
	 * @internalconst T_EQUAL                T_EQUAL token
	 * @internalconst T_CLOSE_OBJECT         T_CLOSE_OBJECT token
	 * @internalconst T_DOC_COMMENT_OPEN_TAG T_DOC_COMMENT_OPEN_TAG token
	 *
	 * @untranslatable JS
	 * @untranslatable IncorrectExact
	 * @untranslatable Line indented incorrectly; expected
	 * @untranslatable at least
	 * @untranslatable Incorrect
	 * @untranslatable %s tabs, found %s
	 * @untranslatable %s spaces, found %s
	 */

	public function process(File $phpcsFile, $stackPtr)
	    {
		if ($this->_tabWidth === null)
		    {
			$cliValues = $phpcsFile->phpcs->cli->getCommandLineValues();
			if (isset($cliValues["tabWidth"]) === false || $cliValues["tabWidth"] === 0)
			    {
				// We have no idea how wide tabs are, so assume 4 spaces for fixing.
				// It shouldn't really matter because indent checks elsewhere in the
				// standard should fix things up.
				$this->_tabWidth = 4;
			    }
			else
			    {
				$this->_tabWidth = $cliValues["tabWidth"];
			    }
		    }

		$currentIndent = 0;
		$lastOpenTag   = $stackPtr;
		$lastCloseTag  = null;
		$openScopes    = array();
		$adjustments   = array();

		$tokens  = &$phpcsFile->tokens;
		$first   = $phpcsFile->findFirstOnLine(T_INLINE_HTML, $stackPtr);
		$trimmed = ltrim($tokens[$first]["content"]);
		if ($trimmed === "")
		    {
			$currentIndent = ($tokens[$stackPtr]["column"] - 1);
		    }
		else
		    {
			$currentIndent = (strlen($tokens[$first]["content"]) - strlen($trimmed));
		    }

		if (empty($this->_ignoreIndentationTokens) === true)
		    {
			$this->_ignoreIndentationTokens = array(T_INLINE_HTML => true);
			foreach ($this->ignoreIndentationTokens as $token)
			    {
				if (is_int($token) === false)
				    {
					if (defined($token) === true)
					    {
						$token = constant($token);
						$this->_ignoreIndentationTokens[$token] = true;
					    }
				    }
				else
				    {
					$this->_ignoreIndentationTokens[$token] = true;
				    }
			    }
		    } //end if

		$this->exact     = (bool) $this->exact;
		$this->tabIndent = (bool) $this->tabIndent;

		for ($i = ($stackPtr + 1); $i < $phpcsFile->numTokens; $i++)
		    {
			if ($i === false)
			    {
				// Something has gone very wrong; maybe a parse error.
				break;
			    }

			$checkToken  = null;
			$checkIndent = null;

			$exact = (bool) $this->exact;
			if ($exact === true && isset($tokens[$i]["nested_parenthesis"]) === true)
			    {
				// Don't check indents exactly between parenthesis as they
				// tend to have custom rules, such as with multi-line function calls
				// and control structure conditions.
				$exact = false;
			    }

			// Detect line changes and figure out where the indent is.
			if ($tokens[$i]["column"] === 1)
			    {
				$trimmed = ltrim($tokens[$i]["content"]);
				if ($trimmed === "")
				    {
					if (isset($tokens[($i + 1)]) === true && $tokens[$i]["line"] === $tokens[($i + 1)]["line"])
					    {
						$checkToken  = ($i + 1);
						$tokenIndent = ($tokens[($i + 1)]["column"] - 1);
					    }
				    }
				else
				    {
					$checkToken  = $i;
					$tokenIndent = (strlen($tokens[$i]["content"]) - strlen($trimmed));
				    }
			    }

			// Closing parenthesis should just be indented to at least
			// the same level as where they were opened (but can be more).
			if ($checkToken !== null && $tokens[$checkToken]["code"] === T_CLOSE_PARENTHESIS && isset($tokens[$checkToken]["parenthesis_opener"]) === true)
			    {
				$first       = $phpcsFile->findFirstOnLine(T_WHITESPACE, $tokens[$checkToken]["parenthesis_opener"], true);
				$checkIndent = ($tokens[$first]["column"] - 1);
				if (isset($adjustments[$first]) === true)
				    {
					$checkIndent += $adjustments[$first];
				    }

				$exact = false;

				$prev = $phpcsFile->findStartOfStatement($first);
				if ($prev !== $first)
				    {
					// This is not the start of the statement.
					$first = $phpcsFile->findFirstOnLine(T_WHITESPACE, $prev, true);
					$prev  = $phpcsFile->findStartOfStatement($first);
					$first = $phpcsFile->findFirstOnLine(T_WHITESPACE, $prev, true);
				    }

				// Don't force current indent to divisible because there could be custom
				// rules in place between parenthesis, such as with arrays.
				$currentIndent = ($tokens[$first]["column"] - 1);
				if (isset($adjustments[$first]) === true)
				    {
					$currentIndent += $adjustments[$first];
				    }
			    } //end if

			// Closing short array bracket should just be indented to at least
			// the same level as where it was opened (but can be more).
			if ($checkToken !== null && $tokens[$checkToken]["code"] === T_CLOSE_SHORT_ARRAY)
			    {
				$first       = $phpcsFile->findFirstOnLine(T_WHITESPACE, $tokens[$checkToken]["bracket_opener"], true);
				$checkIndent = ($tokens[$first]["column"] - 1);
				if (isset($adjustments[$first]) === true)
				    {
					$checkIndent += $adjustments[$first];
				    }

				$exact = false;

				$prev = $phpcsFile->findStartOfStatement($first);
				if ($prev !== $first)
				    {
					// This is not the start of the statement.
					$first = $phpcsFile->findFirstOnLine(T_WHITESPACE, $prev, true);
					$prev  = $phpcsFile->findStartOfStatement($first);
					$first = $phpcsFile->findFirstOnLine(T_WHITESPACE, $prev, true);
				    }

				// Don't force current indent to be divisible because there could be custom
				// rules in place for arrays.
				$currentIndent = ($tokens[$first]["column"] - 1);
				if (isset($adjustments[$first]) === true)
				    {
					$currentIndent += $adjustments[$first];
				    }
			    } //end if

			// Adjust lines within scopes while auto-fixing.
			if ($checkToken !== null && $exact === false && (empty($tokens[$checkToken]["conditions"]) === false ||
			    (isset($tokens[$checkToken]["scope_opener"]) === true && $tokens[$checkToken]["scope_opener"] === $checkToken)))
			    {
				if (empty($tokens[$checkToken]["conditions"]) === false)
				    {
					end($tokens[$checkToken]["conditions"]);
					$condition = key($tokens[$checkToken]["conditions"]);
				    }
				else
				    {
					$condition = $tokens[$checkToken]["scope_condition"];
				    }

				$first = $phpcsFile->findFirstOnLine(T_WHITESPACE, $condition, true);

				if (isset($adjustments[$first]) === true &&
				    (($adjustments[$first] < 0 && $tokenIndent > $currentIndent) || ($adjustments[$first] > 0 && $tokenIndent < $currentIndent)))
				    {
					$padding = ($tokenIndent + $adjustments[$first]);
					if ($padding > 0)
					    {
						if ($this->tabIndent === true)
						    {
							$numTabs   = floor($padding / $this->_tabWidth);
							$numSpaces = ($padding - ($numTabs * $this->_tabWidth));
							$padding   = str_repeat("\t", $numTabs) . str_repeat(" ", $numSpaces);
						    }
						else
						    {
							$padding = str_repeat(" ", $padding);
						    }
					    }
					else
					    {
						$padding = "";
					    }

					if ($checkToken === $i)
					    {
						$phpcsFile->fixer->replaceToken($checkToken, $padding . $trimmed);
					    }
					else
					    {
						// Easier to just replace the entire indent.
						$phpcsFile->fixer->replaceToken(($checkToken - 1), $padding);
					    }

					$adjustments[$checkToken] = $adjustments[$first];
				    } //end if
			    } //end if

			$this->checkScopeCloser($phpcsFile, $tokens, $i, $checkToken, $openScopes, $currentIndent, $exact);

			// Handle scope for JS object notation.
			if ($phpcsFile->tokenizerType === "JS" &&
			    (($checkToken !== null && $tokens[$checkToken]["code"] === T_CLOSE_OBJECT &&
			      $tokens[$checkToken]["line"] !== $tokens[$tokens[$checkToken]["bracket_opener"]]["line"]) ||
			     ($checkToken === null && $tokens[$i]["code"] === T_CLOSE_OBJECT &&
			      $tokens[$i]["line"] !== $tokens[$tokens[$i]["bracket_opener"]]["line"])))
			    {
				$scopeCloser = $checkToken;
				if ($scopeCloser === null)
				    {
					$scopeCloser = $i;
				    }
				else
				    {
					array_pop($openScopes);
				    }

				$parens = 0;
				if (isset($tokens[$scopeCloser]["nested_parenthesis"]) === true && empty($tokens[$scopeCloser]["nested_parenthesis"]) === false)
				    {
					end($tokens[$scopeCloser]["nested_parenthesis"]);
					$parens = key($tokens[$scopeCloser]["nested_parenthesis"]);
				    }

				$condition = 0;
				if (isset($tokens[$scopeCloser]["conditions"]) === true && empty($tokens[$scopeCloser]["conditions"]) === false)
				    {
					end($tokens[$scopeCloser]["conditions"]);
					$condition = key($tokens[$scopeCloser]["conditions"]);
				    }

				if ($parens > $condition)
				    {
					$first     = $phpcsFile->findFirstOnLine(T_WHITESPACE, $parens, true);
					$condition = 0;
				    }
				else if ($condition > 0)
				    {
					$first  = $phpcsFile->findFirstOnLine(T_WHITESPACE, $condition, true);
					$parens = 0;
				    }
				else
				    {
					$first = $phpcsFile->findFirstOnLine(T_WHITESPACE, $tokens[$scopeCloser]["bracket_opener"], true);
				    } //end if

				$currentIndent = ($tokens[$first]["column"] - 1);
				if (isset($adjustments[$first]) === true)
				    {
					$currentIndent += $adjustments[$first];
				    }

				if ($parens > 0 || $condition > 0)
				    {
					$checkIndent = ($tokens[$first]["column"] - 1);
					if (isset($adjustments[$first]) === true)
					    {
						$checkIndent += $adjustments[$first];
					    }

					if ($condition > 0)
					    {
						$checkIndent   += $this->indent;
						$currentIndent += $this->indent;
						$exact          = true;
					    }
				    }
				else
				    {
					$checkIndent = $currentIndent;
				    }

				// Make sure it is divisible by our expected indent.
				$currentIndent = (int) (ceil($currentIndent / $this->indent) * $this->indent);
				$checkIndent   = (int) (ceil($checkIndent / $this->indent) * $this->indent);
			    } //end if

			if ($checkToken !== null && isset(Tokens::$scopeOpeners[$tokens[$checkToken]["code"]]) === true &&
			    in_array($tokens[$checkToken]["code"], $this->nonIndentingScopes) === false && isset($tokens[$checkToken]["scope_opener"]) === true)
			    {
				$exact = true;

				$lastOpener = null;
				if (empty($openScopes) === false)
				    {
					end($openScopes);
					$lastOpener = current($openScopes);
				    }

				// A scope opener that shares a closer with another token (like multiple
				// CASEs using the same BREAK) needs to reduce the indent level so its
				// indent is checked correctly. It will then increase the indent again
				// (as all openers do) after being checked.
				if ($lastOpener !== null && isset($tokens[$lastOpener]["scope_closer"]) === true &&
				    $tokens[$lastOpener]["level"] === $tokens[$checkToken]["level"] &&
				    $tokens[$lastOpener]["scope_closer"] === $tokens[$checkToken]["scope_closer"])
				    {
					$currentIndent -= $this->indent;
				    }

				if ($tokens[$checkToken]["code"] === T_CLOSURE && $tokenIndent > $currentIndent)
				    {
					// The opener is indented more than needed, which is fine.
					// But just check that it is divisible by our expected indent.
					$checkIndent = (int) (ceil($tokenIndent / $this->indent) * $this->indent);
					$exact       = false;
				    }
			    } //end if

			// JS property indentation has to be exact or else if will break
			// things like function and object indentation.
			if ($checkToken !== null && $tokens[$checkToken]["code"] === T_PROPERTY)
			    {
				$exact = true;
			    }

			// PHP tags needs to be indented to exact column positions
			// so they don't cause problems with indent checks for the code
			// within them, but they don't need to line up with the current indent.
			if ($checkToken !== null &&
			    ($tokens[$checkToken]["code"] === T_OPEN_TAG ||
			     $tokens[$checkToken]["code"] === T_OPEN_TAG_WITH_ECHO ||
			     $tokens[$checkToken]["code"] === T_CLOSE_TAG))
			    {
				$exact       = true;
				$checkIndent = ($tokens[$checkToken]["column"] - 1);
				$checkIndent = (int) (ceil($checkIndent / $this->indent) * $this->indent);
			    }

			// Check the line indent.
			if ($checkIndent === null)
			    {
				$checkIndent = $currentIndent;
			    }

			$adjusted = false;
			if ($checkToken !== null && isset($this->_ignoreIndentationTokens[$tokens[$checkToken]["code"]]) === false &&
			    (($tokenIndent !== $checkIndent && $exact === true) || ($tokenIndent < $checkIndent && $exact === false)))
			    {
				$type  = "IncorrectExact";
				$error = "Line indented incorrectly; expected ";
				if ($exact === false)
				    {
					$error .= "at least ";
					$type   = "Incorrect";
				    }

				if ($this->tabIndent === true)
				    {
					$error .= "%s tabs, found %s";
					$data   = array(
						   floor($checkIndent / $this->_tabWidth),
						   floor($tokenIndent / $this->_tabWidth),
						  );
				    }
				else
				    {
					$error .= "%s spaces, found %s";
					$data   = array(
						   $checkIndent,
						   $tokenIndent,
						  );
				    }

				$phpcsFile->addError($error, $checkToken, $type, $data);
			    } //end if

			if ($checkToken !== null)
			    {
				$i = $checkToken;
			    }

			if ($tokens[$i]["code"] === T_START_HEREDOC || $tokens[$i]["code"] === T_START_NOWDOC)
			    {
				// Completely skip here/now docs as the indent is a part of the
				// content itself.
				$i = $phpcsFile->findNext(array(T_END_HEREDOC, T_END_NOWDOC), ($i + 1));
			    }
			else if ($tokens[$i]["code"] === T_CONSTANT_ENCAPSED_STRING || $tokens[$i]["code"] === T_DOUBLE_QUOTED_STRING)
			    {
				// Completely skip multi-line strings as the indent is a part of the
				// content itself.
				$i = $phpcsFile->findNext($tokens[$i]["code"], ($i + 1), null, true);
				$i--;
			    }
			else if ($tokens[$i]["code"] === T_DOC_COMMENT_OPEN_TAG)
			    {
				// Completely skip doc comments as they tend to have complex
				// indentation rules.
				$i = $tokens[$i]["comment_closer"];
			    }
			else if ($tokens[$i]["code"] === T_OPEN_TAG || $tokens[$i]["code"] === T_OPEN_TAG_WITH_ECHO)
			    {
				// Open tags reset the indent level.
				if ($checkToken === null)
				    {
					$first         = $phpcsFile->findFirstOnLine(T_WHITESPACE, $i, true);
					$currentIndent = (strlen($tokens[$first]["content"]) - strlen(ltrim($tokens[$first]["content"])));
				    }
				else
				    {
					$currentIndent = ($tokens[$i]["column"] - 1);
				    }

				$lastOpenTag = $i;

				if (isset($adjustments[$i]) === true)
				    {
					$currentIndent += $adjustments[$i];
				    }

				// Make sure it is divisible by our expected indent.
				$currentIndent = (int) (ceil($currentIndent / $this->indent) * $this->indent);
			    }
			else if ($tokens[$i]["code"] === T_CLOSE_TAG)
			    {
				// Close tags reset the indent level, unless they are closing a tag
				// opened on the same line.
				if ($tokens[$lastOpenTag]["line"] !== $tokens[$i]["line"])
				    {
					$currentIndent = ($tokens[$i]["column"] - 1);
					$lastCloseTag  = $i;
				    }
				else
				    {
					if ($lastCloseTag === null)
					    {
						$currentIndent = 0;
					    }
					else
					    {
						$currentIndent = ($tokens[$lastCloseTag]["column"] - 1);
					    }
				    }

				if (isset($adjustments[$i]) === true)
				    {
					$currentIndent += $adjustments[$i];
				    }

				// Make sure it is divisible by our expected indent.
				$currentIndent = (int) (ceil($currentIndent / $this->indent) * $this->indent);
			    }
			else if ($tokens[$i]["code"] === T_CLOSURE)
			    {
				// Closures set the indent based on their own indent level.
				$closer = $tokens[$i]["scope_closer"];
				if ($tokens[$i]["line"] === $tokens[$closer]["line"])
				    {
					$i = $closer;
				    }
				else
				    {
					$first         = $phpcsFile->findFirstOnLine(T_WHITESPACE, $i, true);
					$currentIndent = (($tokens[$first]["column"] - 1) + $this->indent);

					if (isset($adjustments[$first]) === true)
					    {
						$currentIndent += $adjustments[$first];
					    }

					// Make sure it is divisible by our expected indent.
					$currentIndent = (int) (floor($currentIndent / $this->indent) * $this->indent);
					$i             = $tokens[$i]["scope_opener"];
				    }
			    }
			else if (isset($tokens[$i]["scope_condition"]) === true && isset($tokens[$i]["scope_opener"]) === true && $tokens[$i]["scope_opener"] === $i)
			    {
				// Scope openers increase the indent level.
				$closer = $tokens[$i]["scope_closer"];
				if ($tokens[$i]["line"] === $tokens[$closer]["line"])
				    {
					$i = $closer;
				    }
				else
				    {
					$condition = $tokens[$tokens[$i]["scope_condition"]]["code"];
					if (isset(Tokens::$scopeOpeners[$condition]) === true && in_array($condition, $this->nonIndentingScopes) === false)
					    {
						$currentIndent += $this->indent;
						$openScopes[$tokens[$i]["scope_closer"]] = $tokens[$i]["scope_condition"];
					    }
				    }
			    }
			else if ($phpcsFile->tokenizerType === "JS" && $tokens[$i]["code"] === T_OBJECT)
			    {
				// JS objects set the indent level.
				$closer = $tokens[$i]["bracket_closer"];
				if ($tokens[$i]["line"] === $tokens[$closer]["line"])
				    {
					$i = $closer;
				    }
				else
				    {
					$first         = $phpcsFile->findFirstOnLine(T_WHITESPACE, $i, true);
					$currentIndent = (($tokens[$first]["column"] - 1) + $this->indent);
					if (isset($adjustments[$first]) === true)
					    {
						$currentIndent += $adjustments[$first];
					    }

					// Make sure it is divisible by our expected indent.
					$currentIndent = (int) (ceil($currentIndent / $this->indent) * $this->indent);
				    }
			    }
			else if (isset($tokens[$i]["scope_condition"]) === true &&
			    $tokens[$i]["scope_closer"] === $i &&
			    $tokens[$tokens[$i]["scope_condition"]]["code"] === T_CLOSURE)
			    {
				// Closing a closure.
				$prev = false;

				$object = 0;
				if ($phpcsFile->tokenizerType === "JS")
				    {
					$conditions = $tokens[$i]["conditions"];
					krsort($conditions, SORT_NUMERIC);
					foreach ($conditions as $token => $condition)
					    {
						if ($condition === T_OBJECT)
						    {
							$object = $token;
							break;
						    }
					    }
				    }

				$parens = 0;
				if (isset($tokens[$i]["nested_parenthesis"]) === true && empty($tokens[$i]["nested_parenthesis"]) === false)
				    {
					end($tokens[$i]["nested_parenthesis"]);
					$parens = key($tokens[$i]["nested_parenthesis"]);
				    }

				$condition = 0;
				if (isset($tokens[$i]["conditions"]) === true && empty($tokens[$i]["conditions"]) === false)
				    {
					end($tokens[$i]["conditions"]);
					$condition = key($tokens[$i]["conditions"]);
				    }

				if ($parens > $object && $parens > $condition)
				    {
					$prev      = $phpcsFile->findPrevious(Tokens::$emptyTokens, ($parens - 1), null, true);
					$object    = 0;
					$condition = 0;
				    }
				else if ($object > 0 && $object >= $condition)
				    {
					$prev      = $object;
					$parens    = 0;
					$condition = 0;
				    }
				else if ($condition > 0)
				    {
					$prev   = $condition;
					$object = 0;
					$parens = 0;
				    } //end if

				if ($prev === false)
				    {
					$prev = $phpcsFile->findPrevious(array(T_EQUAL, T_RETURN), ($tokens[$i]["scope_condition"] - 1), null, false, null, true);
					if ($prev === false)
					    {
						$prev = $i;
					    }
				    }

				$first = $phpcsFile->findFirstOnLine(T_WHITESPACE, $prev, true);

				$prev = $phpcsFile->findStartOfStatement($first);
				if ($prev !== $first)
				    {
					// This is not the start of the statement.
					$first = $phpcsFile->findFirstOnLine(T_WHITESPACE, $prev, true);
				    }

				$currentIndent = ($tokens[$first]["column"] - 1);

				if ($object > 0 || $condition > 0)
				    {
					$currentIndent += $this->indent;
				    }

				// Make sure it is divisible by our expected indent.
				$currentIndent = (int) (ceil($currentIndent / $this->indent) * $this->indent);
			    } //end if
		    } //end for

		// Don't process the rest of the file.
		return $phpcsFile->numTokens;
	    } //end process()


	/**
	 * Check scope closer
	 *
	 * @param File  $phpcsFile     All the tokens found in the document.
	 * @param array $tokens        All tokens
	 * @param int   $i             The position of the current token in the stack passed in $tokens.
	 * @param mixed $checkToken    Check token
	 * @param array $openScopes    Open scopes
	 * @param int   $currentIndent Current indent
	 * @param bool  $exact         Exact closer
	 *
	 * @return void
	 *
	 * @internalconst T_CLOSURE             T_CLOSURE token
	 * @internalconst T_CLOSE_CURLY_BRACKET T_CLOSE_CURLY_BRACKET token
	 */

	protected function checkScopeCloser(File $phpcsFile, array &$tokens, $i, &$checkToken, array &$openScopes, &$currentIndent, &$exact)
	    {
		// Scope closers reset the required indent to the same level as the opening condition.
		if (($checkToken !== null && isset($openScopes[$checkToken]) === true ||
		     (isset($tokens[$checkToken]["scope_condition"]) === true && isset($tokens[$checkToken]["scope_closer"]) === true &&
		      $tokens[$checkToken]["scope_closer"] === $checkToken && $tokens[$checkToken]["line"] !== $tokens[$tokens[$checkToken]["scope_opener"]]["line"])) ||
		    ($checkToken === null && isset($openScopes[$i]) === true ||
		     (isset($tokens[$i]["scope_condition"]) === true && isset($tokens[$i]["scope_closer"]) === true &&
		      $tokens[$i]["scope_closer"] === $i && $tokens[$i]["line"] !== $tokens[$tokens[$i]["scope_opener"]]["line"])))
		    {
			$scopeCloser = $checkToken;
			if ($scopeCloser === null)
			    {
				$scopeCloser = $i;
			    }
			else
			    {
				array_pop($openScopes);
			    }

			$first = $phpcsFile->findFirstOnLine(T_WHITESPACE, $tokens[$scopeCloser]["scope_condition"], true);

			$currentIndent = ($tokens[$first]["column"] - 1);
			if (isset($adjustments[$first]) === true)
			    {
				$currentIndent += $adjustments[$first];
			    }

			// Make sure it is divisible by our expected indent.
			if ($tokens[$tokens[$scopeCloser]["scope_condition"]]["code"] !== T_CLOSURE)
			    {
				$currentIndent = (int) (ceil($currentIndent / $this->indent) * $this->indent);
			    }

			// We only check the indent of scope closers if they are
			// curly braces because other constructs tend to have different rules.
			if ($tokens[$scopeCloser]["code"] === T_CLOSE_CURLY_BRACKET)
			    {
				$exact = true;
			    }
			else
			    {
				$checkToken = null;
			    }
		    } //end if
	    } //end checkScopeCloser()


    } //end class

?>