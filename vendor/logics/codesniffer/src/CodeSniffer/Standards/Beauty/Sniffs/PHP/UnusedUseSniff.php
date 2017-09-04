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
 * Beauty_Sniffs_PHP_UnusedUseSniff
 *
 * Unused use statement found
 *
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 * @version   SVN: $Date: 2016-08-16 23:39:01 +0900 (Tue, 16 Aug 2016) $ $Revision: 26 $
 * @link      $HeadURL: https://open.logics.net.au/buildtools/codesniffer/tags/0.1.3/src/CodeSniffer/Standards/Beauty/Sniffs/PHP/UnusedUseSniff.php $
 */

class UnusedUseSniff implements Sniff
    {

	/**
	 * Returns an array of tokens this test wants to listen for.
	 *
	 * @return array
	 */

	public function register()
	    {
		return array(T_USE);
	    } //end register()


	/**
	 * Processes this test, when use statement is used
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $stackPtr  The position of the current token in the stack passed in $tokens.
	 *
	 * @return void
	 *
	 * @internalconst T_SEMICOLON T_SEMICOLON token
	 *
	 * @untranslatable UnusedUse
	 */

	public function process(File $phpcsFile, $stackPtr)
	    {
		$tokens = &$phpcsFile->tokens;
		if ($tokens[$stackPtr]["level"] === 0)
		    {
			$nametoken     = ($phpcsFile->findNext(array(T_SEMICOLON), ($stackPtr + 1)) - 1);
			$fullclassname = $this->_getClassName($nametoken, $tokens, true);
			$bits          = explode("\\", $fullclassname);
			$classname     = array_pop($bits);
			$usedclasses   = $this->_getUsedClasses($phpcsFile);
			if (in_array($classname, $usedclasses) === false)
			    {
				$phpcsFile->addError(_("Unused use statement: ") . $fullclassname, $stackPtr, "UnusedUse");
			    }
		    }
	    } //end process()


	/**
	 * Get array of used classes.
	 *
	 * @param File $phpcsFile The file being scanned.
	 *
	 * @donottranslate
	 *
	 * @return array
	 */

	private function _getUsedClasses(File $phpcsFile)
	    {
		$tokens      = &$phpcsFile->tokens;
		$usedClasses = array();
		for ($i = 0; $i < $phpcsFile->numTokens; $i++)
		    {
			if (($tokens[$i]["code"] === T_NEW && $tokens[($i + 1)]["code"] === T_WHITESPACE) ||
			    ($tokens[$i]["code"] === T_EXTENDS) ||
			    ($tokens[$i]["code"] === T_INSTANCEOF))
			    {
				$i            += 2;
				$usedClasses[] = $this->_getClassName($i, $tokens);
			    }
			else if ($tokens[$i]["code"] === T_DOUBLE_COLON)
			    {
				$tmp           = ($i - 1);
				$usedClasses[] = $this->_getClassName($tmp, $tokens, true);
			    }
			else if ($tokens[$i]["code"] === T_USE)
			    {
				$usedClasses = array_merge($usedClasses, $this->_getUse($phpcsFile, $i, $tokens));
			    }
			else if ($tokens[$i]["code"] === T_IMPLEMENTS)
			    {
				$usedClasses = array_merge($usedClasses, $this->_getImplements($phpcsFile, $i, $tokens));
			    }
			else if ($tokens[$i]["code"] === T_FUNCTION)
			    {
				$usedClasses = array_merge($usedClasses, $this->_getTypeHints($phpcsFile, $i));
			    }
			else if ($tokens[$i]["code"] === T_CATCH)
			    {
				$usedClasses[] = $this->_getCatch($phpcsFile, $i, $tokens);
			    } //end if
		    } //end for

		return $usedClasses;
	    } //end _getUsedClasses()


	/**
	 * Get array of interfaces.
	 *
	 * @param File  $phpcsFile The file being scanned.
	 * @param int   $stackPtr  The position of the current token in the stack passed in $tokens.
	 * @param array $tokens    The array of tokens
	 *
	 * @return array
	 *
	 * @internalconst T_SEMICOLON          T_SEMICOLON token
	 * @internalconst T_OPEN_CURLY_BRACKET T_OPEN_CURLY_BRACKET token
	 */

	private function _getImplements(File $phpcsFile, $stackPtr, array $tokens)
	    {
		$endImplements = $phpcsFile->findNext(array(T_EXTENDS, T_SEMICOLON, T_OPEN_CURLY_BRACKET), ($stackPtr + 1));
		$implements    = array();
		while ($stackPtr !== $endImplements)
		    {
			if ($tokens[$stackPtr]["code"] === T_STRING)
			    {
				$implements[] = $this->_getClassName($stackPtr, $tokens);
			    }
			else
			    {
				$stackPtr++;
			    }
		    }

		return $implements;
	    } //end _getImplements()


	/**
	 * Get array of traits.
	 *
	 * @param File  $phpcsFile The file being scanned.
	 * @param int   $stackPtr  The position of the current token in the stack passed in $tokens.
	 * @param array $tokens    The array of tokens
	 *
	 * @return array
	 *
	 * @internalconst T_OPEN_PARENTHESIS T_OPEN_PARENTHESIS token
	 * @internalconst T_SEMICOLON        T_SEMICOLON token
	 */

	private function _getUse(File $phpcsFile, $stackPtr, array $tokens)
	    {
		$nextnonwhitespacetoken = $tokens[$phpcsFile->findNext(array(T_WHITESPACE), ($stackPtr + 1), null, true)];
		if ($tokens[$stackPtr]["level"] === 0 || $nextnonwhitespacetoken["code"] === T_OPEN_PARENTHESIS)
		    {
			return array();
		    }
		else
		    {
			$use = array();
			while ($tokens[$stackPtr]["code"] !== T_SEMICOLON)
			    {
				if ($tokens[$stackPtr]["code"] === T_STRING)
				    {
					$use[] = $this->_getClassName($stackPtr, $tokens);
				    }
				else
				    {
					$stackPtr++;
				    }
			    }

			return $use;
		    } //end if
	    } //end _getUse()


	/**
	 * Get array of type hints.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $stackPtr  The position of the current token in the stack passed in $tokens.
	 *
	 * @return array
	 */

	private function _getTypeHints(File $phpcsFile, $stackPtr)
	    {
		$params  = $phpcsFile->getMethodParameters($stackPtr);
		$classes = array();
		foreach ($params as $param)
		    {
			$classes[] = $param["type_hint"];
		    }

		return $classes;
	    } //end _getTypeHints()


	/**
	 * Get exception type.
	 *
	 * @param File  $phpcsFile The file being scanned.
	 * @param int   $stackPtr  The position of the current token in the stack passed in $tokens.
	 * @param array $tokens    The array of tokens
	 *
	 * @return array
	 *
	 * @internalconst T_OPEN_PARENTHESIS T_OPEN_PARENTHESIS token
	 */

	private function _getCatch(File $phpcsFile, $stackPtr, array $tokens)
	    {
		$parenthesis = $phpcsFile->findNext(array(T_OPEN_PARENTHESIS), ($stackPtr + 1));
		$type        = $phpcsFile->findNext(array(T_WHITESPACE), ($parenthesis + 1), null, true);
		$classname   = $this->_getClassName($type, $tokens);

		return $classname;
	    } //end _getCatch()


	/**
	 * Get class name from token stream
	 *
	 * @param int   $i       Token position from which class name should be reconstructed
	 * @param array $tokens  All tokens
	 * @param bool  $reverse True if class name reconstruction should be done in reverse
	 *
	 * @return string Class name
	 */

	private function _getClassName(&$i, array &$tokens, $reverse = false)
	    {
		$class = "";
		while ($tokens[$i]["code"] === T_STRING || $tokens[$i]["code"] === T_NS_SEPARATOR || $tokens[$i]["code"] === T_NAMESPACE)
		    {
			if ($reverse === true)
			    {
				$class = $tokens[$i]["content"] . $class;
				$i--;
			    }
			else
			    {
				$class .= $tokens[$i]["content"];
				$i++;
			    }
		    }

		return $class;
	    } //end _getClassName()


    } //end class

?>
