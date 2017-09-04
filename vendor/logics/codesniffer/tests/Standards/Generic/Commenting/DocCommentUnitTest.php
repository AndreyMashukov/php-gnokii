<?php

/**
 * PHP version 5
 *
 * @package Logics\Tests\BuildTools\CodeSniffer\Generic
 */

namespace Logics\Tests\BuildTools\CodeSniffer\Generic;

use \Logics\Tests\BuildTools\CodeSniffer\AbstractSniffUnitTest;

/**
 * Unit test class for DocCommentSniff.
 *
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2006-2012 Squiz Pty Ltd (ABN 77 084 670 600)
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 * @version   SVN: $Date: 2016-08-16 23:39:01 +0900 (Tue, 16 Aug 2016) $ $Revision: 26 $
 * @link      $HeadURL: https://open.logics.net.au/buildtools/codesniffer/tags/0.1.3/tests/Standards/Generic/Commenting/DocCommentUnitTest.php $
 */

class DocCommentUnitTest extends AbstractSniffUnitTest
    {

	/**
	 * This functions set expectations from test case files in form of an array
	 *
	 * @return array Expectations of errors and warnings
	 */

	protected function expectations()
	    {
		$expectations = array(
				 "errors" => array(
					      14  => 1,
					      16  => 1,
					      18  => 1,
					      23  => 1,
					      26  => 1,
					      30  => 1,
					      32  => 1,
					      38  => 2,
					      40  => 1,
					      41  => 1,
					      51  => 1,
					      54  => 1,
					      58  => 1,
					      60  => 2,
					      67  => 1,
					      69  => 2,
					      80  => 1,
					      81  => 2,
					      88  => 1,
					      91  => 1,
					      95  => 1,
					      156 => 1,
					      158 => 1,
					      170 => 3,
					      171 => 3,
					     ),
				);

		return array(
			"DocCommentUnitTest.inc.testfile" => $expectations,
			"DocCommentUnitTest.js.testfile"  => $expectations,
		       );
	    } //end expectations()


    } //end class

?>
