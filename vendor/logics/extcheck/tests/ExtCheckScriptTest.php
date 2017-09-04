<?php

/**
 * PHP version 5.6
 *
 * @package Logics\Tests\BuildTools\ExtCheck
 */

namespace Logics\Tests\BuildTools\ExtCheck;

use \Logics\Tests\ScriptExecutor;
use \PHPUnit_Framework_TestCase;

/**
 * Test for extensions check script
 *
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://opensource.org/licenses/MIT MIT License
 * @version   SVN: $Date: 2016-09-05 01:02:59 +0900 (Mon, 05 Sep 2016) $ $Revision: 10 $
 * @link      $HeadURL: https://open.logics.net.au/buildtools/extcheck/tags/0.1.3/tests/ExtCheckScriptTest.php $
 *
 * @runTestsInSeparateProcesses
 *
 * @donottranslate
 */

class ExtCheckScriptTest extends PHPUnit_Framework_TestCase
    {

	use ScriptExecutor;

	/**
	 * Ensures that all used extensions are mentioned in composer.json
	 *
	 * @return void
	 */

	public function testEnsuresThatAllUsedExtensionsAreMentionedInComposerJson()
	    {
		$this->assertEquals(
		    "#!/usr/bin/php\n",
		    $this->_execute(dirname(__DIR__) . "/src/extcheck", array(__DIR__ . "/testsets/good/composer.json"), "CLI", array())
		);
	    } //end testEnsuresThatAllUsedExtensionsAreMentionedInComposerJson()


	/**
	 * Refuses to run if no composer.json found
	 *
	 * @return void
	 */

	public function testRefusesToRunIfNoComposerJsonFound()
	    {
		$this->assertEquals(
		    "#!/usr/bin/php\nCannot find composer.json file\n",
		    $this->_execute(dirname(__DIR__) . "/src/extcheck", array(), "CLI", array())
		);
	    } //end testRefusesToRunIfNoComposerJsonFound()


	/**
	 * Refuses to run with invalid composer.json
	 *
	 * @return void
	 */

	public function testRefusesToRunWithInvalidComposerJson()
	    {
		$this->assertRegExp(
		    "/Invalid.*composer.json file/",
		    $this->_execute(dirname(__DIR__) . "/src/extcheck", array(__DIR__ . "/testsets/invalid/composer.json"), "CLI", array())
		);
	    } //end testRefusesToRunWithInvalidComposerJson()


	/**
	 * Inconsistencies between extensions used in scripts and extensions declared in composer.json are reported
	 *
	 * @return void
	 */

	public function testReportsInconsitenciesBetweenExtensionsUsedAndExtensionsDeclaredInComposerJson()
	    {
		$s = $this->_execute(dirname(__DIR__) . "/src/extcheck", array(__DIR__ . "/testsets/misconfigured/composer.json"), "CLI", array());
		$this->assertContains("Extension ext-tokenizer mentioned in require section of composer.json but not used", $s);
		$this->assertContains("Following extensions are missing in require section of composer.json: ext-dom ext-gettext", $s);

		$s = $this->_execute(dirname(__DIR__) . "/src/extcheck", array("-v", __DIR__ . "/testsets/allsectionschecked/composer.json"), "CLI", array());
		$this->assertNotContains("ext-xdebug", $s);
	    } //end testReportsInconsitenciesBetweenExtensionsUsedAndExtensionsDeclaredInComposerJson()


	/**
	 * Verbosity may be increased
	 *
	 * @return void
	 */

	public function testVerbosityMayBeIncreased()
	    {
		$s = $this->_execute(dirname(__DIR__) . "/src/extcheck", array("-v", __DIR__ . "/testsets/misconfigured/composer.json"), "CLI", array());
		$this->assertContains("Extension ext-tokenizer mentioned in require section of composer.json but not used", $s);
		$this->assertContains("Following extensions are missing in require section of composer.json:", $s);
		$this->assertContains("Used XML_TEXT_NODE in", $s);
	    } //end testVerbosityMayBeIncreased()


	/**
	 * Test that only files from autoload and bin sections are analyzed
	 *
	 * @return void
	 */

	public function testAnalyzesFilesInAutoloadAndBinSectionsOnly()
	    {
		$s = $this->_execute(dirname(__DIR__) . "/src/extcheck", array("-v", __DIR__ . "/testsets/noautoload/composer.json"), "CLI", array());
		$this->assertContains("Extension ext-SPL mentioned in require section of composer.json but not used", $s);
		$this->assertContains("Extension ext-pcre mentioned in require section of composer.json but not used", $s);
	    } //end testAnalyzesFilesInAutoloadAndBinSectionsOnly()


	/**
	 * Test that instead of including extension to composer.json it may be ignored
	 *
	 * @return void
	 */

	public function testMayIgnoreAbsenceOfOrUseOfSpecifiedExtensions()
	    {
		$this->assertEquals(
		    "#!/usr/bin/php\n",
		    $this->_execute(dirname(__DIR__) . "/src/extcheck", array("-v", "-i", "dom", __DIR__ . "/testsets/ignored/composer.json"), "CLI", array())
		);
	    } //end testMayIgnoreAbsenceOfOrUseOfSpecifiedExtensions()


    } //end class

?>
