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
 * Verifies that :
 * <ul>
 *  <li>A doc comment exists.</li>
 *  <li>Short description must start with a capital letter and end with a period.</li>
 *  <li>There must be one blank newline after the short description.</li>
 *  <li>A PHP version is specified.</li>
 *  <li>Check the order of the tags.</li>
 *  <li>Check the indentation of each tag.</li>
 *  <li>Check required and optional tags and the format of their content.</li>
 * </ul>
 *
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @author    Marc McIntyre <mmcintyre@squiz.net>
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2006-2012 Squiz Pty Ltd (ABN 77 084 670 600)
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 * @version   SVN: $Date: 2016-08-16 23:39:01 +0900 (Tue, 16 Aug 2016) $ $Revision: 26 $
 * @link      $HeadURL: https://open.logics.net.au/buildtools/codesniffer/tags/0.1.3/tests/Standards/PEAR/Commenting/FileCommentUnitTest.php $
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
			"FileCommentUnitTest.inc.testfile" => array(
							       "errors"   => array(
									      21 => 1,
									      23 => 2,
									      24 => 1,
									      26 => 1,
									      28 => 1,
									      29 => 1,
									      30 => 1,
									      31 => 1,
									      32 => 2,
									      33 => 1,
									      34 => 1,
									      35 => 1,
									      40 => 1,
									     ),
							       "warnings" => array(
									      29 => 1,
									      30 => 1,
									      34 => 1,
									      40 => 1,
									     ),
							      ),
		       );
	    } //end expectations()


    } //end class

?>
