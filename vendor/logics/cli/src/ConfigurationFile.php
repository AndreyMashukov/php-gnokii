<?php

/**
 * PHP version 5.6
 *
 * @package Logics\Foundation\CLI
 */

namespace Logics\Foundation\CLI;

use \Exception;

/**
 * Configuration file trait
 *
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://opensource.org/licenses/MIT MIT License
 * @version   SVN: $Date: 2016-08-17 22:49:29 +0900 (Wed, 17 Aug 2016) $ $Revision: 63 $
 * @link      $HeadURL: https://open.logics.net.au/foundation/CLI/tags/0.1.3/src/ConfigurationFile.php $
 */

trait ConfigurationFile
    {

	/**
	 * Get template file contents if it exists
	 *
	 * @param string $template Template file name
	 *
	 * @return string Contents of template
	 */

	protected function getConfigurationTemplate($template)
	    {
		return ((file_exists($template) === true) ? file_get_contents($template) : "");
	    } //end getConfigurationTemplate()


	/**
	 * Commit configuration, do a restart if needed
	 *
	 * @param string $directory  Directory where config files should be stored
	 * @param array  $config     Configuratation files in array
	 * @param bool   $cleanup    Clean up old files in the directory
	 * @param string $restartcmd Command to be executed if configuration has changed
	 * @param string $mode       Mode to set on config files
	 *
	 * @return void
	 */

	protected function commitConfiguration($directory, array $config, $cleanup = true, $restartcmd = "", $mode = 0)
	    {
		$restart = false;
		foreach ($config as $name => $settings)
		    {
			if (file_exists($directory . DIRECTORY_SEPARATOR . $name) === true)
			    {
				$current = file_get_contents($directory . DIRECTORY_SEPARATOR . $name);
				if ($current !== $settings)
				    {
					$this->_storeFile($directory . DIRECTORY_SEPARATOR . $name, $settings, $mode);
					$restart = true;
				    }
			    }
			else
			    {
				$this->_storeFile($directory . DIRECTORY_SEPARATOR . $name, $settings, $mode);
				$restart = true;
			    }
		    }

		if ($cleanup === true)
		    {
			$dir = opendir($directory);
			if ($dir !== false)
			    {
				$name = readdir($dir);
				while ($name !== false)
				    {
					if (is_file($directory . DIRECTORY_SEPARATOR . $name) === true && isset($config[$name]) === false)
					    {
						unlink($directory . DIRECTORY_SEPARATOR . $name);
						$restart = true;
					    }

					$name = readdir($dir);
				    }
			    }

			closedir($dir);
		    } //end if

		$this->_restart($restart, $restartcmd);
	    } //end commitConfiguration()


	/**
	 * Do a restart if needed
	 *
	 * @param bool   $restart True if restart should be performed
	 * @param string $cmd     Command to be executed
	 *
	 * @return void
	 *
	 * @throws Exception Restart command has failed
	 *
	 * @exceptioncode EXCEPTION_RESTART_FAIL
	 *
	 * @untranslatable 2> /dev/null 1> /dev/null
	 */

	private function _restart($restart, $cmd)
	    {
		if ($restart === true && $cmd !== "")
		    {
			system($cmd . " 2> /dev/null 1> /dev/null", $status);
			if ($status !== 0)
			    {
				throw new Exception(_("Execution of restart command failed:") . " " . $cmd, EXCEPTION_RESTART_FAIL);
			    }
		    }
	    } //end _restart()


	/**
	 * Store file and set mode
	 *
	 * @param string $file     File name
	 * @param string $contents Contents of file
	 * @param string $mode     File mode to set
	 *
	 * @return void
	 *
	 * @throws Exception Unable to store file
	 *
	 * @exceptioncode EXCEPTION_UNABLE_TO_COMMIT_CONFIG
	 * @exceptioncode EXCEPTION_FAIL_FILE_PUT_CONTENTS
	 *
	 * @untranslatable file_put_contents
	 */

	private function _storeFile($file, $contents, $mode)
	    {
		set_error_handler(
		function ($errno, $errstr)
		    {
			unset($errno);
			throw new Exception("file_put_contents " . _("fail") . ": " . $errstr, EXCEPTION_FAIL_FILE_PUT_CONTENTS);
		    }
		);

		try
		    {
			file_put_contents($file, $contents);
			restore_error_handler();
		    }
		catch (Exception $e)
		    {
			restore_error_handler();
			throw new Exception(_("Unable to commit configuration"), EXCEPTION_UNABLE_TO_COMMIT_CONFIG);
		    }

		if ($mode !== 0)
		    {
			chmod($file, $mode);
		    }
	    } //end _storeFile()


    } //end trait

?>
