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
 * ForbiddenFunctionsSniff.
 *
 * Discourages the use of alias functions that are kept in PHP for compatibility
 * with older versions. Can be used to forbid the use of any function.
 *
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @author    Marc McIntyre <mmcintyre@squiz.net>
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2006-2012 Squiz Pty Ltd (ABN 77 084 670 600)
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 * @version   SVN: $Date: 2016-08-16 23:39:01 +0900 (Tue, 16 Aug 2016) $ $Revision: 26 $
 * @link      $HeadURL: https://open.logics.net.au/buildtools/codesniffer/tags/0.1.3/src/CodeSniffer/Standards/Generic/Sniffs/PHP/ForbiddenFunctionsSniff.php $
 *
 * @untranslatable count
 * @untranslatable unset
 */

class ForbiddenFunctionsSniff implements Sniff
    {

	/**
	 * A list of forbidden functions with their alternatives.
	 *
	 * The value is NULL if no alternative exists. IE, the
	 * function should just not be used.
	 *
	 * @var array(string => string|null)
	 */
	protected $forbiddenFunctions = array(
					 "sizeof" => "count",
					 "delete" => "unset",
					);

	/**
	 * A cache of forbidden function names, for faster lookups.
	 *
	 * @var array(string)
	 */
	protected $forbiddenFunctionNames = array();

	/**
	 * If true, forbidden functions will be considered regular expressions.
	 *
	 * @var bool
	 */
	protected $patternMatch = false;

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
	 * @untranslatable /i
	 */

	public function register()
	    {
		// Everyone has had a chance to figure out what forbidden functions
		// they want to check for, so now we can cache out the list.
		$this->forbiddenFunctionNames = array_keys($this->forbiddenFunctions);

		if ($this->patternMatch === true)
		    {
			foreach ($this->forbiddenFunctionNames as $i => $name)
			    {
				$this->forbiddenFunctionNames[$i] = "/" . $name . "/i";
			    }
		    }

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
	 *
	 * @untranslatable print
	 */

	public function process(File $phpcsFile, $stackPtr)
	    {
		$tokens = &$phpcsFile->tokens;

		$ignore = array(
			   T_DOUBLE_COLON,
			   T_OBJECT_OPERATOR,
			   T_FUNCTION,
			   T_CONST,
			  );

		$reserved = array("print");

		$prevToken = $phpcsFile->findPrevious(T_WHITESPACE, ($stackPtr - 1), null, true);
		$function  = strtolower($tokens[$stackPtr]["content"]);
		$nextToken = $phpcsFile->findNext(T_WHITESPACE, ($stackPtr + 1), null, true);
		// Check if is a call to a PHP function.
		if (in_array($tokens[$prevToken]["code"], $ignore) === false &&
		    ($tokens[$nextToken]["code"] === T_OPEN_PARENTHESIS || in_array($function, $reserved) === true))
		    {
			$pattern = null;

			if ($this->patternMatch === true)
			    {
				$count   = 0;
				$pattern = preg_replace($this->forbiddenFunctionNames, $this->forbiddenFunctionNames, $function, 1, $count);

				if ($count !== 0)
				    {
					// Remove the pattern delimiters and modifier.
					$pattern = substr($pattern, 1, -2);
					$this->addError($phpcsFile, $stackPtr, $function, $pattern);
				    }
			    }
			else if (in_array($function, $this->forbiddenFunctionNames) === true)
			    {
				$this->addError($phpcsFile, $stackPtr, $function, $pattern);
			    }
		    } //end if
	    } //end process()


	/**
	 * Generates the error or warning for this sniff.
	 *
	 * @param File   $phpcsFile The file being scanned.
	 * @param int    $stackPtr  The position of the forbidden function in the token array.
	 * @param string $function  The name of the forbidden function.
	 * @param string $pattern   The pattern used for the match.
	 *
	 * @return void
	 *
	 * @untranslatable %s()
	 * @untranslatable Found
	 * @untranslatable Discouraged
	 * @untranslatable WithAlternative
	 */

	protected function addError(File $phpcsFile, $stackPtr, $function, $pattern = null)
	    {
		$data  = array($function);
		$error = _("The use of function") . " %s() " . _("is") . " ";
		if ($this->error === true)
		    {
			$type   = "Found";
			$error .= _("forbidden");
		    }
		else
		    {
			$type   = "Discouraged";
			$error .= _("discouraged");
		    }

		if ($pattern === null)
		    {
			$pattern = $function;
		    }

		if ($this->forbiddenFunctions[$pattern] !== null)
		    {
			$type  .= "WithAlternative";
			$data[] = $this->forbiddenFunctions[$pattern];
			$error .= "; " . _("use") . " %s() " . _("instead");
		    }

		if ($this->error === true)
		    {
			$phpcsFile->addError($error, $stackPtr, $type, $data);
		    }
		else
		    {
			$phpcsFile->addWarning($error, $stackPtr, $type, $data);
		    }
	    } //end addError()


    } //end class

?>
