<?php

/**
 * PHP version 5
 *
 * @package Logics\BuildTools\CodeSniffer\PSR2
 */

namespace Logics\BuildTools\CodeSniffer\PSR2;

use \Logics\BuildTools\CodeSniffer\File;
use \Logics\BuildTools\CodeSniffer\Sniff;

/**
 * PSR2_Sniffs_Namespaces_NamespaceDeclarationSniff.
 *
 * Ensures namespaces are declared correctly.
 *
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2006-2012 Squiz Pty Ltd (ABN 77 084 670 600)
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 * @version   SVN: $Date: 2016-08-16 23:39:01 +0900 (Tue, 16 Aug 2016) $ $Revision: 26 $
 * @link      $HeadURL: https://open.logics.net.au/buildtools/codesniffer/tags/0.1.3/src/CodeSniffer/Standards/PSR2/Sniffs/Namespaces/NamespaceDeclarationSniff.php $
 */

class NamespaceDeclarationSniff implements Sniff
    {

	/**
	 * Returns an array of tokens this test wants to listen for.
	 *
	 * @return array
	 */

	public function register()
	    {
		return array(T_NAMESPACE);
	    } //end register()


	/**
	 * Processes this test, when one of its tokens is encountered.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $stackPtr  The position of the current token in the stack passed in $tokens.
	 *
	 * @return void
	 *
	 * @untranslatable BlankLineAfter
	 */

	public function process(File $phpcsFile, $stackPtr)
	    {
		$tokens = &$phpcsFile->tokens;

		for ($i = ($stackPtr + 1); $i < $phpcsFile->numTokens; $i++)
		    {
			if ($tokens[$i]["line"] !== $tokens[$stackPtr]["line"])
			    {
				break;
			    }
		    }

		// The $i var now points to the first token on the line after the
		// namespace declaration, which must be a blank line.
		$next = $phpcsFile->findNext(T_WHITESPACE, $i, $phpcsFile->numTokens, true);
		if ($tokens[$next]["line"] === $tokens[$i]["line"])
		    {
			$error = _("There must be one blank line after the namespace declaration");
			$phpcsFile->addError($error, $stackPtr, "BlankLineAfter");
		    }
	    } //end process()


    } //end class

?>
