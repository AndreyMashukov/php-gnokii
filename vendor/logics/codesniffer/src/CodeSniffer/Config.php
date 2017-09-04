<?php

/**
 * A class to process command line phpcs scripts.
 *
 * PHP version 5.6
 *
 * @package Logics\BuildTools\CodeSniffer
 */

namespace Logics\BuildTools\CodeSniffer;

use \Exception;

/**
 * A class to process command line phpcs scripts.
 *
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2006-2012 Squiz Pty Ltd (ABN 77 084 670 600)
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 * @version   SVN: $Date: 2016-08-16 23:39:01 +0900 (Tue, 16 Aug 2016) $ $Revision: 26 $
 * @link      $HeadURL: https://open.logics.net.au/buildtools/codesniffer/tags/0.1.3/src/CodeSniffer/Config.php $
 */

class Config
    {

	/**
	 * Default PEAR data directory
	 *
	 * @untranslatable /usr/share/pear-data
	 */
	const PEAR_DATA = "/usr/share/pear-data";

	/**
	 * PHP CodeSniffer default directory
	 *
	 * @untranslatable PHP_CodeSniffer
	 */
	const DEFAULT_DIR = "PHP_CodeSniffer";

	/**
	 * Configuration file name
	 *
	 * @untranslatable CodeSniffer.ini
	 */
	const CONFIG_FILE = "CodeSniffer.ini";

	/**
	 * Get a single config value.
	 *
	 * Config data is stored in the data dir, in a file called
	 * CodeSniffer.conf. It is a simple PHP array.
	 *
	 * @param string $key The name of the config value.
	 *
	 * @return string
	 *
	 * @see setConfigData()
	 * @see getAllConfigData()
	 */

	public static function getConfigData($key)
	    {
		$phpCodeSnifferConfig = self::getAllConfigData();

		return (isset($phpCodeSnifferConfig[$key]) === false) ? null : $phpCodeSnifferConfig[$key];
	    } //end getConfigData()


	/**
	 * Set a single config value.
	 *
	 * Config data is stored in the data dir, in a file called
	 * CodeSniffer.conf. It is a simple PHP array.
	 *
	 * @param string      $key   The name of the config value.
	 * @param string|null $value The value to set. If null, the config
	 *                           entry is deleted, reverting it to the
	 *                           default value.
	 * @param bool        $temp  Set this config data temporarily for this
	 *                           script run. This will not write the config
	 *                           data to the config file.
	 *
	 * @return bool False if config was not stored to persistent storage
	 *
	 * @throws Exception If the config file can not be written.
	 *
	 * @exceptioncode EXCEPTION_CONFIG_IS_NOT_WRITEABLE
	 *
	 * @see getConfigData()
	 *
	 * @untranslatable @data_dir
	 */

	public static function setConfigData($key, $value, $temp = false)
	    {
		if ($temp === false)
		    {
			$configFile = __DIR__ . "/" . self::CONFIG_FILE;
			if (is_file($configFile) === false && strpos(self::PEAR_DATA, "@data_dir") === false)
			    {
				// If data_dir was replaced, this is a PEAR install and we can
				// use the PEAR data dir to store the conf file.
				$configFile = self::PEAR_DATA . DIRECTORY_SEPARATOR . self::DEFAULT_DIR . DIRECTORY_SEPARATOR . self::CONFIG_FILE;
			    }

			if (is_file($configFile) === true && is_writable($configFile) === false)
			    {
				throw new Exception(
				    _("Config file") . " " . $configFile . " " . _("is not writable"),
				    EXCEPTION_CONFIG_IS_NOT_WRITEABLE
				);
			    }
		    }

		$phpCodeSnifferConfig = self::getAllConfigData();

		$phpCodeSnifferConfig[$key] = $value;
		if ($value === null)
		    {
			unset($phpCodeSnifferConfig[$key]);
		    }

		$output = "";
		foreach ($phpCodeSnifferConfig as $key => $value)
		    {
			$output .= $key . " = " . $value . "\n";
		    }

		$GLOBALS["PHP_CODESNIFFER_CONFIG_DATA"] = $phpCodeSnifferConfig;

		return ($temp === true || file_put_contents($configFile, $output) === true);
	    } //end setConfigData()


	/**
	 * Get all config data in an array.
	 *
	 * @return string
	 *
	 * @see getConfigData()
	 */

	public static function getAllConfigData()
	    {
		if (isset($GLOBALS["PHP_CODESNIFFER_CONFIG_DATA"]) === true)
		    {
			return $GLOBALS["PHP_CODESNIFFER_CONFIG_DATA"];
		    }
		else
		    {
			$configFile = __DIR__ . "/" . self::CONFIG_FILE;
			if (is_file($configFile) === false)
			    {
				$configFile = self::PEAR_DATA . DIRECTORY_SEPARATOR . self::DEFAULT_DIR . DIRECTORY_SEPARATOR . self::CONFIG_FILE;
			    }

			if (is_file($configFile) === false)
			    {
				return array();
			    }
			else
			    {
				$GLOBALS["PHP_CODESNIFFER_CONFIG_DATA"] = parse_ini_file($configFile);
				return $GLOBALS["PHP_CODESNIFFER_CONFIG_DATA"];
			    }
		    } //end if
	    } //end getAllConfigData()


    } //end class

?>