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
 * UnnecessaryStringConcatSniff.
 *
 * Checks that two strings are not concatenated together; suggests
 * using one string instead.
 *
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2006-2012 Squiz Pty Ltd (ABN 77 084 670 600)
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 * @version   SVN: $Date: 2016-08-16 23:39:01 +0900 (Tue, 16 Aug 2016) $ $Revision: 26 $
 * @link      $HeadURL: https://open.logics.net.au/buildtools/codesniffer/tags/0.1.3/src/CodeSniffer/Standards/Generic/Sniffs/Strings/UnnecessaryStringConcatSniff.php $
 *
 * @untranslatable PHP
 * @untranslatable JS
 */

class UnnecessaryStringConcatSniff implements Sniff
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
	 * If true, an error will be thrown; otherwise a warning.
	 *
	 * @var bool
	 */
	public $error = true;

	/**
	 * Returns an array of tokens this test wants to listen for.
	 *
	 * @return array
	 *
	 * @internalconst T_STRING_CONCAT T_STRING_CONCAT token
	 * @internalconst T_PLUS          T_PLUS token
	 */

	public function register()
	    {
		return array(
			T_STRING_CONCAT,
			T_PLUS,
		       );
	    } //end register()


	/**
	 * Processes this sniff, when one of its tokens is encountered.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $stackPtr  The position of the current token in the stack passed in $tokens.
	 *
	 * @return void
	 *
	 * @internalconst T_STRING_CONCAT T_STRING_CONCAT token
	 *
	 * @untranslatable PHP
	 * @untranslatable JS
	 * @untranslatable Found
	 */

	public function process(File $phpcsFile, $stackPtr)
	    {
		// Work out which type of file this is for.
		$tokens = &$phpcsFile->tokens;
		if (($tokens[$stackPtr]["code"] !== T_STRING_CONCAT || $phpcsFile->tokenizerType !== "JS") &&
		    ($tokens[$stackPtr]["code"] === T_STRING_CONCAT || $phpcsFile->tokenizerType !== "PHP"))
		    {
			$prev = $phpcsFile->findPrevious(T_WHITESPACE, ($stackPtr - 1), null, true);
			$next = $phpcsFile->findNext(T_WHITESPACE, ($stackPtr + 1), null, true);
			if ($prev !== false && $next !== false)
			    {
				$stringTokens = Tokens::$stringTokens;
				if (in_array($tokens[$prev]["code"], $stringTokens) === true && in_array($tokens[$next]["code"], $stringTokens) === true &&
				    $tokens[$prev]["content"][0] === $tokens[$next]["content"][0])
				    {
					// Before we throw an error for PHP, allow strings to be
					// combined if they would have < and ? next to each other because
					// this trick is sometimes required in PHP strings.
					$prevChar = substr($tokens[$prev]["content"], -2, 1);
					$nextChar = $tokens[$next]["content"][1];
					$combined = $prevChar . $nextChar;
					if ($phpcsFile->tokenizerType !== "PHP" ||
					    ($combined !== "?" . ">" && $combined !== "<" . "?"))
					    {
						$error = _("String concat is not required here; use a single string instead");
						if ($this->error === true)
						    {
							$phpcsFile->addError($error, $stackPtr, "Found");
						    }
						else
						    {
							$phpcsFile->addWarning($error, $stackPtr, "Found");
						    }
					    } //end if
				    } //end if
			    } //end if
		    } //end if
	    } //end process()


    } //end class

?>
