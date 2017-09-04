<?php

/**
 * PHP version 5
 *
 * @package Logics\Tests\BuildTools\CodeSniffer\Generic
 */

namespace Logics\Tests\BuildTools\CodeSniffer\Generic;

use \Logics\Tests\BuildTools\CodeSniffer\AbstractSniffUnitTest;

/**
 * Unit test class for the CamelCapsFunctionName sniff.
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
 * @link      $HeadURL: https://open.logics.net.au/buildtools/codesniffer/tags/0.1.3/tests/Standards/Generic/NamingConventions/CamelCapsFunctionNameUnitTest.php $
 */

class CamelCapsFunctionNameUnitTest extends AbstractSniffUnitTest
    {

	/**
	 * This functions set expectations from test case files in form of an array
	 *
	 * @return array Expectations of errors and warnings
	 */

	protected function expectations()
	    {
		$errors = array(
			   10 => 0,
			   11 => 1,
			   12 => 1,
			   13 => 1,
			   16 => 0,
			   17 => 1,
			   19 => 1,
			   20 => 1,
			   21 => 1,
			   24 => 0,
			   25 => 1,
			   30 => 0,
			   31 => 1,
			   50 => 1,
			   52 => 1,
			   53 => 1,
			   57 => 1,
			   58 => 1,
			   59 => 1,
			   60 => 1,
			   61 => 1,
			   62 => 1,
			   63 => 1,
			   64 => 1,
			   65 => 1,
			   66 => 1,
			   67 => 1,
			   68 => 1,
			   69 => 1,
			   71 => 1,
			   72 => 1,
			   73 => 1,
			   74 => 1,
			  );

		// The trait tests will only work in PHP version where traits exist and
		// will throw errors in earlier versions.
		if (version_compare(PHP_VERSION, "5.4.0") < 0)
		    {
			$errors[95] = 1;
		    }

		return array("CamelCapsFunctionNameUnitTest.inc.testfile" => array("errors" => $errors));
	    } //end expectations()


    } //end class

?>
