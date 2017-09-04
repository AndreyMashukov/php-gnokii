<?php

/**
 * PHP version 5
 *
 * @package Logics\Tests\BuildTools\CodeSniffer
 */

namespace Logics\Tests\BuildTools\CodeSniffer;

/**
 * Tests for the Gitblame report of CodeSniffer.
 *
 * @author    Ben Selby <benmatselby@gmail.com>
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2009 SQLI <www.sqli.com>
 * @copyright 2006-2012 Squiz Pty Ltd (ABN 77 084 670 600)
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 * @version   SVN: $Date: 2016-08-16 23:39:01 +0900 (Tue, 16 Aug 2016) $ $Revision: 26 $
 * @link      $HeadURL: https://open.logics.net.au/buildtools/codesniffer/tags/0.1.3/tests/Core/Reports/GitblameTest.php $
 *
 * @donottranslate
 */

class GitblameTest extends AbstractTestCase
    {

	/**
	 * Test standard generation
	 *
	 * @return void
	 */

	public function testGenerate()
	    {
		$fullReport     = new GitblameMock();
		$generated      = $this->getFixtureReport($fullReport);
		$generatedLines = explode(PHP_EOL, $generated);
		$this->assertGreaterThan(10, count($generatedLines));
	    } //end testGenerate()


	/**
	 * Test author recovering from a git blame line
	 *
	 * @param string $line     The git blame output
	 * @param string $expected The author name
	 *
	 * @dataProvider provideDataForGetGitAuthor
	 *
	 * @return void
	 */

	public function testGetGitAuthor($line, $expected)
	    {
		$fullReport = new GitblameMock();
		$author     = $fullReport->testGetGitAuthor($line);
		$this->assertEquals($expected, $author);
	    } //end testGetGitAuthor()


	/**
	 * Data provider for testGetGitAuthor
	 *
	 * @return array
	 */

	public static function provideDataForGetGitAuthor()
	    {
		return array(
			array(
			 "054e758d (Ben Selby 2010-07-03  45)      * @return",
			 "Ben Selby",
			),
			array(
			 "054e758d (Ben Selby Dev 1 2010-07-03  45)      * @return",
			 "Ben Selby Dev 1",
			),
			array(
			 "054e758d (Ben 2010-07-03  45)      * @return",
			 "Ben",
			),
			array(
			 "054e758d (Ben Selby 2010-07-03 45)      * @return",
			 "Ben Selby",
			),
			array(
			 "054e758d (Ben Selby 2010-07-03 1)      * @return",
			 "Ben Selby",
			),
			array(
			 "054e758d (Ben Selby 2010-07-03 11)      * @return",
			 "Ben Selby",
			),
			array(
			 "054e758d (Ben Selby 2010-07-03 111)      * @return",
			 "Ben Selby",
			),
			array(
			 "054e758d (Ben Selby 2010-07-03 1111)      * @return",
			 "Ben Selby",
			),
			array(
			 "054e758d (Ben Selby 2010-07-03 11111)      * @return",
			 "Ben Selby",
			),
		       );
	    } //end provideDataForGetGitAuthor()


    } //end class

?>
