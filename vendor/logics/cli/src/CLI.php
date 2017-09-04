<?php

/**
 * PHP version 5.6
 *
 * @package Logics\Foundation\CLI
 */

namespace Logics\Foundation\CLI;

use \Exception;

/**
 * Command Line Interface class
 *
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://opensource.org/licenses/MIT MIT License
 * @version   SVN: $Date: 2016-10-14 01:18:54 +0800 (Fri, 14 Oct 2016) $ $Revision: 90 $
 * @link      $HeadURL: https://open.logics.net.au/foundation/CLI/tags/0.1.3/src/CLI.php $
 */

abstract class CLI
    {

	use ConfigurationFile;

	/**
	 * True if only one such process is permitted
	 *
	 * @var bool
	 */
	private $_exclusive;

	/**
	 * Process ID
	 *
	 * @var int
	 */
	private $_pid;

	/**
	 * Run file name
	 *
	 * @var string
	 */
	private $_runfile;

	/**
	 * Command line parser
	 *
	 * @var Parser
	 */
	private $_parser;

	/**
	 * Instantiate this class
	 *
	 * @param bool $exclusive True if this script should run in exclusive mode, second process of this script should not start
	 *
	 * @return void
	 *
	 * @optionalconst RUN_DIRECTORY "/var/run" Location where run files are kept
	 *
	 * @untranslatable RUN_DIRECTORY
	 */

	public function __construct($exclusive = false)
	    {
		$this->_exclusive = $exclusive;
		$this->_pid       = posix_getpid();
		$this->_rundir    = (defined("RUN_DIRECTORY") === true) ? RUN_DIRECTORY : sys_get_temp_dir();
		$this->_runfile   = "";
		$this->checkExclusivity();
	    } //end __construct()


	/**
	 * Class destructor removes run file
	 *
	 * @return void
	 */

	public function __destruct()
	    {
		if ($this->_exclusive === true && $this->_pid === posix_getpid())
		    {
			clearstatcache();
			if (file_exists($this->_rundir . DIRECTORY_SEPARATOR . $this->_runfile) === true)
			    {
				unlink($this->_rundir . DIRECTORY_SEPARATOR . $this->_runfile);
			    }
		    }
	    } //end __destruct()


	/**
	 * Perform checks that we are running exclusively
	 *
	 * @param string $runfile Name of run file
	 *
	 * @return void
	 *
	 * @untranslatable cli
	 * @untranslatable /proc/
	 */

	protected function checkExclusivity($runfile = "")
	    {
		$script = stream_resolve_include_path($_SERVER["SCRIPT_FILENAME"]);

		$this->_runfile = ($runfile === "") ? md5($script) : $runfile;

		if ($this->_exclusive === true && PHP_SAPI === "cli")
		    {
			clearstatcache();
			if (file_exists($this->_rundir . DIRECTORY_SEPARATOR . $this->_runfile) === true)
			    {
				list($pid, ) = explode(" ", file_get_contents($this->_rundir . DIRECTORY_SEPARATOR . $this->_runfile), 2);
				if (is_dir("/proc/" . $pid) === true && (int) $pid !== $this->_pid)
				    {
					exit();
				    }
			    }

			file_put_contents($this->_rundir . DIRECTORY_SEPARATOR . $this->_runfile, $this->_pid . " " . $script);
		    } //end if
	    } //end checkExclusivity()


	/**
	 * Register options and arguments on the given $parser object
	 *
	 * @param Parser $parser Command line parser
	 *
	 * @return void
	 */

	abstract protected function setup(Parser $parser);


	/**
	 * Your main program
	 *
	 * Arguments and options have been parsed when this is run
	 *
	 * @param Parser $parser Command line parser
	 *
	 * @return void
	 */

	abstract protected function main(Parser $parser);


	/**
	 * Execute the CLI program
	 *
	 * Executes the setup() routine, adds default options, initiate the options parsing and argument checking
	 * and finally executes main()
	 *
	 * @return void
	 *
	 * @untranslatable cli
	 * @untranslatable no-colors
	 * @untranslatable h|help!
	 * @untranslatable h
	 * @untranslatable white
	 * @untranslatable red
	 */

	public function run()
	    {
		if (PHP_SAPI !== "cli")
		    {
			echo Colorize::text(_("This program has to be run from the command line") . "\n", "white", "red", true);
			exit(255);
		    }

		$this->_parser = new Parser();
		$this->setup($this->_parser);
		$this->_parser->registerOption("no-colors", _("Do not use any colors in output. Useful when piping output to other tools or files."));
		$this->_parser->registerOption("h|help!", _("Display this help screen and exit immediately."));

		try
		    {
			$this->_parser->parse();

			if ($this->_parser->getOption("no-colors") === true)
			    {
				Colorize::disable();
			    }

			if ($this->_parser->getOption("h") === true)
			    {
				echo $this->_parser->help();
				exit(0);
			    }

			$this->main($this->_parser);
		    }
		catch (Exception $e)
		    {
			echo Colorize::text($e->getMessage(), "white", "red", true) . "\n";
			exit($e->getCode());
		    } //end try
	    } //end run()


	/**
	 * Add script to crontab
	 *
	 * @param string $crontabname Name of file in cron.d directory
	 * @param string $schedule    Cron schedule
	 * @param string $executable  Fully qualified executable name
	 * @param string $user        User name under which this cron entry will be executed
	 *
	 * @return void
	 *
	 * @requiredconst CROND "/etc/cron.d" Location where crontab files are kept
	 *
	 * @untranslatable root
	 * @untranslatable cli
	 */

	protected function updateCronTab($crontabname, $schedule = "* * * * *", $executable = "", $user = "root")
	    {
		if (PHP_SAPI === "cli")
		    {
			if ($executable === "")
			    {
				if (dirname($_SERVER["PHP_SELF"]) === ".")
				    {
					$self = $_SERVER["PWD"] . "/" . $_SERVER["PHP_SELF"];
				    }
				else
				    {
					$self = $_SERVER["PHP_SELF"];
				    }

				$executable = PHP_BINARY . " " . $self;
			    }

			$crontab = $schedule . " " . $user . " " . $executable . "\n";

			$cronconfig = array($crontabname => $crontab);
			$this->commitConfiguration(CROND, $cronconfig, false);
		    }
	    } //end updateCronTab()


    } //end class

?>