<?php

/**
 * PHP version 5
 *
 * @package Logics\Tests\BuildTools\CodeSniffer\Squiz
 */

namespace Logics\Tests\BuildTools\CodeSniffer\Squiz;

use \Logics\Tests\BuildTools\CodeSniffer\AbstractSniffUnitTest;

/**
 * Unit test class for the SwitchDeclaration sniff.
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
 * @link      $HeadURL: https://open.logics.net.au/buildtools/codesniffer/tags/0.1.3/tests/Standards/Squiz/ControlStructures/SwitchDeclarationUnitTest.php $
 */

class SwitchDeclarationUnitTest extends AbstractSniffUnitTest
    {

	/**
	 * This functions set expectations from test case files in form of an array
	 *
	 * @return array Expectations of errors and warnings
	 */

	protected function expectations()
	    {
		$expectations = array(
				 "errors" => array(
					      27  => 1,
					      29  => 1,
					      34  => 1,
					      36  => 1,
					      44  => 1,
					      48  => 1,
					      52  => 1,
					      54  => 1,
					      55  => 1,
					      56  => 1,
					      58  => 1,
					      59  => 1,
					      61  => 1,
					      62  => 1,
					      79  => 1,
					      85  => 2,
					      88  => 2,
					      89  => 2,
					      92  => 1,
					      95  => 3,
					      99  => 1,
					      116 => 1,
					      122 => 1,
					      127 => 2,
					      134 => 2,
					      135 => 1,
					      138 => 1,
					      143 => 1,
					      144 => 1,
					      147 => 1,
					      165 => 1,
					      172 => 1,
					      176 => 2,
					      180 => 1,
					      192 => 2,
					      196 => 1,
					      223 => 1,
					     ),
				);

		return array(
			"SwitchDeclarationUnitTest.inc.testfile" => $expectations,
			"SwitchDeclarationUnitTest.js.testfile"  => $expectations,
		       );
	    } //end expectations()


    } //end class

?>
