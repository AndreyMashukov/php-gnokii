<?php

/**
 * PHP version 5
 *
 * @package Logics\Tests\BuildTools\CodeSniffer\PEAR
 */

namespace Logics\Tests\BuildTools\CodeSniffer\PEAR;

use \Logics\Tests\BuildTools\CodeSniffer\AbstractSniffUnitTest;

/**
 * Unit test class for the FunctionCallSignature sniff.
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
 * @link      $HeadURL: https://open.logics.net.au/buildtools/codesniffer/tags/0.1.3/tests/Standards/PEAR/Functions/FunctionCallSignatureUnitTest.php $
 */

class FunctionCallSignatureUnitTest extends AbstractSniffUnitTest
    {

	/**
	 * This functions set expectations from test case files in form of an array
	 *
	 * @return array Expectations of errors and warnings
	 */

	protected function expectations()
	    {
		return array(
			"FunctionCallSignatureUnitTest.inc.testfile" => array(
									 "errors" => array(
										      5   => 1,
										      6   => 2,
										      7   => 1,
										      8   => 1,
										      9   => 2,
										      10  => 3,
										      17  => 1,
										      18  => 1,
										      31  => 1,
										      34  => 1,
										      43  => 2,
										      57  => 1,
										      59  => 1,
										      63  => 1,
										      64  => 1,
										      82  => 1,
										      93  => 1,
										      100 => 1,
										      106 => 2,
										      119 => 1,
										      120 => 1,
										      129 => 1,
										      137 => 1,
										      142 => 2,
										      171 => 1,
										      180 => 1,
										      181 => 1,
										      194 => 1,
										      205 => 1,
										      213 => 2,
										      215 => 2,
										     ),
									),
			"FunctionCallSignatureUnitTest.js.testfile"  => array(
									 "errors" => array(
										      5  => 1,
										      6  => 2,
										      7  => 1,
										      8  => 1,
										      9  => 2,
										      10 => 3,
										      17 => 1,
										      18 => 1,
										      21 => 1,
										      24 => 1,
										      28 => 2,
										      30 => 2,
										      35 => 1,
										      49 => 1,
										      51 => 1,
										      54 => 1,
										      70 => 1,
										      71 => 1,
										     ),
									),
		       );
	    } //end expectations()


    } //end class

?>
