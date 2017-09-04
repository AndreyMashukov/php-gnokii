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
 * Checks the for unused function parameters.
 *
 * This sniff checks that all function parameters are used in the function body.
 * One exception is made for empty function bodies or function bodies that only
 * contain comments. This could be useful for the classes that implement an
 * interface that defines multiple methods but the implementation only needs some
 * of them.
 *
 * @author    Manuel Pichler <mapi@manuel-pichler.de>
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2007-2008 Manuel Pichler. All rights reserved.
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version   SVN: $Date: 2016-08-16 23:39:01 +0900 (Tue, 16 Aug 2016) $ $Revision: 26 $
 * @link      $HeadURL: https://open.logics.net.au/buildtools/codesniffer/tags/0.1.3/src/CodeSniffer/Standards/Generic/Sniffs/CodeAnalysis/UnusedFunctionParameterSniff.php $
 */

class UnusedFunctionParameterSniff implements Sniff
    {

	/**
	 * Returns an array of tokens this test wants to listen for.
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
	 *
	 * @internalconst T_SEMICOLON T_SEMICOLON token
	 */

	public function process(File $phpcsFile, $stackPtr)
	    {
		$tokens = &$phpcsFile->tokens;
		$token  = $tokens[$stackPtr];

		// Skip broken function declarations.
		if (isset($token["scope_opener"]) === true && isset($token["parenthesis_opener"]) === true)
		    {
			$params = array();
			foreach ($phpcsFile->getMethodParameters($stackPtr) as $param)
			    {
				$params[$param["name"]] = $stackPtr;
			    }

			$next = ++$token["scope_opener"];
			$end  = --$token["scope_closer"];

			$foundContent = false;

			for (; $next <= $end; ++$next)
			    {
				$token = $tokens[$next];
				$code  = $token["code"];

				// Ignorable tokens.
				if (in_array($code, Tokens::$emptyTokens) === false)
				    {
					// A throw statement as the first content indicates an interface method.
					if ($foundContent === false && $code === T_THROW)
					    {
						break;
					    }

					// A return statement as the first content indicates an interface method.
					if ($foundContent === false && $code === T_RETURN)
					    {
						$tmp = $phpcsFile->findNext(Tokens::$emptyTokens, ($next + 1), null, true);
						if ($tmp === false || $tokens[$tmp]["code"] === T_SEMICOLON)
						    {
							break;
						    }

						$tmp = $phpcsFile->findNext(Tokens::$emptyTokens, ($tmp + 1), null, true);
						if ($tmp !== false && $tokens[$tmp]["code"] === T_SEMICOLON)
						    {
							// There is a return <token>.
							break;
						    }
					    } //end if

					$foundContent = true;

					$this->_processToken($phpcsFile, $tokens, $next, $end, $token, $params);
				    } //end if
			    } //end for

			$this->_report($phpcsFile, $foundContent, $params);
		    } //end if
	    } //end process()


	/**
	 * Processes single token
	 *
	 * @param File  $phpcsFile The file being scanned.
	 * @param array $tokens    All tokens
	 * @param int   $next      Next token index
	 * @param int   $end       Last token index
	 * @param array $token     Current token
	 * @param array $params    Function parameters
	 *
	 * @return void
	 *
	 * @internalconst T_DOLLAR               T_DOLLAR token
	 * @internalconst T_OPEN_CURLY_BRACKET   T_OPEN_CURLY_BRACKET token
	 * @internalconst T_START_NOWDOC         T_START_NOWDOC token
	 * @internalconst T_HEREDOC              T_HEREDOC token
	 * @internalconst T_NOWDOC               T_NOWDOC token
	 * @internalconst T_END_NOWDOC           T_END_NOWDOC token
	 * @internalconst T_DOUBLE_QUOTED_STRING T_DOUBLE_QUOTED_STRING token
	 */

	private function _processToken(File &$phpcsFile, array &$tokens, &$next, $end, array $token, array &$params)
	    {
		$code = $token["code"];

		if ($code === T_VARIABLE && isset($params[$token["content"]]) === true)
		    {
			unset($params[$token["content"]]);
		    }
		else if ($code === T_DOLLAR)
		    {
			$nextToken = $phpcsFile->findNext(T_WHITESPACE, ($next + 1), null, true);
			if ($tokens[$nextToken]["code"] === T_OPEN_CURLY_BRACKET)
			    {
				$nextToken = $phpcsFile->findNext(T_WHITESPACE, ($nextToken + 1), null, true);
				if ($tokens[$nextToken]["code"] === T_STRING)
				    {
					$varContent = "$" . $tokens[$nextToken]["content"];
					if (isset($params[$varContent]) === true)
					    {
						unset($params[$varContent]);
					    }
				    }
			    }
		    }
		else if ($code === T_DOUBLE_QUOTED_STRING || $code === T_START_HEREDOC || $code === T_START_NOWDOC)
		    {
			// Tokenize strings that can contain variables.
			// Make sure the string is re-joined if it occurs over multiple lines.
			$validTokens = array(
					T_HEREDOC,
					T_NOWDOC,
					T_END_HEREDOC,
					T_END_NOWDOC,
					T_DOUBLE_QUOTED_STRING,
				       );
			$validTokens = array_merge($validTokens, Tokens::$emptyTokens);

			$content = $token["content"];
			for ($i = ($next + 1); $i <= $end; $i++)
			    {
				if (in_array($tokens[$i]["code"], $validTokens) === true)
				    {
					$content .= $tokens[$i]["content"];
					$next++;
				    }
				else
				    {
					break;
				    }
			    }

			$this->_checkVariablesInString($content, $params);
		    } //end if
	    } //end _processToken()


	/**
	 * Check string for variables and remove them from function parameters if found
	 *
	 * @param string $content String to check
	 * @param array  $params  Function parameters
	 *
	 * @return void
	 *
	 * @untranslatable <?php %s;?>
	 */

	private function _checkVariablesInString($content, array &$params)
	    {
		$stringTokens = token_get_all(sprintf("<?php %s;?>", $content));
		foreach ($stringTokens as $stringPtr => $stringToken)
		    {
			if (is_array($stringToken) === true)
			    {
				$varContent = "";
				if ($stringToken[0] === T_DOLLAR_OPEN_CURLY_BRACES)
				    {
					$varContent = "$" . $stringTokens[($stringPtr + 1)][1];
				    }
				else if ($stringToken[0] === T_VARIABLE)
				    {
					$varContent = $stringToken[1];
				    }

				if ($varContent !== "" && isset($params[$varContent]) === true)
				    {
						unset($params[$varContent]);
				    }
			    }
		    } //end foreach
	    } //end _checkVariablesInString()


	/**
	 * Report warnings
	 *
	 * @param File  $phpcsFile    The file being scanned.
	 * @param bool  $foundContent True if function is not empty
	 * @param array $params       List of unused parameters
	 *
	 * @return void
	 *
	 * @untranslatable %s
	 * @untranslatable Found
	 */

	private function _report(File &$phpcsFile, $foundContent, array $params)
	    {
		if ($foundContent === true && count($params) > 0)
		    {
			foreach ($params as $paramName => $position)
			    {
				$data = array($paramName);
				$phpcsFile->addWarning(_("The method parameter") . " %s " . _("is never used"), $position, "Found", $data);
			    }
		    }
	    } //end _report()


    } //end class

?>
