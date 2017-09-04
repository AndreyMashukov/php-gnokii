<?php

/**
 * PHP version 5
 *
 * @package Logics\Tests\BuildTools\CodeSniffer
 */

namespace Logics\Tests\BuildTools\CodeSniffer;

use \Logics\BuildTools\CodeSniffer\SourceReport;

/**
 * Tests for the Source report of CodeSniffer.
 *
 * @author    Gabriele Santini <gsantini@sqli.com>
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2009 SQLI <www.sqli.com>
 * @copyright 2006-2012 Squiz Pty Ltd (ABN 77 084 670 600)
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 * @version   SVN: $Date: 2016-08-16 23:39:01 +0900 (Tue, 16 Aug 2016) $ $Revision: 26 $
 * @link      $HeadURL: https://open.logics.net.au/buildtools/codesniffer/tags/0.1.3/tests/Core/Reports/SourceTest.php $
 *
 * @donottranslate
 */

class SourceTest extends AbstractTestCase
    {

	/**
	 * Test standard generation.
	 *
	 * @return void
	 */

	public function testGenerate()
	    {
		$fullReport = new SourceReport();
		$generated  = $this->getFixtureReport($fullReport);
		$this->assertContains("A TOTAL OF 10 SNIFF VIOLATION(S) WERE FOUND IN 5 SOURCE(S)", $generated);
	    } //end testGenerate()


    } //end class

?>