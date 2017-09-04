<?php

/**
 * PHP version 5
 *
 * @package Logics\Tests\BuildTools\CodeSniffer\PEAR
 */

namespace Logics\Tests\BuildTools\CodeSniffer\PEAR;

use \Logics\Tests\BuildTools\CodeSniffer\AbstractSniffUnitTest;

/**
 * Unit test class for FunctionCommentSniff.
 *
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @author    Marc McIntyre <mmcintyre@squiz.net>
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2006-2012 Squiz Pty Ltd (ABN 77 084 670 600)
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 * @version   SVN: $Date: 2016-08-16 23:39:01 +0900 (Tue, 16 Aug 2016) $ $Revision: 26 $
 * @link      $HeadURL: https://open.logics.net.au/buildtools/codesniffer/tags/0.1.3/tests/Standards/PEAR/Commenting/FunctionCommentUnitTest.php $
 */

class FunctionCommentUnitTest extends AbstractSniffUnitTest
    {

	/**
	 * This functions set expectations from test case files in form of an array
	 *
	 * @return array Expectations of errors and warnings
	 */

	protected function expectations()
	    {
		return array(
			"FunctionCommentUnitTest.inc.testfile" => array(
								   "errors" => array(
										5   => 1,
										10  => 1,
										12  => 1,
										13  => 1,
										14  => 1,
										15  => 1,
										28  => 1,
										76  => 1,
										87  => 1,
										103 => 1,
										109 => 1,
										112 => 1,
										122 => 1,
										123 => 2,
										124 => 2,
										125 => 1,
										126 => 1,
										137 => 1,
										138 => 1,
										139 => 1,
										152 => 1,
										155 => 1,
										165 => 1,
										172 => 1,
										183 => 1,
										190 => 2,
										206 => 1,
										234 => 1,
										272 => 1,
										301 => 1,
										305 => 1,
										307 => 1,
									       ),
								  ),
		       );
	    } //end expectations()


    } //end class

?>
