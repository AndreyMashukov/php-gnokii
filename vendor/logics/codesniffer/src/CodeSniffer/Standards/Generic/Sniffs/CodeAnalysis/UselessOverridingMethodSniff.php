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
 * Detects unnecessary overridden methods that simply call their parent.
 *
 * This rule is based on the PMD rule catalog. The Useless Overriding Method
 * sniff detects the use of methods that only call their parent classes's method
 * with the same name and arguments. These methods are not required.
 *
 * <code>
 * class FooBar {
 *   public function __construct($a, $b) {
 *     parent::__construct($a, $b);
 *   }
 * }
 * </code>
 *
 * @author    Manuel Pichler <mapi@manuel-pichler.de>
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2007-2008 Manuel Pichler. All rights reserved.
 * @copyright 2006-2012 Squiz Pty Ltd (ABN 77 084 670 600)
 * @license   http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version   SVN: $Date: 2016-08-16 23:39:01 +0900 (Tue, 16 Aug 2016) $ $Revision: 26 $
 * @link      $HeadURL: https://open.logics.net.au/buildtools/codesniffer/tags/0.1.3/src/CodeSniffer/Standards/Generic/Sniffs/CodeAnalysis/UselessOverridingMethodSniff.php $
 */

class UselessOverridingMethodSniff implements Sniff
    {

	/**
	 * Registers the tokens that this sniff wants to listen for.
	 *
	 * @return array(int)
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
	 *
	 * @internalconst T_PARENT           T_PARENT token
	 * @internalconst T_OPEN_PARENTHESIS T_OPEN_PARENTHESIS token
	 * @internalconst T_SEMICOLON        T_SEMICOLON token
	 *
	 * @untranslatable trim
	 * @untranslatable Found
	 */

	public function process(File $phpcsFile, $stackPtr)
	    {
		$tokens = &$phpcsFile->tokens;
		$token  = $tokens[$stackPtr];

		// Skip function without body.
		if (isset($token["scope_opener"]) === true)
		    {
			// Get function name.
			$methodName = $phpcsFile->getDeclarationName($stackPtr);

			// Get all parameters from method signature.
			$signature = array();
			foreach ($phpcsFile->getMethodParameters($stackPtr) as $param)
			    {
				$signature[] = $param["name"];
			    }

			$next = ++$token["scope_opener"];
			$end  = --$token["scope_closer"];

			for (; $next <= $end; ++$next)
			    {
				$code = $tokens[$next]["code"];

				if (in_array($code, Tokens::$emptyTokens) === false && $code !== T_RETURN)
				    {
					break;
				    }
			    }

			if ($tokens[$next]["code"] === T_PARENT &&
			    $this->_checkNextTokenCode($phpcsFile, $tokens, $next, T_DOUBLE_COLON) === true &&
			    $this->_checkNextTokenContent($phpcsFile, $tokens, $next, $methodName) === true &&
			    $this->_checkNextTokenCode($phpcsFile, $tokens, $next, T_OPEN_PARENTHESIS) === true)
			    {
				$parameters = array_filter(array_map("trim", $this->_getParameters($tokens, $next)));

				if ($this->_checkNextTokenCode($phpcsFile, $tokens, $next, T_SEMICOLON) === true)
				    {
					$empty = true;

					// Check rest of the scope.
					for (++$next; $next <= $end; ++$next)
					    {
						$code = $tokens[$next]["code"];
						// Skip for any other content.
						if (in_array($code, Tokens::$emptyTokens) === false)
						    {
							$empty = false;
							break;
						    }
					    }

					if ($empty === true && count($parameters) === count($signature) && $parameters === $signature)
					    {
						$phpcsFile->addWarning(_("Useless method overriding detected"), $stackPtr, "Found");
					    }
				    }
			    } //end if
		    } //end if
	    } //end process()


	/**
	 * Check that next token has following code
	 *
	 * @param File   $phpcsFile The file being scanned.
	 * @param array  $tokens    All tokens
	 * @param int    $next      Next token position
	 * @param string $token     Token code
	 *
	 * @return boolean True if token code matches
	 */

	private function _checkNextTokenCode(File &$phpcsFile, array &$tokens, &$next, $token)
	    {
		$next = $phpcsFile->findNext(Tokens::$emptyTokens, ($next + 1), null, true);
		return ($next !== false && $tokens[$next]["code"] === $token);
	    } //end _checkNextTokenCode()


	/**
	 * Check that next token has following content
	 *
	 * @param File   $phpcsFile The file being scanned.
	 * @param array  $tokens    All tokens
	 * @param int    $next      Next token position
	 * @param string $token     Token contents
	 *
	 * @return boolean True if token contents matches
	 */

	private function _checkNextTokenContent(File &$phpcsFile, array &$tokens, &$next, $token)
	    {
		$next = $phpcsFile->findNext(Tokens::$emptyTokens, ($next + 1), null, true);
		return ($next !== false && $tokens[$next]["content"] === $token);
	    } //end _checkNextTokenContent()


	/**
	 * Get function parameters
	 *
	 * @param array $tokens All tokens
	 * @param int   $next   Token where to start looking for function parameters
	 *
	 * @return array List of function parameters
	 *
	 * @internalconst T_OPEN_PARENTHESIS  T_OPEN_PARENTHESIS token
	 * @internalconst T_CLOSE_PARENTHESIS T_CLOSE_PARENTHESIS token
	 * @internalconst T_COMMA             T_COMMA token
	 */

	private function _getParameters(array $tokens, &$next)
	    {
		$validParameterTypes = array(
					T_VARIABLE,
					T_LNUMBER,
					T_CONSTANT_ENCAPSED_STRING,
				       );

		$parameters       = array("");
		$parenthesisCount = 1;
		$count            = count($tokens);
		for (++$next; $next < $count; ++$next)
		    {
			$code = $tokens[$next]["code"];

			if ($code === T_OPEN_PARENTHESIS)
			    {
				++$parenthesisCount;
			    }
			else if ($code === T_CLOSE_PARENTHESIS)
			    {
				--$parenthesisCount;
			    }
			else if ($parenthesisCount === 1 && $code === T_COMMA)
			    {
				$parameters[] = "";
			    }
			else if (in_array($code, Tokens::$emptyTokens) === false)
			    {
				$parameters[(count($parameters) - 1)] .= $tokens[$next]["content"];
			    }

			if ($parenthesisCount === 0)
			    {
				break;
			    }
		    } //end for

		return $parameters;
	    } //end _getParameters()


    } //end class

?>