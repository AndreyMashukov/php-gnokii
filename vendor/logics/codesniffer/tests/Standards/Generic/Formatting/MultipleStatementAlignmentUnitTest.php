<?php

/**
 * PHP version 5
 *
 * @package Logics\Tests\BuildTools\CodeSniffer\Generic
 */

namespace Logics\Tests\BuildTools\CodeSniffer\Generic;

use \Logics\Tests\BuildTools\CodeSniffer\AbstractSniffUnitTest;

/**
 * Unit test class for the MultipleStatementAlignment sniff.
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
 * @link      $HeadURL: https://open.logics.net.au/buildtools/codesniffer/tags/0.1.3/tests/Standards/Generic/Formatting/MultipleStatementAlignmentUnitTest.php $
 */

class MultipleStatementAlignmentUnitTest extends AbstractSniffUnitTest
    {

	/**
	 * This functions set expectations from test case files in form of an array
	 *
	 * @return array Expectations of errors and warnings
	 */

	protected function expectations()
	    {
		return array(
			"MultipleStatementAlignmentUnitTest.inc.testfile" => array(
									      "warnings" => array(
											     11  => 1,
											     12  => 1,
											     23  => 1,
											     24  => 1,
											     26  => 1,
											     27  => 1,
											     37  => 1,
											     38  => 1,
											     48  => 1,
											     50  => 1,
											     51  => 1,
											     61  => 1,
											     62  => 1,
											     64  => 1,
											     65  => 1,
											     71  => 1,
											     78  => 1,
											     79  => 1,
											     86  => 1,
											     92  => 1,
											     93  => 1,
											     94  => 1,
											     95  => 1,
											     123 => 1,
											     124 => 1,
											     126 => 1,
											     129 => 1,
											     154 => 1,
											     161 => 1,
											     178 => 1,
											     179 => 1,
											     182 => 1,
											     206 => 1,
											     207 => 1,
											    ),
									     ),
			"MultipleStatementAlignmentUnitTest.js.testfile"  => array(
									      "warnings" => array(
											     11  => 1,
											     12  => 1,
											     23  => 1,
											     24  => 1,
											     26  => 1,
											     27  => 1,
											     37  => 1,
											     38  => 1,
											     48  => 1,
											     50  => 1,
											     51  => 1,
											     61  => 1,
											     62  => 1,
											     64  => 1,
											     65  => 1,
											     71  => 1,
											     78  => 1,
											     79  => 1,
											     81  => 1,
											     82  => 1,
											     83  => 1,
											     85  => 1,
											     86  => 1,
											     100 => 1,
											    ),
									     ),
		       );
	    } //end expectations()


    } //end class

?>