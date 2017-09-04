<?php

/**
 * PHP version 5
 *
 * @package Logics\Tests\BuildTools\CodeSniffer\Generic
 */

namespace Logics\Tests\BuildTools\CodeSniffer\Generic;

use \Logics\Tests\BuildTools\CodeSniffer\AbstractSniffUnitTest;

/**
 * Unit test class for the EmptyStatement sniff.
 *
 * A sniff unit test checks a .inc file for expected violations of a single
 * coding standard. Expected errors and warnings are stored in this class.
 *
 * @author    Manuel Pichler <mapi@manuel-pichler.de>
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2007-2008 Manuel Pichler. All rights reserved.
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version   SVN: $Date: 2016-08-16 23:39:01 +0900 (Tue, 16 Aug 2016) $ $Revision: 26 $
 * @link      $HeadURL: https://open.logics.net.au/buildtools/codesniffer/tags/0.1.3/tests/Standards/Generic/CodeAnalysis/EmptyStatementUnitTest.php $
 */

class EmptyStatementUnitTest extends AbstractSniffUnitTest
    {

	/**
	 * This functions set expectations from test case files in form of an array
	 *
	 * @return array Expectations of errors and warnings
	 */

	protected function expectations()
	    {
		return array(
			"EmptyStatementUnitTest.inc.testfile" => array(
								  "errors"   => array(
										 3  => 1,
										 15 => 1,
										 17 => 1,
										 19 => 1,
										 30 => 1,
										 35 => 1,
										 41 => 1,
										 47 => 1,
										 52 => 1,
										 55 => 1,
										 72 => 2,
										),
								  "warnings" => array(
										 64 => 1,
										 68 => 1,
										),
								 ),
		       );
	    } //end expectations()


    } //end class

?>
