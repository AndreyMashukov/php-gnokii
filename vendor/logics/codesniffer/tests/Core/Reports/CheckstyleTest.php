<?php

/**
 * PHP version 5
 *
 * @package Logics\Tests\BuildTools\CodeSniffer
 */

namespace Logics\Tests\BuildTools\CodeSniffer;

use \DOMDocument;
use \Logics\BuildTools\CodeSniffer\CheckstyleReport;

/**
 * Tests for the Checkstyle report.
 *
 * @author    Gabriele Santini <gsantini@sqli.com>
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2009 SQLI <www.sqli.com>
 * @copyright 2006-2012 Squiz Pty Ltd (ABN 77 084 670 600)
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 * @version   SVN: $Date: 2016-08-16 23:39:01 +0900 (Tue, 16 Aug 2016) $ $Revision: 26 $
 * @link      $HeadURL: https://open.logics.net.au/buildtools/codesniffer/tags/0.1.3/tests/Core/Reports/CheckstyleTest.php $
 */

class CheckstyleTest extends AbstractTestCase
    {

	/**
	 * Tests standard report.
	 *
	 * @return void
	 */

	public function testGenerate()
	    {
		$checkstyleReport = new CheckstyleReport();
		$generated        = $this->getFixtureReport($checkstyleReport);
		$xmlDocument      = new DOMDocument();

		$xmlDocument->loadXML($generated);
		$result = $xmlDocument->schemaValidate(__DIR__ . "/XSD/Checkstyle.xsd");

		$this->assertTrue($result);
	    } //end testGenerate()


    } //end class

?>