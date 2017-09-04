<?php

/**
 * PHP version 5
 *
 * @package Logics\Tests\BuildTools\CodeSniffer\Beauty
 */

namespace Logics\Tests\BuildTools\CodeSniffer\Beauty;

use \Logics\Tests\BuildTools\CodeSniffer\AbstractSniffUnitTest;

/**
 * Unit test class for the ValidVariableName sniff.
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
 * @link      $HeadURL: https://open.logics.net.au/buildtools/codesniffer/tags/0.1.3/tests/Standards/Beauty/NamingConventions/RequestValidatorUnitTest.php $
 *
 * @donottranslate
 */

class RequestValidatorUnitTest extends AbstractSniffUnitTest
    {

	/**
	 * Tests the extending classes Sniff class.
	 *
	 * @return void
	 */

	public function testSniff()
	    {
		// Skip this test if we can't run in this environment.
		if ($this->shouldSkipTest() === true)
		    {
			$this->markTestSkipped();
		    }

		$standardName = "Beauty";
		$sniffClass   = "Beauty_Sniffs_NamingConventions_ValidVariableNameSniff";
		$testFile     = __DIR__ . "/RequestValidator.php";

		self::$phpcs->process(array(), $standardName, array($sniffClass));
		self::$phpcs->setIgnorePatterns(array());

		$filename = basename($testFile);
		try
		    {
			$cliValues       = $this->getCliValues($filename);
			$_SERVER["argv"] = array_merge(array("phpcs"), $cliValues);
			$GLOBALS["PHP_CODESNIFFER_CONFIG_DATA"] = array();
			self::$phpcs->processFile($testFile);
		    }
		catch (Exception $e)
		    {
			$this->fail("An unexpected exception has been caught: " . $e->getMessage());
		    }

		$files = self::$phpcs->getFiles();
		if (empty($files) === true)
		    {
			// File was skipped for some reason.
			echo "Skipped: " . $testFile . "\n";
			$this->markTestSkipped();
		    }

		$file = array_pop($files);

		$failures = $this->checkForProblems($file);

		if (empty($failures) === false)
		    {
			$this->fail(implode(PHP_EOL, $failures));
		    }
	    } //end testSniff()


	/**
	 * This functions set expectations from test case files in form of an array
	 *
	 * @return array Expectations of errors and warnings
	 */

	protected function expectations()
	    {
		return array("RequestValidator.php" => array("errors" => array()));
	    } //end expectations()


    } //end class

?>
