<?php

/**
 * PHP version 5
 *
 * @package Logics\BuildTools\CodeSniffer\Squiz
 */

namespace Logics\BuildTools\CodeSniffer\Squiz;

use \Logics\BuildTools\CodeSniffer\File;
use \Logics\BuildTools\CodeSniffer\Sniff;
use \Logics\BuildTools\CodeSniffer\Tokens;

/**
 * Squiz_Sniffs_WhiteSpace_ScopeKeywordSpacingSniff.
 *
 * Ensure there is a single space after scope keywords.
 *
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @author    Marc McIntyre <mmcintyre@squiz.net>
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2006-2012 Squiz Pty Ltd (ABN 77 084 670 600)
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 * @version   SVN: $Date: 2016-08-16 23:39:01 +0900 (Tue, 16 Aug 2016) $ $Revision: 26 $
 * @link      $HeadURL: https://open.logics.net.au/buildtools/codesniffer/tags/0.1.3/src/CodeSniffer/Standards/Squiz/Sniffs/WhiteSpace/ScopeKeywordSpacingSniff.php $
 */

class ScopeKeywordSpacingSniff implements Sniff
    {

	/**
	 * Returns an array of tokens this test wants to listen for.
	 *
	 * @return array
	 */

	public function register()
	    {
		$register   = Tokens::$scopeModifiers;
		$register[] = T_STATIC;
		return $register;
	    } //end register()


	/**
	 * Processes this test, when one of its tokens is encountered.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $stackPtr  The position of the current token in the stack passed in $tokens.
	 *
	 * @return void
	 *
	 * @untranslatable Incorrect
	 * @untranslatable \"%s\"
	 */

	public function process(File $phpcsFile, $stackPtr)
	    {
		$tokens = &$phpcsFile->tokens;

		$prevToken = $phpcsFile->findPrevious(T_WHITESPACE, ($stackPtr - 1), null, true);
		$nextToken = $phpcsFile->findNext(T_WHITESPACE, ($stackPtr + 1), null, true);

		// This is late static binding such as static:: OR new static() usage.
		if ($tokens[$stackPtr]["code"] !== T_STATIC || ($tokens[$nextToken]["code"] !== T_DOUBLE_COLON && $tokens[$prevToken]["code"] !== T_NEW))
		    {
			$nextToken = $tokens[($stackPtr + 1)];
			if ($nextToken["code"] !== T_WHITESPACE || strlen($nextToken["content"]) !== 1 || $nextToken["content"] === $phpcsFile->eolChar)
			    {
				$error = _("Scope keyword") . " \"%s\" " . _("must be followed by a single space");
				$data  = array($tokens[$stackPtr]["content"]);
				$phpcsFile->addError($error, $stackPtr, "Incorrect", $data);
			    }
		    }
	    } //end process()


    } //end class

?>
