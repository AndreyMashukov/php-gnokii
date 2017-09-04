<?php

/**
 * PHP version 5
 *
 * @package Logics\BuildTools\CodeSniffer\Squiz
 */

namespace Logics\BuildTools\CodeSniffer\Squiz;

use \Logics\BuildTools\CodeSniffer\File;
use \Logics\BuildTools\CodeSniffer\Sniff;

/**
 * Squiz_Sniffs_PHP_DisallowSizeFunctionsInLoopsSniff.
 *
 * Bans the use of size-based functions in loop conditions.
 *
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2006-2012 Squiz Pty Ltd (ABN 77 084 670 600)
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 * @version   SVN: $Date: 2016-08-16 23:39:01 +0900 (Tue, 16 Aug 2016) $ $Revision: 26 $
 * @link      $HeadURL: https://open.logics.net.au/buildtools/codesniffer/tags/0.1.3/src/CodeSniffer/Standards/Squiz/Sniffs/PHP/DisallowSizeFunctionsInLoopsSniff.php $
 *
 * @untranslatable PHP
 * @untranslatable JS
 * @untranslatable sizeof
 * @untranslatable strlen
 * @untranslatable count
 * @untranslatable length
 */

class DisallowSizeFunctionsInLoopsSniff implements Sniff
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
	 * An array of functions we don't want in the condition of loops.
	 *
	 * @var array
	 */
	protected $forbiddenFunctions = array(
					 "PHP" => array(
						   "sizeof",
						   "strlen",
						   "count",
						  ),
					 "JS"  => array("length"),
					);

	/**
	 * Returns an array of tokens this test wants to listen for.
	 *
	 * @return array
	 */

	public function register()
	    {
		return array(
			T_WHILE,
			T_FOR,
		       );
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
	 *
	 * @untranslatable JS
	 * @untranslatable object.
	 * @untranslatable Found
	 * @untranslatable %s
	 */

	public function process(File $phpcsFile, $stackPtr)
	    {
		$tokens       = &$phpcsFile->tokens;
		$tokenizer    = $phpcsFile->tokenizerType;
		$openBracket  = $tokens[$stackPtr]["parenthesis_opener"];
		$closeBracket = $tokens[$stackPtr]["parenthesis_closer"];

		if ($tokens[$stackPtr]["code"] === T_FOR)
		    {
			// We only want to check the condition in FOR loops.
			$start = $phpcsFile->findNext(T_SEMICOLON, ($openBracket + 1));
			$end   = $phpcsFile->findPrevious(T_SEMICOLON, ($closeBracket - 1));
		    }
		else
		    {
			$start = $openBracket;
			$end   = $closeBracket;
		    }

		for ($i = ($start + 1); $i < $end; $i++)
		    {
			if ($tokens[$i]["code"] === T_STRING && in_array($tokens[$i]["content"], $this->forbiddenFunctions[$tokenizer]) === true)
			    {
				$functionName = $tokens[$i]["content"];
				if ($tokenizer === "JS")
				    {
					// Needs to be in the form object.function to be valid.
					$prev         = $phpcsFile->findPrevious(T_WHITESPACE, ($i - 1), null, true);
					$functionName = ($prev === false || $tokens[$prev]["code"] !== T_OBJECT_OPERATOR) ? false : "object." . $functionName;
				    }
				else
				    {
					// Make sure it isn't a member var.
					$functionName = ($tokens[($i - 1)]["code"] === T_OBJECT_OPERATOR) ? false : "()";
				    }

				if ($functionName !== false)
				    {
					$error = _("The use of") . " %s " .
						 _("inside a loop condition is not allowed; " .
						   "assign the return value to a variable and use the variable in the loop condition instead");
					$data  = array($functionName);
					$phpcsFile->addError($error, $i, "Found", $data);
				    }
			    } //end if
		    } //end for
	    } //end process()


    } //end class

?>