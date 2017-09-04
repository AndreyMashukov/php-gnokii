<?php

/**
 * PHP version 5
 *
 * @package Logics\Tests\BuildTools\CodeSniffer\Generic
 */

namespace Logics\Tests\BuildTools\CodeSniffer\Generic;

use \Logics\Tests\BuildTools\CodeSniffer\AbstractSniffUnitTest;

/**
 * Unit test class for the OneTraitPerFile sniff.
 *
 * @author    Alexander Obuhovich <aik.bold@gmail.com>
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2010-2014 Alexander Obuhovich
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 * @version   SVN: $Date: 2016-08-16 23:39:01 +0900 (Tue, 16 Aug 2016) $ $Revision: 26 $
 * @link      $HeadURL: https://open.logics.net.au/buildtools/codesniffer/tags/0.1.3/tests/Standards/Generic/Files/OneTraitPerFileUnitTest.php $
 */

class OneTraitPerFileUnitTest extends AbstractSniffUnitTest
    {

	/**
	 * Should this test be skipped for some reason.
	 *
	 * @return bool
	 */

	protected function shouldSkipTest()
	    {
		return version_compare(PHP_VERSION, "5.4.0", "<");
	    } //end shouldSkipTest()


	/**
	 * This functions set expectations from test case files in form of an array
	 *
	 * @return array Expectations of errors and warnings
	 */

	protected function expectations()
	    {
		return array(
			"OneTraitPerFileUnitTest.inc.testfile" => array(
								   "errors" => array(
										6  => 1,
										10 => 1,
									       ),
								  ),
		       );
	    } //end expectations()


    } //end class

?>
