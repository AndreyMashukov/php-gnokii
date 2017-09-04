<?php

/**
 * Ensures the create() method of widget types properly uses callbacks.
 *
 * PHP version 5
 *
 * @package Logics\BuildTools\CodeSniffer\MySource
 */

namespace Logics\BuildTools\CodeSniffer\MySource;

use \Logics\BuildTools\CodeSniffer\File;
use \Logics\BuildTools\CodeSniffer\Sniff;
use \Logics\BuildTools\CodeSniffer\Tokens;

/**
 * Ensures the create() method of widget types properly uses callbacks.
 *
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2006-2012 Squiz Pty Ltd (ABN 77 084 670 600)
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 * @version   SVN: $Date: 2016-08-16 23:39:01 +0900 (Tue, 16 Aug 2016) $ $Revision: 26 $
 * @link      $HeadURL: https://open.logics.net.au/buildtools/codesniffer/tags/0.1.3/src/CodeSniffer/Standards/MySource/Sniffs/Objects/CreateWidgetTypeCallbackSniff.php $
 *
 * @untranslatable JS
 */

class CreateWidgetTypeCallbackSniff implements Sniff
    {

	/**
	 * A list of tokenizers this sniff supports.
	 *
	 * @var array
	 */
	public $supportedTokenizers = array("JS");

	/**
	 * Returns an array of tokens this test wants to listen for.
	 *
	 * @return array
	 *
	 * @internalconst T_OBJECT T_OBJECT token
	 */

	public function register()
	    {
		return array(T_OBJECT);
	    } //end register()


	/**
	 * Processes this test, when one of its tokens is encountered.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $stackPtr  The position of the current token in the stack passed in $tokens.
	 *
	 * @return void
	 *
	 * @internalconst T_PROPERTY            T_PROPERTY token
	 * @internalconst T_COLON               T_COLON token
	 * @internalconst T_CLOSURE             T_CLOSURE token
	 * @internalconst T_SEMICOLON           T_SEMICOLON token
	 * @internalconst T_OPEN_PARENTHESIS    T_OPEN_PARENTHESIS token
	 * @internalconst T_CLOSE_CURLY_BRACKET T_CLOSE_CURLY_BRACKET token
	 *
	 * @untranslatable widgettype
	 * @untranslatable create
	 * @untranslatable callback
	 * @untranslatable FirstArgNotCallback
	 * @untranslatable CallbackNotCalled
	 * @untranslatable ReturnValue
	 * @untranslatable call
	 * @untranslatable this
	 * @untranslatable self
	 * @untranslatable FirstArgNotSelf
	 * @untranslatable NoReturn
	 */

	public function process(File $phpcsFile, $stackPtr)
	    {
		$tokens = &$phpcsFile->tokens;

		$className = $phpcsFile->findPrevious(T_STRING, ($stackPtr - 1));
		if (substr(strtolower($tokens[$className]["content"]), -10) !== "widgettype")
		    {
			return;
		    }

		// Search for a create method.
		$create = $phpcsFile->findNext(T_PROPERTY, $stackPtr, $tokens[$stackPtr]["bracket_closer"], null, "create");
		if ($create === false)
		    {
			return;
		    }

		$function = $phpcsFile->findNext(array(T_WHITESPACE, T_COLON), ($create + 1), null, true);
		if ($tokens[$function]["code"] !== T_FUNCTION && $tokens[$function]["code"] !== T_CLOSURE)
		    {
			return;
		    }

		$start = ($tokens[$function]["scope_opener"] + 1);
		$end   = ($tokens[$function]["scope_closer"] - 1);

		// Check that the first argument is called "callback".
		$arg = $phpcsFile->findNext(T_WHITESPACE, ($tokens[$function]["parenthesis_opener"] + 1), null, true);
		if ($tokens[$arg]["content"] !== "callback")
		    {
			$error = _("The first argument of the create() method of a widget type must be called \"callback\"");
			$phpcsFile->addError($error, $arg, "FirstArgNotCallback");
		    }

		/*
		    Look for return statements within the function. They cannot return
		    anything and must be preceded by the callback.call() line. The
		    callback itself must contain "self" or "this" as the first argument
		    and there needs to be a call to the callback function somewhere
		    in the create method. All calls to the callback function must be
		    followed by a return statement or the end of the method.
		*/

		$foundCallback  = false;
		$passedCallback = false;
		$nestedFunction = null;
		for ($i = $start; $i <= $end; $i++)
		    {
			// Keep track of nested functions.
			if ($nestedFunction !== null)
			    {
				if ($i === $nestedFunction)
				    {
					$nestedFunction = null;
					continue;
				    }
			    }
			else if (($tokens[$i]["code"] === T_FUNCTION || $tokens[$i]["code"] === T_CLOSURE) && isset($tokens[$i]["scope_closer"]) === true)
			    {
				$nestedFunction = $tokens[$i]["scope_closer"];
				continue;
			    }

			if ($nestedFunction === null && $tokens[$i]["code"] === T_RETURN)
			    {
				// Make sure return statements are not returning anything.
				if ($tokens[($i + 1)]["code"] !== T_SEMICOLON)
				    {
					$error = _("The create() method of a widget type must not return a value");
					$phpcsFile->addError($error, $i, "ReturnValue");
				    }

				continue;
			    }
			else if ($tokens[$i]["code"] !== T_STRING || $tokens[$i]["content"] !== "callback")
			    {
				continue;
			    }

			// If this is the form "callback.call(" then it is a call
			// to the callback function.
			if ($tokens[($i + 1)]["code"] !== T_OBJECT_OPERATOR || $tokens[($i + 2)]["content"] !== "call" || $tokens[($i + 3)]["code"] !== T_OPEN_PARENTHESIS)
			    {
				// One last chance; this might be the callback function
				// being passed to another function, like this
				// "this.init(something, callback, something)".
				if (isset($tokens[$i]["nested_parenthesis"]) === false)
				    {
					continue;
				    }

				// Just make sure those brackets dont belong to anyone,
				// like an IF or FOR statement.
				foreach ($tokens[$i]["nested_parenthesis"] as $bracket)
				    {
					if (isset($tokens[$bracket]["parenthesis_owner"]) === true)
					    {
						continue(2);
					    }
				    }

				// Note that we use this endBracket down further when checking
				// for a RETURN statement.
				$endBracket = end($tokens[$i]["nested_parenthesis"]);
				$bracket    = key($tokens[$i]["nested_parenthesis"]);

				$prev = $phpcsFile->findPrevious(Tokens::$emptyTokens, ($bracket - 1), null, true);

				if ($tokens[$prev]["code"] !== T_STRING)
				    {
					// This is not a function passing the callback.
					continue;
				    }

				$passedCallback = true;
			    } //end if

			$foundCallback = true;

			if ($passedCallback === false)
			    {
				// The first argument must be "this" or "self".
				$arg = $phpcsFile->findNext(T_WHITESPACE, ($i + 4), null, true);
				if ($tokens[$arg]["content"] !== "this" && $tokens[$arg]["content"] !== "self")
				    {
					$error = _("The first argument passed to the callback function must be \"this\" or \"self\"");
					$phpcsFile->addError($error, $arg, "FirstArgNotSelf");
				    }
			    }

			// Now it must be followed by a return statement or the end of the function.
			if ($passedCallback === false)
			    {
				$endBracket = $tokens[($i + 3)]["parenthesis_closer"];
			    }

			for ($next = $endBracket; $next <= $end; $next++)
			    {
				// Skip whitespace so we find the next content after the call.
				if (isset(Tokens::$emptyTokens[$tokens[$next]["code"]]) === true)
				    {
					continue;
				    }

				// Skip closing braces like END IF because it is not executable code.
				if ($tokens[$next]["code"] === T_CLOSE_CURLY_BRACKET)
				    {
					continue;
				    }

				// We don't care about anything on the current line, like a
				// semicolon. It doesn't matter if there are other statements on the
				// line because another sniff will check for those.
				if ($tokens[$next]["line"] === $tokens[$endBracket]["line"])
				    {
					continue;
				    }

				break;
			    } //end for

			if ($next !== $tokens[$function]["scope_closer"] && $tokens[$next]["code"] !== T_RETURN)
			    {
				$error = _("The call to the callback function must be followed by a return statement if it is not the last statement in the create() method");
				$phpcsFile->addError($error, $i, "NoReturn");
			    }
		    } //end for

		if ($foundCallback === false)
		    {
			$error = _("The create() method of a widget type must call the callback function");
			$phpcsFile->addError($error, $create, "CallbackNotCalled");
		    }
	    } //end process()


	/**
	 * Look for return statements within the function. They cannot return
	 * anything and must be preceded by the callback.call() line. The
	 * callback itself must contain "self" or "this" as the first argument
	 * and there needs to be a call to the callback function somewhere
	 * in the create method. All calls to the callback function must be
	 * followed by a return statement or the end of the method.
	 *
	 * @param File  $phpcsFile The file being scanned.
	 * @param array $tokens    All tokens
	 * @param int   $function  Function token position
	 * @param int   $start     Function start token position
	 * @param int   $end       Function end token position
	 *
	 * @return boolean True if callback pattern found
	 *
	 * @internalconst T_SEMICOLON T_SEMICOLON token
	 *
	 * @untranslatable ReturnValue
	 * @untranslatable callback
	 */

	private function _lookForCallbackPattern(File &$phpcsFile, array &$tokens, $function, $start, $end)
	    {
		$foundCallback  = false;
		$passedCallback = false;
		$nestedFunction = null;
		for ($i = $start; $i <= $end; $i++)
		    {
			// Keep track of nested functions.
			if ($nestedFunction !== null && $i === $nestedFunction)
			    {
				$nestedFunction = null;
			    }
			else if ($tokens[$i]["code"] === T_FUNCTION && isset($tokens[$i]["scope_closer"]) === true)
			    {
				$nestedFunction = $tokens[$i]["scope_closer"];
			    }
			else if ($nestedFunction === null && $tokens[$i]["code"] === T_RETURN && $tokens[($i + 1)]["code"] !== T_SEMICOLON)
			    {
				// Make sure return statements are not returning anything.
				$error = _("The create() method of a widget type must not return a value");
				$phpcsFile->addError($error, $i, "ReturnValue");
			    }
			else if ($tokens[$i]["code"] === T_STRING && $tokens[$i]["content"] === "callback" &&
				 $this->_lookForCallback($phpcsFile, $tokens, $i, $endBracket, $passedCallback) === true)
			    {
				$this->_processCallback($phpcsFile, $tokens, $i, $function, $end, $endBracket, $foundCallback, $passedCallback);
			    } //end if
		    } //end for

		return $foundCallback;
	    } //end _lookForCallbackPattern()


	/**
	 * Look for callback
	 *
	 * @param File  $phpcsFile      The file being scanned.
	 * @param array $tokens         All tokens
	 * @param int   $i              Current token position
	 * @param int   $endBracket     End bracket position
	 * @param bool  $passedCallback True if callback was passed
	 *
	 * @return bool True if callback found
	 *
	 * @internalconst T_OPEN_PARENTHESIS T_OPEN_PARENTHESIS token
	 *
	 * @untranslatable call
	 */

	private function _lookForCallback(File &$phpcsFile, array &$tokens, $i, &$endBracket, &$passedCallback)
	    {
		$hasCallback = true;

		// If this is the form "callback.call(" then it is a call
		// to the callback function.
		if ($tokens[($i + 1)]["code"] !== T_OBJECT_OPERATOR ||
		    $tokens[($i + 2)]["content"] !== "call" ||
		    $tokens[($i + 3)]["code"] !== T_OPEN_PARENTHESIS)
		    {
			// One last chance; this might be the callback function
			// being passed to another function, like this
			// "this.init(something, callback, something)".
			if (isset($tokens[$i]["nested_parenthesis"]) === false)
			    {
				$hasCallback = false;
			    }
			else
			    {
				// Just make sure those brackets dont belong to anyone,
				// like an IF or FOR statement.
				foreach ($tokens[$i]["nested_parenthesis"] as $bracket)
				    {
					if (isset($tokens[$bracket]["parenthesis_owner"]) === true)
					    {
						$hasCallback = false;
						break;
					    }
				    }

				if ($hasCallback === true)
				    {
					// Note that we use this endBracket down further when checking
					// for a RETURN statement.
					$endBracket = end($tokens[$i]["nested_parenthesis"]);
					$bracket    = key($tokens[$i]["nested_parenthesis"]);

					$prev = $phpcsFile->findPrevious(Tokens::$emptyTokens, ($bracket - 1), null, true);

					if ($tokens[$prev]["code"] !== T_STRING)
					    {
						// This is not a function passing the callback.
						$hasCallback = false;
					    }
					else
					    {
						$passedCallback = true;
					    }
				    }
			    } //end if
		    } //end if

		return $hasCallback;
	    } //end _lookForCallback()


	/**
	 * Check that callback is correct and followed by return
	 *
	 * @param File  $phpcsFile      The file being scanned.
	 * @param array $tokens         All tokens
	 * @param int   $i              Current token position
	 * @param int   $function       Function token position
	 * @param int   $end            Function end token position
	 * @param int   $endBracket     End bracket position
	 * @param bool  $foundCallback  True if callback was found
	 * @param bool  $passedCallback True if callback was passed
	 *
	 * @return void
	 *
	 * @internalconst T_CLOSE_CURLY_BRACKET T_CLOSE_CURLY_BRACKET token
	 *
	 * @untranslatable this
	 * @untranslatable self
	 * @untranslatable FirstArgNotSelf
	 * @untranslatable NoReturn
	 */

	private function _processCallback(File &$phpcsFile, array &$tokens, $i, $function, $end, &$endBracket, &$foundCallback, $passedCallback)
	    {
		$foundCallback = true;

		if ($passedCallback === false)
		    {
			// The first argument must be "this" or "self".
			$arg = $phpcsFile->findNext(T_WHITESPACE, ($i + 4), null, true);
			if ($tokens[$arg]["content"] !== "this" && $tokens[$arg]["content"] !== "self")
			    {
				$error = _("The first argument passed to the callback function must be \"this\" or \"self\"");
				$phpcsFile->addError($error, $arg, "FirstArgNotSelf");
			    }

			// Now it must be followed by a return statement or the end of the function.
			$endBracket = $tokens[($i + 3)]["parenthesis_closer"];
		    }

		for ($next = $endBracket; $next <= $end; $next++)
		    {
			// Skip whitespace so we find the next content after the call.
			if (in_array($tokens[$next]["code"], Tokens::$emptyTokens) === false)
			    {
				// Skip closing braces like END IF because it is not executable code.
				if ($tokens[$next]["code"] !== T_CLOSE_CURLY_BRACKET)
				    {
					// We don't care about anything on the current line, like a
					// semicolon. It doesn't matter if there are other statements on the
					// line because another sniff will check for those.
					if ($tokens[$next]["line"] !== $tokens[$endBracket]["line"])
					    {
						break;
					    }
				    }
			    }
		    } //end for

		if ($next !== $tokens[$function]["scope_closer"] && $tokens[$next]["code"] !== T_RETURN)
		    {
			$error = _("The call to the callback function must be followed by a return statement") . " " .
				 _("if it is not the last statement in the create() method");
			$phpcsFile->addError($error, $i, "NoReturn");
		    }
	    } //end _processCallback()


    } //end class

?>
