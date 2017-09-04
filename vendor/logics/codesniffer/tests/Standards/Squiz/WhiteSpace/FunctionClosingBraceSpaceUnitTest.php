<?php

/**
 * PHP version 5
 *
 * @package Logics\Tests\BuildTools\CodeSniffer\Squiz
 */

namespace Logics\Tests\BuildTools\CodeSniffer\Squiz;

use \Logics\Tests\BuildTools\CodeSniffer\AbstractSniffUnitTest;

/**
 * Unit test class for the FunctionClosingBraceSpace sniff.
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
 * @link      $HeadURL: https://open.logics.net.au/buildtools/codesniffer/tags/0.1.3/tests/Standards/Squiz/WhiteSpace/FunctionClosingBraceSpaceUnitTest.php $
 */

class FunctionClosingBraceSpaceUnitTest extends AbstractSniffUnitTest
    {

	/**
	 * This functions set expectations from test case files in form of an array
	 *
	 * @return array Expectations of errors and warnings
	 */

	protected function expectations()
	    {
		return array(
			"FunctionClosingBraceSpaceUnitTest.inc.testfile" => array(
									     "errors" => array(
											  10 => 1,
											  21 => 1,
											  28 => 1,
											  29 => 1,
											  31 => 1,
											  39 => 1,
											 ),
									    ),
			"FunctionClosingBraceSpaceUnitTest.js.testfile"  => array(
									     "errors" => array(
											  13  => 1,
											  25  => 1,
											  32  => 1,
											  53  => 1,
											  59  => 1,
											  67  => 1,
											  84  => 1,
											  128 => 1,
											 ),
									    ),
		       );
	    } //end expectations()


    } //end class

?>