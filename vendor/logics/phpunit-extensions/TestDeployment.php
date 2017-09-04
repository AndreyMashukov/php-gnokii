<?php

/**
 * PHP version 5.6
 *
 * @package Logics\Tests
 */

namespace Logics\Tests;

/**
 * TestDeployment trait
 *
 * Trait for nametrace processing tools
 *
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @author    Alexander Shumilov <alex@logics.net.au>
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://opensource.org/licenses/MIT MIT License
 * @version   SVN: $Date: 2016-08-17 22:45:36 +0900 (Wed, 17 Aug 2016) $ $Revision: 232 $
 * @link      $HeadURL: https://open.logics.net.au/phpunit-extensions/tags/0.2.5/TestDeployment.php $
 *
 * @donottranslate
 */

trait TestDeployment
    {

	/**
	 * Deploying environment for specific test.
	 *
	 * @param string $dirname          Name of the folder to create
	 * @param string $buildxmlfilepath Filepath of the build.xml file for Ant
	 *
	 * @return void
	 *
	 * @throws Exception Unable to locate build.xml
	 */

	private function _deploy($dirname, $buildxmlfilepath)
	    {
		if (file_exists($buildxmlfilepath) === false)
		    {
			throw new Exception("Unable to locate build.xml with given filepath: " . $buildxmlfilepath, 0);
		    }

		$desiredfolder = stream_resolve_include_path("tests") . DIRECTORY_SEPARATOR . $dirname;
		if (is_dir($desiredfolder) === true)
		    {
			shell_exec("rm -R " . $desiredfolder);
		    }

		mkdir($desiredfolder);
		chdir($desiredfolder);
		copy($buildxmlfilepath, $desiredfolder . DIRECTORY_SEPARATOR . "build.xml");

		shell_exec("ant");
		shell_exec("chmod 774 templates_c");
	    } //end _deploy()


    } //end trait

?>
