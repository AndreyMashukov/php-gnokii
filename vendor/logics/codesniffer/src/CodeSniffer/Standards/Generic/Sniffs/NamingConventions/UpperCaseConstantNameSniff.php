<?php

/**
 * PHP version 5
 *
 * @package Logics\BuildTools\CodeSniffer\Generic
 */

namespace Logics\BuildTools\CodeSniffer\Generic;

use \Logics\BuildTools\CodeSniffer\File;
use \Logics\BuildTools\CodeSniffer\Sniff;

/**
 * UpperCaseConstantNameSniff.
 *
 * Ensures that constant names are all uppercase.
 *
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @author    Marc McIntyre <mmcintyre@squiz.net>
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2006-2012 Squiz Pty Ltd (ABN 77 084 670 600)
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 * @version   SVN: $Date: 2016-08-16 23:39:01 +0900 (Tue, 16 Aug 2016) $ $Revision: 26 $
 * @link      $HeadURL: https://open.logics.net.au/buildtools/codesniffer/tags/0.1.3/src/CodeSniffer/Standards/Generic/Sniffs/NamingConventions/UpperCaseConstantNameSniff.php $
 *
 * @untranslatable define
 * @untranslatable constant
 */

class UpperCaseConstantNameSniff implements Sniff
    {

	/**
	 * Array declarations codes
	 */
	const DECLARATIONS = array(
			      T_FUNCTION,
			      T_CLASS,
			      T_INTERFACE,
			      T_TRAIT,
			      T_IMPLEMENTS,
			      T_EXTENDS,
			      T_INSTANCEOF,
			      T_NEW,
			      T_NAMESPACE,
			      T_USE,
			      T_AS,
			      T_GOTO,
			      T_INSTEADOF,
			     );

	/**
	 * Array keywords constant definition
	 */
	const CONSTANT_DEFINITION_KEYWORDS = array(
					      "define",
					      "constant",
					     );

	/**
	 * Returns an array of tokens this test wants to listen for.
	 *
	 * @return array
	 */

	public function register()
	    {
		return array(T_STRING);
	    } //end register()


	/**
	 * Processes this test, when one of its tokens is encountered.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $stackPtr  The position of the current token in the stack passed in $tokens.
	 *
	 * @return void
	 *
	 * @internalconst T_OPEN_PARENTHESIS T_OPEN_PARENTHESIS token
	 * @internalconst T_COMMA            T_COMMA token
	 *
	 * @untranslatable PHPUnit_MAIN_METHOD
	 */

	public function process(File $phpcsFile, $stackPtr)
	    {
		$tokens    = &$phpcsFile->tokens;
		$constName = $tokens[$stackPtr]["content"];

		// If this token is in a heredoc, ignore it or special case for PHPUnit.
		if ($phpcsFile->hasCondition($stackPtr, T_START_HEREDOC) === false && $constName !== "PHPUnit_MAIN_METHOD")
		    {
			// If the next non-whitespace token after this token is not an opening parenthesis then it is not a function call.
			$openBracket = $phpcsFile->findNext(T_WHITESPACE, ($stackPtr + 1), null, true);
			if ($tokens[$openBracket]["code"] !== T_OPEN_PARENTHESIS)
			    {
				$functionKeyword = $phpcsFile->findPrevious(
						    array(
						     T_WHITESPACE,
						     T_COMMA,
						     T_COMMENT,
						     T_STRING,
						     T_NS_SEPARATOR,
						    ),
						    ($stackPtr - 1), null, true
						   );

				// This is not a declaration; we may have constants here.
				if (in_array($tokens[$functionKeyword]["code"], self::DECLARATIONS) === false)
				    {
					$this->_lookForConstants($phpcsFile, $tokens, $stackPtr, $functionKeyword, $constName);
				    } //end if
			    }
			else if (in_array(strtolower($constName), self::CONSTANT_DEFINITION_KEYWORDS) === true)
			    {
				// Make sure this is not a method call.
				$prev               = $phpcsFile->findPrevious(T_WHITESPACE, ($stackPtr - 1), null, true);
				$prevTokensNotConst = array(
						       T_OBJECT_OPERATOR,
						       T_DOUBLE_COLON,
						      );
				if (in_array($tokens[$prev]["code"], $prevTokensNotConst) === false)
				    {
					// The next non-whitespace token must be the constant name.
					$constPtr = $phpcsFile->findNext(T_WHITESPACE, ($openBracket + 1), null, true);
					if ($tokens[$constPtr]["code"] === T_CONSTANT_ENCAPSED_STRING)
					    {
						$constName = $tokens[$constPtr]["content"];

						// Check for constants like self::CONSTANT.
						$prefix   = "";
						$splitPos = strpos($constName, "::");
						if ($splitPos !== false)
						    {
							$prefix    = substr($constName, 0, ($splitPos + 2));
							$constName = substr($constName, ($splitPos + 2));
						    }

						$this->_checkUpperCase($phpcsFile, $stackPtr, $constName, $prefix);
					    } //end if
				    } //end if
			    } //end if
		    } //end if
	    } //end process()


	/**
	 * Look for constants
	 *
	 * @param File   $phpcsFile       The file being scanned.
	 * @param array  $tokens          All tokens
	 * @param int    $stackPtr        The position of the current token in the stack passed in $tokens
	 * @param int    $functionKeyword The position of function keyword
	 * @param string $constName       Name of current constant
	 *
	 * @return void
	 *
	 * @internalconst T_CLOSE_CURLY_BRACKET T_CLOSE_CURLY_BRACKET token
	 * @internalconst T_OPEN_PARENTHESIS    T_OPEN_PARENTHESIS token
	 * @internalconst T_SEMICOLON           T_SEMICOLON token
	 * @internalconst T_OPEN_CURLY_BRACKET  T_OPEN_CURLY_BRACKET token
	 * @internalconst T_COLON               T_COLON token
	 */

	private function _lookForConstants(File &$phpcsFile, array &$tokens, $stackPtr, $functionKeyword, $constName)
	    {
		if ($tokens[$functionKeyword]["code"] === T_CONST)
		    {
			$this->_checkUpperCase($phpcsFile, $stackPtr, $constName);
		    }
		else
		    {
			$prevPtr = $phpcsFile->findPrevious(T_WHITESPACE, ($stackPtr - 1), null, true);
			$nextPtr = $phpcsFile->findNext(T_WHITESPACE, ($stackPtr + 1), null, true);

			// Is this a class/namespace/insteadof name or type hint?
			// Is this a member var name?
			// Is this a variable name, in the form ${varname} ?
			// Is this a namespace name?
			$nextTokensNotConst = array(
					       T_DOUBLE_COLON,
					       T_NS_SEPARATOR,
					       T_INSTEADOF,
					       T_VARIABLE,
					      );
			$prevTokensNotConst = array(
					       T_OBJECT_OPERATOR,
					       T_NS_SEPARATOR,
					      );
			if (in_array($tokens[$nextPtr]["code"], $nextTokensNotConst) === false && $phpcsFile->isReference($nextPtr) === false &&
			    in_array($tokens[$prevPtr]["code"], $prevTokensNotConst) === false &&
			    ($tokens[$prevPtr]["code"] !== T_OPEN_CURLY_BRACKET || $tokens[$nextPtr]["code"] !== T_CLOSE_CURLY_BRACKET))
			    {
				// Is this an instance of declare() or is this a goto label target?
				$prevPtrDeclare = $phpcsFile->findPrevious(array(T_WHITESPACE, T_OPEN_PARENTHESIS), ($stackPtr - 1), null, true);
				if ($tokens[$prevPtrDeclare]["code"] !== T_DECLARE &&
				    ($tokens[$nextPtr]["code"] !== T_COLON ||
				     in_array($tokens[$prevPtr]["code"], array(T_SEMICOLON, T_OPEN_CURLY_BRACKET, T_COLON), true) === false))
				    {
					$this->_checkUpperCase($phpcsFile, $stackPtr, $constName);
				    }
			    }
		    } //end if
	    } //end _lookForConstants()


	/**
	 * Check uppercase
	 *
	 * @param File   $phpcsFile The file being scanned.
	 * @param int    $stackPtr  The position of the current token in the stack passed in $tokens
	 * @param string $constName Constant name
	 * @param string $prefix    Constant prefix
	 *
	 * @return void
	 *
	 * @untranslatable %s
	 * @untranslatable ConstantNotUpperCase
	 */

	private function _checkUpperCase(File &$phpcsFile, $stackPtr, $constName, $prefix = "")
	    {
		if (strtoupper($constName) !== $constName)
		    {
			$error = _("Class constants must be uppercase; expected") . " %s " . _("but found") . " %s";
			$data  = array(
				  $prefix . strtoupper($constName),
				  $prefix . $constName,
				 );
			$phpcsFile->addError($error, $stackPtr, "ConstantNotUpperCase", $data);
		    }
	    } //end _checkUpperCase()


    } //end class

?>
