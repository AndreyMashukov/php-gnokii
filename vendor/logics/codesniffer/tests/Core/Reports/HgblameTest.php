<?php

/**
 * PHP version 5
 *
 * @package Logics\Tests\BuildTools\CodeSniffer
 */

namespace Logics\Tests\BuildTools\CodeSniffer;

/**
 * Tests for the Hgblame report of CodeSniffer.
 *
 * @author    Ben Selby <benmatselby@gmail.com>
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2009 SQLI <www.sqli.com>
 * @copyright 2006-2012 Squiz Pty Ltd (ABN 77 084 670 600)
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 * @version   SVN: $Date: 2016-08-16 23:39:01 +0900 (Tue, 16 Aug 2016) $ $Revision: 26 $
 * @link      $HeadURL: https://open.logics.net.au/buildtools/codesniffer/tags/0.1.3/tests/Core/Reports/HgblameTest.php $
 *
 * @donottranslate
 */

class HgblameTest extends AbstractTestCase
    {

	/**
	 * Test standard generation
	 *
	 * @return void
	 */

	public function testGenerate()
	    {
		$fullReport     = new HgblameMock();
		$generated      = $this->getFixtureReport($fullReport);
		$generatedLines = explode(PHP_EOL, $generated);
		$this->assertGreaterThan(10, count($generatedLines));
	    } //end testGenerate()


	/**
	 * Test author recovering from a hg blame line
	 *
	 * @param string $line     The hg blame output
	 * @param string $expected The author name
	 *
	 * @dataProvider provideDataForGetHgAuthor
	 *
	 * @return void
	 */

	public function testGetHgAuthor($line, $expected)
	    {
		$fullReport = new HgblameMock();
		$author     = $fullReport->testGetHgAuthor($line);
		$this->assertEquals($expected, $author);
	    } //end testGetHgAuthor()


	/**
	 * Data provider for testGetHgAuthor
	 *
	 * @return array
	 */

	public static function provideDataForGetHgAuthor()
	    {
		return array(
			array(
			 "Ben Selby <benmatselby@gmail.com> Sun May 29 00:05:15 2011 +0300:     /**",
			 "Ben Selby",
			),
			array(
			 "    benmatselby@somewhere Sun May 29 00:05:15 2011 +0300:     /**",
			 "benmatselby@somewhere",
			),
			array(
			 "Ben Selby <benmatselby@gmail.com> Tue Apr 26 00:36:36 2011 +0300:  * // Some random text with dates (e.g. 2011-05-01 12:30:00, Y-m-d H:i:s",
			 "Ben Selby",
			),
		       );
	    } //end provideDataForGetHgAuthor()


    } //end class

?>
