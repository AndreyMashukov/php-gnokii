<?php

/**
 * Ensures that self is not used to call public method in action classes.
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
 * Ensures that self is not used to call public method in action classes.
 *
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2006-2012 Squiz Pty Ltd (ABN 77 084 670 600)
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 * @version   SVN: $Date: 2016-08-16 23:39:01 +0900 (Tue, 16 Aug 2016) $ $Revision: 26 $
 * @link      $HeadURL: https://open.logics.net.au/buildtools/codesniffer/tags/0.1.3/src/CodeSniffer/Standards/MySource/Sniffs/Channels/DisallowSelfActionsSniff.php $
 */

class DisallowSelfActionsSniff implements Sniff
    {

	/**
	 * Returns an array of tokens this test wants to listen for.
	 *
	 * @return array
	 */

	public function register()
	    {
		return array(T_CLASS);
	    } //end register()


	/**
	 * Processes this sniff, when one of its tokens is encountered.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $stackPtr  The position of the current token in the stack passed in $tokens.
	 *
	 * @return void
	 *
	 * @untranslatable Actions
	 * @untranslatable self
	 * @untranslatable public
	 * @untranslatable %s::%s()
	 * @untranslatable Found
	 */

	public function process(File $phpcsFile, $stackPtr)
	    {
		$tokens = &$phpcsFile->tokens;

		// We are not interested in abstract classes.
		$prev = $phpcsFile->findPrevious(T_WHITESPACE, ($stackPtr - 1), null, true);
		// We are only interested in Action classes.
		$classNameToken = $phpcsFile->findNext(T_WHITESPACE, ($stackPtr + 1), null, true);
		$className      = $tokens[$classNameToken]["content"];
		if (($prev !== false && $tokens[$prev]["code"] !== T_ABSTRACT) && substr($className, -7) === "Actions")
		    {
			$foundFunctions = array();
			$foundCalls     = array();

			// Find all static method calls in the form self::method() in the class.
			$classEnd = $tokens[$stackPtr]["scope_closer"];
			for ($i = ($classNameToken + 1); $i < $classEnd; $i++)
			    {
				if ($tokens[$i]["code"] !== T_DOUBLE_COLON)
				    {
					if ($tokens[$i]["code"] === T_FUNCTION)
					    {
						// Cache the function information.
						$funcName  = $phpcsFile->findNext(T_STRING, ($i + 1));
						$funcScope = $phpcsFile->findPrevious(Tokens::$scopeModifiers, ($i - 1));

						$foundFunctions[$tokens[$funcName]["content"]] = strtolower($tokens[$funcScope]["content"]);
					    }
				    }
				else
				    {
					$prevToken = $phpcsFile->findPrevious(T_WHITESPACE, ($i - 1), null, true);
					if ($tokens[$prevToken]["content"] === "self")
					    {
						$funcNameToken = $phpcsFile->findNext(T_WHITESPACE, ($i + 1), null, true);
						$funcName      = $tokens[$funcNameToken]["content"];
						if ($tokens[$funcNameToken]["code"] !== T_VARIABLE && $funcName{0} !== "_")
						    {
							// We are only interested in function calls.
							// We've found the function, now we need to find it and see if it is
							// public, private or protected. If it starts with an underscore we
							// can assume it is private.
							$foundCalls[$i] = $funcName;
						    }
					    }
				    } //end if
			    } //end for

			$errorClassName = substr($className, 0, -7);

			foreach ($foundCalls as $token => $funcName)
			    {
				// Function was not in this class, might have come from the parent.
				// Either way, we can't really check this.
				if (isset($foundFunctions[$funcName]) === true && $foundFunctions[$funcName] === "public")
				    {
					$error = _("Static calls to public methods in Action classes must not use the self keyword; use") . " %s::%s() " . _("instead");
					$data  = array(
						  $errorClassName,
						  $funcName,
						 );
					$phpcsFile->addError($error, $token, "Found", $data);
				    }
			    }
		    } //end if
	    } //end process()


    } //end class

?>