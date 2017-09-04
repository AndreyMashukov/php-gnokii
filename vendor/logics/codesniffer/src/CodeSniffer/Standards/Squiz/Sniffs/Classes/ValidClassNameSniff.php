<?php

/**
 * PHP version 5
 *
 * @package Logics\BuildTools\CodeSniffer\Squiz
 */

namespace Logics\BuildTools\CodeSniffer\Squiz;

use \Logics\BuildTools\CodeSniffer\CodeSniffer;
use \Logics\BuildTools\CodeSniffer\File;
use \Logics\BuildTools\CodeSniffer\Sniff;

/**
 * ValidClassNameSniff
 *
 * Ensures classes are in camel caps, and the first letter is capitalised
 *
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @author    Marc McIntyre <mmcintyre@squiz.net>
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2006-2012 Squiz Pty Ltd (ABN 77 084 670 600)
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 * @version   SVN: $Date: 2016-08-16 23:39:01 +0900 (Tue, 16 Aug 2016) $ $Revision: 26 $
 * @link      $HeadURL: https://open.logics.net.au/buildtools/codesniffer/tags/0.1.3/src/CodeSniffer/Standards/Squiz/Sniffs/Classes/ValidClassNameSniff.php $
 */

class ValidClassNameSniff implements Sniff
    {

	/**
	 * Returns an array of tokens this test wants to listen for.
	 *
	 * @return array
	 */

	public function register()
	    {
		return array(
			T_CLASS,
			T_INTERFACE,
		       );
	    } //end register()


	/**
	 * Processes this test, when one of its tokens is encountered.
	 *
	 * @param File $phpcsFile The current file being processed.
	 * @param int  $stackPtr  The position of the current token in the stack passed in $tokens.
	 *
	 * @return void
	 *
	 * @untranslatable MissingBrace
	 * @untranslatable NotCamelCaps
	 * @untranslatable %s
	 * @untranslatable \"%s\"
	 */

	public function process(File $phpcsFile, $stackPtr)
	    {
		$tokens = &$phpcsFile->tokens;

		if (isset($tokens[$stackPtr]["scope_opener"]) === false)
		    {
			$error = _("Possible parse error:") . " %s " . _("missing opening or closing brace");
			$data  = array($tokens[$stackPtr]["content"]);
			$phpcsFile->addWarning($error, $stackPtr, "MissingBrace", $data);
		    }
		else
		    {
			// Determine the name of the class or interface. Note that we cannot
			// simply look for the first T_STRING because a class name
			// starting with the number will be multiple tokens.
			$opener    = $tokens[$stackPtr]["scope_opener"];
			$nameStart = $phpcsFile->findNext(T_WHITESPACE, ($stackPtr + 1), $opener, true);
			$nameEnd   = $phpcsFile->findNext(T_WHITESPACE, $nameStart, $opener);
			$name      = trim($phpcsFile->getTokensAsString($nameStart, ($nameEnd - $nameStart)));

			// Check for camel caps format.
			$valid = CodeSniffer::isCamelCaps($name, true, true, false);
			if ($valid === false)
			    {
				$type  = ucfirst($tokens[$stackPtr]["content"]);
				$error = "%s " . _("name") . " \"%s\" " . _("is not in camel caps format");
				$data  = array(
					  $type,
					  $name,
					 );
				$phpcsFile->addError($error, $stackPtr, "NotCamelCaps", $data);
			    }
		    } //end if
	    } //end process()


    } //end class

?>
