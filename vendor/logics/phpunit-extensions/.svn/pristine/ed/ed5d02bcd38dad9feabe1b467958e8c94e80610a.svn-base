<?php

/**
 * PHP version 5.6
 *
 * @package Logics\Tests
 */

namespace Logics\Tests;

/**
 * Setup/teardown for framework based scripts.
 *
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://opensource.org/licenses/MIT MIT License
 * @version   SVN: $Date: 2016-09-19 17:44:58 +0930 (Mon, 19 Sep 2016) $ $Revision: 240 $
 * @link      $HeadURL: https://svn.logics.net.au/phpunit-extensions/trunk/PHPUnit_Extensions_ScenarioPlayer_TestCase.php $
 *
 * @donottranslate
 */

abstract class PHPUnit_Extensions_Service_TestCase extends PHPUnit_Extensions_Database_SQL_TestCase
    {

	/**
	 * Config file
	 *
	 * @var string
	 */
	private $_config;

	/**
	 * Sets up config for tests
	 *
	 * @param string $basedir Base directory
	 * @param string $config  Config name
	 *
	 * @return void
	 */

	protected function setUpConfig($basedir, $config = "config.php")
	    {
		ini_set("include_path", get_include_path() . ":" . $basedir);

		$this->_config = $basedir . "/local/config.php";
		copy($basedir . "/tests/" . $config, $this->_config);
	    } //end setUpConfig()


	/**
	 * Tears down the fixture, for example, closes a network connection.
	 * This method is called after a test is executed.
	 *
	 * @return void
	 */

	protected function tearDown()
	    {
		if (file_exists($this->_config) === true)
		    {
			unlink($this->_config);
		    }

		parent::tearDown();
	    } //end tearDown()


    } //end class

?>
