<?php

/**
 * PHP version 5
 *
 * @package Logics\Tests\BuildTools\CodeSniffer\Generic
 */

namespace Logics\Tests\BuildTools\CodeSniffer\Generic;

use \Logics\Tests\BuildTools\CodeSniffer\AbstractSniffUnitTest;

/**
 * Unit test class for the ValidConstantName sniff.
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
 * @link      $HeadURL: https://open.logics.net.au/buildtools/codesniffer/tags/0.1.3/tests/Standards/Generic/NamingConventions/UpperCaseConstantNameUnitTest.php $
 */

class UpperCaseConstantNameUnitTest extends AbstractSniffUnitTest
    {

	/**
	 * This functions set expectations from test case files in form of an array
	 *
	 * @return array Expectations of errors and warnings
	 */

	protected function expectations()
	    {
		$errors = array(
			   8   => 1,
			   10  => 1,
			   15  => 1,
			   25  => 1,
			   26  => 1,
			   27  => 1,
			   28  => 1,
			   29  => 1,
			   32  => 1,
			   35  => 1,
			   100 => 1,
			  );

		// The trait insteadof test will only work in PHP version where traits exist
		// and will throw errors in earlier versions.
		if (version_compare(PHP_VERSION, "5.4.0") < 0)
		    {
			$errors[131] = 3;
		    }

		return array("UpperCaseConstantNameUnitTest.inc.testfile" => array("errors" => $errors));
	    } //end expectations()


    } //end class

?>
