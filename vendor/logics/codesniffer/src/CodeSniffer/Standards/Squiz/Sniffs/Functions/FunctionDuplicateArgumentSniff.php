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
 * Squiz_Sniffs_Functions_FunctionDuplicateArgumentSpacingSniff.
 *
 * Checks that duplicate arguments are not used in function declarations.
 *
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @author    Marc McIntyre <mmcintyre@squiz.net>
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2006-2012 Squiz Pty Ltd (ABN 77 084 670 600)
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 * @version   SVN: $Date: 2016-08-16 23:39:01 +0900 (Tue, 16 Aug 2016) $ $Revision: 26 $
 * @link      $HeadURL: https://open.logics.net.au/buildtools/codesniffer/tags/0.1.3/src/CodeSniffer/Standards/Squiz/Sniffs/Functions/FunctionDuplicateArgumentSniff.php $
 */

class FunctionDuplicateArgumentSniff implements Sniff
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
	 * @untranslatable Found
	 * @untranslatable \"%s\"
	 */

	public function process(File $phpcsFile, $stackPtr)
	    {
		$tokens = &$phpcsFile->tokens;

		$openBracket  = $tokens[$stackPtr]["parenthesis_opener"];
		$closeBracket = $tokens[$stackPtr]["parenthesis_closer"];

		$foundVariables = array();
		for ($i = ($openBracket + 1); $i < $closeBracket; $i++)
		    {
			if ($tokens[$i]["code"] === T_VARIABLE)
			    {
				$variable = $tokens[$i]["content"];
				if (in_array($variable, $foundVariables) === true)
				    {
					$error = _("Variable") . " \"%s\" " . _("appears more than once in function declaration");
					$data  = array($variable);
					$phpcsFile->addError($error, $i, "Found", $data);
				    }
				else
				    {
					$foundVariables[] = $variable;
				    }
			    }
		    }
	    } //end process()


    } //end class

?>
