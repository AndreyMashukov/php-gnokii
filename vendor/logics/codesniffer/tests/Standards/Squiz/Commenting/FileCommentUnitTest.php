<?php

/**
 * PHP version 5
 *
 * @package Logics\Tests\BuildTools\CodeSniffer\Squiz
 */

namespace Logics\Tests\BuildTools\CodeSniffer\Squiz;

use \Logics\Tests\BuildTools\CodeSniffer\AbstractSniffUnitTest;

/**
 * Unit test class for FileCommentSniff.
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
 * @link      $HeadURL: https://open.logics.net.au/buildtools/codesniffer/tags/0.1.3/tests/Standards/Squiz/Commenting/FileCommentUnitTest.php $
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
								 "errors" => array(
									      1  => 1,
									      22 => 2,
									      23 => 1,
									      24 => 2,
									      25 => 2,
									      26 => 1,
									      27 => 2,
									      28 => 2,
									      32 => 2,
									     ),
								),
			"FileCommentUnitTest.1.js.testfile"  => array(
								 "errors" => array(
									      1  => 1,
									      22 => 2,
									      23 => 1,
									      24 => 2,
									      25 => 2,
									      26 => 1,
									      27 => 2,
									      28 => 2,
									      32 => 2,
									     ),
								),
		       );
	    } //end expectations()


    } //end class

?>