<?php

/**
 * PHP version 5
 *
 * @package Logics\Tests\BuildTools\CodeSniffer\Squiz
 */

namespace Logics\Tests\BuildTools\CodeSniffer\Squiz;

use \Logics\Tests\BuildTools\CodeSniffer\AbstractSniffUnitTest;

/**
 * Unit test class for the ClassDeclaration sniff.
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
 * @link      $HeadURL: https://open.logics.net.au/buildtools/codesniffer/tags/0.1.3/tests/Standards/Squiz/Classes/ClassDeclarationUnitTest.php $
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
			"ClassDeclarationUnitTest.inc.testfile" => array(
								    "errors" => array(
										 10  => 1,
										 15  => 2,
										 18  => 1,
										 22  => 3,
										 23  => 3,
										 24  => 3,
										 27  => 2,
										 28  => 1,
										 30  => 1,
										 32  => 1,
										 36  => 2,
										 40  => 1,
										 41  => 1,
										 45  => 1,
										 48  => 2,
										 51  => 1,
										 54  => 1,
										 56  => 2,
										 61  => 1,
										 65  => 4,
										 69  => 1,
										 71  => 1,
										 75  => 3,
										 80  => 2,
										 83  => 1,
										 86  => 1,
										 91  => 3,
										 95  => 1,
										 98  => 1,
										 103 => 1,
										 106 => 1,
										),
								   ),
		       );
	    } //end expectations()


    } //end class

?>
