<?php

/**
 * PHP version 5
 *
 * @package Logics\Tests\BuildTools\CodeSniffer\Generic
 */

namespace Logics\Tests\BuildTools\CodeSniffer\Generic;

use \Logics\Tests\BuildTools\CodeSniffer\AbstractSniffUnitTest;

/**
 * Unit test class for the DisallowShortOpenTag sniff.
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
 * @link      $HeadURL: https://open.logics.net.au/buildtools/codesniffer/tags/0.1.3/tests/Standards/Generic/PHP/DisallowShortOpenTagUnitTest.php $
 *
 * @donottranslate
 */

class DisallowShortOpenTagUnitTest extends AbstractSniffUnitTest
    {

	/**
	 * This functions set expectations from test case files in form of an array
	 *
	 * @return array Expectations of errors and warnings
	 */

	protected function expectations()
	    {
		$option = (boolean) ini_get("short_open_tag");
		if ($option === true)
		    {
			$errors = array(
				   4 => 1,
				   5 => 1,
				  );
		    }
		else if (version_compare(PHP_VERSION, "5.4.0RC1") >= 0)
		    {
			// Shorthand echo is always available from PHP 5.4.0 but needed the
			// short_open_tag ini var to be set for versions before this.
			$errors = array(4 => 1);
		    }

		return array("DisallowShortOpenTagUnitTest.inc.testfile" => array("errors" => $errors));
	    } //end expectations()


    } //end class

?>
