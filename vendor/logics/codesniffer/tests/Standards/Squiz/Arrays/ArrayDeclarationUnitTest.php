<?php

/**
 * PHP version 5
 *
 * @package Logics\Tests\BuildTools\CodeSniffer\Squiz
 */

namespace Logics\Tests\BuildTools\CodeSniffer\Squiz;

use \Logics\Tests\BuildTools\CodeSniffer\AbstractSniffUnitTest;

/**
 * Unit test class for the ArrayDeclaration sniff.
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
 * @link      $HeadURL: https://open.logics.net.au/buildtools/codesniffer/tags/0.1.3/tests/Standards/Squiz/Arrays/ArrayDeclarationUnitTest.php $
 */

class ArrayDeclarationUnitTest extends AbstractSniffUnitTest
    {

	/**
	 * This functions set expectations from test case files in form of an array
	 *
	 * @return array Expectations of errors and warnings
	 */

	protected function expectations()
	    {
		return array(
			"ArrayDeclarationUnitTest.inc.testfile" => array(
								    "errors" => array(
										 7   => 2,
										 9   => 2,
										 22  => 1,
										 23  => 1,
										 24  => 1,
										 25  => 1,
										 31  => 1,
										 35  => 1,
										 36  => 1,
										 41  => 1,
										 46  => 1,
										 47  => 1,
										 50  => 1,
										 51  => 1,
										 53  => 1,
										 56  => 1,
										 58  => 1,
										 61  => 1,
										 62  => 1,
										 63  => 1,
										 64  => 1,
										 65  => 1,
										 66  => 3,
										 70  => 1,
										 76  => 2,
										 77  => 1,
										 78  => 7,
										 79  => 2,
										 81  => 2,
										 82  => 4,
										 87  => 1,
										 88  => 1,
										 92  => 1,
										 97  => 1,
										 100 => 1,
										 101 => 1,
										 102 => 1,
										 105 => 1,
										 106 => 1,
										 107 => 1,
										 125 => 1,
										 126 => 1,
										 141 => 1,
										 144 => 1,
										 146 => 1,
										 148 => 1,
										 151 => 1,
										 157 => 1,
										 174 => 3,
										 179 => 1,
										),
								   ),
		       );
	    } //end expectations()


    } //end class

?>