<?php

/**
 * PHP version 5
 *
 * @package Logics\BuildTools\CodeSniffer\Squiz
 */

namespace Logics\BuildTools\CodeSniffer\Squiz;

use \Logics\BuildTools\CodeSniffer\Generic\EmptyStatementSniff as Generic_EmptyStatementSniff;

/**
 * This sniff class detects empty statement.
 *
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2006-2012 Squiz Pty Ltd (ABN 77 084 670 600)
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 * @version   SVN: $Date: 2016-08-16 23:39:01 +0900 (Tue, 16 Aug 2016) $ $Revision: 26 $
 * @link      $HeadURL: https://open.logics.net.au/buildtools/codesniffer/tags/0.1.3/src/CodeSniffer/Standards/Squiz/Sniffs/CodeAnalysis/EmptyStatementSniff.php $
 */

class EmptyStatementSniff extends Generic_EmptyStatementSniff
    {

	/**
	 * List of block tokens that this sniff covers.
	 *
	 * The key of this hash identifies the required token while the boolean value says mark an error or mark a warning.
	 *
	 * @var array
	 */
	protected $checkedTokens = array(
				    T_DO      => true,
				    T_ELSE    => true,
				    T_ELSEIF  => true,
				    T_FOR     => true,
				    T_FOREACH => true,
				    T_IF      => true,
				    T_SWITCH  => true,
				    T_WHILE   => true,
				   );

    } //end class

?>
