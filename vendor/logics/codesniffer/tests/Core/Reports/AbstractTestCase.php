<?php

/**
 * PHP version 5
 *
 * @package Logics\Tests\BuildTools\CodeSniffer
 */

namespace Logics\Tests\BuildTools\CodeSniffer;

use \Logics\BuildTools\CodeSniffer\Report;
use \PHPUnit_Framework_TestCase;

/**
 * TestCase Abstract Helper class.
 *
 * @author    Gabriele Santini <gsantini@sqli.com>
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2009 SQLI <www.sqli.com>
 * @copyright 2006-2012 Squiz Pty Ltd (ABN 77 084 670 600)
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 * @version   SVN: $Date: 2016-08-16 23:39:01 +0900 (Tue, 16 Aug 2016) $ $Revision: 26 $
 * @link      $HeadURL: https://open.logics.net.au/buildtools/codesniffer/tags/0.1.3/tests/Core/Reports/AbstractTestCase.php $
 *
 * @donottranslate
 */

class AbstractTestCase extends PHPUnit_Framework_TestCase
    {

	/**
	 * Fixtures of report data.
	 *
	 * @var array
	 */
	protected $fixtureReportData = array(
					"totals" => array(
						     "warnings" => 2,
						     "errors"   => 8,
						    ),
					"files"  => array(
						     "bar" => array(
							       "errors"   => 4,
							       "warnings" => 0,
							       "messages" => array(
									      1  => array(
										     1  => array(
											    0 => array(
												  "message"  => "First error message",
												  "source"   => "MyStandard.Mytype.Mysniff1Sniff.Mycode2",
												  "type"     => "ERROR",
												  "severity" => 5,
												 ),
											   ),
										     10 => array(
											    0 => array(
												  "message"  => "Second error message",
												  "source"   => "MyStandard.Mytype.Mysniff2Sniff.Mycode1",
												  "type"     => "ERROR",
												  "severity" => 5,
												 ),
											    1 => array(
												  "message"  => "Third error message",
												  "source"   => "MyStandard.Mytype.Mysniff1Sniff.Mycode2",
												  "type"     => "ERROR",
												  "severity" => 5,
												 )
											   )
										    ),
									      10 => array(
										     1 => array(
											   0 => array(
												 "message"  => "Fourth error message",
												 "source"   => "MyStandard.Mytype.Mysniff1Sniff.Mycode1",
												 "type"     => "ERROR",
												 "severity" => 5,
												)
											  )
										    )
									     )
							      ),
						     "baz" => array(
							       "errors"   => 0,
							       "warnings" => 0,
							       "messages" => array(),
							      ),
						     "foo" => array(
							       "errors"   => 4,
							       "warnings" => 2,
							       "messages" => array(
									      1  => array(
										     1  => array(
											    0 => array(
												  "message"  => "First error message",
												  "source"   => "MyStandard.Mytype.Mysniff1Sniff.Mycode2",
												  "type"     => "ERROR",
												  "severity" => 5,
												 )
											   ),
										     10 => array(
											    0 => array(
												  "message"  => "Second error message",
												  "source"   => "MyStandard.Mytype.Mysniff2Sniff.Mycode1",
												  "type"     => "ERROR",
												  "severity" => 5,
												 ),
											    1 => array(
												  "message"  => "Third error message",
												  "source"   => "MyStandard.Mytype.Mysniff1Sniff.Mycode2",
												  "type"     => "ERROR",
												  "severity" => 5,
												 )
											   )
										    ),
									      5  => array(
										     1 => array(
											   0 => array(
												 "message"  => "First warning message",
												 "source"   => "MyStandard.Mytype.Mysniff2Sniff.Mycode2",
												 "type"     => "WARNING",
												 "severity" => 5,
												)
											  )
										    ),
									      10 => array(
										     1 => array(
											   0 => array(
												 "message"  => "Second warning message",
												 "source"   => "MyStandard.Mytype.Mysniff1Sniff.Mycode3",
												 "type"     => "WARNING",
												 "severity" => 5,
												),
											   1 => array(
												 "message"  => "Fourth error message",
												 "source"   => "MyStandard.Mytype.Mysniff1Sniff.Mycode1",
												 "type"     => "ERROR",
												 "severity" => 5,
												)
											  )
										    )
									     )
							      )
						    )
				       );

	/**
	 * Returns report standard generation.
	 *
	 * @param Report $report The report under test.
	 *
	 * @return string
	 */

	protected function getFixtureReport(Report $report)
	    {
		ob_start();
		$report->generate($this->fixtureReportData);
		$generated = ob_get_clean();

		return $generated;
	    } //end getFixtureReport()


	/**
	 * Check report's line length not exceeding max allowed.
	 *
	 * @param string $report Report
	 * @param int    $max    Maximum report width
	 *
	 * @return void
	 */

	protected function checkLineLength($report, $max)
	    {
		$i    = 1;
		$line = strtok($report, PHP_EOL);
		while (false !== $line)
		    {
			$this->assertLessThan(($max + 1), strlen($line), "Report line " . $i . " is longer than " . $max . " characters");
			$i++;
			$line = strtok(PHP_EOL);
		    }
	    } //end checkLineLength()


    } //end class

?>
