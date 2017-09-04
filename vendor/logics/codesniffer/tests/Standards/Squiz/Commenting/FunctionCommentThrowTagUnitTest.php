<?php

/**
 * PHP version 5
 *
 * @package Logics\Tests\BuildTools\CodeSniffer\Squiz
 */

namespace Logics\Tests\BuildTools\CodeSniffer\Squiz;

use \Logics\Tests\BuildTools\CodeSniffer\AbstractSniffUnitTest;

/**
 * Unit test class for FunctionCommentThrowTagSniff.
 *
 * Verifies that :
 * <ul>
 *  <li>A @throws tag exists for a function that throws exceptions.</li>
 *  <li>The number of @throws tags and the number of throw tokens matches.</li>
 *  <li>The exception type in comment matches the token.</li>
 * </ul>
 *
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @author    Marc McIntyre <mmcintyre@squiz.net>
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2006-2012 Squiz Pty Ltd (ABN 77 084 670 600)
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 * @version   SVN: $Date: 2016-08-16 23:39:01 +0900 (Tue, 16 Aug 2016) $ $Revision: 26 $
 * @link      $HeadURL: https://open.logics.net.au/buildtools/codesniffer/tags/0.1.3/tests/Standards/Squiz/Commenting/FunctionCommentThrowTagUnitTest.php $
 */

class FunctionCommentThrowTagUnitTest extends AbstractSniffUnitTest
    {

	/**
	 * This functions set expectations from test case files in form of an array
	 *
	 * @return array Expectations of errors and warnings
	 */

	protected function expectations()
	    {
		return array(
			"FunctionCommentThrowTagUnitTest.inc.testfile" => array(
									   "errors" => array(
											9   => 1,
											21  => 1,
											35  => 1,
											47  => 1,
											61  => 2,
											106 => 1,
											123 => 1,
											215 => 1,
										       ),
									  ),
		       );
	    } //end expectations()


    } //end class

?>
