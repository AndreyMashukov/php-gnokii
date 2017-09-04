<?php

/**
 * PHP version 5
 *
 * @package Logics\Tests\BuildTools\CodeSniffer\Squiz
 */

namespace Logics\Tests\BuildTools\CodeSniffer\Squiz;

use \Logics\Tests\BuildTools\CodeSniffer\AbstractSniffUnitTest;

/**
 * Unit test class for the BlockComment sniff.
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
 * @link      $HeadURL: https://open.logics.net.au/buildtools/codesniffer/tags/0.1.3/tests/Standards/Squiz/Commenting/BlockCommentUnitTest.php $
 */

class BlockCommentUnitTest extends AbstractSniffUnitTest
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
			   20  => 1,
			   24  => 1,
			   30  => 1,
			   31  => 1,
			   34  => 1,
			   40  => 1,
			   45  => 1,
			   49  => 1,
			   51  => 1,
			   53  => 1,
			   57  => 1,
			   60  => 1,
			   61  => 1,
			   63  => 1,
			   65  => 1,
			   68  => 1,
			   70  => 1,
			   72  => 1,
			   75  => 1,
			   84  => 1,
			   87  => 1,
			   89  => 1,
			   92  => 1,
			   111 => 1,
			   159 => 1,
			   181 => 1,
			   188 => 1,
			  );

		// The trait tests will only work in PHP version where traits exist and
		// will throw errors in earlier versions.
		if (version_compare(PHP_VERSION, "5.4.0") < 0)
		    {
			$errors[169] = 1;
		    }

		return array("BlockCommentUnitTest.inc.testfile" => array("errors" => $errors));
	    } //end expectations()


    } //end class

?>
