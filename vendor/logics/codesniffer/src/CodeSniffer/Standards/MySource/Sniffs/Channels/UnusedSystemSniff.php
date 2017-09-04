<?php

/**
 * Ensures that systems, asset types and libs are included before they are used.
 *
 * PHP version 5
 *
 * @package Logics\BuildTools\CodeSniffer\MySource
 */

namespace Logics\BuildTools\CodeSniffer\MySource;

use \Logics\BuildTools\CodeSniffer\File;
use \Logics\BuildTools\CodeSniffer\Sniff;

/**
 * Ensures that systems and asset types are used if they are included.
 *
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2006-2012 Squiz Pty Ltd (ABN 77 084 670 600)
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 * @version   SVN: $Date: 2016-08-16 23:39:01 +0900 (Tue, 16 Aug 2016) $ $Revision: 26 $
 * @link      $HeadURL: https://open.logics.net.au/buildtools/codesniffer/tags/0.1.3/src/CodeSniffer/Standards/MySource/Sniffs/Channels/UnusedSystemSniff.php $
 */

class UnusedSystemSniff implements Sniff
    {

	/**
	 * Returns an array of tokens this test wants to listen for.
	 *
	 * @return array
	 */

	public function register()
	    {
		return array(T_DOUBLE_COLON);
	    } //end register()


	/**
	 * Processes this sniff, when one of its tokens is encountered.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $stackPtr  The position of the current token in the stack passed in $tokens.
	 *
	 * @return void
	 *
	 * @untranslatable includesystem
	 * @untranslatable includeasset
	 * @untranslatable includewidget
	 * @untranslatable assettype
	 * @untranslatable widgettype
	 * @untranslatable \"%s\"
	 * @untranslatable Found
	 */

	public function process(File $phpcsFile, $stackPtr)
	    {
		$tokens = &$phpcsFile->tokens;

		// Check if this is a call to includeSystem, includeAsset or includeWidget.
		$methodName = strtolower($tokens[($stackPtr + 1)]["content"]);
		if (in_array($methodName, array("includesystem", "includeasset", "includewidget")) === true)
		    {
			$systemName = $phpcsFile->findNext(T_WHITESPACE, ($stackPtr + 3), null, true);
			// Must be using a variable instead of a specific system name.
			// We can't accurately check that.
			if ($systemName !== false && $tokens[$systemName]["code"] === T_CONSTANT_ENCAPSED_STRING)
			    {
				$systemName = trim($tokens[$systemName]["content"], " '");

				if ($methodName === "includeasset")
				    {
					$systemName .= "assettype";
				    }
				else if ($methodName === "includewidget")
				    {
					$systemName .= "widgettype";
				    }

				$systemName = strtolower($systemName);

				if ($this->_isSystemUsedAnywhereInTheScope($phpcsFile, $tokens, $stackPtr, $systemName) === false)
				    {
					// If we get to here, the system was not use.
					$error = _("Included system") . " \"%s\" " . _("is never used");
					$data  = array($systemName);
					$phpcsFile->addError($error, $stackPtr, "Found", $data);
				    }
			    } //end if
		    } //end if
	    } //end process()


	/**
	 * Check if this system is used anywhere in this scope.
	 *
	 * @param File   $phpcsFile  The file where this token was found.
	 * @param array  $tokens     All tokens
	 * @param int    $stackPtr   The position where the token was found.
	 * @param string $systemName System name
	 *
	 * @return boolean True if system is used
	 */

	private function _isSystemUsedAnywhereInTheScope(File &$phpcsFile, array &$tokens, $stackPtr, $systemName)
	    {
		$used  = false;
		$level = $tokens[$stackPtr]["level"];
		for ($i = ($stackPtr + 1); $i < $phpcsFile->numTokens; $i++)
		    {
			$next = false;

			if ($tokens[$i]["level"] < $level)
			    {
				// We have gone out of scope.
				// If the original include was inside an IF statement that
				// is checking if the system exists, check the outer scope
				// as well.
				if ($tokens[$stackPtr]["level"] === $level)
				    {
					// We are still in the base level, so this is the first
					// time we have got here.
					$conditions = array_keys($tokens[$stackPtr]["conditions"]);
					if (empty($conditions) === false)
					    {
						$cond = array_pop($conditions);
						if ($tokens[$cond]["code"] === T_IF)
						    {
							$i = $tokens[$cond]["scope_closer"];
							$level--;
							$next = true;
						    }
					    }
				    }

				if ($next === false)
				    {
					break;
				    }
			    } //end if

			if ($next === false)
			    {
				$used = $this->_checkIfUsed($phpcsFile, $tokens, $i, $systemName);
				if ($used === true)
				    {
					break;
				    }
			    }
		    } //end for

		return $used;
	    } //end _isSystemUsedAnywhereInTheScope()


	/**
	 * Check if this system is used anywhere in this scope.
	 *
	 * @param File   $phpcsFile  The file where this token was found.
	 * @param array  $tokens     All tokens
	 * @param int    $i          The position of current token.
	 * @param string $systemName System name
	 *
	 * @return boolean True if system is used
	 *
	 * @internalconst T_OPEN_CURLY_BRACKET T_OPEN_CURLY_BRACKET token
	 */

	private function _checkIfUsed(File &$phpcsFile, array &$tokens, $i, $systemName)
	    {
		$validTokens = array(
				T_DOUBLE_COLON,
				T_EXTENDS,
				T_IMPLEMENTS,
			       );

		$used = false;

		if (in_array($tokens[$i]["code"], $validTokens) === true)
		    {
			switch ($tokens[$i]["code"])
			    {
				case T_DOUBLE_COLON:
					$usedName = strtolower($tokens[($i - 1)]["content"]);
					if ($usedName === $systemName)
					    {
						// The included system was used, so it is fine.
						$used = true;
					    }
				    break;
				case T_EXTENDS:
					$classNameToken = $phpcsFile->findNext(T_STRING, ($i + 1));
					$className      = strtolower($tokens[$classNameToken]["content"]);
					if ($className === $systemName)
					    {
						// The included system was used, so it is fine.
						$used = true;
					    }
				    break;
				case T_IMPLEMENTS:
					$endImplements = $phpcsFile->findNext(array(T_EXTENDS, T_OPEN_CURLY_BRACKET), ($i + 1));
					for ($x = ($i + 1); $x < $endImplements; $x++)
					    {
						$className = strtolower($tokens[$x]["content"]);
						if ($tokens[$x]["code"] === T_STRING && $className === $systemName)
						    {
							// The included system was used, so it is fine.
							$used = true;
							break;
						    }
					    }
				    break;
			    } //end switch
		    } //end if

		return $used;
	    } //end _checkIfUsed()


    } //end class

?>