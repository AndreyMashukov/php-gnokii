<?php

/**
 * PHP version 5
 *
 * @package Logics\Tests\BuildTools\CodeSniffer\Squiz
 */

namespace Logics\Tests\BuildTools\CodeSniffer\Squiz;

use \Logics\Tests\BuildTools\CodeSniffer\AbstractSniffUnitTest;

/**
 * Unit test class for the NonExecutableCode sniff.
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
 * @link      $HeadURL: https://open.logics.net.au/buildtools/codesniffer/tags/0.1.3/tests/Standards/Squiz/PHP/NonExecutableCodeUnitTest.php $
 */

class NonExecutableCodeUnitTest extends AbstractSniffUnitTest
    {

	/**
	 * This functions set expectations from test case files in form of an array
	 *
	 * @return array Expectations of errors and warnings
	 */

	protected function expectations()
	    {
		return array(
			"NonExecutableCodeUnitTest.inc.testfile" => array(
								     "warnings" => array(
										    5   => 1,
										    11  => 1,
										    17  => 1,
										    18  => 1,
										    19  => 2,
										    28  => 1,
										    32  => 1,
										    33  => 2,
										    34  => 2,
										    42  => 1,
										    45  => 1,
										    54  => 1,
										    58  => 1,
										    73  => 1,
										    83  => 1,
										    95  => 1,
										    105 => 1,
										    123 => 1,
										    146 => 1,
										    149 => 1,
										    152 => 1,
										    165 => 1,
										    179 => 1,
										    221 => 1,
										    222 => 1,
										    223 => 1,
										    224 => 2,
										    228 => 1,
										   ),
								    ),
		       );
	    } //end expectations()


    } //end class

?>
