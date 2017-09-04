<?php

/**
 * PHP version 5
 *
 * @package Logics\Tests\BuildTools\CodeSniffer\Squiz
 */

namespace Logics\Tests\BuildTools\CodeSniffer\Squiz;

use \Logics\Tests\BuildTools\CodeSniffer\AbstractSniffUnitTest;

/**
 * Unit test class for the MultiLineFunctionDeclaration sniff.
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
 * @link      $HeadURL: https://open.logics.net.au/buildtools/codesniffer/tags/0.1.3/tests/Standards/Squiz/Functions/MultiLineFunctionDeclarationUnitTest.php $
 */

class MultiLineFunctionDeclarationUnitTest extends AbstractSniffUnitTest
    {

	/**
	 * This functions set expectations from test case files in form of an array
	 *
	 * @return array Expectations of errors and warnings
	 */

	protected function expectations()
	    {
		return array(
			"MultiLineFunctionDeclarationUnitTest.inc.testfile" => array(
										"errors" => array(
											     2  => 1,
											     3  => 1,
											     4  => 3,
											     5  => 1,
											     7  => 1,
											     11 => 1,
											     12 => 1,
											     13 => 1,
											     16 => 1,
											     33 => 1,
											     36 => 1,
											     43 => 2,
											     48 => 1,
											     81 => 1,
											     82 => 2,
											     88 => 2,
											    ),
									       ),
		       );
	    } //end expectations()


    } //end class

?>
