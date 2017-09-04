<?php

/**
 * PHP version 5
 *
 * @package Logics\Tests\BuildTools\CodeSniffer\Generic
 */

namespace Logics\Tests\BuildTools\CodeSniffer\Generic;

use \Logics\Tests\BuildTools\CodeSniffer\AbstractSniffUnitTest;

/**
 * Unit test class for the ScopeIndent sniff.
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
 * @link      $HeadURL: https://open.logics.net.au/buildtools/codesniffer/tags/0.1.3/tests/Standards/Generic/WhiteSpace/ScopeIndentUnitTest.php $
 */

class ScopeIndentUnitTest extends AbstractSniffUnitTest
    {

	/**
	 * Get a list of CLI values to set befor the file is tested.
	 *
	 * @param string $testFile The name of the file being tested.
	 *
	 * @return array
	 *
	 * @untranslatable ScopeIndentUnitTest.2.inc.testfile
	 * @untranslatable --tab-width=4
	 */

	public function getCliValues($testFile)
	    {
		// Tab width setting is only needed for the tabbed file.
		if ($testFile === "ScopeIndentUnitTest.2.inc.testfile")
		    {
			return array("--tab-width=4");
		    }
		else
		    {
			return array();
		    }
	    } //end getCliValues()


	/**
	 * This functions set expectations from test case files in form of an array
	 *
	 * @return array Expectations of errors and warnings
	 */

	protected function expectations()
	    {
		$expectations = array(
				 "errors" => array(
					      7   => 1,
					      10  => 1,
					      13  => 1,
					      17  => 1,
					      20  => 1,
					      24  => 1,
					      25  => 1,
					      27  => 1,
					      28  => 1,
					      29  => 1,
					      30  => 1,
					      58  => 1,
					      123 => 1,
					      224 => 1,
					      225 => 1,
					      279 => 1,
					      280 => 1,
					      281 => 1,
					      284 => 1,
					      336 => 1,
					      349 => 1,
					      380 => 1,
					      386 => 1,
					      388 => 1,
					      390 => 1,
					      391 => 1,
					      397 => 1,
					      419 => 1,
					      420 => 1,
					      465 => 1,
					      472 => 1,
					      473 => 1,
					      496 => 1,
					      524 => 1,
					      544 => 1,
					      545 => 1,
					      639 => 1,
					      660 => 1,
					      662 => 1,
					      802 => 1,
					      803 => 1,
					      823 => 1,
					      856 => 1,
					      868 => 1,
					      869 => 1,
					      871 => 1,
					      873 => 1,
					     ),
				);

		return array(
			"ScopeIndentUnitTest.1.inc.testfile" => $expectations,
			"ScopeIndentUnitTest.2.inc.testfile" => $expectations,
			"ScopeIndentUnitTest.3.inc.testfile" => array(
								 "errors" => array(
									      6  => 1,
									      7  => 1,
									      10 => 1,
									     ),
								),
			"ScopeIndentUnitTest.1.js.testfile"  => array(
								 "errors" => array(
									      6   => 1,
									      14  => 1,
									      21  => 1,
									      30  => 1,
									      31  => 1,
									      33  => 1,
									      34  => 1,
									      39  => 1,
									      42  => 1,
									      47  => 1,
									      59  => 1,
									      60  => 1,
									      75  => 1,
									      120 => 1,
									      121 => 1,
									      122 => 1,
									      141 => 1,
									      155 => 1,
									      168 => 1,
									      184 => 1,
									     ),
								),
		       );
	    } //end expectations()


    } //end class

?>