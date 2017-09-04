<?php

/**
 * PHP version 5
 *
 * @package Logics\Tests\BuildTools\CodeSniffer\Generic
 */

namespace Logics\Tests\BuildTools\CodeSniffer\Generic;

use \Logics\Tests\BuildTools\CodeSniffer\AbstractSniffUnitTest;

/**
 * Unit test class for the OpeningFunctionBraceKernighanRitchie sniff.
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
 * @link      $HeadURL: https://open.logics.net.au/buildtools/codesniffer/tags/0.1.3/tests/Standards/Generic/Functions/OpeningFunctionBraceKernighanRitchieUnitTest.php $
 */

class OpeningFunctionBraceKernighanRitchieUnitTest extends AbstractSniffUnitTest
    {

	/**
	 * This functions set expectations from test case files in form of an array
	 *
	 * @return array Expectations of errors and warnings
	 */

	protected function expectations()
	    {
		return array(
			"OpeningFunctionBraceKernighanRitchieUnitTest.inc.testfile" => array(
											"errors" => array(
												     9   => 1,
												     13  => 1,
												     17  => 1,
												     29  => 1,
												     33  => 1,
												     37  => 1,
												     53  => 1,
												     58  => 1,
												     63  => 1,
												     77  => 1,
												     82  => 1,
												     87  => 1,
												     104 => 1,
												    ),
										       ),
		       );
	    } //end expectations()


    } //end class

?>
