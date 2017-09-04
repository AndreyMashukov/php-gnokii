<?php

/**
 * PHP version 5.6
 *
 * @package Logics\BuildTools\CodeSniffer\Beauty
 */

namespace Logics\BuildTools\CodeSniffer\Beauty;

use \Logics\BuildTools\CodeSniffer\File;
use \Logics\BuildTools\CodeSniffer\PEAR\ScopeClosingBraceSniff as PEAR_ScopeClosingBraceSniff;

/**
 * ScopeClosingBraceSniff.
 *
 * Checks that the closing braces of scopes are aligned correctly.
 *
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @author    Marc McIntyre <mmcintyre@squiz.net>
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2006-2012 Squiz Pty Ltd (ABN 77 084 670 600)
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   http://matrix.squiz.net/developer/tools/php_cs/licence BSD Licence
 * @version   SVN: $Date: 2016-08-16 23:39:01 +0900 (Tue, 16 Aug 2016) $ $Revision: 26 $
 * @link      $HeadURL: https://open.logics.net.au/buildtools/codesniffer/tags/0.1.3/src/CodeSniffer/Standards/Beauty/Sniffs/WhiteSpace/ScopeClosingBraceSniff.php $
 */

class ScopeClosingBraceSniff extends PEAR_ScopeClosingBraceSniff
    {

	/**
	 * Check now that the closing brace is lined up correctly.
	 *
	 * @param File  $phpcsFile   All the tokens found in the document.
	 * @param int   $stackPtr    The position of the current token in the stack passed in $tokens.
	 * @param array $tokens      All tokens
	 * @param int   $startColumn Start column
	 * @param int   $scopeEnd    Scope end
	 *
	 * @return void
	 *
	 * @untranslatable BreakIndent
	 * @untranslatable %s
	 * @untranslatable Indent
	 */

	protected function checkCloser(File &$phpcsFile, $stackPtr, array &$tokens, $startColumn, $scopeEnd)
	    {
		// Check now that the closing brace is lined up correctly.
		$braceIndent   = $tokens[$scopeEnd]["column"];
		$isBreakCloser = ($tokens[$scopeEnd]["code"] === T_BREAK);
		if (in_array($tokens[$stackPtr]["code"], array(T_CASE, T_DEFAULT)) === true && $isBreakCloser === true)
		    {
			// BREAK statements should be indented n spaces from the
			// CASE or DEFAULT statement.
			if ($braceIndent !== ($startColumn + $this->indent))
			    {
				$error = _("Case breaking statement indented incorrectly; expected") . " %s " . _("spaces, found") . " %s";
				$data  = array(
					  ($startColumn + $this->indent - 1),
					  ($braceIndent - 1),
					 );
				$phpcsFile->addError($error, $scopeEnd, "BreakIndent", $data);
			    }
		    }
		else
		    {
			if ($braceIndent !== ($startColumn + $this->indent))
			    {
				$error = _("Closing brace indented incorrectly; expected") . " %s " . _("spaces, found") . " %s";
				$data  = array(
					  ($startColumn + $this->indent - 1),
					  ($braceIndent - 1),
					 );
				$phpcsFile->addError($error, $scopeEnd, "Indent", $data);
			    }
		    } //end if
	    } //end checkCloser()


    } //end class

?>
