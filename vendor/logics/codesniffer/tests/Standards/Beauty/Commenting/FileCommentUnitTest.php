<?php

/**
 * PHP version 5
 *
 * @package Logics\Tests\BuildTools\CodeSniffer\Beauty
 */

namespace Logics\Tests\BuildTools\CodeSniffer\Beauty;

use \Logics\Tests\BuildTools\CodeSniffer\AbstractSniffUnitTest;

/**
 * Unit test class for the FileComment sniff.
 *
 * A sniff unit test checks a .inc file for expected violations of a single
 * coding standard. Expected errors and warnings are stored in this class.
 *
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @author    Marc McIntyre <mmcintyre@squiz.net>
 * @author    Anastasia Bashkirtseva <anastasia@logics.net.au>
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2006-2012 Squiz Pty Ltd (ABN 77 084 670 600)
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 * @version   SVN: $Date: 2016-09-19 20:10:11 +0900 (Mon, 19 Sep 2016) $ $Revision: 44 $
 * @link      $HeadURL: https://open.logics.net.au/buildtools/codesniffer/tags/0.1.3/tests/Standards/Beauty/Commenting/FileCommentUnitTest.php $
 */

class FileCommentUnitTest extends AbstractSniffUnitTest
    {

	/**
	 * This functions set expectations from test case files in form of an array
	 *
	 * @return array Expectations of errors and warnings
	 */

	protected function expectations()
	    {
		return array(
			"FileCommentUnitTest.1.inc.testfile" => array(
								 "errors" => array(1 => 1),
								),
			"FileCommentUnitTest.2.inc.testfile" => array(
								 "errors" => array(),
								),
			"FileCommentUnitTest.3.inc.testfile" => array(
								 "errors" => array(2 => 1),
								),
			"FileCommentUnitTest.4.inc.testfile" => array(
								 "errors"   => array(
										7  => 1,
										8  => 2,
										9  => 2,
										10 => 1,
										11 => 1,
										13 => 1,
									       ),
								 "warnings" => array(
										12 => 1,
										13 => 1,
										14 => 1,
										16 => 1,
									       ),
								),
			"FileCommentUnitTest.5.inc.testfile" => array(
								 "errors"   => array(
										6 => 2,
										7 => 1,
										8 => 1,
									       ),
								 "warnings" => array(6 => 1),
								),
		       );
	    } //end expectations()


    } //end class

?>
