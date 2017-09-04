<?php

/**
 * PHP version 5
 *
 * @package Logics\Tests\BuildTools\CodeSniffer\MySource
 */

namespace Logics\Tests\BuildTools\CodeSniffer\MySource;

use \Logics\Tests\BuildTools\CodeSniffer\AbstractSniffUnitTest;

/**
 * Unit test class for the IncludeSystem sniff.
 *
 * A sniff unit test checks a .inc file for expected violations of a single
 * coding standard. Expected errors and warnings are stored in this class.
 *
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2006-2012 Squiz Pty Ltd (ABN 77 084 670 600)
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 * @version   SVN: $Date: 2016-08-16 23:39:01 +0900 (Tue, 16 Aug 2016) $ $Revision: 26 $
 * @link      $HeadURL: https://open.logics.net.au/buildtools/codesniffer/tags/0.1.3/tests/Standards/MySource/Channels/IncludeSystemUnitTest.php $
 */

class IncludeSystemUnitTest extends AbstractSniffUnitTest
    {

	/**
	 * This functions set expectations from test case files in form of an array
	 *
	 * @return array Expectations of errors and warnings
	 */

	protected function expectations()
	    {
		return array(
			"IncludeSystemUnitTest.inc.testfile" => array(
								 "errors" => array(
									      9  => 1,
									      14 => 1,
									      24 => 1,
									      27 => 1,
									      28 => 1,
									      31 => 1,
									      36 => 1,
									      41 => 1,
									      60 => 1,
									      69 => 1,
									      88 => 1,
									     ),
								),
		       );
	    } //end expectations()


    } //end class

?>
