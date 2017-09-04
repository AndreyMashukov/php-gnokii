<?php

/**
 * PHP version 5.6
 *
 * @package Logics\Tests\Foundation\CLI
 */

namespace Logics\Tests\Foundation\CLI;

use \Exception;
use \Logics\Foundation\CLI\CLI;
use \Logics\Foundation\CLI\Parser;

/**
 * Test helper for CLI class
 *
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://opensource.org/licenses/MIT MIT License
 * @version   SVN: $Date: 2016-08-25 01:09:07 +0900 (Thu, 25 Aug 2016) $ $Revision: 65 $
 * @link      $HeadURL: https://open.logics.net.au/foundation/CLI/tags/0.1.3/tests/CLIHelper.php $
 *
 * @donottranslate
 */

class CLIHelper extends CLI
    {

	/**
	 * Perform checks that our environment is good to go
	 *
	 * @param string $runfile Name of run file
	 *
	 * @return void
	 */

	protected function checkExclusivity($runfile = "")
	    {
		if ($runfile === "")
		    {
			$runfile = "CLI-test";
		    }

		parent::checkExclusivity($runfile);
	    } //end checkExclusivity()


	/**
	 * Run protected method checkExclusivity()
	 *
	 * @return void
	 */

	public function runCheckExclusivity()
	    {
		$this->checkExclusivity();
	    } //end runCheckExclusivity()


	/**
	 * Configure command line option parser
	 *
	 * @param Parser $parser Command line options parser
	 *
	 * @return void
	 */

	protected function setup(Parser $parser)
	    {
		$parser->setHelp("A very minimal example that does nothing but print a version");
		$parser->registerOption("v|version?<name>=bool/on", "Print version");
		$parser->registerArgument("folder?=dir/directory", "Folder");
		$parser->registerArgument("exceptions?=file/exceptions.php", "Exceptions file");
	    } //end setup()


	/**
	 * Main function
	 *
	 * @param Parser $parser Command line option parser
	 *
	 * @return void
	 */

	protected function main(Parser $parser)
	    {
		if ($parser->getOption("version") !== null)
		    {
			$this->info("1.0.0");
		    }
		else
		    {
			echo $parser->help();
		    }
	    } //end main()


	/**
	 * Public proxy to commitConfiguration()
	 *
	 * @param string $directory  Directory where config files should be stored
	 * @param array  $config     Configuratation files in array
	 * @param bool   $cleanup    Clean up old files in the directory
	 * @param string $restartcmd Command to be executed if configuration has changed
	 * @param string $mode       Mode to set on config files
	 *
	 * @return void
	 *
	 * @throws Exception Re-throws an exception
	 */

	public function testCommitConfiguration($directory, array $config, $cleanup = true, $restartcmd = "", $mode = 0)
	    {
		ob_start();
		try
		    {
			$this->commitConfiguration($directory, $config, $cleanup, $restartcmd, $mode);
		    }
		catch (Exception $e)
		    {
			ob_get_clean();
			throw $e;
		    }
	    } //end testCommitConfiguration()


	/**
	 * Public proxy to updateCrontab()
	 *
	 * @param string $crontab Crontab file name
	 *
	 * @return void
	 */

	public function testUpdateCrontab($crontab)
	    {
		$this->updateCrontab($crontab);
	    } //end testUpdateCrontab()


	/**
	 * Public proxy to getConfigurationTemplate()
	 *
	 * @param string $template Configuration template file name
	 *
	 * @return string
	 */

	public function testGetConfigurationTemplate($template)
	    {
		return $this->getConfigurationTemplate($template);
	    } //end testGetConfigurationTemplate()


    } //end class

?>