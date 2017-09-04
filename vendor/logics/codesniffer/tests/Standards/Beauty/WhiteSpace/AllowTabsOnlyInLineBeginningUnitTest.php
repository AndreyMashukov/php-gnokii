<?php

/**
 * PHP version 5
 *
 * @package Logics\Tests\BuildTools\CodeSniffer\Beauty
 */

namespace Logics\Tests\BuildTools\CodeSniffer\Beauty;

use \Logics\Tests\BuildTools\CodeSniffer\AbstractSniffUnitTest;

/**
 * Unit test class for the AllowTabsOnlyInLineBeginning sniff.
 *
 * A sniff unit test checks a .inc file for expected violations of a single
 * coding standard. Expected errors and warnings are stored in this class.
 *
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @author    Marc McIntyre <mmcintyre@squiz.net>
 * @author    Anastasia Bashkirtseva <anastasia@logics.net.au>
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2006-2012 Squiz Pty Ltd (ABN 77 084 670 600)
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 * @version   SVN: $Date: 2016-08-16 23:39:01 +0900 (Tue, 16 Aug 2016) $ $Revision: 26 $
 * @link      $HeadURL: https://open.logics.net.au/buildtools/codesniffer/tags/0.1.3/tests/Standards/Beauty/WhiteSpace/AllowTabsOnlyInLineBeginningUnitTest.php $
 *
 * @runTestsInSeparateProcesses
 */

class AllowTabsOnlyInLineBeginningUnitTest extends AbstractSniffUnitTest
    {

	/**
	 * Tests the extending classes Sniff class.
	 *
	 * @return void
	 */

	public function testSniff()
	    {
		define("EXCEPTION_TAB_WIDTH_CANNOT_BE_ZERO", 1);

		parent::testSniff();
	    } //end testSniff()


	/**
	 * Get a list of CLI values to set befor the file is tested.
	 *
	 * @param string $testFile The name of the file being tested.
	 *
	 * @return array
	 *
	 * @untranslatable AllowTabsOnlyInLineBeginningUnitTest.1.inc.testfile
	 * @untranslatable --tab-width=8
	 * @untranslatable --tab-width=0
	 */

	public function getCliValues($testFile)
	    {
		if ($testFile === "AllowTabsOnlyInLineBeginningUnitTest.1.inc.testfile")
		    {
			return array("--tab-width=8");
		    }
		else
		    {
			return array("--tab-width=0");
		    }
	    } //end getCliValues()


	/**
	 * This functions set expectations from test case files in form of an array
	 *
	 * @return array Expectations of errors and warnings
	 */

	protected function expectations()
	    {
		return array(
			"AllowTabsOnlyInLineBeginningUnitTest.1.inc.testfile" => array(
										  "errors" => array(
											       4  => 1,
											       6  => 1,
											       8  => 1,
											       11 => 1,
											      )
										 ),
			"AllowTabsOnlyInLineBeginningUnitTest.2.inc.testfile" => array(
										  "errors" => array(1 => 1),
										 ),
		       );
	    } //end expectations()


    } //end class

?>
