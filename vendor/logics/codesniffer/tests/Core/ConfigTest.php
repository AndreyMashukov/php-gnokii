<?php

/**
 * PHP version 5
 *
 * @package Logics\Tests\BuildTools\CodeSniffer
 */

namespace Logics\Tests\BuildTools\CodeSniffer;

use \Logics\BuildTools\CodeSniffer\Config;
use \PHPUnit_Framework_TestCase;

/**
 * Tests for config data
 *
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2006-2012 Squiz Pty Ltd (ABN 77 084 670 600)
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 * @version   SVN: $Date: 2016-08-16 23:39:01 +0900 (Tue, 16 Aug 2016) $ $Revision: 26 $
 * @link      $HeadURL: https://open.logics.net.au/buildtools/codesniffer/tags/0.1.3/tests/Core/ConfigTest.php $
 *
 * @runTestsInSeparateProcesses
 *
 * @donottranslate
 */

class ConfigTest extends PHPUnit_Framework_TestCase
    {

	/**
	 * Tests set up
	 *
	 * @return void
	 */

	public function setUp()
	    {
		$peardata = Config::PEAR_DATA;
		unset($peardata);
	    } //end setUp()


	/**
	 * Tear down
	 *
	 * @return void
	 */

	public function tearDown()
	    {
		if (file_exists(__DIR__ . "/notwriteable") === true)
		    {
			unlink(__DIR__ . "/notwriteable");
		    }
	    } //end tearDown()


	/**
	 * Test config value fetch
	 *
	 * @return void
	 *
	 * @requires extension runkit
	 */

	public function testCanGetParticularConfigurationValueFromIniFile()
	    {
		runkit_constant_redefine("\Logics\BuildTools\CodeSniffer\Config::PEAR_DATA", "");
		runkit_constant_redefine("\Logics\BuildTools\CodeSniffer\Config::DEFAULT_DIR", __DIR__);

		$this->assertEquals("one", Config::getConfigData("first"));
		$this->assertEquals("two", Config::getConfigData("second"));
		$this->assertNull(Config::getConfigData("nonexistent"));
	    } //end testCanGetParticularConfigurationValueFromIniFile()


	/**
	 * Test config value fetch
	 *
	 * @return void
	 *
	 * @requires extension runkit
	 */

	public function testDoesNotComplainWhenNoConfigFileIsPresentButReturnsNullValues()
	    {
		runkit_constant_redefine("\Logics\BuildTools\CodeSniffer\Config::PEAR_DATA", "");
		runkit_constant_redefine("\Logics\BuildTools\CodeSniffer\Config::DEFAULT_DIR", __DIR__);
		runkit_constant_redefine("\Logics\BuildTools\CodeSniffer\Config::CONFIG_FILE", "noconfig");

		$this->assertNull(Config::getConfigData("first"));
		$this->assertNull(Config::getConfigData("second"));
		$this->assertNull(Config::getConfigData("nonexistent"));
	    } //end testDoesNotComplainWhenNoConfigFileIsPresentButReturnsNullValues()


	/**
	 * Test exception if unable to store config file
	 *
	 * @return void
	 *
	 * @expectedException     Exception
	 * @expectedExceptionCode 1
	 *
	 * @requires extension runkit
	 */

	public function testUnableToStoreSettingsIfConfigFileIsNotWriteable()
	    {
		define("EXCEPTION_CONFIG_IS_NOT_WRITEABLE", 1);

		runkit_constant_redefine("\Logics\BuildTools\CodeSniffer\Config::PEAR_DATA", "");
		runkit_constant_redefine("\Logics\BuildTools\CodeSniffer\Config::DEFAULT_DIR", __DIR__);
		runkit_constant_redefine("\Logics\BuildTools\CodeSniffer\Config::CONFIG_FILE", "notwriteable");

		touch(__DIR__ . "/notwriteable");
		chmod(__DIR__ . "/notwriteable", 0444);

		Config::setConfigData("test", "test");
	    } //end testUnableToStoreSettingsIfConfigFileIsNotWriteable()


	/**
	 * Test storing of settings in config file
	 *
	 * @return void
	 *
	 * @requires extension runkit
	 */

	public function testCanStoreSettingsToIniFile()
	    {
		runkit_constant_redefine("\Logics\BuildTools\CodeSniffer\Config::PEAR_DATA", "");
		runkit_constant_redefine("\Logics\BuildTools\CodeSniffer\Config::DEFAULT_DIR", __DIR__);
		runkit_constant_redefine("\Logics\BuildTools\CodeSniffer\Config::CONFIG_FILE", "newconfig");

		file_put_contents(__DIR__ . "/newconfig", "");

		$this->assertNull(Config::getConfigData("test"));
		$this->assertNull(Config::getConfigData("another"));
		$this->assertNull(Config::getConfigData("nonexistent"));

		Config::setConfigData("test", "a");
		Config::setConfigData("another", "b");

		$this->assertEquals("a", Config::getConfigData("test"));
		$this->assertEquals("b", Config::getConfigData("another"));
		$this->assertNull(Config::getConfigData("nonexistent"));

		$config = file_get_contents(__DIR__ . "/newconfig", "");
		$this->assertEquals("test = a\nanother = b\n", $config);

		unlink(__DIR__ . "/newconfig");
	    } //end testCanStoreSettingsToIniFile()


	/**
	 * Test removal of setting from config file
	 *
	 * @return void
	 *
	 * @requires extension runkit
	 */

	public function testCanRemoveSettingsFromIniFile()
	    {
		runkit_constant_redefine("\Logics\BuildTools\CodeSniffer\Config::PEAR_DATA", "");
		runkit_constant_redefine("\Logics\BuildTools\CodeSniffer\Config::DEFAULT_DIR", __DIR__);
		runkit_constant_redefine("\Logics\BuildTools\CodeSniffer\Config::CONFIG_FILE", "newconfig");

		file_put_contents(__DIR__ . "/newconfig", "");

		$this->assertNull(Config::getConfigData("test"));
		$this->assertNull(Config::getConfigData("another"));
		$this->assertNull(Config::getConfigData("nonexistent"));

		Config::setConfigData("test", "a");
		Config::setConfigData("another", "b");

		$this->assertEquals("a", Config::getConfigData("test"));
		$this->assertEquals("b", Config::getConfigData("another"));
		$this->assertNull(Config::getConfigData("nonexistent"));

		Config::setConfigData("test", null);

		$this->assertNull(Config::getConfigData("test"));
		$this->assertEquals("b", Config::getConfigData("another"));
		$this->assertNull(Config::getConfigData("nonexistent"));

		$config = file_get_contents(__DIR__ . "/newconfig", "");
		$this->assertEquals("another = b\n", $config);

		unlink(__DIR__ . "/newconfig");
	    } //end testCanRemoveSettingsFromIniFile()


    } //end class

?>