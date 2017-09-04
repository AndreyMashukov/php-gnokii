<?php

/**
 * PHP version 5.6
 *
 * @package Logics\Tests
 */

namespace Logics\Tests;

use \PHPUnit_Framework_TestCase;

/**
 * Commmon generator fixture
 *
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://opensource.org/licenses/MIT MIT License
 * @version   SVN: $Date: 2016-09-25 17:02:06 +0800 (Sun, 25 Sep 2016) $ $Revision: 250 $
 * @link      $HeadURL: https://open.logics.net.au/phpunit-extensions/tags/0.2.5/PHPUnit_Extensions_Generator_TestCase.php $
 *
 * @runTestsInSeparateProcesses
 *
 * @donottranslate
 */

class PHPUnit_Extensions_Generator_TestCase extends PHPUnit_Framework_TestCase
    {

	/**
	 * Temporary folder for tests
	 *
	 * @var string
	 */
	protected $dir;

	/**
	 * Sets up the the directory for the tests
	 *
	 * @param string $dir Directory name
	 *
	 * @return void
	 */

	protected function setUpDirectory($dir)
	    {
		$this->dir = $dir;
		if (is_dir($this->dir) === false)
		    {
			mkdir($this->dir);
		    }
	    } //end setUpDirectory()


	/**
	 * Tears down the fixture, for example, closes a network connection.
	 * This method is called after a test is executed.
	 *
	 * @return void
	 */

	protected function tearDown()
	    {
		if (is_dir($this->dir) === true)
		    {
			$this->_rmdir($this->dir);
		    }

		parent::tearDown();
	    } //end tearDown()


	/**
	 * Remove folder recursively
	 *
	 * @param string $folder Name of the folder to remove
	 *
	 * @return void
	 */

	private function _rmdir($folder)
	    {
		$dir = opendir($folder);
		while ($name = readdir($dir))
		    {
			if (is_file($folder . DIRECTORY_SEPARATOR . $name) === true ||
			    is_link($folder . DIRECTORY_SEPARATOR . $name) === true)
			    {
				unlink($folder . DIRECTORY_SEPARATOR . $name);
			    }
			else if ($name !== "." && $name !== ".." && is_dir($folder . DIRECTORY_SEPARATOR . $name) === true)
			    {
				$this->_rmdir($folder . DIRECTORY_SEPARATOR . $name);
			    }
		    }

		if (is_dir($folder) === true)
		    {
			rmdir($folder);
		    }
	    } //end _rmdir()


    } //end class

?>
