<?php

/**
 * PHP version 5.6
 *
 * @package Logics\Tests\Foundation\CLI
 */

namespace Logics\Tests\Foundation\CLI;

use \Exception;
use \PHPUnit_Framework_TestCase;

/**
 * Test for CLI class
 *
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://opensource.org/licenses/MIT MIT License
 * @version   SVN: $Date: 2017-01-09 22:01:17 +0800 (Mon, 09 Jan 2017) $ $Revision: 93 $
 * @link      $HeadURL: https://open.logics.net.au/foundation/CLI/tags/0.1.3/tests/CLITest.php $
 *
 * @runTestsInSeparateProcesses
 *
 * @donottranslate
 */

class CLITest extends PHPUnit_Framework_TestCase
    {

	/**
	 * Testing oject
	 *
	 * @var CLIHelper
	 */
	protected $object;

	/**
	 * Cron directory
	 *
	 * @var string
	 */
	private $_crondir;

	/**
	 * Cron file
	 *
	 * @var string
	 */
	private $_cronfile;

	/**
	 * Run directory
	 *
	 * @var string
	 */
	private $_rundir;

	/**
	 * Run file
	 *
	 * @var string
	 */
	private $_runfile;

	/**
	 * Config directory
	 *
	 * @var string
	 */
	private $_configs;

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 *
	 * @return void
	 */

	protected function setUp()
	    {
		$this->_crondir  = sys_get_temp_dir();
		$this->_cronfile = "testcron";
		$this->_rundir   = sys_get_temp_dir();
		$this->_runfile  = $this->_rundir . "/CLI-test";

		$this->_configs = sys_get_temp_dir() . "/testCommitConfiguration";
		if (is_dir($this->_configs) === false)
		    {
			mkdir($this->_configs);
		    }
	    } //end setUp()


	/**
	 * Tears down the fixture, for example, closes a network connection.
	 * This method is called after a test is executed.
	 *
	 * @return void
	 */

	protected function tearDown()
	    {
		if (file_exists($this->_crondir . DIRECTORY_SEPARATOR . $this->_cronfile) === true)
		    {
			unlink($this->_crondir . DIRECTORY_SEPARATOR . $this->_cronfile);
		    }

		if (file_exists($this->_runfile) === true)
		    {
			unlink($this->_runfile);
		    }

		$dir = opendir($this->_configs);
		while ($name = readdir($dir))
		    {
			if (is_file($this->_configs . DIRECTORY_SEPARATOR . $name) === true)
			    {
				unlink($this->_configs . DIRECTORY_SEPARATOR . $name);
			    }
		    }

		if (is_dir($this->_configs) === true)
		    {
			rmdir($this->_configs);
		    }
	    } //end tearDown()


	/**
	 * Testing checkExclusivity()
	 *
	 * @return string
	 *
	 * @requires extension test_helpers
	 *
	 * @throws Exception If exit() is encountered
	 *
	 * @exceptioncode EXCEPTION_EXIT
	 */

	public function testCheckExclusivity()
	    {
		putenv("PATH=/sbin");
		define("CROND", $this->_crondir);
		define("RUN_DIRECTORY", $this->_rundir);
		defined("EXCEPTION_EXIT") || define("EXCEPTION_EXIT", 1);

		file_put_contents($this->_runfile, "0");
		$this->object = new CLIHelper();
		unset($this->object);

		set_exit_overload(
		function()
		    {
			throw new Exception("exit()", EXCEPTION_EXIT);
		    }
		);
		ob_start();
		try
		    {
			file_put_contents($this->_runfile, "1");
			$this->object = new CLIHelper();
			unset($this->object);
		    }
		catch (Exception $e)
		    {
			if ($e->getCode() !== EXCEPTION_EXIT)
			    {
				throw $e;
			    }
		    }

		unset_exit_overload();
		return ob_get_clean();
	    } //end testCheckExclusivity()


	/**
	 * Testing multiple calls to checkExclusivity() : check that subsequent call does not terminate due to the same pid
	 *
	 * @return string
	 *
	 * @requires extension test_helpers
	 *
	 * @throws Exception If exit() is encountered
	 *
	 * @exceptioncode EXCEPTION_EXIT
	 */

	public function testCheckExclusivityReentry()
	    {
		define("CROND", $this->_crondir);
		define("RUN_DIRECTORY", $this->_rundir);
		defined("EXCEPTION_EXIT") || define("EXCEPTION_EXIT", 1);

		set_exit_overload(
		function()
		    {
			throw new Exception("exit()", EXCEPTION_EXIT);
		    }
		);
		ob_start();
		try
		    {
			$this->object = new CLIHelper();
			$this->object->runCheckExclusivity();
			unset($this->object);
		    }
		catch (Exception $e)
		    {
			unset_exit_overload();
			throw $e;
		    }

		unset_exit_overload();
		return ob_get_clean();
	    } //end testCheckExclusivityReentry()


	/**
	 * Testing commitConfiguration()
	 *
	 * @return void
	 */

	public function testCommitConfiguration()
	    {
		define("CROND", $this->_crondir);
		define("RUN_DIRECTORY", $this->_rundir);

		file_put_contents($this->_configs . "/bad.cfg", "badcontents");

		$this->object = new CLIHelper();

		$config = array("test.cfg" => "contents");
		$this->object->testCommitConfiguration($this->_configs, $config, true, "ls", 0666);
		$this->assertEquals("contents", file_get_contents($this->_configs . "/test.cfg"));

		unset($this->object);

		$this->assertFileNotExists($this->_configs . "/bad.cfg");
	    } //end testCommitConfiguration()


	/**
	 * Testing commitConfiguration() with bad restart command
	 *
	 * @return void
	 *
	 * @exceptioncode EXCEPTION_RESTART_FAIL
	 */

	public function testCommitConfigurationBadCommand()
	    {
		define("CROND", $this->_crondir);
		define("RUN_DIRECTORY", $this->_rundir);
		defined("EXCEPTION_RESTART_FAIL") || define("EXCEPTION_RESTART_FAIL", 1);

		$this->object = new CLIHelper();

		$config = array("test.cfg" => "newcontents");
		$this->expectException(Exception::CLASS);
		$this->expectExceptionCode(EXCEPTION_RESTART_FAIL);
		$this->object->testCommitConfiguration($this->_configs, $config, true, "badcommand", 0666);
	    } //end testCommitConfigurationBadCommand()


	/**
	 * Testing commitConfiguration() with permission denied
	 *
	 * @return void
	 *
	 * @exceptioncode EXCEPTION_UNABLE_TO_COMMIT_CONFIG
	 */

	public function testCommitConfigurationPermissionDenied()
	    {
		define("CROND", $this->_crondir);
		define("RUN_DIRECTORY", $this->_rundir);
		defined("EXCEPTION_FAIL_FILE_PUT_CONTENTS") || define("EXCEPTION_FAIL_FILE_PUT_CONTENTS", 1);
		defined("EXCEPTION_UNABLE_TO_COMMIT_CONFIG") || define("EXCEPTION_UNABLE_TO_COMMIT_CONFIG", 2);

		$directory = "/";

		$this->object = new CLIHelper();

		$config = array("test.cfg" => "newcontents");
		$this->expectException(Exception::CLASS);
		$this->expectExceptionCode(EXCEPTION_UNABLE_TO_COMMIT_CONFIG);
		$this->object->testCommitConfiguration($directory, $config, true, "ls", 0666);
	    } //end testCommitConfigurationPermissionDenied()


	/**
	 * Testing updateCrontab()
	 *
	 * @return void
	 */

	public function testUpdateCrontab()
	    {
		define("CROND", $this->_crondir);
		define("RUN_DIRECTORY", $this->_rundir);

		$_SERVER["PHP_SELF"] = "/bin/sh";

		$this->object = new CLIHelper();
		$this->object->testUpdateCrontab($this->_cronfile);
		unset($this->object);

		$this->assertFileEquals(__DIR__ . "/testcron", $this->_crondir . DIRECTORY_SEPARATOR . $this->_cronfile);

		$_SERVER["PWD"]      = "/bin";
		$_SERVER["PHP_SELF"] = "sh";

		$this->object = new CLIHelper();
		$this->object->testUpdateCrontab($this->_cronfile);
		unset($this->object);

		$this->assertFileEquals(__DIR__ . "/testcron", $this->_crondir . DIRECTORY_SEPARATOR . $this->_cronfile);
	    } //end testUpdateCrontab()


	/**
	 * Testing getConfigurationTemplate()
	 *
	 * @return void
	 */

	public function testGetConfigurationTemplate()
	    {
		define("CROND", $this->_crondir);
		define("RUN_DIRECTORY", $this->_rundir);

		$this->object = new CLIHelper();
		$this->assertEquals("", $this->object->testGetConfigurationTemplate(__DIR__ . "/nonexistenttemplate"));
		unset($this->object);

		$this->object = new CLIHelper();
		$this->assertEquals("# Configuration template\n", $this->object->testGetConfigurationTemplate(__DIR__ . "/configTemplate"));
		unset($this->object);
	    } //end testGetConfigurationTemplate()


    } //end class

?>
