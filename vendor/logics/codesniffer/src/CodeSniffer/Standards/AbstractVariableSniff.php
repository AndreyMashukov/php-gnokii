<?php

/**
 * A class to find T_VARIABLE tokens.
 *
 * PHP version 5.6
 *
 * @package Logics\BuildTools\CodeSniffer
 */

namespace Logics\BuildTools\CodeSniffer;

/**
 * A class to find T_VARIABLE tokens.
 *
 * This class can distinguish between normal T_VARIABLE tokens, and those tokens
 * that represent class members. If a class member is encountered, then then
 * processMemberVar method is called so the extending class can process it. If
 * the token is found to be a normal T_VARIABLE token, then processVariable is
 * called.
 *
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @author    Marc McIntyre <mmcintyre@squiz.net>
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2006-2012 Squiz Pty Ltd (ABN 77 084 670 600)
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 * @version   SVN: $Date: 2016-08-16 23:39:01 +0900 (Tue, 16 Aug 2016) $ $Revision: 26 $
 * @link      $HeadURL: https://open.logics.net.au/buildtools/codesniffer/tags/0.1.3/src/CodeSniffer/Standards/AbstractVariableSniff.php $
 */

abstract class AbstractVariableSniff extends AbstractScopeSniff
    {

	/**
	 * The end token of the current function that we are in.
	 *
	 * @var int
	 */
	private $_endFunction = -1;

	/**
	 * True if a function is currently open.
	 *
	 * @var bool
	 */
	private $_functionOpen = false;

	/**
	 * The current CodeSniffer file that we are processing.
	 *
	 * @var File
	 */
	protected $currentFile = null;

	/**
	 * Constructs an AbstractVariableTest.
	 *
	 * @return void
	 *
	 * @internalconst T_DOUBLE_QUOTED_STRING T_DOUBLE_QUOTED_STRING token
	 * @internalconst T_HEREDOC              T_HEREDOC token
	 */

	public function __construct()
	    {
		$scopes = array(
			   T_CLASS,
			   T_INTERFACE,
			   T_TRAIT,
			  );

		$listen = array(
			   T_FUNCTION,
			   T_VARIABLE,
			   T_DOUBLE_QUOTED_STRING,
			   T_HEREDOC,
			  );

		parent::__construct($scopes, $listen, true);
	    } //end __construct()


	/**
	 * Processes the token in the specified File.
	 *
	 * @param File $phpcsFile The PHP_CodeSniffer file where this token was found.
	 * @param int  $stackPtr  The position where the token was found.
	 * @param int  $currScope The current scope opener token.
	 *
	 * @return void
	 *
	 * @internalconst T_SEMICOLON            T_SEMICOLON token
	 * @internalconst T_DOUBLE_QUOTED_STRING T_DOUBLE_QUOTED_STRING token
	 * @internalconst T_HEREDOC              T_HEREDOC token
	 */

	protected final function processTokenWithinScope(File &$phpcsFile, $stackPtr, $currScope)
	    {
		if ($this->currentFile !== $phpcsFile)
		    {
			$this->currentFile   = $phpcsFile;
			$this->_functionOpen = false;
			$this->_endFunction  = -1;
		    }

		$tokens = &$phpcsFile->tokens;

		$this->_functionOpen = ($stackPtr > $this->_endFunction) ? false : $this->_functionOpen;

		$parseError = false;

		if ($tokens[$stackPtr]["code"] === T_FUNCTION && $this->_functionOpen === false)
		    {
			$this->_functionOpen = true;

			$methodProps = $phpcsFile->getMethodProperties($stackPtr);

			// If the function is abstract, or is in an interface,
			// then set the end of the function to it's closing semicolon.
			if ($methodProps["is_abstract"] === true || $tokens[$currScope]["code"] === T_INTERFACE)
			    {
				$this->_endFunction = $phpcsFile->findNext(array(T_SEMICOLON), $stackPtr);
			    }
			else
			    {
				if (isset($tokens[$stackPtr]["scope_closer"]) === false)
				    {
					$phpcsFile->addWarning(_("Possible parse error: non-abstract method defined as abstract"), $stackPtr);
					$parseError = true;
				    }
				else
				    {
					$this->_endFunction = $tokens[$stackPtr]["scope_closer"];
				    }
			    }
		    } //end if

		if ($parseError === false)
		    {
			if ($tokens[$stackPtr]["code"] === T_DOUBLE_QUOTED_STRING || $tokens[$stackPtr]["code"] === T_HEREDOC)
			    {
				// Check to see if this string has a variable in it.
				if (preg_match('/(?<!\\\\)(?:\\\\{2})*\${?[a-zA-Z0-9_]+}?/', $tokens[$stackPtr]["content"]) !== 0)
				    {
					$this->processVariableInString($phpcsFile, $stackPtr);
				    }
			    }
			else
			    {
				if ($this->_functionOpen === true)
				    {
					if ($tokens[$stackPtr]["code"] === T_VARIABLE)
					    {
						$this->processVariable($phpcsFile, $stackPtr);
					    }
				    }
				else
				    {
					// What if we assign a member variable to another? ie. private $_count = $this->_otherCount + 1; .
					$this->processMemberVar($phpcsFile, $stackPtr);
				    }
			    } //end if
		    } //end if
	    } //end processTokenWithinScope()


	/**
	 * Processes the token outside the scope in the file.
	 *
	 * @param File $phpcsFile The PHP_CodeSniffer file where this token was found.
	 * @param int  $stackPtr  The position where the token was found.
	 *
	 * @return void
	 *
	 * @internalconst T_DOUBLE_QUOTED_STRING T_DOUBLE_QUOTED_STRING token
	 * @internalconst T_HEREDOC              T_HEREDOC token
	 */

	protected final function processTokenOutsideScope(File &$phpcsFile, $stackPtr)
	    {
		$tokens = &$phpcsFile->tokens;
		// These variables are not member vars.
		if ($tokens[$stackPtr]["code"] === T_VARIABLE)
		    {
			$this->processVariable($phpcsFile, $stackPtr);
		    }
		else if ($tokens[$stackPtr]["code"] === T_DOUBLE_QUOTED_STRING || $tokens[$stackPtr]["code"] === T_HEREDOC)
		    {
			// Check to see if this string has a variable in it.
			if (preg_match('/(?<!\\\\)(?:\\\\{2})*\${?[a-zA-Z0-9_]+}?/', $tokens[$stackPtr]["content"]) !== 0)
			    {
				$this->processVariableInString($phpcsFile, $stackPtr);
			    }
		    }
	    } //end processTokenOutsideScope()


	/**
	 * Called to process class member vars.
	 *
	 * @param File $phpcsFile The PHP_CodeSniffer file where this token was found.
	 * @param int  $stackPtr  The position where the token was found.
	 *
	 * @return void
	 */

	abstract protected function processMemberVar(File &$phpcsFile, $stackPtr);


	/**
	 * Called to process normal member vars.
	 *
	 * @param File $phpcsFile The PHP_CodeSniffer file where this token was found.
	 * @param int  $stackPtr  The position where the token was found.
	 *
	 * @return void
	 */

	abstract protected function processVariable(File &$phpcsFile, $stackPtr);


	/**
	 * Called to process variables found in double quoted strings or heredocs.
	 *
	 * Note that there may be more than one variable in the string, which will
	 * result only in one call for the string or one call per line for heredocs.
	 *
	 * @param File $phpcsFile The PHP_CodeSniffer file where this token was found.
	 * @param int  $stackPtr  The position where the double quoted string was found.
	 *
	 * @return void
	 */

	abstract protected function processVariableInString(File &$phpcsFile, $stackPtr);


    } //end class

?>
