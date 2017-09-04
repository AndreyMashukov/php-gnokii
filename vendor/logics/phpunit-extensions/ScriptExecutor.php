<?php

/**
 * PHP version 5.6
 *
 * @package Logics\Tests
 */

namespace Logics\Tests;

use \Exception;

/**
 * ScriptExecutor trait
 *
 * Some tests should start PHP scripts in the way they get started under web server environment. This trait provides such functionality.
 *
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @author    Vladimir Skorov <voroks@logics.net.au>
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://opensource.org/licenses/MIT MIT License
 * @version   SVN: $Date: 2016-09-26 11:17:14 +0800 (Mon, 26 Sep 2016) $ $Revision: 252 $
 * @link      $HeadURL: https://open.logics.net.au/phpunit-extensions/tags/0.2.5/ScriptExecutor.php $
 *
 * @codeCoverageIgnore
 *
 * @donottranslate
 */

trait ScriptExecutor
    {

	/**
	 * Saved include path
	 *
	 * @var string
	 */
	protected $scriptExecutorIncludePath = false;

	/**
	 * Saved autoload functions
	 *
	 * @var array
	 */
	protected $scriptExecutorAutoloadFunctions = false;

	/**
	 * Execute script with parameters
	 *
	 * @param string $script Script name to execute
	 * @param array  $params Containing parameters for the script
	 * @param string $method GET or POST or CLI
	 * @param array  $files  Uploading files
	 * @param string $path   Optional additional include path
	 * @param string $uri    Request URI
	 *
	 * @return string containing script output
	 *
	 * @throws Exception Unhandled expection is encountered
	 */

	private function _execute($script, array $params = array(), $method = "GET", array $files = array(), $path = "", $uri = "")
	    {
		if (function_exists("set_exit_overload") === true)
		    {
			set_exit_overload(
			function()
			    {
				throw new Exception("exit()", -1);
			    }
			);
			ob_start();
			session_write_close();

			try
			    {
				$this->_run($script, $params, $method, $files, $path, $uri);
			    }
			catch (Exception $e)
			    {
				$this->_restoreIncudePath();

				if ($e->getCode() !== -1)
				    {
					unset_exit_overload();
					ob_get_clean();
					if ((ini_get("runkit.internal_override") === true) && (count($files) > 0))
					    {
						runkit_function_remove("is_uploaded_file");
						runkit_function_rename("is_uploaded_file_original", "is_uploaded_file");
					    }

					throw $e;
				    }
			    } //end try

			unset_exit_overload();
			return ob_get_clean();
		    }
		else
		    {
			$this->markTestSkipped("test_helpers extension is not available");
		    } //end if
	    } //end _execute()


	/**
	 * Execute script with parameters
	 *
	 * @param string $script Script name to execute
	 * @param array  $params Containing parameters for the script
	 * @param string $method GET or POST or CLI
	 * @param array  $files  Uploading files
	 * @param string $path   Optional additional include path
	 * @param string $uri    Request URI
	 *
	 * @return void
	 */

	private function _run($script, array $params, $method, array $files, $path, $uri)
	    {
		$_SERVER["SCRIPT_FILENAME"] = $this->_resolveIncludePath($script);
		chdir(dirname($_SERVER["SCRIPT_FILENAME"]));
		$_GET   = array();
		$_POST  = array();
		$_FILES = array();

		$uploadedfiles = array();

		$parsed = $this->_parseScriptParameters($params, $script);
		if (strtoupper($method) === "GET")
		    {
			$this->_setServerVariables($script, $uri);
			$_SERVER["QUERY_STRING"] = http_build_query($parsed, "", "&");
			$_GET                    = $parsed;
		    }
		else if (strtoupper($method) === "POST")
		    {
			$this->_setServerVariables($script, $uri);
			$_SERVER["QUERY_STRING"] = "";
			$_POST                   = $parsed;
			$uploadedfiles           = $this->_loadFiles($files);
		    }
		else if (strtoupper($method) === "CLI")
		    {
			$GLOBALS["argv"] = array_merge(array($script), array_values($parsed));
			$GLOBALS["argc"] = count($GLOBALS["argv"]);
		    } //end if

		class_exists("PHPUnit_Framework_TestFailure");
		class_exists("PHPUnit_Util_Filter");
		class_exists("PHPUnit_Util_Blacklist");

		$this->_resetIncludePath($path);
		include $_SERVER["SCRIPT_FILENAME"];
		$this->_restoreIncudePath();

		if ((ini_get("runkit.internal_override") === true) && (count($files) > 0))
		    {
			foreach ($uploadedfiles as $uploadedfile)
			    {
				unlink($uploadedfile);
			    }

			runkit_function_remove("is_uploaded_file");
			runkit_function_rename("is_uploaded_file_original", "is_uploaded_file");
		    }
	    } //end _run()


	/**
	 * Set $_SERVER variables
	 *
	 * @param string $script Script name
	 * @param string $uri    Request URI
	 *
	 * @return void
	 */

	private function _setServerVariables($script, $uri)
	    {
		$host = (method_exists($this, "webserverURL") === true) ? $this->webserverURL() : "http://localhost";
		$url  = parse_url($host);

		$_SERVER["REQUEST_SCHEME"] = "http";
		$_SERVER["HTTP_HOST"]      = $url["host"] . ((isset($url["port"]) === true) ? ":" . $url["port"] : "");

		if ($uri === "")
		    {
			$_SERVER["REQUEST_URI"] = ((isset($url["path"]) === true) ? $url["path"] : "") . "/" . $script;
		    }
		else
		    {
			$_SERVER["REQUEST_URI"] = $uri;
			$parseduri              = parse_url($uri);
			$_SERVER["PATH_INFO"]   = $parseduri["path"];
		    }

		$_SERVER["SERVER_NAME"] = $url["host"];
		$_SERVER["SCRIPT_NAME"] = "/" . $script;
		$_SERVER["PHP_SELF"]    = "/" . $script;
	    } //end _setServerVariables()


	/**
	 * Obtain and set system default include path
	 *
	 * @param string $path Additional include path
	 *
	 * @return void
	 */

	private function _resetIncludePath($path)
	    {
		$this->scriptExecutorIncludePath = get_include_path();
		set_include_path((($path !== "") ? $path . ":" : "") . ".:/usr/share/pear:/usr/share/php");

		$this->scriptExecutorAutoloadFunctions = spl_autoload_functions();
		foreach ($this->scriptExecutorAutoloadFunctions as $f)
		    {
			spl_autoload_unregister($f);
		    }

		$classes = get_declared_classes();
		foreach ($classes as $class)
		    {
			if (preg_match("/^ComposerAutoloaderInit/", $class) > 0)
			    {
				$reflectedClass    = new \ReflectionClass($class);
				$reflectedProperty = $reflectedClass->getProperty("loader");
				$reflectedProperty->setAccessible(true);
				$reflectedProperty = $reflectedProperty->setValue(null);
			    }
		    }
	    } //end _resetIncludePath()


	/**
	 * Restore include path to default for phpunit value
	 *
	 * @return void
	 */

	private function _restoreIncudePath()
	    {
		if ($this->scriptExecutorIncludePath !== false)
		    {
			set_include_path($this->scriptExecutorIncludePath);
		    }

		if (is_array($this->scriptExecutorAutoloadFunctions) === true)
		    {
			foreach ($this->scriptExecutorAutoloadFunctions as $f)
			    {
				spl_autoload_register($f);
			    }
		    }
	    } //end _restoreIncudePath()


	/**
	 * Parse string script parameters into parameters array
	 *
	 * @param array  $params Array containing name/value pairs
	 * @param string $script Script name
	 *
	 * @return array Parameters array
	 *
	 * @throws Exception Only strings allowed as parameter values
	 */

	private function _parseScriptParameters(array $params, $script)
	    {
		$s = "";
		foreach ($params as $key => $value)
		    {
			if (is_string($value) === false)
			    {
				throw new Exception("Only strings may be used as script parameters: " . $key . " for " . $script . " is not a string", 0);
			    }

			$s .= (($s !== "") ? "&" : "") . $key . "=" . urlencode($value);
		    }

		parse_str($s, $parsed);
		return $parsed;
	    } //end _parseScriptParameters()


	/**
	 * Resolve filename against the include path. Differs from stream_resolve_include_path by not resolving symlinks
	 *
	 * @param string $filename File name to resolve
	 *
	 * @return mixed Resolved absolute file name or false on failure
	 */

	private function _resolveIncludePath($filename)
	    {
		$dir = false;
		foreach (explode(PATH_SEPARATOR, PATH_SEPARATOR . get_include_path()) as $path)
		    {
			if (file_exists((($path === "") ? "" : $path . DIRECTORY_SEPARATOR) . $filename) === true)
			    {
				$dir = (($path === "") ? ((strpos($filename, DIRECTORY_SEPARATOR) === 0) ? "" : getcwd()) : $path);
				break;
			    }
		    }

		return (($dir === false) ? false : $dir . DIRECTORY_SEPARATOR . $filename);
	    } //end _resolveIncludePath()


	/**
	 * Load files
	 *
	 * @param array $files Files
	 *
	 * @return array Uploaded files
	 *
	 * @throws Exception Use PHPT test for test with files array
	 */

	private function _loadFiles(array $files)
	    {
		$uploadedfiles = array();
		if ((ini_get("runkit.internal_override") === "1") && (count($files) > 0))
		    {
			foreach ($files as $name => $data)
			    {
				$file = sys_get_temp_dir() . DIRECTORY_SEPARATOR . md5(uniqid());
				file_put_contents($file, $data["contents"]);
				$uploadedfiles[] = $file;
				if (preg_match("/^(?P<form>.*)\[(?P<name>.*)\]$/", $name, $matches) > 0)
				    {
					$_FILES[$matches["form"]] = array(
								     "name"     => array($matches["name"] => $data["name"]),
								     "type"     => array($matches["name"] => $data["type"]),
								     "tmp_name" => array($matches["name"] => $file),
								     "error"    => array($matches["name"] => UPLOAD_ERR_OK),
								     "size"     => array($matches["name"] => strlen($data["contents"])),
								    );
				    }
				else
				    {
					$_FILES[$name] = array(
							  "name"     => $data["name"],
							  "type"     => $data["type"],
							  "tmp_name" => $file,
							  "error"    => UPLOAD_ERR_OK,
							  "size"     => strlen($data["contents"]),
							 );
				    }
			    } //end foreach

			runkit_function_rename("is_uploaded_file", "is_uploaded_file_original");
			runkit_function_add("is_uploaded_file",
			    '$filename', 'return in_array($filename, array("' . implode("\",\"", $uploadedfiles) . "\"));"
			);
		    } //end if

		if ((ini_get("runkit.internal_override") !== "1") && count($files) > 0)
		    {
			throw new Exception("Use PHPT test for test with files array", 0);
		    }

		return $uploadedfiles;
	    } //end _loadFiles()


    } //end trait

?>
