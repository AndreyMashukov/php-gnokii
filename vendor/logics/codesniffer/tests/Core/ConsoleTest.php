<?php

/**
 * PHP version 5
 *
 * @package Logics\Tests\BuildTools\CodeSniffer
 */

namespace Logics\Tests\BuildTools\CodeSniffer;

use \Logics\BuildTools\CodeSniffer\Console;
use \PHPUnit_Framework_TestCase;

/**
 * Tests for console reporting
 *
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2006-2012 Squiz Pty Ltd (ABN 77 084 670 600)
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 * @version   SVN: $Date: 2016-08-16 23:39:01 +0900 (Tue, 16 Aug 2016) $ $Revision: 26 $
 * @link      $HeadURL: https://open.logics.net.au/buildtools/codesniffer/tags/0.1.3/tests/Core/ConsoleTest.php $
 *
 * @runTestsInSeparateProcesses
 *
 * @donottranslate
 */

class ConsoleTest extends PHPUnit_Framework_TestCase
    {

	/**
	 * Test printing of indented line when verbosity level is exceeded
	 *
	 * @return void
	 */

	public function testPrintsIndentedLineWhenVerbosityLevelIsExceeded()
	    {
		define("PHP_CODESNIFFER_VERBOSITY", 1);

		ob_start();
		Console::report("Test", 1, 0);
		$s = ob_get_contents();
		ob_end_clean();

		$this->assertEquals("\tTest\n", $s);

		ob_start();
		Console::report("Another test", 2, 2);
		$s = ob_get_contents();
		ob_end_clean();

		$this->assertEquals("", $s);
	    } //end testPrintsIndentedLineWhenVerbosityLevelIsExceeded()


	/**
	 * Test preparation of output
	 *
	 * @return void
	 */

	public function testMakesSpecialCharactersInAStringSuitableForConsoleOutput()
	    {
		$this->assertEquals("\033[30;1m\\t\033[0m", Console::prepareForOutput("\t"));
	    } //end testMakesSpecialCharactersInAStringSuitableForConsoleOutput()


    } //end class

?>
