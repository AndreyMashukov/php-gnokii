<?php

/**
 * PHP version 5
 *
 * @package Logics\Tests\BuildTools\CodeSniffer\PSR2
 */

namespace Logics\Tests\BuildTools\CodeSniffer\PSR2;

use \Logics\Tests\BuildTools\CodeSniffer\AbstractSniffUnitTest;

/**
 * Unit test class for the ClassDeclaration sniff.
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
 * @link      $HeadURL: https://open.logics.net.au/buildtools/codesniffer/tags/0.1.3/tests/Standards/PSR2/Classes/ClassDeclarationUnitTest.php $
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
										 2  => 1,
										 7  => 3,
										 12 => 1,
										 13 => 1,
										 17 => 1,
										 19 => 2,
										 20 => 1,
										 21 => 1,
										 22 => 1,
										 25 => 1,
										 27 => 1,
										 28 => 1,
										 29 => 1,
										 34 => 1,
										 35 => 2,
										 44 => 1,
										 45 => 1,
										 63 => 1,
										),
								   ),
		       );
	    } //end expectations()


    } //end class

?>
