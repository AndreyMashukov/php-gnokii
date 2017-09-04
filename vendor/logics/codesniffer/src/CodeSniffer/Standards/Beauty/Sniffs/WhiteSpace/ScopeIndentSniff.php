<?php

/**
 * PHP version 5
 *
 * @package Logics\BuildTools\CodeSniffer\Beauty
 */

namespace Logics\BuildTools\CodeSniffer\Beauty;

use \Logics\BuildTools\CodeSniffer\File;
use \Logics\BuildTools\CodeSniffer\Generic\ScopeIndentSniff as Generic_ScopeIndentSniff;

/**
 * ScopeIndentSniff.
 *
 * Checks that control structures are structured correctly, and their content
 * is indented correctly. This sniff will throw errors if tabs are used
 * for indentation rather than spaces.
 *
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @author    Marc McIntyre <mmcintyre@squiz.net>
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2006-2012 Squiz Pty Ltd (ABN 77 084 670 600)
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   http://matrix.squiz.net/developer/tools/php_cs/licence BSD Licence
 * @version   SVN: $Date: 2016-08-16 23:39:01 +0900 (Tue, 16 Aug 2016) $ $Revision: 26 $
 * @link      $HeadURL: https://open.logics.net.au/buildtools/codesniffer/tags/0.1.3/src/CodeSniffer/Standards/Beauty/Sniffs/WhiteSpace/ScopeIndentSniff.php $
 */

class ScopeIndentSniff extends Generic_ScopeIndentSniff
    {

	/**
	 * Construct new sniff instance
	 *
	 * @return void
	 */

	public function __construct()
	    {
		$this->indent = 8;
		$this->exact  = false;
	    } //end __construct()


	/**
	 * Check scope closer
	 *
	 * @param File  $phpcsFile     All the tokens found in the document.
	 * @param array $tokens        All tokens
	 * @param int   $i             The position of the current token in the stack passed in $tokens.
	 * @param mixed $checkToken    Check token
	 * @param array $openScopes    Open scopes
	 * @param int   $currentIndent Current indent
	 * @param bool  $exact         Exact closer
	 *
	 * @return void
	 */

	protected function checkScopeCloser(File $phpcsFile, array &$tokens, $i, &$checkToken, array &$openScopes, &$currentIndent, &$exact)
	    {
		unset($exact);
		parent::checkScopeCloser($phpcsFile, $tokens, $i, $checkToken, $openScopes, $currentIndent, $exact);
	    } //end checkScopeCloser()


    } //end class

?>
