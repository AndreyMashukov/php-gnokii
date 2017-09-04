<?php

/**
 * PHP version 5
 *
 * @package Logics\Tests\BuildTools\CodeSniffer\PEAR
 */

namespace Logics\Tests\BuildTools\CodeSniffer\PEAR;

use \Logics\Tests\BuildTools\CodeSniffer\AbstractSniffUnitTest;

/**
 * Unit test class for the ValidFunctionName sniff.
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
 * @link      $HeadURL: https://open.logics.net.au/buildtools/codesniffer/tags/0.1.3/tests/Standards/PEAR/NamingConventions/ValidFunctionNameUnitTest.php $
 */

class ValidFunctionNameUnitTest extends AbstractSniffUnitTest
    {

	/**
	 * This functions set expectations from test case files in form of an array
	 *
	 * @return array Expectations of errors and warnings
	 */

	protected function expectations()
	    {
		$errors = array(
			   11  => 1,
			   12  => 1,
			   13  => 1,
			   14  => 1,
			   15  => 1,
			   16  => 1,
			   17  => 1,
			   18  => 1,
			   19  => 1,
			   20  => 1,
			   24  => 1,
			   25  => 1,
			   26  => 1,
			   27  => 1,
			   28  => 1,
			   29  => 1,
			   30  => 1,
			   31  => 1,
			   32  => 1,
			   33  => 1,
			   35  => 1,
			   36  => 1,
			   37  => 1,
			   38  => 1,
			   39  => 1,
			   40  => 1,
			   43  => 1,
			   44  => 1,
			   45  => 1,
			   46  => 1,
			   50  => 1,
			   51  => 1,
			   52  => 1,
			   53  => 1,
			   56  => 1,
			   57  => 1,
			   58  => 1,
			   59  => 1,
			   67  => 1,
			   68  => 1,
			   69  => 1,
			   70  => 1,
			   71  => 1,
			   72  => 1,
			   73  => 1,
			   74  => 1,
			   75  => 1,
			   76  => 1,
			   80  => 1,
			   81  => 1,
			   82  => 1,
			   83  => 1,
			   84  => 1,
			   85  => 1,
			   86  => 1,
			   87  => 1,
			   88  => 1,
			   89  => 1,
			   91  => 1,
			   92  => 1,
			   93  => 1,
			   94  => 1,
			   95  => 1,
			   96  => 1,
			   99  => 1,
			   100 => 1,
			   101 => 1,
			   102 => 1,
			   106 => 1,
			   107 => 1,
			   108 => 1,
			   109 => 1,
			   112 => 1,
			   113 => 1,
			   114 => 1,
			   115 => 1,
			   121 => 1,
			   122 => 1,
			   123 => 1,
			   124 => 1,
			   125 => 1,
			   126 => 1,
			   127 => 1,
			   128 => 1,
			   129 => 1,
			   130 => 1,
			   149 => 1,
			   151 => 1,
			   152 => 1,
			   155 => 1,
			   156 => 1,
			   157 => 1,
			   158 => 1,
			   159 => 1,
			   160 => 1,
			   161 => 1,
			   162 => 1,
			   163 => 1,
			   164 => 1,
			   165 => 1,
			   166 => 1,
			   167 => 1,
			   169 => 1,
			   170 => 1,
			   171 => 1,
			   173 => 1,
			   174 => 1,
			   175 => 1,
			  );

		// The trait tests will only work in PHP version where traits exist and
		// will throw errors in earlier versions.
		if (version_compare(PHP_VERSION, "5.4.0") < 0)
		    {
			$errors[196] = 1;
		    }

		return array("ValidFunctionNameUnitTest.inc.testfile" => array("errors" => $errors));
	    } //end expectations()


    } //end class

?>
