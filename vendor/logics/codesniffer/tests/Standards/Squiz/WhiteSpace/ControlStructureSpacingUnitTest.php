<?php

/**
 * PHP version 5
 *
 * @package Logics\Tests\BuildTools\CodeSniffer\Squiz
 */

namespace Logics\Tests\BuildTools\CodeSniffer\Squiz;

use \Logics\Tests\BuildTools\CodeSniffer\AbstractSniffUnitTest;

/**
 * Unit test class for the ControlStructureSpacing sniff.
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
 * @link      $HeadURL: https://open.logics.net.au/buildtools/codesniffer/tags/0.1.3/tests/Standards/Squiz/WhiteSpace/ControlStructureSpacingUnitTest.php $
 */

class ControlStructureSpacingUnitTest extends AbstractSniffUnitTest
    {

	/**
	 * This functions set expectations from test case files in form of an array
	 *
	 * @return array Expectations of errors and warnings
	 */

	protected function expectations()
	    {
		return array(
			"ControlStructureSpacingUnitTest.inc.testfile" => array(
									   "errors" => array(
											3   => 1,
											5   => 1,
											8   => 1,
											15  => 1,
											23  => 1,
											74  => 1,
											79  => 1,
											82  => 1,
											83  => 1,
											87  => 1,
											103 => 1,
											113 => 2,
											114 => 2,
											118 => 1,
											150 => 1,
											153 => 1,
											154 => 1,
											157 => 1,
											170 => 1,
											176 => 2,
											179 => 1,
											189 => 1,
										       ),
									  ),
			"ControlStructureSpacingUnitTest.js.testfile"  => array(
									   "errors" => array(
											3  => 1,
											9  => 1,
											15 => 1,
											21 => 1,
											56 => 1,
											61 => 1,
											64 => 1,
											65 => 1,
											68 => 1,
											74 => 2,
											75 => 2,
										       ),
									  ),
		       );
	    } //end expectations()


    } //end class

?>
