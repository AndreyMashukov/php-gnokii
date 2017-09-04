<?php

/**
 * PHP version 5
 *
 * @package Logics\Tests\BuildTools\CodeSniffer\Zend
 */

namespace Logics\Tests\BuildTools\CodeSniffer\Zend;

use \Logics\Tests\BuildTools\CodeSniffer\AbstractSniffUnitTest;

/**
 * Unit test class for the ValidVariableName sniff.
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
 * @link      $HeadURL: https://open.logics.net.au/buildtools/codesniffer/tags/0.1.3/tests/Standards/Zend/NamingConventions/ValidVariableNameUnitTest.php $
 */

class ValidVariableNameUnitTest extends AbstractSniffUnitTest
    {

	/**
	 * This functions set expectations from test case files in form of an array
	 *
	 * @return array Expectations of errors and warnings
	 */

	protected function expectations()
	    {
		return array(
			"ValidVariableNameUnitTest.inc.testfile" => array(
								     "errors"   => array(
										    3  => 1,
										    5  => 1,
										    11 => 1,
										    13 => 1,
										    17 => 1,
										    19 => 1,
										    23 => 1,
										    25 => 1,
										    29 => 1,
										    31 => 1,
										    36 => 1,
										    38 => 1,
										    42 => 1,
										    44 => 1,
										    48 => 1,
										    50 => 1,
										    61 => 1,
										    67 => 1,
										    72 => 1,
										    74 => 1,
										    75 => 1,
										    76 => 1,
										    79 => 1,
										    90 => 1,
										    92 => 1,
										    96 => 1,
										    99 => 1,
										   ),
								     "warnings" => array(
										    6  => 1,
										    14 => 1,
										    20 => 1,
										    26 => 1,
										    32 => 1,
										    39 => 1,
										    45 => 1,
										    51 => 1,
										    64 => 1,
										    70 => 1,
										    73 => 1,
										    76 => 1,
										    79 => 1,
										    82 => 1,
										    94 => 1,
										   ),
								    ),
		       );
	    } //end expectations()


    } //end class

?>
