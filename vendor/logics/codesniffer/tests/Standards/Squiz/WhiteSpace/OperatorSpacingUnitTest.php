<?php

/**
 * PHP version 5
 *
 * @package Logics\Tests\BuildTools\CodeSniffer\Squiz
 */

namespace Logics\Tests\BuildTools\CodeSniffer\Squiz;

use \Logics\Tests\BuildTools\CodeSniffer\AbstractSniffUnitTest;

/**
 * Unit test class for the OperatorSpacing sniff.
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
 * @link      $HeadURL: https://open.logics.net.au/buildtools/codesniffer/tags/0.1.3/tests/Standards/Squiz/WhiteSpace/OperatorSpacingUnitTest.php $
 */

class OperatorSpacingUnitTest extends AbstractSniffUnitTest
    {

	/**
	 * This functions set expectations from test case files in form of an array
	 *
	 * @return array Expectations of errors and warnings
	 */

	protected function expectations()
	    {
		$errors = array(
			   4  => 1,
			   5  => 2,
			   6  => 1,
			   7  => 1,
			   8  => 2,
			   11 => 1,
			   12 => 2,
			   13 => 1,
			   14 => 1,
			   15 => 2,
			   18 => 1,
			   19 => 2,
			   20 => 1,
			   21 => 1,
			   22 => 2,
			   25 => 1,
			   26 => 2,
			   27 => 1,
			   28 => 1,
			   29 => 2,
			   32 => 1,
			   33 => 2,
			   34 => 1,
			   35 => 1,
			   36 => 2,
			   40 => 2,
			   42 => 2,
			   44 => 2,
			   45 => 1,
			   46 => 2,
			  );

		$additional = array(
			       53 => 2,
			       54 => 1,
			       59 => 10,
			       64 => 1,
			       77 => 4,
			       78 => 1,
			       79 => 1,
			       80 => 2,
			       81 => 1,
			       84 => 6,
			       85 => 6,
			       87 => 4,
			       88 => 5,
			       90 => 4,
			       91 => 5,
			      );

		return array(
			"OperatorSpacingUnitTest.inc.testfile" => array("errors" => $errors + $additional),
			"OperatorSpacingUnitTest.js.testfile"  => array("errors" => $errors),
		       );
	    } //end expectations()


    } //end class

?>
