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

abstract class PHPUnit_Extensions_Script_TestCase extends PHPUnit_Extensions_Service_TestCase
    {

	/**
	 * Script under test
	 *
	 * @var string
	 */
	protected $script;

	/**
	 * Sets up script for testing
	 *
	 * @param string $script Script name
	 *
	 * @return void
	 */

	protected function setUpScript($script)
	    {
		$GLOBALS["argv"] = array();

		$this->script = $script;

		$this->setUpConfig(dirname(stream_resolve_include_path($this->script)));
	    } //end setUpScript()


    } //end class

?>
