<?php

/**
 * PHP version 5
 *
 * @package Logics\Tests\BuildTools\CodeSniffer\Squiz
 */

namespace Logics\Tests\BuildTools\CodeSniffer\Squiz;

use \Logics\Tests\BuildTools\CodeSniffer\AbstractSniffUnitTest;

/**
 * Unit test class for VariableCommentSniff.
 *
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @author    Marc McIntyre <mmcintyre@squiz.net>
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2006-2012 Squiz Pty Ltd (ABN 77 084 670 600)
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 * @version   SVN: $Date: 2016-08-16 23:39:01 +0900 (Tue, 16 Aug 2016) $ $Revision: 26 $
 * @link      $HeadURL: https://open.logics.net.au/buildtools/codesniffer/tags/0.1.3/tests/Standards/Squiz/Commenting/VariableCommentUnitTest.php $
 */

class VariableCommentUnitTest extends AbstractSniffUnitTest
    {

	/**
	 * This functions set expectations from test case files in form of an array
	 *
	 * @return array Expectations of errors and warnings
	 */

	protected function expectations()
	    {
		return array(
			"VariableCommentUnitTest.inc.testfile" => array(
								   "errors"   => array(
										  21  => 1,
										  24  => 1,
										  56  => 1,
										  64  => 1,
										  73  => 1,
										  84  => 1,
										  131 => 1,
										  137 => 1,
										  145 => 1,
										  153 => 1,
										  163 => 1,
										  178 => 1,
										 ),
								   "warnings" => array(93 => 1),
								  ),
		       );
	    } //end expectations()


    } //end class

?>
