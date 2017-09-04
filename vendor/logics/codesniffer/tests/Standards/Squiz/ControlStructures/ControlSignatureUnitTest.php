<?php

/**
 * PHP version 5
 *
 * @package Logics\Tests\BuildTools\CodeSniffer\Squiz
 */

namespace Logics\Tests\BuildTools\CodeSniffer\Squiz;

use \Logics\Tests\BuildTools\CodeSniffer\AbstractSniffUnitTest;

/**
 * Unit test class for the ControlSignature sniff.
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
 * @link      $HeadURL: https://open.logics.net.au/buildtools/codesniffer/tags/0.1.3/tests/Standards/Squiz/ControlStructures/ControlSignatureUnitTest.php $
 */

class ControlSignatureUnitTest extends AbstractSniffUnitTest
    {

	/**
	 * This functions set expectations from test case files in form of an array
	 *
	 * @return array Expectations of errors and warnings
	 */

	protected function expectations()
	    {
		return array(
			"ControlSignatureUnitTest.inc.testfile" => array(
								    "errors" => array(
										 9   => 1,
										 14  => 1,
										 20  => 1,
										 22  => 1,
										 32  => 1,
										 36  => 1,
										 44  => 1,
										 48  => 1,
										 56  => 1,
										 60  => 1,
										 68  => 1,
										 72  => 1,
										 84  => 1,
										 88  => 2,
										 100 => 1,
										 104 => 2,
										 116 => 2,
										 120 => 3,
										 122 => 1,
										 126 => 1,
										 130 => 1,
										 134 => 1,
										 139 => 1,
										 148 => 1,
										 152 => 1,
										 158 => 1,
										),
								   ),
			"ControlSignatureUnitTest.js.testfile"  => array(
								    "errors" => array(
										 7   => 1,
										 12  => 1,
										 18  => 1,
										 20  => 1,
										 29  => 1,
										 33  => 1,
										 40  => 1,
										 44  => 1,
										 51  => 1,
										 55  => 1,
										 66  => 1,
										 70  => 2,
										 88  => 2,
										 92  => 3,
										 94  => 1,
										 98  => 1,
										 102 => 1,
										 106 => 1,
										 111 => 1,
										 120 => 1,
										 124 => 1,
										),
								   ),
		       );
	    } //end expectations()


    } //end class

?>
