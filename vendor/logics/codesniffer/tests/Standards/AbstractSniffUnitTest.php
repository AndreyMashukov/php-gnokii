<?php

/**
 * PHP version 5
 *
 * @package Logics\Tests\BuildTools\CodeSniffer
 */

namespace Logics\Tests\BuildTools\CodeSniffer;

use \DirectoryIterator;
use \Exception;
use \PHPUnit_Framework_TestCase;
use \Logics\BuildTools\CodeSniffer\CodeSniffer;
use \Logics\BuildTools\CodeSniffer\File;
use \ReflectionClass;

/**
 * An abstract class that all sniff unit tests must extend.
 *
 * A sniff unit test checks a .inc file for expected violations of a single
 * coding standard. Expected errors and warnings that are not found, or
 * warnings and errors that are not expected, are considered test failures.
 *
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @author    Marc McIntyre <mmcintyre@squiz.net>
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2006-2012 Squiz Pty Ltd (ABN 77 084 670 600)
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 * @version   SVN: $Date: 2016-08-16 23:39:01 +0900 (Tue, 16 Aug 2016) $ $Revision: 26 $
 * @link      $HeadURL: https://open.logics.net.au/buildtools/codesniffer/tags/0.1.3/tests/Standards/AbstractSniffUnitTest.php $
 *
 * @donottranslate
 */

