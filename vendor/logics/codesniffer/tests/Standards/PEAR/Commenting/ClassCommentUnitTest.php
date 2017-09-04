<?php

/**
 * PHP version 5
 *
 * @package Logics\Tests\BuildTools\CodeSniffer\PEAR
 */

namespace Logics\Tests\BuildTools\CodeSniffer\PEAR;

use \Logics\Tests\BuildTools\CodeSniffer\AbstractSniffUnitTest;

/**
 * Unit test class for ClassCommentSniff.
 *
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @author    Marc McIntyre <mmcintyre@squiz.net>
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2006-2012 Squiz Pty Ltd (ABN 77 084 670 600)
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 * @version   SVN: $Date: 2016-08-16 23:39:01 +0900 (Tue, 16 Aug 2016) $ $Revision: 26 $
 * @link      $HeadURL: https://open.logics.net.au/buildtools/codesniffer/tags/0.1.3/tests/Standards/PEAR/Commenting/ClassCommentUnitTest.php $
 */

class ClassCommentUnitTest extends AbstractSniffUnitTest
    {

	/**
	 * This functions set expectations from test case files in form of an array
	 *
	 * @return array Expectations of errors and warnings
	 */

	protected function expectations()
	    {
		return array(
			"ClassCommentUnitTest.inc.testfile" => array(
								"errors"   => array(
									       4   => 1,
									       15  => 1,
									       51  => 1,
									       63  => 1,
									       65  => 2,
									       66  => 1,
									       68  => 1,
									       70  => 1,
									       71  => 1,
									       72  => 1,
									       74  => 2,
									       75  => 1,
									       76  => 1,
									       77  => 1,
									       85  => 1,
									       96  => 5,
									       106 => 5,
									      ),
								"warnings" => array(
									       71 => 1,
									       73 => 1,
									      ),
							       ),
		       );
	    } //end expectations()


    } //end class

?>
