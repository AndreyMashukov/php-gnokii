<?php

/**
 * PHP version 5
 *
 * @package Logics\BuildTools\CodeSniffer\PEAR
 */

namespace Logics\BuildTools\CodeSniffer\PEAR;

use \Logics\BuildTools\CodeSniffer\Generic\ScopeIndentSniff as Generic_ScopeIndentSniff;

/**
 * ScopeIndentSniff.
 *
 * Checks that control structures are structured correctly, and their content
 * is indented correctly.
 *
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @author    Marc McIntyre <mmcintyre@squiz.net>
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2006-2012 Squiz Pty Ltd (ABN 77 084 670 600)
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 * @version   SVN: $Date: 2016-08-16 23:39:01 +0900 (Tue, 16 Aug 2016) $ $Revision: 26 $
 * @link      $HeadURL: https://open.logics.net.au/buildtools/codesniffer/tags/0.1.3/src/CodeSniffer/Standards/PEAR/Sniffs/WhiteSpace/ScopeIndentSniff.php $
 */

class ScopeIndentSniff extends Generic_ScopeIndentSniff
    {

	/**
	 * Any scope openers that should not cause an indent.
	 *
	 * @var array(int)
	 */
	protected $nonIndentingScopes = array(T_SWITCH);

    } //end class

?>
