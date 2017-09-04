<?php

/**
 * PHP version 5
 *
 * @package Logics\Tests\BuildTools\CodeSniffer\Beauty
 */

namespace Logics\Tests\BuildTools\CodeSniffer\Beauty;

use \Logics\Tests\BuildTools\CodeSniffer\AbstractSniffUnitTest;

/**
 * Unit test class for the ClosingDeclarationComment sniff.
 *
 * A sniff unit test checks a .inc file for expected violations of a single
 * coding standard. Expected errors and warnings are stored in this class.
 *
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @author    Marc McIntyre <mmcintyre@squiz.net>
 * @author    Anastasia Bashkirtseva <anastasia@logics.net.au>
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2006-2012 Squiz Pty Ltd (ABN 77 084 670 600)
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 * @version   SVN: $Date: 2016-08-16 23:39:01 +0900 (Tue, 16 Aug 2016) $ $Revision: 26 $
 * @link      $HeadURL: https://open.logics.net.au/buildtools/codesniffer/tags/0.1.3/tests/Standards/Beauty/Commenting/ClosingDeclarationCommentUnitTest.php $
 */

class ClosingDeclarationCommentUnitTest extends AbstractSniffUnitTest
    {

	/**
	 * This functions set expectations from test case files in form of an array
	 *
	 * @return array Expectations of errors and warnings
	 */

	protected function expectations()
	    {
		return array(
			"ClosingDeclarationCommentUnitTest.inc.testfile" => array(
									     "errors"   => array(
											    8  => 1,
											    23 => 1,
											    25 => 1,
											    33 => 1,
											    38 => 1,
											   ),
									     "warnings" => array(
											    14 => 1,
											    44 => 1,
											   ),
									    )
		       );
	    } //end expectations()


    } //end class

?>
