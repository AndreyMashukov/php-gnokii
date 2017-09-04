<?php

/**
 * PHP version 5
 *
 * @package Logics\Tests\BuildTools\CodeSniffer\Beauty
 */

namespace Logics\Tests\BuildTools\CodeSniffer\Beauty;

use \Logics\Tests\BuildTools\CodeSniffer\AbstractSniffUnitTest;

/**
 * Unit test class for the ClassDeclaration sniff.
 *
 * A sniff unit test checks a .inc file for expected violations of a single
 * coding standard. Expected errors and warnings are stored in this class.
 *
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 * @version   SVN: $Date: 2016-08-16 23:39:01 +0900 (Tue, 16 Aug 2016) $ $Revision: 26 $
 * @link      $HeadURL: https://open.logics.net.au/buildtools/codesniffer/tags/0.1.3/tests/Standards/Beauty/Classes/ClassDeclarationUnitTest.php $
 */

class ClassDeclarationUnitTest extends AbstractSniffUnitTest
    {

	/**
	 * This functions set expectations from test case files in form of an array
	 *
	 * @return array Expectations of errors and warnings
	 */

	protected function expectations()
	    {
		return array(
			"ClassDeclarationUnitTest.1.inc.testfile" => array(
								      "warnings" => array(3 => 1),
								     ),
			"ClassDeclarationUnitTest.2.inc.testfile" => array(
								      "errors" => array(
										   3  => 1,
										   7  => 1,
										   11 => 1,
										   14 => 1,
										  ),
								     ),
		       );
	    } //end expectations()


    } //end class

?>
