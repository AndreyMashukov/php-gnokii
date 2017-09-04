<?php

/**
 * Processes pattern strings and checks that the code conforms to the pattern.
 *
 * PHP version 5.6
 *
 * @package Logics\BuildTools\CodeSniffer
 */

namespace Logics\BuildTools\CodeSniffer;

use \Exception;

/**
 * Processes pattern strings and checks that the code conforms to the pattern.
 *
 * This test essentially checks that code is correctly formatted with whitespace.
 *
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @author    Marc McIntyre <mmcintyre@squiz.net>
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2006-2012 Squiz Pty Ltd (ABN 77 084 670 600)
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 * @version   SVN: $Date: 2016-08-16 23:39:01 +0900 (Tue, 16 Aug 2016) $ $Revision: 26 $
 * @link      $HeadURL: https://open.logics.net.au/buildtools/codesniffer/tags/0.1.3/src/CodeSniffer/Standards/AbstractPatternSniff.php $
 */

abstract class AbstractPatternSniff implements Sniff
    {

	/**
	 * If true, comments will be ignored if they are found in the code.
	 *
	 * @var bool
	 */
	public $ignoreComments = false;

	/**
	 * The current file being checked.
	 *
	 * @var string
	 */
	protected $currFile = "";

	/**
	 * The parsed patterns array.
	 *
	 * @var array
	 */
	private $_parsedPatterns = array();

	/**
	 * Tokens that this sniff wishes to process outside of the patterns.
	 *
	 * @var array(int)
	 * @see registerSupplementary()
	 * @see processSupplementary()
	 */
	private $_supplementaryTokens = array();

	/**
	 * Positions in the stack where errors have occurred.
	 *
	 * @var array
	 */
	private $_errorPos = array();

	/**
	 * Constructs a AbstractPatternSniff.
	 *
	 * @param bool $ignoreComments If true, comments will be ignored.
	 *
	 * @return void
	 */

	public function __construct($ignoreComments = null)
	    {
		// This is here for backwards compatibility.
		if ($ignoreComments !== null)
		    {
			$this->ignoreComments = $ignoreComments;
		    }

		$this->_supplementaryTokens = $this->registerSupplementary();
	    } //end __construct()


	/**
	 * Registers the tokens to listen to.
	 *
	 * Classes extending <i>AbstractPatternTest</i> should implement the
	 * <i>getPatterns()</i> method to register the patterns they wish to test.
	 *
	 * @return array(int)
	 *
	 * @see process()
	 */

	public final function register()
	    {
		$listenTypes = array();
		$patterns    = $this->getPatterns();

		foreach ($patterns as $pattern)
		    {
			$parsedPattern = $this->_parse($pattern);

			// Find a token position in the pattern that we can use
			// for a listener token.
			$pos           = $this->_getListenerTokenPos($parsedPattern);
			$tokenType     = $parsedPattern[$pos]["token"];
			$listenTypes[] = $tokenType;

			$patternArray = array(
					 "listen_pos"   => $pos,
					 "pattern"      => $parsedPattern,
					 "pattern_code" => $pattern,
					);

			if (isset($this->_parsedPatterns[$tokenType]) === false)
			    {
				$this->_parsedPatterns[$tokenType] = array();
			    }

			$this->_parsedPatterns[$tokenType][] = $patternArray;
		    } //end foreach

		return array_unique(array_merge($listenTypes, $this->_supplementaryTokens));
	    } //end register()


	/**
	 * Returns the token types that the specified pattern is checking for.
	 *
	 * Returned array is in the format:
	 * <code>
	 *   array(
	 *      T_WHITESPACE => 0, // 0 is the position where the T_WHITESPACE token
	 *                         // should occur in the pattern.
	 *   );
	 * </code>
	 *
	 * @param array $pattern The parsed pattern to find the acquire the token
	 *                       types from.
	 *
	 * @return array(int => int)
	 *
	 * @untranslatable token
	 */

	private function _getPatternTokenTypes(array $pattern)
	    {
		$tokenTypes = array();
		foreach ($pattern as $pos => $patternInfo)
		    {
			if ($patternInfo["type"] === "token")
			    {
				if (isset($tokenTypes[$patternInfo["token"]]) === false)
				    {
					$tokenTypes[$patternInfo["token"]] = $pos;
				    }
			    }
		    }

		return $tokenTypes;
	    } //end _getPatternTokenTypes()


	/**
	 * Returns the position in the pattern that this test should register as a listener for the pattern.
	 *
	 * @param array $pattern The pattern to acquire the listener for.
	 *
	 * @return int The postition in the pattern that this test should register as the listener.
	 *
	 * @throws Exception If we could not determine a token to listen for.
	 *
	 * @exceptioncode EXCEPTION_NO_TOKEN_TO_LISTEN_FOR
	 */

	private function _getListenerTokenPos(array $pattern)
	    {
		$tokenTypes = $this->_getPatternTokenTypes($pattern);
		$tokenCodes = array_keys($tokenTypes);
		$token      = Tokens::getHighestWeightedToken($tokenCodes);

		// If we could not get a token.
		if ($token === false)
		    {
			throw new Exception(_("Could not determine a token to listen for"), EXCEPTION_NO_TOKEN_TO_LISTEN_FOR);
		    }

		return $tokenTypes[$token];
	    } //end _getListenerTokenPos()


	/**
	 * Processes the test.
	 *
	 * @param File $phpcsFile The PHP_CodeSniffer file where the token occured.
	 * @param int  $stackPtr  The postion in the tokens stack where the listening token type was found.
	 *
	 * @return void
	 *
	 * @see register()
	 */

	public final function process(File $phpcsFile, $stackPtr)
	    {
		$file = $phpcsFile->getFilename();
		if ($this->currFile !== $file)
		    {
			// We have changed files, so clean up.
			$this->_errorPos = array();
			$this->currFile  = $file;
		    }

		$tokens = &$phpcsFile->tokens;

		if (in_array($tokens[$stackPtr]["code"], $this->_supplementaryTokens) === true)
		    {
			$this->processSupplementary($phpcsFile, $stackPtr);
		    }

		$type = $tokens[$stackPtr]["code"];

		// If the type is not set, then it must have been a token registered
		// with registerSupplementary().
		if (isset($this->_parsedPatterns[$type]) === true)
		    {
			$allErrors = array();

			// Loop over each pattern that is listening to the current token type
			// that we are processing.
			foreach ($this->_parsedPatterns[$type] as $patternInfo)
			    {
				// If processPattern returns false, then the pattern that we are
				// checking the code with must not be designed to check that code.
				$errors = $this->processPattern($patternInfo, $phpcsFile, $stackPtr);
				if ($errors !== false)
				    {
					if (empty($errors) === true)
					    {
						// The pattern matched, but there were no errors.
						break;
					    }

					foreach ($errors as $stackPtr => $error)
					    {
						if (isset($this->_errorPos[$stackPtr]) === false)
						    {
							$this->_errorPos[$stackPtr] = true;
							$allErrors[$stackPtr]       = $error;
						    }
					    }
				    }
			    } //end foreach

			foreach ($allErrors as $stackPtr => $error)
			    {
				$phpcsFile->addError($error, $stackPtr);
			    }
		    } //end if
	    } //end process()


	/**
	 * Processes the pattern and verifies the code at $stackPtr.
	 *
	 * @param array $patternInfo Information about the pattern used for checking, which includes are
	 *                           parsed token representation of the pattern.
	 * @param File  $phpcsFile   The CodeSniffer file where the token occured.
	 * @param int   $stackPtr    The postion in the tokens stack where the listening token type was found.
	 *
	 * @return array(errors)
	 */

	protected function processPattern(array &$patternInfo, File $phpcsFile, $stackPtr)
	    {
		$hasError = false;
		$found    = "";

		try
		    {
			$this->_processPatternBackwards($patternInfo, $phpcsFile, $stackPtr, $hasError, $found);
			$this->_processPatternForward($patternInfo, $phpcsFile, $stackPtr, $hasError, $found);

			$errors = array();
			if ($hasError === true)
			    {
				$error             = $this->prepareError($found, $patternInfo["pattern_code"]);
				$errors[$stackPtr] = $error;
			    }
		    }
		catch (Exception $e)
		    {
			$errors = false;
		    }

		return $errors;
	    } //end processPattern()


	/**
	 * Process pattern in backward direction
	 *
	 * @param array  $patternInfo Current pattern
	 * @param File   $phpcsFile   The PHP_CodeSniffer file
	 * @param int    $stackPtr    Current token position
	 * @param bool   $hasError    True if pattern does not match to current code
	 * @param string $found       What was actually found in current code
	 *
	 * @return void
	 *
	 * @throws Exception Pattern is wrong
	 *
	 * @exceptioncode EXCEPTION_WRONG_PATTERN
	 *
	 * @untranslatable token
	 * @untranslatable skip
	 * @untranslatable parenthesis_closer
	 * @untranslatable parenthesis_opener
	 * @untranslatable scope_opener
	 * @untranslatable string
	 * @untranslatable abc
	 * @untranslatable newline
	 */

	private function _processPatternBackwards(array &$patternInfo, File $phpcsFile, $stackPtr, &$hasError, &$found)
	    {
		$tokens  = &$phpcsFile->tokens;
		$pattern = $patternInfo["pattern"];

		$ignoreTokens = array(T_WHITESPACE);
		if ($this->ignoreComments === true)
		    {
			$ignoreTokens = array_merge($ignoreTokens, Tokens::$commentTokens);
		    }

		if ($patternInfo["listen_pos"] > 0)
		    {
			$stackPtr--;

			for ($i = ($patternInfo["listen_pos"] - 1); $i >= 0; $i--)
			    {
				if ($pattern[$i]["type"] === "token")
				    {
					$this->_processTokenBackwards($pattern, $phpcsFile, $i, $tokens, $stackPtr, $hasError, $found);
				    }
				else if ($pattern[$i]["type"] === "skip")
				    {
					// Skip to next piece of relevant code.
					$to = ($pattern[$i]["to"] === "parenthesis_closer") ? "parenthesis_opener" : "scope_opener";

					// Find the previous opener.
					$prev = $phpcsFile->findPrevious($ignoreTokens, $stackPtr, null, true);

					if ($prev === false || isset($tokens[$prev][$to]) === false)
					    {
						// If there was not opener, then we must be
						// using the wrong pattern.
						throw new Exception(_("Wrong pattern"), EXCEPTION_WRONG_PATTERN);
					    }

					$found = "..." . (($to === "parenthesis_opener") ? "{" : "(") . $found;

					// Skip to the opening token.
					$stackPtr = ($tokens[$prev][$to] - 1);
				    }
				else if ($pattern[$i]["type"] === "string")
				    {
					$found = "abc";
				    }
				else if ($pattern[$i]["type"] === "newline")
				    {
					$this->_processNewLineBackwards($pattern, $phpcsFile, $i, $tokens, $stackPtr, $hasError, $found);
				    } //end if
			    } //end for
		    } //end if
	    } //end _processPatternBackwards()


	/**
	 * Process token in backward direction
	 *
	 * @param array  $pattern   Current pattern
	 * @param File   $phpcsFile The CodeSniffer file
	 * @param int    $i         Current token in pattern
	 * @param array  $tokens    Tokens array
	 * @param int    $stackPtr  Current token position
	 * @param bool   $hasError  True if pattern does not match to current code
	 * @param string $found     What was actually found in current code
	 *
	 * @return void
	 *
	 * @throws Exception Pattern is not for this code
	 *
	 * @exceptioncode EXCEPTION_PATTERN_NOT_FOR_THIS_CODE
	 *
	 * @untranslatable skip
	 */

	private function _processTokenBackwards(array $pattern, File $phpcsFile, $i, array &$tokens, &$stackPtr, &$hasError, &$found)
	    {
		$ignoreTokens = array(T_WHITESPACE);
		if ($this->ignoreComments === true)
		    {
			$ignoreTokens = array_merge($ignoreTokens, Tokens::$commentTokens);
		    }

		if ($pattern[$i]["token"] === T_WHITESPACE)
		    {
			if ($tokens[$stackPtr]["code"] === T_WHITESPACE)
			    {
				$found = $tokens[$stackPtr]["content"] . $found;
			    }

			// Only check the size of the whitespace if this is not
			// the first token. We don't care about the size of
			// leading whitespace, just that there is some.
			if ($i !== 0 && $tokens[$stackPtr]["content"] !== $pattern[$i]["value"])
			    {
				$hasError = true;
			    }
		    }
		else
		    {
			// Check to see if this important token is the same as the
			// previous important token in the pattern. If it is not,
			// then the pattern cannot be for this piece of code.
			$prev = $phpcsFile->findPrevious($ignoreTokens, $stackPtr, null, true);

			if ($prev === false || $tokens[$prev]["code"] !== $pattern[$i]["token"])
			    {
				throw new Exception(_("Pattern not for this code"), EXCEPTION_PATTERN_NOT_FOR_THIS_CODE);
			    }

			// If we skipped past some whitespace tokens, then add them
			// to the found string.
			$tokenContent = $phpcsFile->getTokensAsString(($prev + 1), ($stackPtr - $prev - 1));

			$found = $tokens[$prev]["content"] . $tokenContent . $found;

			$stackPtr = (isset($pattern[($i - 1)]) === true && $pattern[($i - 1)]["type"] === "skip") ? $prev : ($prev - 1);
		    } //end if
	    } //end _processTokenBackwards()


	/**
	 * Process new line in backward direction
	 *
	 * @param array  $pattern   Current pattern
	 * @param File   $phpcsFile The CodeSniffer file
	 * @param int    $i         Current token in pattern
	 * @param array  $tokens    Tokens array
	 * @param int    $stackPtr  Current token position
	 * @param bool   $hasError  True if pattern does not match to current code
	 * @param string $found     What was actually found in current code
	 *
	 * @return void
	 *
	 * @untranslatable EOL
	 * @untranslatable newline
	 */

	private function _processNewLineBackwards(array $pattern, File $phpcsFile, $i, array &$tokens, &$stackPtr, &$hasError, &$found)
	    {
		if ($this->ignoreComments === true && in_array($tokens[$stackPtr]["code"], Tokens::$commentTokens) === true)
		    {
			$startComment = $phpcsFile->findPrevious(Tokens::$commentTokens, ($stackPtr - 1), null, true);

			if ($tokens[$startComment]["line"] !== $tokens[($startComment + 1)]["line"])
			    {
				$startComment++;
			    }

			$tokenContent = $phpcsFile->getTokensAsString($startComment, ($stackPtr - $startComment + 1));

			$found    = $tokenContent . $found;
			$stackPtr = ($startComment - 1);
		    }

		if ($tokens[$stackPtr]["code"] === T_WHITESPACE)
		    {
			if ($tokens[$stackPtr]["content"] !== $phpcsFile->eolChar)
			    {
				$found = $tokens[$stackPtr]["content"] . $found;

				// This may just be an indent that comes after a newline
				// so check the token before to make sure. If it is a newline, we
				// can ignore the error here.
				if ($tokens[($stackPtr - 1)]["content"] !== $phpcsFile->eolChar)
				    {
					$hasError = true;
				    }
				else
				    {
					$stackPtr--;
				    }
			    }
			else
			    {
				$found = "EOL" . $found;
			    }
		    }
		else
		    {
			$found    = $tokens[$stackPtr]["content"] . $found;
			$hasError = true;
		    } //end if

		if ($hasError === false && $pattern[($i - 1)]["type"] !== "newline")
		    {
			$ignoreTokens = array(T_WHITESPACE);
			if ($this->ignoreComments === true)
			    {
				$ignoreTokens = array_merge($ignoreTokens, Tokens::$commentTokens);
			    }

			// Make sure they only have 1 newline.
			$prev = $phpcsFile->findPrevious($ignoreTokens, ($stackPtr - 1), null, true);
			if ($prev !== false && $tokens[$prev]["line"] !== $tokens[$stackPtr]["line"])
			    {
				$hasError = true;
			    }
		    }
	    } //end _processNewLineBackwards()


	/**
	 * Process pattern in forward direction
	 *
	 * @param array  $patternInfo Current pattern
	 * @param File   $phpcsFile   The CodeSniffer file
	 * @param int    $stackPtr    Current token position
	 * @param bool   $hasError    True if pattern does not match to current code
	 * @param string $found       What was actually found in current code
	 *
	 * @return bool
	 *
	 * @untranslatable token
	 * @untranslatable skip
	 * @untranslatable string
	 * @untranslatable abc
	 * @untranslatable newline
	 */

	private function _processPatternForward(array &$patternInfo, File $phpcsFile, $stackPtr, &$hasError, &$found)
	    {
		$tokens            = &$phpcsFile->tokens;
		$pattern           = &$patternInfo["pattern"];
		$lastAddedStackPtr = null;
		$patternLen        = count($pattern);

		$ignoreTokens = array(T_WHITESPACE);
		if ($this->ignoreComments === true)
		    {
			$ignoreTokens = array_merge($ignoreTokens, Tokens::$commentTokens);
		    }

		for ($i = $patternInfo["listen_pos"]; $i < $patternLen; $i++)
		    {
			if ($pattern[$i]["type"] === "token")
			    {
				$this->_processTokenForward($pattern, $phpcsFile, $i, $tokens, $stackPtr, $lastAddedStackPtr, $hasError, $found);
			    }
			else if ($pattern[$i]["type"] === "skip")
			    {
				$this->_processSkipForward($pattern, $phpcsFile, $i, $tokens, $stackPtr, $found);
			    }
			else if ($pattern[$i]["type"] === "string")
			    {
				if ($tokens[$stackPtr]["code"] !== T_STRING)
				    {
					$hasError = true;
				    }

				if ($stackPtr !== $lastAddedStackPtr)
				    {
					$found            .= "abc";
					$lastAddedStackPtr = $stackPtr;
				    }

				$stackPtr++;
			    }
			else if ($pattern[$i]["type"] === "newline")
			    {
				$this->_processNewLineForward($phpcsFile, $tokens, $stackPtr, $lastAddedStackPtr, $hasError, $found);
			    } //end if
		    } //end for

		return true;
	    } //end _processPatternForward()


	/**
	 * Process token in forward direction
	 *
	 * @param array  $pattern           Current pattern
	 * @param File   $phpcsFile         The CodeSniffer file
	 * @param int    $i                 Current token in pattern
	 * @param array  $tokens            Tokens array
	 * @param int    $stackPtr          Current token position
	 * @param int    $lastAddedStackPtr Last position where token was added
	 * @param bool   $hasError          True if pattern does not match to current code
	 * @param string $found             What was actually found in current code
	 *
	 * @return void
	 *
	 * @throws Exception Pattern is not for this code
	 *
	 * @exceptioncode EXCEPTION_PATTERN_NOT_FOR_THIS_CODE
	 *
	 * @internalconst T_OPEN_CURLY_BRACKET  T_OPEN_CURLY_BRACKET token
	 * @internalconst T_CLOSE_CURLY_BRACKET T_CLOSE_CURLY_BRACKET token
	 * @internalconst T_OPEN_PARENTHESIS    T_OPEN_PARENTHESIS token
	 * @internalconst T_CLOSE_PARENTHESIS   T_CLOSE_PARENTHESIS token
	 *
	 * @untranslatable skip
	 */

	private function _processTokenForward(array $pattern, File $phpcsFile, $i, array &$tokens, &$stackPtr, &$lastAddedStackPtr, &$hasError, &$found)
	    {
		if ($pattern[$i]["token"] === T_WHITESPACE)
		    {
			$this->_processWhitespaceTokenForward($pattern, $phpcsFile, $i, $tokens, $stackPtr, $lastAddedStackPtr, $hasError, $found);
		    }
		else
		    {
			$ignoreTokens = array(T_WHITESPACE);
			if ($this->ignoreComments === true)
			    {
				$ignoreTokens = array_merge($ignoreTokens, Tokens::$commentTokens);
			    }

			// Check to see if this important token is the same as the
			// next important token in the pattern. If it is not, then
			// the pattern cannot be for this piece of code.
			$next = $phpcsFile->findNext($ignoreTokens, $stackPtr, null, true);

			if ($next === false || $tokens[$next]["code"] !== $pattern[$i]["token"])
			    {
				// The next important token did not match the pattern.
				throw new Exception(_("Pattern not for this code"), EXCEPTION_PATTERN_NOT_FOR_THIS_CODE);
			    }

			if ($lastAddedStackPtr !== null &&
			    ((($tokens[$next]["code"] === T_OPEN_CURLY_BRACKET || $tokens[$next]["code"] === T_CLOSE_CURLY_BRACKET) &&
			     isset($tokens[$next]["scope_condition"]) === true && $tokens[$next]["scope_condition"] > $lastAddedStackPtr) ||
			    (($tokens[$next]["code"] === T_OPEN_PARENTHESIS || $tokens[$next]["code"] === T_CLOSE_PARENTHESIS) &&
			     isset($tokens[$next]["parenthesis_owner"]) === true && $tokens[$next]["parenthesis_owner"] > $lastAddedStackPtr)))
			    {
				// This is a brace or bracket, but the owner of it is after the current
				// token, which means it does not belong to any token in
				// our pattern. This means the pattern is not for us.
				throw new Exception(_("Pattern not for this code"), EXCEPTION_PATTERN_NOT_FOR_THIS_CODE);
			    }

			// If we skipped past some whitespace tokens, then add them
			// to the found string.
			if (($next - $stackPtr) > 0)
			    {
				$hasComment = false;
				for ($j = $stackPtr; $j < $next; $j++)
				    {
					$found .= $tokens[$j]["content"];
					if (in_array($tokens[$j]["code"], Tokens::$commentTokens) === true)
					    {
						$hasComment = true;
					    }
				    }

				// If we are not ignoring comments, this additional
				// whitespace or comment is not allowed. If we are
				// ignoring comments, there needs to be at least one
				// comment for this to be allowed.
				// Even when ignoring comments, we are not allowed to include
				// newlines without the pattern specifying them, so
				// everything should be on the same line.
				if ($this->ignoreComments === false ||
				    ($this->ignoreComments === true && $hasComment === false) ||
				    $tokens[$next]["line"] !== $tokens[$stackPtr]["line"])
				    {
					$hasError = true;
				    }
			    } //end if

			if ($next !== $lastAddedStackPtr)
			    {
				$found            .= $tokens[$next]["content"];
				$lastAddedStackPtr = $next;
			    }

			$stackPtr = (isset($pattern[($i + 1)]) === true && $pattern[($i + 1)]["type"] === "skip") ? $next : ($next + 1);
		    } //end if
	    } //end _processTokenForward()


	/**
	 * Process white space in forward direction
	 *
	 * @param array  $pattern           Current pattern
	 * @param File   $phpcsFile         The CodeSniffer file
	 * @param int    $i                 Current token in pattern
	 * @param array  $tokens            Tokens array
	 * @param int    $stackPtr          Current token position
	 * @param int    $lastAddedStackPtr Last position where token was added
	 * @param bool   $hasError          True if pattern does not match to current code
	 * @param string $found             What was actually found in current code
	 *
	 * @return void
	 *
	 * @untranslatable skip
	 */

	private function _processWhitespaceTokenForward(array $pattern, File $phpcsFile, $i, array &$tokens,
							&$stackPtr, &$lastAddedStackPtr, &$hasError, &$found)
	    {
		if ($this->ignoreComments === false ||
		    ((in_array($tokens[$stackPtr]["code"], Tokens::$commentTokens) === false) &&
		     (in_array($tokens[($stackPtr + 1)]["code"], Tokens::$commentTokens) === false)))
		    {
			$tokenContent = "";
			if ($tokens[$stackPtr]["code"] === T_WHITESPACE)
			    {
				if (isset($pattern[($i + 1)]) === false)
				    {
					// This is the last token in the pattern, so just compare
					// the next token of content.
					$tokenContent = $tokens[$stackPtr]["content"];
				    }
				else
				    {
					// Get all the whitespace to the next token.
					$next = $phpcsFile->findNext(Tokens::$emptyTokens, $stackPtr, null, true);

					$tokenContent = $phpcsFile->getTokensAsString($stackPtr, ($next - $stackPtr));

					$lastAddedStackPtr = $stackPtr;
					$stackPtr          = $next;
				    }

				if ($stackPtr !== $lastAddedStackPtr)
				    {
					$found .= $tokenContent;
				    }
			    }
			else
			    {
				if ($stackPtr !== $lastAddedStackPtr)
				    {
					$found            .= $tokens[$stackPtr]["content"];
					$lastAddedStackPtr = $stackPtr;
				    }
			    } //end if

			if (isset($pattern[($i + 1)]) === true && $pattern[($i + 1)]["type"] === "skip")
			    {
				// The next token is a skip token, so we just need to make
				// sure the whitespace we found has *at least* the
				// whitespace required.
				if (strpos($tokenContent, $pattern[$i]["value"]) !== 0)
				    {
					$hasError = true;
				    }
			    }
			else
			    {
				if ($tokenContent !== $pattern[$i]["value"])
				    {
					$hasError = true;
				    }
			    }
		    } //end if
	    } //end _processWhitespaceTokenForward()


	/**
	 * Process skip in forward direction
	 *
	 * @param array  $pattern   Current pattern
	 * @param File   $phpcsFile The CodeSniffer file
	 * @param int    $i         Current token in pattern
	 * @param array  $tokens    Tokens array
	 * @param int    $stackPtr  Current token position
	 * @param string $found     What was actually found in current code
	 *
	 * @return void
	 *
	 * @throws Exception Pattern is wrong
	 *
	 * @exceptioncode EXCEPTION_WRONG_PATTERN
	 *
	 * @untranslatable unknown
	 * @untranslatable parenthesis_closer
	 */

	private function _processSkipForward(array $pattern, File $phpcsFile, $i, array &$tokens, &$stackPtr, &$found)
	    {
		if ($pattern[$i]["to"] === "unknown")
		    {
			$next = $phpcsFile->findNext($pattern[($i + 1)]["token"], $stackPtr);

			if ($next === false)
			    {
				// Couldn't find the next token, sowe we must
				// be using the wrong pattern.
				throw new Exception(_("Wrong pattern"), EXCEPTION_WRONG_PATTERN);
			    }

			$found   .= "...";
			$stackPtr = $next;
		    }
		else
		    {
			// Find the previous opener.
			$next = $phpcsFile->findPrevious(Tokens::$blockOpeners, $stackPtr);

			if ($next === false || isset($tokens[$next][$pattern[$i]["to"]]) === false)
			    {
				// If there was not opener, then we must
				// be using the wrong pattern.
				throw new Exception(_("Wrong pattern"), EXCEPTION_WRONG_PATTERN);
			    }

			$found .= "...";
			if ($pattern[$i]["to"] === "parenthesis_closer")
			    {
				$found .= ")";
			    }
			else
			    {
				$found .= "}";
			    }

			// Skip to the closing token.
			$stackPtr = ($tokens[$next][$pattern[$i]["to"]] + 1);
		    } //end if
	    } //end _processSkipForward()


	/**
	 * Process new line in forward direction
	 *
	 * @param File   $phpcsFile         The PHP_CodeSniffer file
	 * @param array  $tokens            Tokens array
	 * @param int    $stackPtr          Current token position
	 * @param int    $lastAddedStackPtr Last position where token was added
	 * @param bool   $hasError          True if pattern does not match to current code
	 * @param string $found             What was actually found in current code
	 *
	 * @return void
	 */

	private function _processNewLineForward(File &$phpcsFile, array &$tokens, &$stackPtr, &$lastAddedStackPtr, &$hasError, &$found)
	    {
		$ignoreTokens = array(T_WHITESPACE);
		if ($this->ignoreComments === true)
		    {
			$ignoreTokens = array_merge($ignoreTokens, Tokens::$commentTokens);
		    }

		// Find the next token that contains a newline character.
		$newline = 0;
		for ($j = $stackPtr; $j < $phpcsFile->numTokens; $j++)
		    {
			if (strpos($tokens[$j]["content"], $phpcsFile->eolChar) !== false)
			    {
				$newline = $j;
				break;
			    }
		    }

		if ($newline === 0)
		    {
			// We didn't find a newline character in the rest of the file.
			$next     = ($phpcsFile->numTokens - 1);
			$hasError = true;
		    }
		else
		    {
			if ($this->ignoreComments === false)
			    {
				// The newline character cannot be part of a comment.
				if (in_array($tokens[$newline]["code"], Tokens::$commentTokens) === true)
				    {
					$hasError = true;
				    }
			    }

			if ($newline === $stackPtr)
			    {
				$next = ($stackPtr + 1);
			    }
			else
			    {
				// Check that there were no significant tokens that we
				// skipped over to find our newline character.
				$next = $phpcsFile->findNext($ignoreTokens, $stackPtr, null, true);

				if ($next < $newline)
				    {
					// We skipped a non-ignored token.
					$hasError = true;
				    }
				else
				    {
					$next = ($newline + 1);
				    }
			    }
		    } //end if

		if ($stackPtr !== $lastAddedStackPtr)
		    {
			$found .= $phpcsFile->getTokensAsString($stackPtr, ($next - $stackPtr));

			$diff              = ($next - $stackPtr);
			$lastAddedStackPtr = ($next - 1);
		    }

		$stackPtr = $next;
	    } //end _processNewLineForward()


	/**
	 * Prepares an error for the specified patternCode.
	 *
	 * @param string $found       The actual found string in the code.
	 * @param string $patternCode The expected pattern code.
	 *
	 * @return string The error message.
	 *
	 * @untranslatable \r\n
	 * @untranslatable EOL
	 */

	protected function prepareError($found, $patternCode)
	    {
		$found    = str_replace("\r\n", '\n', $found);
		$found    = str_replace("\n", '\n', $found);
		$found    = str_replace("\r", '\n', $found);
		$found    = str_replace("EOL", '\n', $found);
		$expected = str_replace("EOL", '\n', $patternCode);

		$error = _("Expected") . " \"" . $expected . "\"; " . _("found") . " \"" . $found . "\"";

		return $error;
	    } //end prepareError()


	/**
	 * Returns the patterns that should be checked.
	 *
	 * @return array(string)
	 */

	protected abstract function getPatterns();


	/**
	 * Registers any supplementary tokens that this test might wish to process.
	 *
	 * A sniff may wish to register supplementary tests when it wishes to group
	 * an arbitary validation that cannot be performed using a pattern, with
	 * other pattern tests.
	 *
	 * @return array(int)
	 *
	 * @see processSupplementary()
	 */

	protected function registerSupplementary()
	    {
		return array();
	    } //end registerSupplementary()


	/**
	 * Processes any tokens registered with registerSupplementary().
	 *
	 * @param File $phpcsFile The PHP_CodeSniffer file where to process the skip.
	 * @param int  $stackPtr  The position in the tokens stack to process.
	 *
	 * @return void
	 *
	 * @see registerSupplementary()
	 */

	protected function processSupplementary(File &$phpcsFile, $stackPtr)
	    {
		unset($phpcsFile);
		unset($stackPtr);
	    } //end processSupplementary()


	/**
	 * Parses a pattern string into an array of pattern steps.
	 *
	 * @param string $pattern The pattern to parse.
	 *
	 * @return array The parsed pattern array.
	 *
	 * @see _createSkipPattern()
	 * @see _createTokenPattern()
	 *
	 * @untranslatable unknown
	 * @untranslatable abc
	 * @untranslatable string
	 * @untranslatable EOL
	 * @untranslatable newline
	 */

	private function _parse($pattern)
	    {
		$patterns   = array();
		$length     = strlen($pattern);
		$lastToken  = 0;
		$firstToken = 0;

		for ($i = 0; $i < $length; $i++)
		    {
			$specialPattern = false;
			$isLastChar     = ($i === ($length - 1));
			$oldFirstToken  = $firstToken;

			if (substr($pattern, $i, 3) === "...")
			    {
				// It's a skip pattern. The skip pattern requires the
				// content of the token in the "from" position and the token
				// to skip to.
				$specialPattern = $this->_createSkipPattern($pattern, ($i - 1));
				$lastToken      = ($i - $firstToken);
				$firstToken     = ($i + 3);
				$i              = ($i + 2);

				$firstToken += ($specialPattern["to"] !== "unknown") ? 1 : 0;
			    }
			else if (substr($pattern, $i, 3) === "abc")
			    {
				$specialPattern = array("type" => "string");
				$lastToken      = ($i - $firstToken);
				$firstToken     = ($i + 3);
				$i              = ($i + 2);
			    }
			else if (substr($pattern, $i, 3) === "EOL")
			    {
				$specialPattern = array("type" => "newline");
				$lastToken      = ($i - $firstToken);
				$firstToken     = ($i + 3);
				$i              = ($i + 2);
			    } //end if

			if ($specialPattern !== false || $isLastChar === true)
			    {
				$str = ($isLastChar === true) ? substr($pattern, $oldFirstToken) : (($lastToken === 0) ? "" : substr($pattern, $oldFirstToken, $lastToken));

				if ($str !== "")
				    {
					$tokenPatterns = $this->_createTokenPattern($str);
					foreach ($tokenPatterns as $tokenPattern)
					    {
						$patterns[] = $tokenPattern;
					    }
				    }

				// Make sure we don't skip the last token.
				if ($isLastChar === false && $i === ($length - 1))
				    {
					$i--;
				    }
			    } //end if

			// Add the skip pattern *after* we have processed
			// all the tokens from the end of the last skip pattern
			// to the start of this skip pattern.
			if ($specialPattern !== false)
			    {
				$patterns[] = $specialPattern;
			    }
		    } //end for

		return $patterns;
	    } //end _parse()


	/**
	 * Creates a skip pattern.
	 *
	 * @param string $pattern The pattern being parsed.
	 * @param string $from    The token content that the skip pattern starts from.
	 *
	 * @return array The pattern step.
	 *
	 * @see _createTokenPattern()
	 * @see _parse()
	 *
	 * @untranslatable skip
	 * @untranslatable parenthesis_closer
	 * @untranslatable scope_closer
	 * @untranslatable unknown
	 */

	private function _createSkipPattern($pattern, $from)
	    {
		$skip = array("type" => "skip");

		$nestedParenthesis = 0;
		$nestedBraces      = 0;
		for ($start = $from; $start >= 0; $start--)
		    {
			switch ($pattern[$start])
			    {
				case "(":
					if ($nestedParenthesis === 0)
					    {
						$skip["to"] = "parenthesis_closer";
					    }

					$nestedParenthesis--;
				    break;
				case "{":
					if ($nestedBraces === 0)
					    {
						$skip["to"] = "scope_closer";
					    }

					$nestedBraces--;
				    break;
				case "}":
					$nestedBraces++;
				    break;
				case ")":
					$nestedParenthesis++;
				    break;
			    } //end switch

			if (isset($skip["to"]) === true)
			    {
				break;
			    }
		    } //end for

		if (isset($skip["to"]) === false)
		    {
			$skip["to"] = "unknown";
		    }

		return $skip;
	    } //end _createSkipPattern()


	/**
	 * Creates a token pattern.
	 *
	 * @param string $str The tokens string that the pattern should match.
	 *
	 * @return array The pattern step.
	 *
	 * @see _createSkipPattern()
	 * @see _parse()
	 *
	 * @untranslatable <?php
	 * @untranslatable token
	 */

	private function _createTokenPattern($str)
	    {
		// Don't add a space after the closing php tag as it will add a new
		// whitespace token.
		$tokens = token_get_all("<?php " . $str . "?>");

		// Remove the <?php tag from the front and the end php tag from the back.
		$tokens = array_slice($tokens, 1, (count($tokens) - 2));

		foreach ($tokens as &$token)
		    {
			$token = Tokens::standardiseToken($token);
		    }

		$patterns = array();
		foreach ($tokens as $patternInfo)
		    {
			$patterns[] = array(
				       "type"  => "token",
				       "token" => $patternInfo["code"],
				       "value" => $patternInfo["content"],
				      );
		    }

		return $patterns;
	    } //end _createTokenPattern()


    } //end class

?>