abstract class AbstractSniffUnitTest extends PHPUnit_Framework_TestCase
    {

	/**
	 * The CodeSniffer object used for testing.
	 *
	 * @var CodeSniffer
	 */
	protected static $phpcs = null;

	/**
	 * Sets up this unit test.
	 *
	 * @return void
	 */

	protected function setUp()
	    {
		if (self::$phpcs === null)
		    {
			self::$phpcs                        = new CodeSniffer();
			self::$phpcs->allowedFileExtensions = array(
							       "php"          => "PHP",
							       "php.testfile" => "PHP",
							       "inc"          => "PHP",
							       "inc.testfile" => "PHP",
							       "js"           => "JS",
							       "js.testfile"  => "JS",
							       "css"          => "CSS",
							       "css.testfile" => "CSS",
							      );
		    }
	    } //end setUp()


	/**
	 * Should this test be skipped for some reason.
	 *
	 * @return bool
	 */

	protected function shouldSkipTest()
	    {
		return false;
	    } //end shouldSkipTest()


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

		list($sniffClass, $testFileBase, $standardName) = $this->_getSniffDetails();

		// Get a list of all test files to check. These will have the same base
		// name but different extensions. We ignore the .php file as it is the
		// class.
		$testFiles = array();

		$dir = substr($testFileBase, 0, strrpos($testFileBase, DIRECTORY_SEPARATOR));
		$di  = new DirectoryIterator($dir);

		foreach ($di as $file)
		    {
			$path = $file->getPathname();
			if (substr($path, 0, strlen($testFileBase)) === $testFileBase)
			    {
				if ($path !== $testFileBase . "php")
				    {
					$testFiles[] = $path;
				    }
			    }
		    }

		// Get them in order.
		sort($testFiles);

		self::$phpcs->process(array(), $standardName, array($sniffClass));
		self::$phpcs->setIgnorePatterns(array());

		$failureMessages = array();
		foreach ($testFiles as $testFile)
		    {
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

			$failures        = $this->checkForProblems($file);
			$failureMessages = array_merge($failureMessages, $failures);
		    } //end foreach

		if (empty($failureMessages) === false)
		    {
			$this->fail(implode(PHP_EOL, $failureMessages));
		    }
	    } //end testSniff()


	/**
	 * Get details of sniff for testing
	 *
	 * @return array
	 */

	private function _getSniffDetails()
	    {
		$reflector                        = new ReflectionClass(get_class($this));
		$classFile                        = $reflector->getFileName();
		list($name, $type, $standardName) = array_reverse(explode("/", $classFile));

		$path         = pathinfo($classFile);
		$testFileBase = $path["dirname"] . DIRECTORY_SEPARATOR . $path["filename"] . ".";

		// The basis for determining file locations.
		if (preg_match("/(?P<standard>.*)_(?P<type>.*)_(?P<basename>.*)UnitTest.php/", $name, $m) > 0)
		    {
			$standardName = $m["standard"];
			$basename     = $m["basename"];
		    }
		else if (preg_match("/(?P<type>.*)_(?P<basename>.*)UnitTest.php/", $name, $m) > 0)
		    {
			$type     = $m["type"];
			$basename = $m["basename"];
		    }
		else if (preg_match("/(?P<basename>.*)UnitTest.php/", $name, $m) > 0)
		    {
			$basename = $m["basename"];
		    }
		else
		    {
			$this->fail("Test file " . $name . " does not end with UnitTest.php");
		    }

		// The class name of the sniff we are testing.
		$sniffClass = $standardName . "_Sniffs_" . $type . "_" . $basename . "Sniff";

		return array(
			$sniffClass,
			$testFileBase,
			$standardName,
		       );
	    } //end _getSniffDetails()


	/**
	 * Generate a list of test failures for a given sniffed file.
	 *
	 * @param File $file The file being tested.
	 *
	 * @return array
	 *
	 * @throws Exception Errors and warnings should be arrays
	 *
	 * @exceptioncode EXCEPTION_GETERRORLIST_MUST_RETURN_ARRAY
	 * @exceptioncode EXCEPTION_GETWARNINGLIST_MUST_RETURN_ARRAY
	 */

	protected function checkForProblems(File $file)
	    {
		$testFile = $file->getFilename();

		$foundErrors      = $file->getErrors();
		$foundWarnings    = $file->getWarnings();
		$expectedErrors   = $this->getErrorList(basename($testFile));
		$expectedWarnings = $this->getWarningList(basename($testFile));

		if (is_array($expectedErrors) === false)
		    {
			throw new Exception("getErrorList() must return an array", EXCEPTION_GETERRORLIST_MUST_RETURN_ARRAY);
		    }

		if (is_array($expectedWarnings) === false)
		    {
			throw new Exception("getWarningList() must return an array", EXCEPTION_GETWARNINGLIST_MUST_RETURN_ARRAY);
		    }

		/*
		    We merge errors and warnings together to make it easier
		    to iterate over them and produce the errors string. In this way,
		    we can report on errors and warnings in the same line even though
		    it's not really structured to allow that.
		*/

		$allProblems = array();
		$empty       = array(
				"expected_errors"   => 0,
				"expected_warnings" => 0,
				"found_errors"      => array(),
				"found_warnings"    => array(),
			       );

		foreach ($foundErrors as $line => $lineErrors)
		    {
			$problems = ((isset($allProblems[$line]) === false) ? $empty : $allProblems[$line]);

			foreach ($lineErrors as $errors)
			    {
				$problems["found_errors"] = array_merge($problems["found_errors"], array_column($errors, "message"));
			    } //end foreach

			$problems["expected_errors"] = ((isset($expectedErrors[$line]) === true) ? $expectedErrors[$line] : 0);

			$allProblems[$line] = $problems;
			unset($expectedErrors[$line]);
		    } //end foreach

		foreach ($expectedErrors as $line => $numErrors)
		    {
			$problems                    = ((isset($allProblems[$line]) === false) ? $empty : $allProblems[$line]);
			$problems["expected_errors"] = $numErrors;
			$allProblems[$line]          = $problems;
		    }

		foreach ($foundWarnings as $line => $lineWarnings)
		    {
			$problems = ((isset($allProblems[$line]) === false) ? $empty : $allProblems[$line]);

			foreach ($lineWarnings as $warnings)
			    {
				$problems["found_warnings"] = array_merge($problems["found_warnings"], array_column($warnings, "message"));
			    } //end foreach

			$problems["expected_warnings"] = ((isset($expectedWarnings[$line]) === true) ? $expectedWarnings[$line] : 0);

			$allProblems[$line] = $problems;
			unset($expectedWarnings[$line]);
		    } //end foreach

		foreach ($expectedWarnings as $line => $numWarnings)
		    {
			$problems                      = ((isset($allProblems[$line]) === false) ? $empty : $allProblems[$line]);
			$problems["expected_warnings"] = $numWarnings;
			$allProblems[$line]            = $problems;
		    }

		return $this->_generateFailureMessages($testFile, $allProblems);
	    } //end checkForProblems()


	/**
	 * Generate a list of test failures for a given sniffed file.
	 *
	 * @param string $testFile    Test file name
	 * @param array  $allProblems List of all detected problems
	 *
	 * @return array
	 */

	private function _generateFailureMessages($testFile, array $allProblems)
	    {
		// Order the messages by line number.
		ksort($allProblems);

		$failureMessages = array();

		foreach ($allProblems as $line => $problems)
		    {
			$numErrors        = count($problems["found_errors"]);
			$numWarnings      = count($problems["found_warnings"]);
			$expectedErrors   = $problems["expected_errors"];
			$expectedWarnings = $problems["expected_warnings"];

			$errors      = "";
			$foundString = "";

			if ($expectedErrors !== $numErrors || $expectedWarnings !== $numWarnings)
			    {
				$lineMessage     = "[LINE " . $line . "]";
				$expectedMessage = "";
				$foundMessage    = "";

				if ($expectedErrors !== $numErrors)
				    {
					$expectedMessage .= $expectedErrors . " error(s)";
					$foundMessage    .= $numErrors . " error(s)";
					if ($numErrors !== 0)
					    {
						$foundString .= "error(s)";
						$errors      .= PHP_EOL . " -> " . implode(PHP_EOL . " -> ", $problems["found_errors"]);
					    }
				    } //end if

				if ($expectedWarnings !== $numWarnings)
				    {
					$expectedMessage .= (($expectedMessage === "") ? "" : " and ") . $expectedWarnings . " warning(s)";
					$foundMessage    .= (($foundMessage === "") ? "" : " and ") . $numWarnings . " warning(s)";
					if ($numWarnings !== 0)
					    {
						$foundString .= (($foundString === "") ? "" : " and ") . "warning(s)";
						$errors      .= PHP_EOL . " -> " . implode(PHP_EOL . " -> ", $problems["found_warnings"]);
					    }
				    }

				$fullMessage = $lineMessage . " Expected " . $expectedMessage . " in " . basename($testFile) . " but found " . $foundMessage . ".";
				if ($errors !== "")
				    {
					$fullMessage .= " The " . $foundString . " found were:" . $errors;
				    }

				$failureMessages[] = $fullMessage;
			    } //end if
		    } //end foreach

		return $failureMessages;
	    } //end _generateFailureMessages()


	/**
	 * Get a list of CLI values to set before the file is tested.
	 *
	 * @param string $filename The name of the file being tested.
	 *
	 * @return array
	 */

	public function getCliValues($filename)
	    {
		unset($filename);
		return array();
	    } //end getCliValues()


	/**
	 * Returns the lines where errors should occur.
	 *
	 * The key of the array should represent the line number and the value
	 * should represent the number of errors that should occur on that line.
	 *
	 * @param string $testFile The name of the file being tested.
	 *
	 * @return array(int => int)
	 */

	protected function getErrorList($testFile)
	    {
		$expected = $this->expectations();
		if (isset($expected[$testFile]["errors"]) === true)
		    {
			return $expected[$testFile]["errors"];
		    }
		else
		    {
			return array();
		    }
	    } //end getErrorList()


	/**
	 * Returns the lines where warnings should occur.
	 *
	 * The key of the array should represent the line number and the value
	 * should represent the number of warnings that should occur on that line.
	 *
	 * @param string $testFile The name of the file being tested.
	 *
	 * @return array(int => int)
	 */

	protected function getWarningList($testFile)
	    {
		$expected = $this->expectations();
		if (isset($expected[$testFile]["warnings"]) === true)
		    {
			return $expected[$testFile]["warnings"];
		    }
		else
		    {
			return array();
		    }
	    } //end getWarningList()


	/**
	 * This functions set expectations from test case files in form of an array
	 *
	 * @return array Expectations of errors and warnings
	 */

	protected function expectations()
	    {
		return array();
	    } //end expectations()


    } //end class

?>
