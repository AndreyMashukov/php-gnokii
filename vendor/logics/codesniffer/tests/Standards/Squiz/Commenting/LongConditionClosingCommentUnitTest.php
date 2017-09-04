<?php

/**
 * PHP version 5
 *
 * @package Logics\Tests\BuildTools\CodeSniffer\Squiz
 */

namespace Logics\Tests\BuildTools\CodeSniffer\Squiz;

use \Logics\Tests\BuildTools\CodeSniffer\AbstractSniffUnitTest;

/**
 * Unit test class for the LongConditionClosingComment sniff.
 *
 * A sniff unit test checks a .inc file for expected violations of a single
 * coding standard. Expected errors and warnings are stored in this class.
 *
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @author    Marc McIntyre <mmcintyre@squiz.net>
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2006-2012 Squiz Pty Ltd (ABN 77 084 670 600)
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 * @version   SVN: $Date: 2016-08-16 23:39:01 +0900 (Tue, 16 Aug 2016) $ $Revision: 26 $
 * @link      $HeadURL: https://open.logics.net.au/buildtools/codesniffer/tags/0.1.3/tests/Standards/Squiz/Commenting/LongConditionClosingCommentUnitTest.php $
 */

class LongConditionClosingCommentUnitTest extends AbstractSniffUnitTest
    {

	/**
	 * This functions set expectations from test case files in form of an array
	 *
	 * @return array Expectations of errors and warnings
	 */

	protected function expectations()
	    {
		return array(
			"LongConditionClosingCommentUnitTest.inc.testfile" => array(
									       "errors" => array(
											    49  => 1,
											    99  => 1,
											    146 => 1,
											    192 => 1,
											    215 => 1,
											    238 => 1,
											    261 => 1,
											    286 => 1,
											    309 => 1,
											    332 => 1,
											    355 => 1,
											    378 => 1,
											    493 => 1,
											    531 => 1,
											    536 => 1,
											    540 => 1,
											    562 => 1,
											    601 => 1,
											    629 => 1,
											    663 => 1,
											   ),
									      ),
			"LongConditionClosingCommentUnitTest.js.testfile"  => array(
									       "errors" => array(
											    47  => 1,
											    97  => 1,
											    144 => 1,
											    190 => 1,
											    213 => 1,
											    238 => 1,
											    261 => 1,
											    284 => 1,
											    307 => 1,
											    401 => 1,
											    439 => 1,
											    444 => 1,
											   ),
									      ),
		       );
	    } //end expectations()


    } //end class

?>
