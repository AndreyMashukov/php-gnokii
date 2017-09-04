<?php

/**
 * Notify-send report for CodeSniffer.
 *
 * PHP version 5.6
 *
 * @package Logics\BuildTools\CodeSniffer
 */

namespace Logics\BuildTools\CodeSniffer;

/**
 * Notify-send report for CodeSniffer.
 *
 * Supported configuration parameters:
 * - notifysend_path    - Full path to notify-send cli command
 * - notifysend_timeout - Timeout in milliseconds
 * - notifysend_showok  - Show "ok, all fine" messages (0/1)
 *
 * @author    Christian Weiske <christian.weiske@netresearch.de>
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2012 Christian Weiske
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 * @version   SVN: $Date: 2016-08-16 23:39:01 +0900 (Tue, 16 Aug 2016) $ $Revision: 26 $
 * @link      $HeadURL: https://open.logics.net.au/buildtools/codesniffer/tags/0.1.3/src/CodeSniffer/Reports/NotifysendReport.php $
 *
 * @untranslatable notify-send
 */

class NotifysendReport implements Report
    {

	/**
	 * Notification timeout in milliseconds.
	 *
	 * @var int
	 */
	protected $timeout = 3000;

	/**
	 * Path to notify-send command.
	 *
	 * @var string
	 */
	protected $path = "notify-send";

	/**
	 * Show "ok, all fine" messages.
	 *
	 * @var bool
	 */
	protected $showOk = true;

	/**
	 * Version of installed notify-send executable.
	 *
	 * @var string
	 */
	protected $version = null;

	/**
	 * Load configuration data.
	 *
	 * @return void
	 *
	 * @untranslatable notifysend_path
	 * @untranslatable notifysend_timeout
	 * @untranslatable notifysend_showok
	 * @untranslatable notify-send
	 * @untranslatable --version
	 */

	public function __construct()
	    {
		$path = Config::getConfigData("notifysend_path");
		if ($path !== null)
		    {
			$this->path = $path;
		    }

		$timeout = Config::getConfigData("notifysend_timeout");
		if ($timeout !== null)
		    {
			$this->timeout = (int) $timeout;
		    }

		$showOk = Config::getConfigData("notifysend_showok");
		if ($showOk !== null)
		    {
			$this->showOk = (boolean) $showOk;
		    }

		$this->version = str_replace("notify-send ", "", exec($this->path . " --version"));
	    } //end __construct()


	/**
	 * Generates a summary of errors and warnings for each file processed.
	 *
	 * If verbose output is enabled, results are shown for all files, even if
	 * they have no errors or warnings. If verbose output is disabled, we only
	 * show files that have at least one warning or error.
	 *
	 * @param array $report      Prepared report.
	 * @param bool  $showSources Show sources?
	 * @param int   $width       Maximum allowed line width.
	 * @param bool  $toScreen    Is the report being printed to screen?
	 *
	 * @return string
	 */

	public function generate(array $report, $showSources = false, $width = 80, $toScreen = true)
	    {
		unset($showSources);
		unset($width);
		unset($toScreen);

		$msg = $this->generateMessage($report);
		if ($msg === null)
		    {
			if ($this->showOk === true)
			    {
				$this->notifyAllFine();
			    }

			return 0;
		    }
		else
		    {
			$this->notifyErrors($msg);

			return ($report["totals"]["errors"] + $report["totals"]["warnings"]);
		    }
	    } //end generate()


	/**
	 * Generate the error message to show to the user.
	 *
	 * @param array $report CS report data.
	 *
	 * @return string Error message or NULL if no error/warning found.
	 */

	protected function generateMessage(array $report)
	    {
		$allErrors   = $report["totals"]["errors"];
		$allWarnings = $report["totals"]["warnings"];

		if ($allErrors === 0 && $allWarnings === 0)
		    {
			// Nothing to print.
			return null;
		    }
		else
		    {
			$msg = "";
			if (count($report["files"]) > 1)
			    {
				$msg .= _("Checked") . " " . count($report["files"]) . " " . _("files") . PHP_EOL;
			    }
			else
			    {
				$msg .= key($report["files"]) . PHP_EOL;
			    }

			if ($allWarnings > 0)
			    {
				$msg .= $allWarnings . " " . _("warnings") . PHP_EOL;
			    }

			if ($allErrors > 0)
			    {
				$msg .= $allErrors . " " . _("errors") . PHP_EOL;
			    }

			return $msg;
		    } //end if
	    } //end generateMessage()


	/**
	 * Tell the user that all is fine and no error/warning has been found.
	 *
	 * @return void
	 *
	 * @untranslatable -i info
	 * @untranslatable \"PHP CodeSniffer: Ok\"
	 * @untranslatable \"All fine\"
	 */

	protected function notifyAllFine()
	    {
		$cmd  = $this->getBasicCommand();
		$cmd .= " -i info";
		$cmd .= " \"PHP CodeSniffer: Ok\"";
		$cmd .= " \"All fine\"";
		exec($cmd);
	    } //end notifyAllFine()


	/**
	 * Tell the user that errors/warnings have been found.
	 *
	 * @param string $msg Message to display.
	 *
	 * @return void
	 *
	 * @untranslatable -i error
	 * @untranslatable \"PHP CodeSniffer: Error\"
	 */

	protected function notifyErrors($msg)
	    {
		$cmd  = $this->getBasicCommand();
		$cmd .= " -i error";
		$cmd .= " \"PHP CodeSniffer: Error\"";
		$cmd .= " " . escapeshellarg(trim($msg));
		exec($cmd);
	    } //end notifyErrors()


	/**
	 * Generate and return the basic notify-send command string to execute.
	 *
	 * @return string Shell command with common parameters.
	 *
	 * @untranslatable --category dev.validate
	 * @untranslatable -t
	 * @untranslatable -a phpcs
	 */

	protected function getBasicCommand()
	    {
		$cmd  = escapeshellcmd($this->path);
		$cmd .= " --category dev.validate";
		$cmd .= " -t " . (int) $this->timeout;
		if (version_compare($this->version, "0.7.3", ">=") === true)
		    {
			$cmd .= " -a phpcs";
		    }

		return $cmd;
	    } //end getBasicCommand()


    } //end class

?>
