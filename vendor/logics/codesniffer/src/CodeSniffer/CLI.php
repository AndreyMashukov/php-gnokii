<?php

/**
 * A class to process command line phpcs scripts.
 *
 * PHP version 5.6
 *
 * @package Logics\BuildTools\CodeSniffer
 */

namespace Logics\BuildTools\CodeSniffer;

use \PHP_Timer;

/**
 * A class to process command line phpcs scripts.
 *
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2006-2012 Squiz Pty Ltd (ABN 77 084 670 600)
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 * @version   SVN: $Date: 2016-08-16 23:39:01 +0900 (Tue, 16 Aug 2016) $ $Revision: 26 $
 * @link      $HeadURL: https://open.logics.net.au/buildtools/codesniffer/tags/0.1.3/src/CodeSniffer/CLI.php $
 */

class CLI
    {

	/**
	 * An array of all values specified on the command line.
	 *
	 * @var array
	 */
	protected $values = array();

	/**
	 * The minimum severity level errors must have to be displayed.
	 *
	 * @var bool
	 */
	public $errorSeverity = 0;

	/**
	 * The minimum severity level warnings must have to be displayed.
	 *
	 * @var bool
	 */
	public $warningSeverity = 0;

	/**
	 * Whether or not to kill the process when an unknown command line arg is found.
	 *
	 * If FALSE, arguments that are not command line options or file/directory paths
	 * will be ignored and execution will continue.
	 *
	 * @var bool
	 */
	public $dieOnUnknownArg = true;

	/**
	 * Exits if the minimum requirements of PHP_CodSniffer are not met.
	 *
	 * @return void
	 *
	 * @untranslatable tokenizer
	 */

	public function checkRequirements()
	    {
		// Check the PHP version.
		if (version_compare(PHP_VERSION, "5.1.2") === -1)
		    {
			echo _("ERROR: CodeSniffer requires PHP version 5.1.2 or greater.") . PHP_EOL;
			exit(2);
		    }

		if (extension_loaded("tokenizer") === false)
		    {
			echo _("ERROR: CodeSniffer requires the tokenizer extension to be enabled.") . PHP_EOL;
			exit(2);
		    }
	    } //end checkRequirements()


	/**
	 * Get a list of default values for all possible command line arguments.
	 *
	 * @return array
	 *
	 * @untranslatable report_format
	 * @untranslatable tab_width
	 * @untranslatable encoding
	 * @untranslatable iso-8859-1
	 * @untranslatable severity
	 * @untranslatable error_severity
	 * @untranslatable warning_severity
	 * @untranslatable show_warnings
	 * @untranslatable report_width
	 * @untranslatable show_progress
	 */

	public function getDefaults()
	    {
		// The default values for config settings.
		$defaults["files"]           = array();
		$defaults["standard"]        = null;
		$defaults["verbosity"]       = 0;
		$defaults["interactive"]     = false;
		$defaults["explain"]         = false;
		$defaults["local"]           = false;
		$defaults["showSources"]     = false;
		$defaults["extensions"]      = array();
		$defaults["sniffs"]          = array();
		$defaults["ignored"]         = array();
		$defaults["reportFile"]      = null;
		$defaults["generator"]       = "";
		$defaults["reports"]         = array();
		$defaults["errorSeverity"]   = null;
		$defaults["warningSeverity"] = null;

		$reportFormat = Config::getConfigData("report_format");
		if ($reportFormat !== null)
		    {
			$defaults["reports"][$reportFormat] = null;
		    }

		$tabWidth             = Config::getConfigData("tab_width");
		$defaults["tabWidth"] = ($tabWidth === null) ? 0 : (int) $tabWidth;

		$encoding             = Config::getConfigData("encoding");
		$defaults["encoding"] = ($encoding === null) ? "iso-8859-1" : strtolower($encoding);

		$severity = Config::getConfigData("severity");
		if ($severity !== null)
		    {
			$defaults["errorSeverity"]   = (int) $severity;
			$defaults["warningSeverity"] = (int) $severity;
		    }

		$severity = Config::getConfigData("error_severity");
		if ($severity !== null)
		    {
			$defaults["errorSeverity"] = (int) $severity;
		    }

		$severity = Config::getConfigData("warning_severity");
		if ($severity !== null)
		    {
			$defaults["warningSeverity"] = (int) $severity;
		    }

		$showWarnings = Config::getConfigData("show_warnings");
		if ($showWarnings !== null)
		    {
			$showWarnings = (bool) $showWarnings;
			if ($showWarnings === false)
			    {
				$defaults["warningSeverity"] = 0;
			    }
		    }

		$reportWidth             = Config::getConfigData("report_width");
		$defaults["reportWidth"] = ($reportWidth === null) ? 80 : (int) $reportWidth;

		$showProgress             = Config::getConfigData("show_progress");
		$defaults["showProgress"] = ($showProgress === null) ? false : (bool) $showProgress;

		return $defaults;
	    } //end getDefaults()


	/**
	 * Gets the processed command line values.
	 *
	 * If the values have not yet been set, the values will be sourced
	 * from the command line arguments.
	 *
	 * @return array
	 */

	public function getCommandLineValues()
	    {
		$values = $this->getDefaults();

		$args = $_SERVER["argv"];
		array_shift($args);

		$values = $this->setCommandLineValues($args);
		return $values;
	    } //end getCommandLineValues()


	/**
	 * Set the command line values.
	 *
	 * @param array $args An array of command line arguments to process.
	 *
	 * @return array
	 */

	public function setCommandLineValues(array $args)
	    {
		$values = $this->getDefaults();

		$this->_cliArgs = $args;
		$numArgs        = count($args);

		for ($i = 0; $i < $numArgs; $i++)
		    {
			$arg = $this->_cliArgs[$i];
			if ($arg !== "" && $arg{0} === "-")
			    {
				if ($arg !== "-" && $arg !== "--" && $arg{1} === "-")
				    {
					$values = $this->processLongArgument(substr($arg, 2), $i, $values);
				    }
				else if ($arg !== "-" && $arg !== "--")
				    {
					$switches = str_split($arg);
					foreach ($switches as $switch)
					    {
						if ($switch !== "-")
						    {
							$values = $this->processShortArgument($switch, $i, $values);
						    }
					    }
				    }
			    }
			else if ($arg !== "")
			    {
				$values = $this->processUnknownArgument($arg, $i, $values);
			    } //end if
		    } //end for

		return $values;
	    } //end setCommandLineValues()


	/**
	 * Processes a short (-e) command line argument.
	 *
	 * @param string $arg    The command line argument.
	 * @param int    $pos    The position of the argument on the command line.
	 * @param array  $values An array of values determined from CLI args.
	 *
	 * @return array The updated CLI values.
	 *
	 * @see getCommandLineValues()
	 */

	public function processShortArgument($arg, $pos, array $values)
	    {
		switch ($arg)
		    {
			case "h":
			case "?":
				$this->printUsage();
			    exit(0);
			case "i" :
				$this->printInstalledStandards();
			    exit(0);
			case "d" :
				$ini = explode("=", $_SERVER["argv"][($pos + 1)]);
				$_SERVER["argv"][($pos + 1)] = "";
				ini_set($ini[0], (isset($ini[1]) === true) ? $ini[1] : true);
			    break;
			default:
				$options = array(
					    "v" => array("verbosity" => $values["verbosity"] + 1),
					    "l" => array("local" => true),
					    "s" => array("showSources" => true),
					    "a" => array("interactive" => true),
					    "e" => array("explain" => true),
					    "p" => array("showProgress" => true),
					    "n" => array("warningSeverity" => 0),
					    "w" => array("warningSeverity" => null),
					   );

				$found = false;
				foreach ($options as $option => $setting)
				    {
					if ($option === $arg)
					    {
						foreach ($setting as $key => $value)
						    {
							$values[$key] = $value;
						    }

						$found = true;
						break;
					    }
				    }

				if ($found === false)
				    {
					$values = $this->processUnknownArgument("-" . $arg, $pos, $values);
				    }
			    break;
		    } //end switch

		return $values;
	    } //end processShortArgument()


	/**
	 * Processes a long (--example) command line argument.
	 *
	 * @param string $arg    The command line argument.
	 * @param int    $pos    The position of the argument on the command line.
	 * @param array  $values An array of values determined from CLI args.
	 *
	 * @return array The updated CLI values.
	 *
	 * @see getCommandLineValues()
	 *
	 * @untranslatable CodeSniffer version 1.4.5 (stable)
	 * @untranslatable by Squiz Pty Ltd. (http://www.squiz.com.au)
	 * @untranslatable setSniffs
	 * @untranslatable setReportFile
	 * @untranslatable setReportWidth
	 * @untranslatable setReport
	 * @untranslatable setStandard
	 * @untranslatable setExtensions
	 * @untranslatable setSeverity
	 * @untranslatable setErrorSeverity
	 * @untranslatable setWarningSeverity
	 * @untranslatable setIgnore
	 * @untranslatable setGenerator
	 * @untranslatable setEncoding
	 * @untranslatable setTabWidth
	 */

	public function processLongArgument($arg, $pos, array $values)
	    {
		switch ($arg)
		    {
			case "help":
				$this->printUsage();
			    exit(0);
			case "version":
				echo "CodeSniffer version 1.4.5 (stable) ";
				echo "by Squiz Pty Ltd. (http://www.squiz.com.au)" . PHP_EOL;
			    exit(0);
			case "config-set":
				$key   = $_SERVER["argv"][($pos + 1)];
				$value = $_SERVER["argv"][($pos + 2)];
				Config::setConfigData($key, $value);
			    exit(0);
			case "config-delete":
				$key = $_SERVER["argv"][($pos + 1)];
				Config::setConfigData($key, null);
			    exit(0);
			case "config-show":
				$data = Config::getAllConfigData();
				var_dump($data);
			    exit(0);
			default:
				$options = array(
					    "^sniffs=(?P<arg>.*)$"                                => "setSniffs",
					    "^report-file=(?P<arg>.*)$"                           => "setReportFile",
					    "^report-width=(?P<arg>.*)$"                          => "setReportWidth",
					    "^report(?P<sign>-)(?P<report>.*)(=(?P<output>.*))?$" => "setReport",
					    "^report(?P<sign>=)(?P<report>.*)$"                   => "setReport",
					    "^standard=(?P<arg>.*)$"                              => "setStandard",
					    "^extensions=(?P<arg>.*)$"                            => "setExtensions",
					    "^severity=(?P<arg>.*)$"                              => "setSeverity",
					    "^error-severity=(?P<arg>.*)$"                        => "setErrorSeverity",
					    "^warning-severity=(?P<arg>.*)$"                      => "setWarningSeverity",
					    "^ignore=(?P<arg>.*)$"                                => "setIgnore",
					    "^generator=(?P<arg>.*)$"                             => "setGenerator",
					    "^encoding=(?P<arg>.*)$"                              => "setEncoding",
					    "^tab-width=(?P<arg>.*)$"                             => "setTabWidth",
					   );

				$found = false;
				foreach ($options as $pattern => $method)
				    {
					if (preg_match("/" . $pattern . "/", $arg, $match) > 0)
					    {
						$values = call_user_func(array($this, "_" . $method), $match, $values);
						$found  = true;
						break;
					    }
				    }

				if ($found === false)
				    {
					$values = $this->processUnknownArgument("--" . $arg, $pos, $values);
				    }
			    break;
		    } //end switch

		return $values;
	    } //end processLongArgument()


	/**
	 * Set sniffs
	 *
	 * @param array $match  Regexp match result
	 * @param array $values CLI config values
	 *
	 * @return array Amended CLI config values
	 *
	 * @untranslatable _Sniffs_
	 * @untranslatable Sniff
	 */

	private function _setSniffs(array $match, array $values)
	    {
		$values["sniffs"] = array();

		$sniffs = explode(",", $match["arg"]);

		// Convert the sniffs to class names.
		foreach ($sniffs as $sniff)
		    {
			$parts              = explode(".", $sniff);
			$values["sniffs"][] = $parts[0] . "_Sniffs_" . $parts[1] . "_" . $parts[2] . "Sniff";
		    }

		return $values;
	    } //end _setSniffs()


	/**
	 * Set report file
	 *
	 * @param array $match  Regexp match result
	 * @param array $values CLI config values
	 *
	 * @return array Amended CLI config values
	 */

	private function _setReportFile(array $match, array $values)
	    {
		$values["reportFile"] = realpath($match["arg"]);

		// It may not exist and return false instead.
		if ($values["reportFile"] === false)
		    {
			$values["reportFile"] = $match["arg"];
		    }

		if (is_dir($values["reportFile"]) === true)
		    {
			echo _("ERROR: The specified report file path") . " \"" . $values["reportFile"] . "\" " . _("is a directory.") . PHP_EOL . PHP_EOL;
			$this->printUsage();
			exit(2);
		    }

		$dir = dirname($values["reportFile"]);
		if (is_dir($dir) === false)
		    {
			echo _("ERROR: The specified report file path") . " \"" . $values["reportFile"] . "\" " . _("points to a non-existent directory.") . PHP_EOL . PHP_EOL;
			$this->printUsage();
			exit(2);
		    }

		return $values;
	    } //end _setReportFile()


	/**
	 * Set report width
	 *
	 * @param array $match  Regexp match result
	 * @param array $values CLI config values
	 *
	 * @return array Amended CLI config values
	 */

	private function _setReportWidth(array $match, array $values)
	    {
		$values["reportWidth"] = (int) $match["arg"];
		return $values;
	    } //end _setReportWidth()


	/**
	 * Set report
	 *
	 * @param array $match  Regexp match result
	 * @param array $values CLI config values
	 *
	 * @return array Amended CLI config values
	 *
	 * @untranslatable full
	 * @untranslatable xml
	 * @untranslatable checkstyle
	 * @untranslatable csv
	 * @untranslatable emacs
	 * @untranslatable notifysend
	 * @untranslatable source
	 * @untranslatable summary
	 * @untranslatable svnblame
	 * @untranslatable gitblame
	 * @untranslatable hgblame
	 */

	private function _setReport(array $match, array $values)
	    {
		$report = $match["report"];
		if ($match["sign"] === "=")
		    {
			if (isset($match["output"]) === true && $match["output"] !== "")
			    {
				$output = $match["output"];
			    }
			else
			    {
				$output = null;
			    }
		    }

		$validReports = array(
				 "full",
				 "xml",
				 "checkstyle",
				 "csv",
				 "emacs",
				 "notifysend",
				 "source",
				 "summary",
				 "svnblame",
				 "gitblame",
				 "hgblame",
				);

		if (in_array($report, $validReports) === false)
		    {
			echo _("ERROR: Report type") . " \"" . $report . "\" " . _("not known.") . PHP_EOL;
			exit(2);
		    }

		$values["reports"][$report] = $output;
		return $values;
	    } //end _setReport()


	/**
	 * Set standard
	 *
	 * @param array $match  Regexp match result
	 * @param array $values CLI config values
	 *
	 * @return array Amended CLI config values
	 */

	private function _setStandard(array $match, array $values)
	    {
		$values["standard"] = $match["arg"];
		return $values;
	    } //end _setStandard()


	/**
	 * Set extensions
	 *
	 * @param array $match  Regexp match result
	 * @param array $values CLI config values
	 *
	 * @return array Amended CLI config values
	 */

	private function _setExtensions(array $match, array $values)
	    {
		$values["extensions"] = explode(",", $match["arg"]);
		return $values;
	    } //end _setExtensions()


	/**
	 * Set severity
	 *
	 * @param array $match  Regexp match result
	 * @param array $values CLI config values
	 *
	 * @return array Amended CLI config values
	 */

	private function _setSeverity(array $match, array $values)
	    {
		$values["errorSeverity"]   = (int) $match["arg"];
		$values["warningSeverity"] = (int) $match["arg"];
		return $values;
	    } //end _setSeverity()


	/**
	 * Set error severity
	 *
	 * @param array $match  Regexp match result
	 * @param array $values CLI config values
	 *
	 * @return array Amended CLI config values
	 */

	private function _setErrorSeverity(array $match, array $values)
	    {
		$values["errorSeverity"] = (int) $match["arg"];
		return $values;
	    } //end _setErrorSeverity()


	/**
	 * Set warning severity
	 *
	 * @param array $match  Regexp match result
	 * @param array $values CLI config values
	 *
	 * @return array Amended CLI config values
	 */

	private function _setWarningSeverity(array $match, array $values)
	    {
		$values["warningSeverity"] = (int) $match["arg"];
		return $values;
	    } //end _setWarningSeverity()


	/**
	 * Set ignore
	 *
	 * @param array $match  Regexp match result
	 * @param array $values CLI config values
	 *
	 * @return array Amended CLI config values
	 *
	 * @untranslatable absolute
	 */

	private function _setIgnore(array $match, array $values)
	    {
		// Split the ignore string on commas, unless the comma is escaped using 1 or 3 slashes (\, or \\\,).
		$ignored = preg_split('/(?<=(?<!\\\\)\\\\\\\\),|(?<!\\\\),/', $match["arg"]);
		foreach ($ignored as $pattern)
		    {
			$values["ignored"][$pattern] = "absolute";
		    }

		return $values;
	    } //end _setIgnore()


	/**
	 * Set generator
	 *
	 * @param array $match  Regexp match result
	 * @param array $values CLI config values
	 *
	 * @return array Amended CLI config values
	 */

	private function _setGenerator(array $match, array $values)
	    {
		$values["generator"] = $match["arg"];
		return $values;
	    } //end _setGenerator()


	/**
	 * Set encoding
	 *
	 * @param array $match  Regexp match result
	 * @param array $values CLI config values
	 *
	 * @return array Amended CLI config values
	 */

	private function _setEncoding(array $match, array $values)
	    {
		$values["encoding"] = strtolower($match["arg"]);
		return $values;
	    } //end _setEncoding()


	/**
	 * Set tab width
	 *
	 * @param array $match  Regexp match result
	 * @param array $values CLI config values
	 *
	 * @return array Amended CLI config values
	 */

	private function _setTabWidth(array $match, array $values)
	    {
		$values["tabWidth"] = (int) $match["arg"];
		return $values;
	    } //end _setTabWidth()


	/**
	 * Processes an unknown command line argument.
	 *
	 * Assumes all unknown arguments are files and folders to check.
	 *
	 * @param string $arg    The command line argument.
	 * @param int    $pos    The position of the argument on the command line.
	 * @param array  $values An array of values determined from CLI args.
	 *
	 * @return array The updated CLI values.
	 *
	 * @see getCommandLineValues()
	 */

	public function processUnknownArgument($arg, $pos, array $values)
	    {
		unset($pos);

		// We don't know about any additional switches; just files.
		if ($arg{0} === "-")
		    {
			if ($this->dieOnUnknownArg === true)
			    {
				echo _("ERROR: option") . " \"" . $arg . "\" " . _("not known.") . PHP_EOL . PHP_EOL;
				$this->printUsage();
				exit(2);
			    }
		    }
		else
		    {
			$file = realpath($arg);
			if (file_exists($file) === false)
			    {
				if ($file !== false)
				    {
					if ($this->dieOnUnknownArg === true)
					    {
						echo _("ERROR: The file") . " \"" . $arg . "\" " . _("does not exist.") . PHP_EOL . PHP_EOL;
						$this->printUsage();
						exit(2);
					    }
				    }
			    }
			else
			    {
				$values["files"][] = $file;
			    }
		    } //end if

		return $values;
	    } //end processUnknownArgument()


	/**
	 * Runs CodeSniffer over files and directories.
	 *
	 * @param array $values An array of values determined from CLI args.
	 *
	 * @return int The number of error and warning messages shown.
	 *
	 * @see getCommandLineValues()
	 *
	 * @internalconst PHPCS_DEFAULT_ERROR_SEV Error severity
	 * @internalconst PHPCS_DEFAULT_WARN_SEV  Warning severity
	 *
	 * @untranslatable r
	 * @untranslatable STDIN
	 */

	public function process(array $values = array())
	    {
		if (empty($values) === true)
		    {
			$values = $this->getCommandLineValues();
		    }

		if ($values["generator"] !== "")
		    {
			$phpcs = new CodeSniffer($values["verbosity"]);
			$phpcs->generateDocs($values["standard"], $values["files"], $values["generator"]);
			exit(0);
		    }

		$values["standard"] = $this->validateStandard($values["standard"]);
		if (CodeSniffer::isInstalledStandard($values["standard"]) === false)
		    {
			// They didn't select a valid coding standard, so help them
			// out by letting them know which standards are installed.
			echo _("ERROR: the") . " \"" . $values["standard"] . "\" " . _("coding standard is not installed. ");
			$this->printInstalledStandards();
			exit(2);
		    }

		if ($values["explain"] === true)
		    {
			$this->explainStandard($values["standard"]);
			exit(0);
		    }

		$fileContents = "";
		if (empty($values["files"]) === true)
		    {
			// Check if they passing in the file contents.
			$handle       = fopen("php://stdin", "r");
			$fileContents = stream_get_contents($handle);
			fclose($handle);

			if ($fileContents === "")
			    {
				// No files and no content passed in.
				echo _("ERROR: You must supply at least one file or directory to process.") . PHP_EOL . PHP_EOL;
				$this->printUsage();
				exit(2);
			    }
		    }

		$phpcs = new CodeSniffer($values["verbosity"], $values["tabWidth"], $values["encoding"], $values["interactive"]);

		// Set file extensions if they were specified. Otherwise,
		// let CodeSniffer decide on the defaults.
		if (empty($values["extensions"]) === false)
		    {
			$phpcs->setAllowedFileExtensions($values["extensions"]);
		    }

		// Set ignore patterns if they were specified.
		if (empty($values["ignored"]) === false)
		    {
			$phpcs->setIgnorePatterns($values["ignored"]);
		    }

		// Set some convenience member vars.
		$this->errorSeverity   = (($values["errorSeverity"] === null) ? PHPCS_DEFAULT_ERROR_SEV : $values["errorSeverity"]);
		$this->warningSeverity = (($values["warningSeverity"] === null) ? PHPCS_DEFAULT_WARN_SEV : $values["warningSeverity"]);

		$phpcs->setCli($this);
		$phpcs->process($values["files"], $values["standard"], $values["sniffs"], $values["local"]);

		if ($fileContents !== "")
		    {
			$phpcs->processFile("STDIN", $fileContents);
		    }

		return $this->printErrorReport($phpcs, $values["reports"], $values["showSources"], $values["reportFile"], $values["reportWidth"]);
	    } //end process()


	/**
	 * Prints the error report for the run.
	 *
	 * Note that this function may actually print multiple reports
	 * as the user may have specified a number of output formats.
	 *
	 * @param CodeSniffer $phpcs       The PHP_CodeSniffer object containing
	 *                                 the errors.
	 * @param array       $reports     A list of reports to print.
	 * @param bool        $showSources TRUE if report should show error sources
	 *                                 (not used by all reports).
	 * @param string      $reportFile  A default file to log report output to.
	 * @param int         $reportWidth How wide the screen reports should be.
	 *
	 * @return int The number of error and warning messages shown.
	 *
	 * @internalconst PHP_CODESNIFFER_INTERACTIVE Interactive
	 *
	 * @untranslatable PHP_Timer
	 */

	public function printErrorReport(CodeSniffer $phpcs, array $reports, $showSources, $reportFile, $reportWidth)
	    {
		$reporting       = new Reporting();
		$filesViolations = $phpcs->getFilesErrors();

		if (empty($reports) === true)
		    {
			$reports["full"] = $reportFile;
		    }

		$errors   = 0;
		$toScreen = false;

		foreach ($reports as $report => $output)
		    {
			if ($output === null)
			    {
				$output = $reportFile;
			    }

			if ($reportFile === null)
			    {
				$toScreen = true;
			    }

			// We don't add errors here because the number of
			// errors reported by each report type will always be the
			// same, so we really just need 1 number.
			$errors = $reporting->printReport($report, $filesViolations, $showSources, $output, $reportWidth);
		    }

		// Only print PHP_Timer output if no reports were
		// printed to the screen so we don't put additional output
		// in something like an XML report. If we are printing to screen,
		// the report types would have already worked out who should
		// print the timer info.
		if ($toScreen === false && PHP_CODESNIFFER_INTERACTIVE === false && class_exists("PHP_Timer", false) === true)
		    {
			echo PHP_Timer::resourceUsage() . PHP_EOL . PHP_EOL;
		    }

		// They should all return the same value, so it
		// doesn't matter which return value we end up using.
		return $errors;
	    } //end printErrorReport()


	/**
	 * Convert the passed standard into a valid standard.
	 *
	 * Checks things like default values and case.
	 *
	 * @param string $standard The standard to validate.
	 *
	 * @return string
	 *
	 * @untranslatable default_standard
	 * @untranslatable Beauty
	 */

	public function validateStandard($standard)
	    {
		if ($standard === null)
		    {
			// They did not supply a standard to use.
			// Try to get the default from the config system.
			$standard = Config::getConfigData("default_standard");
			if ($standard === null)
			    {
				$standard = "Beauty";
			    }
		    }

		// Check if the standard name is valid. If not, check that the case
		// was not entered incorrectly.
		if (CodeSniffer::isInstalledStandard($standard) === false)
		    {
			$installedStandards = CodeSniffer::getInstalledStandards();
			foreach ($installedStandards as $validStandard)
			    {
				if (strtolower($standard) === strtolower($validStandard))
				    {
					$standard = $validStandard;
					break;
				    }
			    }
		    }

		return $standard;
	    } //end validateStandard()


	/**
	 * Prints a report showing the sniffs contained in a standard.
	 *
	 * @param string $standard The standard to validate.
	 *
	 * @return void
	 */

	public function explainStandard($standard)
	    {
		$phpcs = new CodeSniffer();
		$phpcs->setTokenListeners($standard);
		$sniffs = $phpcs->getSniffs();
		$sniffs = array_keys($sniffs);
		sort($sniffs);

		ob_start();

		$lastStandard = "";
		$lastCount    = "";
		$sniffCount   = count($sniffs);
		$sniffs[]     = "___";

		echo PHP_EOL . _("The") . " " . $standard . " " . _("standard contains") . " " . $sniffCount . " " . _("sniffs") . PHP_EOL;

		ob_start();

		foreach ($sniffs as $sniff)
		    {
			$parts = explode("_", $sniff);
			if ($lastStandard === "")
			    {
				$lastStandard = $parts[0];
			    }

			if ($parts[0] !== $lastStandard)
			    {
				$sniffList = ob_get_contents();
				ob_end_clean();

				echo PHP_EOL . $lastStandard . " (" . $lastCount . " " . _("sniffs") . ")" . PHP_EOL;
				echo str_repeat("-", (strlen($lastStandard . $lastCount) + 10));
				echo PHP_EOL;
				echo $sniffList;

				$lastStandard = $parts[0];
				$lastCount    = 0;

				ob_start();
			    }

			echo "  " . $parts[0] . "." . $parts[2] . "." . substr($parts[3], 0, -5) . PHP_EOL;
			$lastCount++;
		    } //end foreach

		ob_end_clean();
	    } //end explainStandard()


	/**
	 * Prints out the usage information for this script.
	 *
	 * @return void
	 *
	 * @untranslatable phpcs [-nwlsaepvi] [-d key[=value]]
	 * @untranslatable [--report=<report>] [--report-file=<reportfile>] [--report-<report>=<reportfile>] ...
	 * @untranslatable [--report-width=<reportWidth>] [--generator=<generator>] [--tab-width=<tabWidth>]
	 * @untranslatable [--severity=<severity>] [--error-severity=<severity>] [--warning-severity=<severity>]
	 * @untranslatable [--config-set key value] [--config-delete key] [--config-show]
	 * @untranslatable [--standard=<standard>] [--sniffs=<sniffs>] [--encoding=<encoding>]
	 * @untranslatable [--extensions=<extensions>] [--ignore=<patterns>] <file> ...
	 * @untranslatable -n
	 * @untranslatable --warning-severity=0)
	 * @untranslatable -w
	 * @untranslatable -l
	 * @untranslatable -s
	 * @untranslatable -a
	 * @untranslatable -e
	 * @untranslatable -p
	 * @untranslatable -v[v][v]
	 * @untranslatable -i
	 * @untranslatable -d
	 * @untranslatable [key] php.ini
	 * @untranslatable [value]
	 * @untranslatable [true]
	 * @untranslatable --help
	 * @untranslatable --version
	 * @untranslatable <file>
	 * @untranslatable <extensions>
	 * @untranslatable <patterns>
	 * @untranslatable <encoding>
	 * @untranslatable <sniffs>
	 * @untranslatable <severity>
	 * @untranslatable <standard>
	 * @untranslatable <tabWidth>
	 * @untranslatable <generator>
	 * @untranslatable <report>
	 * @untranslatable \"full\", \"xml\", \"checkstyle\", \"csv\", \"emacs\"
	 * @untranslatable \"source\", \"summary\", \"svnblame\", \"gitblame\", \"hgblame\"
	 * @untranslatable \"notifysend\"
	 * @untranslatable \"full\"
	 * @untranslatable <reportfile>
	 * @untranslatable <reportWidth>
	 */

	public function printUsage()
	    {
		echo _("Usage:") . " phpcs [-nwlsaepvi] [-d key[=value]]" . PHP_EOL;
		echo "    [--report=<report>] [--report-file=<reportfile>] [--report-<report>=<reportfile>] ..." . PHP_EOL;
		echo "    [--report-width=<reportWidth>] [--generator=<generator>] [--tab-width=<tabWidth>]" . PHP_EOL;
		echo "    [--severity=<severity>] [--error-severity=<severity>] [--warning-severity=<severity>]" . PHP_EOL;
		echo "    [--config-set key value] [--config-delete key] [--config-show]" . PHP_EOL;
		echo "    [--standard=<standard>] [--sniffs=<sniffs>] [--encoding=<encoding>]" . PHP_EOL;
		echo "    [--extensions=<extensions>] [--ignore=<patterns>] <file> ..." . PHP_EOL;
		echo "        -n            " . _("Do not print warnings (shortcut for") . " --warning-severity=0)" . PHP_EOL;
		echo "        -w            " . _("Print both warnings and errors (on by default)") . PHP_EOL;
		echo "        -l            " . _("Local directory only, no recursion") . PHP_EOL;
		echo "        -s            " . _("Show sniff codes in all reports") . PHP_EOL;
		echo "        -a            " . _("Run interactively") . PHP_EOL;
		echo "        -e            " . _("Explain a standard by showing the sniffs it includes") . PHP_EOL;
		echo "        -p            " . _("Show progress of the run") . PHP_EOL;
		echo "        -v[v][v]      " . _("Print verbose output") . PHP_EOL;
		echo "        -i            " . _("Show a list of installed coding standards") . PHP_EOL;
		echo "        -d            " . _("Set the") . " [key] php.ini " . _("value to") . " [value] " . _("or") . " [true] " . _("if value is omitted") . PHP_EOL;
		echo "        --help        " . _("Print this help message") . PHP_EOL;
		echo "        --version     " . _("Print version information") . PHP_EOL;
		echo "        <file>        " . _("One or more files and/or directories to check") . PHP_EOL;
		echo "        <extensions>  " . _("A comma separated list of file extensions to check") . PHP_EOL;
		echo "                      " . _("(only valid if checking a directory)") . PHP_EOL;
		echo "        <patterns>    " . _("A comma separated list of patterns to ignore files and directories") . PHP_EOL;
		echo "        <encoding>    " . _("The encoding of the files being checked (default is iso-8859-1)") . PHP_EOL;
		echo "        <sniffs>      " . _("A comma separated list of sniff codes to limit the check to") . PHP_EOL;
		echo "                      " . _("(all sniffs must be part of the specified standard)") . PHP_EOL;
		echo "        <severity>    " . _("The minimum severity required to display an error or warning") . PHP_EOL;
		echo "        <standard>    " . _("The name or path of the coding standard to use") . PHP_EOL;
		echo "        <tabWidth>    " . _("The number of spaces each tab represents") . PHP_EOL;
		echo "        <generator>   " . _("The name of a doc generator to use") . PHP_EOL;
		echo "                      " . _("(forces doc generation instead of checking)") . PHP_EOL;
		echo "        <report>      " . _("Print either the") . " \"full\", \"xml\", \"checkstyle\", \"csv\", \"emacs\"" . PHP_EOL;
		echo "                      \"source\", \"summary\", \"svnblame\", \"gitblame\", \"hgblame\" " . _("or") . PHP_EOL;
		echo "                      \"notifysend\" " . _("report") . PHP_EOL;
		echo "                      " . _("(the") . " \"full\" " . _("report is printed by default)") . PHP_EOL;
		echo "        <reportfile>  " . _("Write the report to the specified file path") . PHP_EOL;
		echo "        <reportWidth> " . _("How many columns wide screen reports should be printed") . PHP_EOL;
	    } //end printUsage()


	/**
	 * Prints out a list of installed coding standards.
	 *
	 * @return void
	 */

	public function printInstalledStandards()
	    {
		$installedStandards = CodeSniffer::getInstalledStandards();
		$numStandards       = count($installedStandards);

		if ($numStandards === 0)
		    {
			echo _("No coding standards are installed.") . PHP_EOL;
		    }
		else
		    {
			$lastStandard = array_pop($installedStandards);
			if ($numStandards === 1)
			    {
				echo _("The only coding standard installed is") . " " . $lastStandard . PHP_EOL;
			    }
			else
			    {
				$standardList  = implode(", ", $installedStandards);
				$standardList .= " " . _("and") . " " . $lastStandard;
				echo _("The installed coding standards are") . " " . $standardList . PHP_EOL;
			    }
		    }
	    } //end printInstalledStandards()


    } //end class

?>
