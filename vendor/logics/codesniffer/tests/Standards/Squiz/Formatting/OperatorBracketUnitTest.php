<?php

/**
 * PHP version 5
 *
 * @package Logics\Tests\BuildTools\CodeSniffer\Squiz
 */

namespace Logics\Tests\BuildTools\CodeSniffer\Squiz;

use \Logics\Tests\BuildTools\CodeSniffer\AbstractSniffUnitTest;

/**
 * Unit test class for the OperatorBracket sniff.
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
 * @link      $HeadURL: https://open.logics.net.au/buildtools/codesniffer/tags/0.1.3/tests/Standards/Squiz/Formatting/OperatorBracketUnitTest.php $
 */

class OperatorBracketUnitTest extends AbstractSniffUnitTest
    {

	/**
	 * This functions set expectations from test case files in form of an array
	 *
	 * @return array Expectations of errors and warnings
	 */

	protected function expectations()
	    {
		return array(
			"OperatorBracketUnitTest.inc.testfile" => array(
								   "errors" => array(
										3   => 1,
										6   => 1,
										9   => 1,
										12  => 1,
										15  => 1,
										18  => 2,
										20  => 1,
										25  => 1,
										28  => 1,
										31  => 1,
										34  => 1,
										37  => 1,
										40  => 1,
										43  => 2,
										45  => 1,
										47  => 5,
										48  => 1,
										50  => 2,
										55  => 2,
										56  => 1,
										63  => 2,
										64  => 1,
										67  => 1,
										86  => 1,
										90  => 1,
										109 => 1,
										130 => 1,
									       ),
								  ),
			"OperatorBracketUnitTest.js.testfile"  => array(
								   "errors" => array(
										5  => 1,
										8  => 1,
										11 => 1,
										14 => 1,
										24 => 1,
										30 => 1,
										33 => 1,
										36 => 1,
										39 => 1,
										46 => 1,
										47 => 1,
										63 => 1,
									       ),
								  ),
		       );
	    } //end expectations()


    } //end class

?>
