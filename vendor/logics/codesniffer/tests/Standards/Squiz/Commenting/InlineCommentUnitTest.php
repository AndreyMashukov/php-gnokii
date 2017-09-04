<?php

/**
 * PHP version 5
 *
 * @package Logics\Tests\BuildTools\CodeSniffer\Squiz
 */

namespace Logics\Tests\BuildTools\CodeSniffer\Squiz;

use \Logics\Tests\BuildTools\CodeSniffer\AbstractSniffUnitTest;

/**
 * Unit test class for the InlineComment sniff.
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
 * @link      $HeadURL: https://open.logics.net.au/buildtools/codesniffer/tags/0.1.3/tests/Standards/Squiz/Commenting/InlineCommentUnitTest.php $
 */

class InlineCommentUnitTest extends AbstractSniffUnitTest
    {

	/**
	 * This functions set expectations from test case files in form of an array
	 *
	 * @return array Expectations of errors and warnings
	 */

	protected function expectations()
	    {
		$errors = array(
			   17  => 1,
			   27  => 1,
			   28  => 1,
			   32  => 2,
			   36  => 1,
			   44  => 2,
			   58  => 1,
			   61  => 1,
			   64  => 2,
			   67  => 1,
			   95  => 1,
			   96  => 1,
			   97  => 3,
			   118 => 1,
			  );

		// The trait tests will only work in PHP version where traits exist and
		// will throw errors in earlier versions.
		if (version_compare(PHP_VERSION, "5.4.0") < 0)
		    {
			$errors[106] = 1;
		    }

		return array(
			"InlineCommentUnitTest.inc.testfile" => array("errors" => $errors),
			"InlineCommentUnitTest.js.testfile"  => array(
								 "errors" => array(
									      31  => 1,
									      36  => 2,
									      48  => 1,
									      51  => 1,
									      54  => 2,
									      57  => 1,
									      102 => 1,
									      103 => 1,
									      104 => 3,
									      118 => 1,
									     ),
								),
		       );
	    } //end expectations()


    } //end class

?>
