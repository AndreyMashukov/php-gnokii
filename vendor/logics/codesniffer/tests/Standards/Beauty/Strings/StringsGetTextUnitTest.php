<?php

/**
 * PHP version 5
 *
 * @package Logics\Tests\BuildTools\CodeSniffer\Beauty
 */

namespace Logics\Tests\BuildTools\CodeSniffer\Beauty;

use \Logics\Tests\BuildTools\CodeSniffer\AbstractSniffUnitTest;

/**
 * Unit test class for the UseSingleQuotesForEscapeCharactersOnly sniff.
 *
 * A sniff unit test checks a .inc file for expected violations of a single
 * coding standard. Expected errors and warnings are stored in this class.
 *
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @author    Marc McIntyre <mmcintyre@squiz.net>
 * @author    Ekaterina Bizimova <kate@logics.net.au>
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2006-2012 Squiz Pty Ltd (ABN 77 084 670 600)
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 * @version   SVN: $Date: 2016-08-16 23:39:01 +0900 (Tue, 16 Aug 2016) $ $Revision: 26 $
 * @link      $HeadURL: https://open.logics.net.au/buildtools/codesniffer/tags/0.1.3/tests/Standards/Beauty/Strings/StringsGetTextUnitTest.php $
 */

class StringsGetTextUnitTest extends AbstractSniffUnitTest
    {

	/**
	 * This functions set expectations from test case files in form of an array
	 *
	 * @return array Expectations of errors and warnings
	 */

	protected function expectations()
	    {
		return array(
			"StringsGetTextUnitTest.1.inc.testfile" => array(
								    "errors" => array(
										 23  => 1,
										 34  => 1,
										 42  => 1,
										 46  => 2,
										 52  => 1,
										 55  => 1,
										 56  => 1,
										 70  => 1,
										 88  => 1,
										 89  => 1,
										 110 => 1,
										 129 => 1,
										)
								   ),
			"StringsGetTextUnitTest.2.inc.testfile" => array(
								    "errors" => array(
										 23  => 1,
										 34  => 1,
										 101 => 1,
										)
								   ),
			"StringsGetTextUnitTest.3.inc.testfile" => array(
								    "errors" => array(
										 23  => 1,
										 34  => 1,
										 101 => 1,
										)
								   ),
			"StringsGetTextUnitTest.4.inc.testfile" => array(
								    "errors" => array(
										 22 => 1,
										 23 => 1,
										 24 => 1,
										 25 => 1,
										)
								   )
		       );
	    } //end expectations()


    } //end class

?>