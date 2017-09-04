<?php

/**
 * CodeSniffer tokenises PHP code and detects violations of a
 * defined set of coding standards.
 *
 * PHP version 5.6
 *
 * @package Logics\BuildTools\CodeSniffer
 */

namespace Logics\BuildTools\CodeSniffer;

use \DirectoryIterator;
use \Exception;
use \RecursiveDirectoryIterator;
use \RecursiveIteratorIterator;
use \SimpleXMLElement;

/**
 * CodeSniffer tokenises PHP code and detects violations of a
 * defined set of coding standards.
 *
 * Standards are specified by classes that implement the Sniff
 * interface. A sniff registers what token types it wishes to listen for, then
 * CodeSniffer encounters that token, the sniff is invoked and passed
 * information about where the token was found in the stack, and the token stack
 * itself.
 *
 * Sniff files and their containing class must be prefixed with Sniff, and
 * have an extension of .php.
 *
 * Multiple CodeSniffer operations can be performed by re-calling the
 * process function with different parameters.
 *
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @author    Marc McIntyre <mmcintyre@squiz.net>
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2006-2012 Squiz Pty Ltd (ABN 77 084 670 600)
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 * @version   SVN: $Date: 2016-08-16 23:39:01 +0900 (Tue, 16 Aug 2016) $ $Revision: 26 $
 * @link      $HeadURL: https://open.logics.net.au/buildtools/codesniffer/tags/0.1.3/src/CodeSniffer.php $
 *
 * @untranslatable PHP
 * @untranslatable JS
 * @untranslatable CSS
 * @untranslatable array
 * @untranslatable boolean
 * @untranslatable bool
 * @untranslatable float
 * @untranslatable integer
 * @untranslatable int
 * @untranslatable mixed
 * @untranslatable object
 * @untranslatable string
 * @untranslatable resource
 * @untranslatable callable
 */

class CodeSniffer
    {

	/**
	 * The file or directory that is currently being processed.
	 *
	 * @var string
	 */
	protected $file = "";

	/**
	 * The files that have been processed.
	 *
	 * @var array(Logics\BuildTools\CodeSniffer\File)
	 */
	protected $files = array();

	/**
	 * The directory to search for sniffs in.
	 *
	 * This is declared static because it is also used in the
	 * autoloader to look for sniffs outside the PHPCS install.
	 * This way, standards designed to be installed inside PHPCS can
	 * also be used from outside the PHPCS Standards directory.
	 *
	 * @var string
	 */
	protected static $standardDir = "";

	/**
	 * The CLI object controlling the run.
	 *
	 * @var string
	 */
	public $cli = null;

	/**
	 * An array of sniffs that are being used to check files.
	 *
	 * @var array(Sniff)
	 */
	protected $listeners = array();

	/**
	 * An array of rules from the ruleset.xml file.
	 *
	 * It may be empty, indicating that the ruleset does not override
	 * any of the default sniff settings.
	 *
	 * @var array
	 */
	protected $ruleset = array();

	/**
	 * The listeners array, indexed by token type.
	 *
	 * @var array
	 */
	private $_tokenListeners = array(
				    "file"      => array(),
				    "multifile" => array(),
				   );

	/**
	 * An array of patterns to use for skipping files.
	 *
	 * @var array
	 */
	protected $ignorePatterns = array();

	/**
	 * An array of extensions for files we will check.
	 *
	 * @var array
	 */
	public $allowedFileExtensions = array(
					 "php" => "PHP",
					 "inc" => "PHP",
					 "js"  => "JS",
					 "css" => "CSS",
					);

	/**
	 * An array of variable types for param/var we will check.
	 *
	 * @var array(string)
	 */
	public static $allowedTypes = array(
				       "array",
				       "boolean",
				       "bool",
				       "float",
				       "integer",
				       "int",
				       "mixed",
				       "object",
				       "string",
				       "resource",
				       "callable",
				      );

	/**
	 * Constructs a CodeSniffer object.
	 *
	 * @param int    $verbosity   The verbosity level.
	 *                            1: Print progress information.
	 *                            2: Print tokenizer debug information.
	 *                            3: Print sniff debug information.
	 * @param int    $tabWidth    The number of spaces each tab represents.
	 *                            If greater than zero, tabs will be replaced
	 *                            by spaces before testing each file.
	 * @param string $encoding    The charset of the sniffed files.
	 *                            This is important for some reports that output
	 *                            with utf-8 encoding as you don't want it double
	 *                            encoding messages.
	 * @param bool   $interactive If TRUE, will stop after each file with errors
	 *                            and wait for user input.
	 *
	 * @return void
	 *
	 * @see process()
	 *
	 * @internalconst PHPCS_DEFAULT_ERROR_SEV Error severity
	 * @internalconst PHPCS_DEFAULT_WARN_SEV  Warning severity
	 *
	 * @untranslatable Logics\BuildTools\CodeSniffer\Tokens
	 * @untranslatable iso-8859-1
	 * @untranslatable PHP_CODESNIFFER_VERBOSITY
	 * @untranslatable PHP_CODESNIFFER_TAB_WIDTH
	 * @untranslatable PHP_CODESNIFFER_ENCODING
	 * @untranslatable PHP_CODESNIFFER_INTERACTIVE
	 * @untranslatable PHPCS_DEFAULT_ERROR_SEV
	 * @untranslatable PHPCS_DEFAULT_WARN_SEV
	 * @untranslatable PHPCS_CWD
	 * @untranslatable /CodeSniffer/
	 * @untranslatable include_path
	 */

	public function __construct($verbosity = 0, $tabWidth = 0, $encoding = "iso-8859-1", $interactive = false)
	    {
		class_exists("Logics\BuildTools\CodeSniffer\Tokens", true);

		if (defined("PHP_CODESNIFFER_VERBOSITY") === false)
		    {
			define("PHP_CODESNIFFER_VERBOSITY", $verbosity);
		    }

		if (defined("PHP_CODESNIFFER_TAB_WIDTH") === false)
		    {
			define("PHP_CODESNIFFER_TAB_WIDTH", $tabWidth);
		    }

		if (defined("PHP_CODESNIFFER_ENCODING") === false)
		    {
			define("PHP_CODESNIFFER_ENCODING", $encoding);
		    }

		if (defined("PHP_CODESNIFFER_INTERACTIVE") === false)
		    {
			define("PHP_CODESNIFFER_INTERACTIVE", $interactive);
		    }

		if (defined("PHPCS_DEFAULT_ERROR_SEV") === false)
		    {
			define("PHPCS_DEFAULT_ERROR_SEV", 5);
		    }

		if (defined("PHPCS_DEFAULT_WARN_SEV") === false)
		    {
			define("PHPCS_DEFAULT_WARN_SEV", 5);
		    }

		// Change into a directory that we know about to stop any
		// relative path conflicts.
		if (defined("PHPCS_CWD") === false)
		    {
			define("PHPCS_CWD", getcwd());
		    }

		chdir(__DIR__ . "/CodeSniffer/");
		ini_set("include_path", get_include_path() . ":" . __DIR__);

		// Set default CLI object in case someone is running us
		// without using the command line script.
		$this->cli                  = new CLI();
		$this->cli->errorSeverity   = PHPCS_DEFAULT_ERROR_SEV;
		$this->cli->warningSeverity = PHPCS_DEFAULT_WARN_SEV;
		$this->cli->dieOnUnknownArg = false;
	    } //end __construct()


	/**
	 * Destructs a CodeSniffer object.
	 *
	 * Restores the current working directory to what it
	 * was before we started our run.
	 *
	 * @return void
	 *
	 * @internalconst PHPCS_CWD Current working directory
	 */

	public function __destruct()
	    {
		chdir(PHPCS_CWD);
	    } //end __destruct()


	/**
	 * Sets an array of file extensions that we will allow checking of.
	 *
	 * If the extension is one of the defaults, a specific tokenizer
	 * will be used. Otherwise, the PHP tokenizer will be used for
	 * all extensions passed.
	 *
	 * @param array $extensions An array of file extensions.
	 *
	 * @return void
	 *
	 * @untranslatable PHP
	 */

	public function setAllowedFileExtensions(array $extensions)
	    {
		$newExtensions = array();
		foreach ($extensions as $ext)
		    {
			if (isset($this->allowedFileExtensions[$ext]) === true)
			    {
				$newExtensions[$ext] = $this->allowedFileExtensions[$ext];
			    }
			else
			    {
				$newExtensions[$ext] = "PHP";
			    }
		    }

		$this->allowedFileExtensions = $newExtensions;
	    } //end setAllowedFileExtensions()


	/**
	 * Sets an array of ignore patterns that we use to skip files and folders.
	 *
	 * Patterns are not case sensitive.
	 *
	 * @param array $patterns An array of ignore patterns. The pattern is the key
	 *                        and the value is either "absolute" or "relative",
	 *                        depending on how the pattern should be applied to a
	 *                        file path.
	 *
	 * @return void
	 */

	public function setIgnorePatterns(array $patterns)
	    {
		$this->ignorePatterns = $patterns;
	    } //end setIgnorePatterns()


	/**
	 * Gets the array of ignore patterns.
	 *
	 * Optionally takes a listener to get ignore patterns specified
	 * for that sniff only.
	 *
	 * @param string $listener The listener to get patterns for. If NULL, all
	 *                         patterns are returned.
	 *
	 * @return array
	 */

	public function getIgnorePatterns($listener = null)
	    {
		if ($listener === null)
		    {
			return $this->ignorePatterns;
		    }
		else if (isset($this->ignorePatterns[$listener]) === true)
		    {
			return $this->ignorePatterns[$listener];
		    }
		else
		    {
			return array();
		    }
	    } //end getIgnorePatterns()


	/**
	 * Sets the internal CLI object.
	 *
	 * @param object $cli The CLI object controlling the run.
	 *
	 * @return void
	 */

	public function setCli($cli)
	    {
		$this->cli = $cli;
	    } //end setCli()


	/**
	 * Adds a file to the list of checked files.
	 *
	 * Checked files are used to generate error reports after the run.
	 *
	 * @param File $phpcsFile The file to add.
	 *
	 * @return void
	 */

	public function addFile(File $phpcsFile)
	    {
		$this->files[] = $phpcsFile;
	    } //end addFile()


	/**
	 * Processes the files/directories that CodeSniffer was constructed with.
	 *
	 * @param string|array $files    The files and directories to process. For
	 *                               directories, each sub directory will also
	 *                               be traversed for source files.
	 * @param string       $standard The set of code sniffs we are testing against.
	 * @param array        $sniffs   The sniff names to restrict the allowed listeners to.
	 * @param bool         $local    If true, don't recurse into directories.
	 *
	 * @return void
	 *
	 * @throws Exception If files or standard are invalid.
	 *
	 * @exceptioncode EXCEPTION_MUST_BE_A_STRING
	 *
	 * @untranslatable \$file
	 * @untranslatable \$standard
	 * @untranslatable auto_detect_line_endings
	 */

	public function process($files, $standard, array $sniffs = array(), $local = false)
	    {
		if (is_array($files) === false)
		    {
			if (is_string($files) === false || $files === null)
			    {
				throw new Exception("\$file " . _("must be a string"), EXCEPTION_MUST_BE_A_STRING);
			    }

			$files = array($files);
		    }

		if (is_string($standard) === false || $standard === null)
		    {
			throw new Exception("\$standard " . _("must be a string"), EXCEPTION_MUST_BE_A_STRING);
		    }

		// Reset the members.
		$this->listeners       = array();
		$this->files           = array();
		$this->ruleset         = array();
		$this->_tokenListeners = array(
					  "file"      => array(),
					  "multifile" => array(),
					 );

		// Ensure this option is enabled or else line endings will not always
		// be detected properly for files created on a Mac with the /r line ending.
		ini_set("auto_detect_line_endings", true);

		$this->_registerSniffs($standard, $sniffs);

		// The SVN pre-commit calls process() to init the sniffs
		// and ruleset so there may not be any files to process.
		// But this has to come after that initial setup.
		if (empty($files) === false)
		    {
			$cliValues    = $this->cli->getCommandLineValues();
			$showProgress = $cliValues["showProgress"];

			Console::report(_("Creating file list") . "... ", 0, 0, "");

			$todo     = $this->getFilesToProcess($files, $local);
			$numFiles = count($todo);

			Console::report(_("DONE") . " (" . $numFiles . " " . _("files in queue") . ")", 0, 0);

			$this->_processSingle($todo, $showProgress);

			// Now process the multi-file sniffs, assuming there are
			// multiple files being sniffed.
			if (count($files) > 1 || (count($files) === 1 && is_dir($files[0]) === true))
			    {
				$this->processMulti();
			    }
		    } //end if
	    } //end process()


	/**
	 * Register sniffs
	 *
	 * @param string $standard The set of code sniffs we are testing against
	 * @param array  $sniffs   The sniff names to restrict the allowed listeners to
	 *
	 * @return void
	 *
	 * @internalconst PHP_CODESNIFFER_VERBOSITY Verbosity
	 * @internalconst PHPCS_CWD                 Current working directory
	 */

	private function _registerSniffs($standard, array $sniffs)
	    {
		if (PHP_CODESNIFFER_VERBOSITY > 0)
		    {
			// If this is a custom ruleset.xml file, load the standard name
			// from the file. I know this looks a little ugly, but it is
			// just when verbose output is on so we have to go to the effort
			// of finding the correct name.
			$standardName = basename($standard);
			if (is_file($standard) === true)
			    {
				$ruleset = simplexml_load_file($standard);
				if ($ruleset !== false)
				    {
					$standardName = (string) $ruleset["name"];
				    }
			    }
			else if (is_file(realpath(PHPCS_CWD . "/" . $standard)) === true)
			    {
				$ruleset = simplexml_load_file(realpath(PHPCS_CWD . "/" . $standard));
				if ($ruleset !== false)
				    {
					$standardName = (string) $ruleset["name"];
				    }
			    }

			Console::report(_("Registering sniffs in") . " " . $standardName . " " . _("standard") . "... ", 0, 0, "");
			Console::report("", 0, 2);
		    } //end if

		$this->setTokenListeners($standard, $sniffs);
		$this->populateCustomRules();
		$this->populateTokenListeners();

		Console::report(_("DONE") . " (" . count($this->listeners) . " " . _("sniffs registered") . ")", 0, 0);
	    } //end _registerSniffs()


	/**
	 * Process files one by one
	 *
	 * @param array $files        List of files to check
	 * @param bool  $showProgress True if progress must be shown
	 *
	 * @return void
	 *
	 * @internalconst PHP_CODESNIFFER_VERBOSITY   Verbosity
	 * @internalconst PHP_CODESNIFFER_INTERACTIVE Interactive
	 *
	 * @untranslatable S
	 * @untranslatable E
	 * @untranslatable W
	 */

	private function _processSingle(array $files, $showProgress)
	    {
		$numFiles     = count($files);
		$numProcessed = 0;
		$dots         = 0;
		$maxLength    = strlen($numFiles);
		$lastDir      = "";
		foreach ($files as $file)
		    {
			$this->file = $file;
			$currDir    = dirname($file);
			if ($lastDir !== $currDir)
			    {
				Console::report(_("Changing into directory") . " " . $currDir, 0, 0);

				$lastDir = $currDir;
			    }

			$phpcsFile = $this->processFile($file);
			$numProcessed++;

			if (PHP_CODESNIFFER_VERBOSITY === 0 && PHP_CODESNIFFER_INTERACTIVE === false && $showProgress === true)
			    {
				// Show progress information.
				if ($phpcsFile === null)
				    {
					echo "S";
				    }
				else if ($phpcsFile->getErrorCount() > 0)
				    {
					echo "E";
				    }
				else if ($phpcsFile->getWarningCount() > 0)
				    {
					echo "W";
				    }
				else
				    {
					echo ".";
				    }

				$dots++;
				if ($dots === 60)
				    {
					$padding = ($maxLength - strlen($numProcessed));
					echo str_repeat(" ", $padding);
					echo " " . $numProcessed . " / " . $numFiles . PHP_EOL;
					$dots = 0;
				    }
			    } //end if
		    } //end foreach

		if (PHP_CODESNIFFER_VERBOSITY === 0 && PHP_CODESNIFFER_INTERACTIVE === false && $showProgress === true)
		    {
			echo PHP_EOL . PHP_EOL;
		    }
	    } //end _processSingle()


	/**
	 * Processes multi-file sniffs.
	 *
	 * @return void
	 */

	public function processMulti()
	    {
		foreach ($this->_tokenListeners["multifile"] as $listenerData)
		    {
			// Set the name of the listener for error messages.
			foreach ($this->files as $file)
			    {
				$file->setActiveListener($listenerData["class"]);
			    }

			$listenerData["listener"]->process($this->files);
		    }
	    } //end processMulti()


	/**
	 * Sets installed sniffs in the coding standard being used.
	 *
	 * Traverses the standard directory for classes that implement the
	 * Sniff interface asks them to register. Each of the
	 * sniff's class names must be exact as the basename of the sniff file.
	 * If the standard is a file, will skip transversal and just load sniffs
	 * from the file.
	 *
	 * @param string $standard The name of the coding standard we are checking.
	 *                         Can also be a path to a custom standard dir
	 *                         containing a ruleset.xml file or can be a path
	 *                         to a custom ruleset file.
	 * @param array  $sniffs   The sniff names to restrict the allowed listeners to.
	 *
	 * @return void
	 *
	 * @throws Exception If the standard is not valid.
	 *
	 * @exceptioncode EXCEPTION_INVALID_RULESET
	 * @internalconst PHPCS_CWD Current working directory
	 *
	 * @untranslatable /CodeSniffer/Standards/
	 */

	public function setTokenListeners($standard, array $sniffs = array())
	    {
		if (is_dir($standard) === true)
		    {
			// This is an absolute path to a custom standard.
			self::$standardDir = $standard;
			$standard          = basename($standard);
		    }
		else if (is_file($standard) === true)
		    {
			// Might be a custom ruleset file.
			$ruleset = simplexml_load_file($standard);
			if ($ruleset === false)
			    {
				throw new Exception(_("Ruleset") . " " . $standard . " " . _("is not valid"), EXCEPTION_INVALID_RULESET);
			    }

			if (basename($standard) === "ruleset.xml")
			    {
				// The ruleset uses the generic name, so this may actually
				// be a complete standard with it's own sniffs. By setting the
				// the standardDir to be the directory, we will process both
				// the directory (for custom sniffs) and the ruleset.xml file
				// (as it uses the generic name) in getSniffFiles.
				self::$standardDir = dirname($standard);
			    }
			else
			    {
				// This is a custom ruleset file with a custom name, so we have
				// to assume there are no custom sniffs to go with this otherwise
				// we'd be recursing through directories on every run, even if
				// we don't need to.
				self::$standardDir = $standard;
			    }

			$standard = (string) $ruleset["name"];
		    }
		else
		    {
			self::$standardDir = realpath(__DIR__ . "/CodeSniffer/Standards/" . $standard);
			if (is_dir(self::$standardDir) === false)
			    {
				// This isn't looking good. Let's see if this
				// is a relative path to a custom standard.
				$path = realpath(PHPCS_CWD . "/" . $standard);
				if (is_dir($path) === true)
				    {
					// This is a relative path to a custom standard.
					self::$standardDir = $path;
					$standard          = basename($standard);
				    }
				else if (is_file($path) === true)
				    {
					// Might be a custom ruleset file.
					$ruleset = simplexml_load_file($path);
					if ($ruleset === false)
					    {
						throw new Exception(_("Ruleset") . " " . $path . " " . _("is not valid"), EXCEPTION_INVALID_RULESET);
					    }

					// See comments in ELSE IF condition above for why we do this.
					if (basename($path) === "ruleset.xml")
					    {
						self::$standardDir = dirname($path);
					    }
					else
					    {
						self::$standardDir = $path;
					    }

					$standard = (string) $ruleset["name"];
				    } //end if
			    } //end if
		    } //end if

		$this->_setTokenListeners($this->getSniffFiles(self::$standardDir, $standard), $sniffs);
	    } //end setTokenListeners()


	/**
	 * Sets token listeners in sniffs
	 *
	 * @param string $files  List of files containg sniffs
	 * @param array  $sniffs The sniff names to restrict the allowed listeners to
	 *
	 * @return void
	 *
	 * @untranslatable _Sniffs_
	 */

	private function _setTokenListeners($files, array $sniffs)
	    {
		// Convert the allowed sniffs to lower case so
		// they are easier to check.
		foreach ($sniffs as $idx => $sniff)
		    {
			$sniffs[$idx] = strtolower($sniff);
		    }

		$listeners = array();

		foreach ($files as $file)
		    {
			// Work out where the position of /StandardName/Sniffs/... is
			// so we can determine what the class will be called.
			if (preg_match("/^.*\/(?P<standard>.*)\/Sniffs\/(?P<type>.*)\/(.*_)*(?P<basename>.*).php$/", $file, $m) > 0)
			    {
				$sniff = $m["standard"] . "_Sniffs_" . $m["type"] . "_" . $m["basename"];

				// If they have specified a list of sniffs to restrict to, check
				// to see if this sniff is allowed.
				$allowed = in_array(strtolower($sniff), $sniffs);
				if (empty($sniffs) === true || $allowed === true)
				    {
					$className         = $this->_getSniffClassName($sniff);
					$listeners[$sniff] = $className;

					Console::report(_("Registered ") . " " . $className, 1, 2);
				    } //end if
			    } //end if
		    } //end foreach

		$this->listeners = $listeners;
	    } //end _setTokenListeners()


	/**
	 * Get sniff class name from sniff name
	 *
	 * @param string $sniff Sniff name
	 *
	 * @return string Sniff class name
	 *
	 * @throws Exception No sniff class is found
	 *
	 * @exceptioncode EXCEPTION_NO_SNIFF_CLASS
	 *
	 * @untranslatable _Sniffs_
	 * @untranslatable \\Logics\\BuildTools\\CodeSniffer\\
	 */

	private function _getSniffClassName($sniff)
	    {
		if (preg_match("/(?P<standard>.*)_Sniffs_(?P<type>.*)_(?P<basename>.*)/", $sniff, $m) > 0)
		    {
			$standardName = $m["standard"];
			$type         = $m["type"];
			$basename     = $m["basename"];
		    }
		else
		    {
			$standardName = "";
			$type         = "";
			$basename     = "";
		    }

		if (class_exists("\\Logics\\BuildTools\\CodeSniffer\\" . $standardName . "\\" . $type . "_" . $basename) === true)
		    {
			$className = "\\Logics\\BuildTools\\CodeSniffer\\" . $standardName . "\\" . $type . "_" . $basename;
		    }
		else if (class_exists("\\Logics\\BuildTools\\CodeSniffer\\" . $standardName . "\\" . $standardName . "_Sniffs_" . $type . "_" . $basename) === true)
		    {
			$className = "\\Logics\\BuildTools\\CodeSniffer\\" . $standardName . "\\" . $standardName . "_Sniffs_" . $type . "_" . $basename;
		    }
		else if (class_exists("\\Logics\\BuildTools\\CodeSniffer\\" . $standardName . "\\" . $standardName . "_" . $type . "_" . $basename) === true)
		    {
			$className = "\\Logics\\BuildTools\\CodeSniffer\\" . $standardName . "\\" . $standardName . "_" . $type . "_" . $basename;
		    }
		else if (class_exists("\\Logics\\BuildTools\\CodeSniffer\\" . $standardName . "\\" . $standardName . "_" . $basename) === true)
		    {
			$className = "\\Logics\\BuildTools\\CodeSniffer\\" . $standardName . "\\" . $standardName . "_" . $basename;
		    }
		else if (class_exists("\\Logics\\BuildTools\\CodeSniffer\\" . $standardName . "\\" . $basename) === true)
		    {
			$className = "\\Logics\\BuildTools\\CodeSniffer\\" . $standardName . "\\" . $basename;
		    }
		else if (class_exists($sniff) === true)
		    {
			$className = $sniff;
		    }
		else
		    {
			throw new Exception(_("No sniff class found for sniff") . " " . $sniff, EXCEPTION_NO_SNIFF_CLASS);
		    } //end if

		return $className;
	    } //end _getSniffClassName()


	/**
	 * Return a list of sniffs that a coding standard has defined.
	 *
	 * Sniffs are found by recursing the standard directory and also by
	 * asking the standard for included sniffs.
	 *
	 * @param string $dir      The directory where to look for the files.
	 * @param string $standard The name of the coding standard. If NULL, no included sniffs will be checked for.
	 *
	 * @return array
	 *
	 * @throws Exception If an included or excluded sniff does not exist.
	 *
	 * @exceptioncode EXCEPTION_INVALID_RULESET
	 *
	 * @untranslatable ^[^\.].*Sniff\.php$
	 */

	public function getSniffFiles($dir, $standard = null)
	    {
		$ownSniffs      = $this->_getFilesByRegexp($dir, "^[^\.].*Sniff\.php$");
		$includedSniffs = array();
		$excludedSniffs = array();

		if ($standard !== null)
		    {
			$rulesetPath = ((is_dir($dir) === true) ? $dir . "/ruleset.xml" : $dir);

			$ruleset = simplexml_load_file($rulesetPath);
			if ($ruleset === false)
			    {
				throw new Exception(_("Ruleset") . " " . $rulesetPath . " " . _("is not valid"), EXCEPTION_INVALID_RULESET);
			    }

			foreach ($ruleset->rule as $rule)
			    {
				$includedSniffs = array_merge($includedSniffs, $this->_expandRulesetReference($rule["ref"]));

				if (isset($rule->exclude) === true)
				    {
					foreach ($rule->exclude as $exclude)
					    {
						$excludedSniffs = array_merge($excludedSniffs, $this->_expandRulesetReference($exclude["name"]));
					    }
				    }
			    } //end foreach
		    } //end if

		$includedSniffs = array_unique($includedSniffs);
		$excludedSniffs = array_unique($excludedSniffs);

		// Merge our own sniff list with our externally included
		// sniff list, but filter out any excluded sniffs.
		$files = array();
		foreach (array_merge($ownSniffs, $includedSniffs) as $sniff)
		    {
			if (in_array($sniff, $excludedSniffs) === false)
			    {
				$files[] = realpath($sniff);
			    }
		    }

		return array_unique($files);
	    } //end getSniffFiles()


	/**
	 * Get list of files from directory by regexp
	 *
	 * @param string $dir    Directory
	 * @param string $regexp Selection regular expression
	 *
	 * @return array Matching file names
	 */

	private function _getFilesByRegexp($dir, $regexp)
	    {
		$files = array();

		if (is_dir($dir) === true)
		    {
			$ri = new RecursiveDirectoryIterator($dir);
			$di = new RecursiveIteratorIterator($ri);
			foreach ($di as $file)
			    {
				if (preg_match("/" . $regexp . "/", $file->getFilename()) > 0)
				    {
					$files[] = $file->getPathname();
				    }
			    } //end foreach
		    } //end if

		return $files;
	    } //end _getFilesByRegexp()


	/**
	 * Expand a ruleset sniff reference into a list of sniff files.
	 *
	 * @param string $sniff The sniff reference from the rulset.xml file.
	 *
	 * @return array
	 *
	 * @throws Exception If the sniff reference is invalid.
	 *
	 * @exceptioncode EXCEPTION_SNIFF_DOES_NOT_EXIST
	 *
	 * @untranslatable Internal.
	 * @untranslatable /CodeSniffer/Standards/
	 * @untranslatable /Sniffs/
	 */

	private function _expandRulesetReference($sniff)
	    {
		$referencedSniffs = array();

		// Ignore internal sniffs as they are used to only
		// hide and change internal messages.
		if (substr($sniff, 0, 9) !== "Internal.")
		    {
			// As sniffs can't begin with a full stop, assume sniffs in
			// this format are relative paths and attempt to convert them
			// to absolute paths. If this fails, let the sniff path run through
			// the normal checks and have it fail as normal.
			if (substr($sniff, 0, 1) === ".")
			    {
				$standardDir = ((substr(self::$standardDir, -4) === ".xml") ? dirname(self::$standardDir) : self::$standardDir);

				$realpath = realpath($standardDir . "/" . $sniff);
				$sniff    = ($realpath !== false) ? $realpath : $sniff;
			    }

			$isDir = false;
			$path  = $sniff;
			if (is_dir($sniff) === true)
			    {
				// Referencing a custom standard.
				$isDir = true;
				$path  = $sniff;
				$sniff = basename($path);
			    }
			else if (is_file($sniff) === false)
			    {
				// See if this is a whole standard being referenced.
				$path = realpath(__DIR__ . "/CodeSniffer/Standards/" . $sniff);
				if (is_dir($path) === true)
				    {
					$isDir = true;
				    }
				else
				    {
					// Work out the sniff path.
					$parts = explode(".", $sniff);
					if (count($parts) < 3)
					    {
						throw new Exception(_("Referenced sniff") . " " . $sniff . " " . _("does not exist"), EXCEPTION_SNIFF_DOES_NOT_EXIST);
					    }

					$path = $parts[0] . "/Sniffs/" . $parts[1] . "/" . $parts[0] . "_" . $parts[1] . "_" . $parts[2] . "Sniff.php";
					$path = realpath(__DIR__ . "/CodeSniffer/Standards/" . $path);

					if ($path === false)
					    {
						$path = $parts[0] . "/Sniffs/" . $parts[1] . "/" . $parts[0] . "_" . $parts[2] . "Sniff.php";
						$path = realpath(__DIR__ . "/CodeSniffer/Standards/" . $path);
					    }

					if ($path === false)
					    {
						$path = $parts[0] . "/Sniffs/" . $parts[1] . "/" . $parts[2] . "Sniff.php";
						$path = realpath(__DIR__ . "/CodeSniffer/Standards/" . $path);
					    }

					if ($path === false && self::$standardDir !== "")
					    {
						// The sniff is not locally installed, so check if it is being
						// referenced as a remote sniff outside the install. We do this by
						// looking directly in the passed standard dir to see if it is
						// installed in there.
						$path = realpath(self::$standardDir . "/Sniffs/" . $parts[1] . "/" . $parts[2] . "Sniff.php");
					    }
				    } //end if
			    } //end if

			$referencedSniffs = $this->_getReferencedSniffs($sniff, $isDir, $path);
		    } //end if

		return $referencedSniffs;
	    } //end _expandRulesetReference()


	/**
	 * Get referenced sniffs
	 *
	 * @param string $sniff The sniff reference from the rulset.xml file.
	 * @param bool   $isDir True if $path pointing to directory
	 * @param string $path  Path to sniff
	 *
	 * @return array
	 *
	 * @throws Exception If the sniff reference is invalid.
	 *
	 * @exceptioncode EXCEPTION_SNIFF_DOES_NOT_EXIST
	 */

	private function _getReferencedSniffs($sniff, $isDir, $path)
	    {
		if ($isDir === true)
		    {
			if (self::isInstalledStandard($sniff) === true)
			    {
				// We are referencing a coding standard.
				$referencedSniffs = $this->getSniffFiles($path, $sniff);
				$this->populateCustomRules($path);
			    }
			else
			    {
				// We are referencing a whole directory of sniffs.
				$referencedSniffs = $this->getSniffFiles($path);
			    }
		    }
		else
		    {
			if (is_file($path) === false)
			    {
				throw new Exception(_("Referenced sniff") . " " . $sniff . " " . _("does not exist"), EXCEPTION_SNIFF_DOES_NOT_EXIST);
			    }

			if (substr($path, -9) === "Sniff.php")
			    {
				// A single sniff.
				$referencedSniffs[] = $path;
			    }
			else
			    {
				// Assume an external ruleset.xml file.
				$referencedSniffs = $this->getSniffFiles($path, $sniff);
			    }
		    } //end if

		return $referencedSniffs;
	    } //end _getReferencedSniffs()


	/**
	 * Sets installed sniffs in the coding standard being used.
	 *
	 * @param string $standard The name of the coding standard we are checking.
	 *                         Can also be a path to a custom ruleset.xml file.
	 *
	 * @return void
	 *
	 * @untranslatable exclude-pattern
	 * @untranslatable absolute
	 */

	public function populateCustomRules($standard = null)
	    {
		if ($standard === null)
		    {
			$standard = self::$standardDir;
		    }

		$standard .= ((is_file($standard) === false) ? "/ruleset.xml" : "");

		if (is_file($standard) === true)
		    {
			$ruleset = simplexml_load_file($standard);
			foreach ($ruleset->rule as $rule)
			    {
				$this->_processRule($rule);
			    }

			// Process custom ignore pattern rules.
			foreach ($ruleset->{"exclude-pattern"} as $pattern)
			    {
				$pattern["type"] = ((isset($pattern["type"]) === true) ? $pattern["type"] : "absolute");
				$this->ignorePatterns[(string) $pattern] = (string) $pattern["type"];
			    }
		    } //end if
	    } //end populateCustomRules()


	/**
	 * Process custom rule
	 *
	 * @param SimpleXMLElement $rule Custom rule
	 *
	 * @return void
	 *
	 * @untranslatable array
	 * @untranslatable exclude-pattern
	 * @untranslatable absolute
	 */

	private function _processRule(SimpleXMLElement $rule)
	    {
		if (isset($rule["ref"]) === true)
		    {
			$code = (string) $rule["ref"];

			// Custom severity.
			if (isset($rule->severity) === true)
			    {
				$this->ruleset[$code]["severity"] = (int) $rule->severity;
			    }

			// Custom message type.
			if (isset($rule->type) === true)
			    {
				$this->ruleset[$code]["type"] = (string) $rule->type;
			    }

			// Custom message.
			if (isset($rule->message) === true)
			    {
				$this->ruleset[$code]["message"] = (string) $rule->message;
			    }

			// Custom properties.
			if (isset($rule->properties) === true)
			    {
				foreach ($rule->properties->property as $prop)
				    {
					$name = (string) $prop["name"];
					if (isset($prop["type"]) === true && (string) $prop["type"] === "array")
					    {
						$this->ruleset[$code]["properties"][$name] = explode(",", (string) $prop["value"]);
					    }
					else
					    {
						$this->ruleset[$code]["properties"][$name] = (string) $prop["value"];
					    }
				    } //end foreach
			    } //end if

			// Ignore patterns.
			foreach ($rule->{"exclude-pattern"} as $pattern)
			    {
				$pattern["type"] = ((isset($pattern["type"]) === true) ? $pattern["type"] : "absolute");
				$this->ignorePatterns[$code][(string) $pattern] = (string) $pattern["type"];
			    }
		    } //end if
	    } //end _processRule()


	/**
	 * Populates the array of Sniff's for this file.
	 *
	 * @return void
	 *
	 * @throws Exception If sniff registration fails.
	 *
	 * @exceptioncode EXCEPTION_CLASS_NOT_FOUND
	 *
	 * @untranslatable PHP
	 */

	public function populateTokenListeners()
	    {
		// Construct a list of listeners indexed by token being listened for.
		$this->_tokenListeners = array(
					  "file"      => array(),
					  "multifile" => array(),
					 );

		foreach ($this->listeners as $sniff => $listenerClass)
		    {
			// Work out the internal code for this sniff. Detect usage of namespace
			// separators instead of underscores to support PHP namespaces.
			$parts = explode(((strstr($listenerClass, "\\\\") === false) ? "_" : "\\\\"), $sniff);

			$code = $parts[0] . "." . $parts[2] . "." . $parts[3];
			$code = substr($code, 0, -5);

			$listenerClass = $listenerClass;
			if (class_exists($listenerClass, true) === false)
			    {
				throw new Exception(_("Class") . " " . $listenerClass . " " . _("not found"), EXCEPTION_CLASS_NOT_FOUND);
			    }

			$this->listeners[$listenerClass] = new $listenerClass();

			// Set custom properties.
			if (isset($this->ruleset[$code]["properties"]) === true)
			    {
				foreach ($this->ruleset[$code]["properties"] as $name => $value)
				    {
					$this->setSniffProperty($listenerClass, $name, $value);
				    }
			    }

			$vars       = get_class_vars($listenerClass);
			$tokenizers = ((isset($vars["supportedTokenizers"]) === true) ? $vars["supportedTokenizers"] : array("PHP"));

			$this->_populateTokenListener($listenerClass, $tokenizers);
		    } //end foreach
	    } //end populateTokenListeners()


	/**
	 * Populate token listener
	 *
	 * @param string $listenerClass Name of listener class
	 * @param array  $tokenizers    Tokenizers utilized by the class
	 *
	 * @return void
	 *
	 * @throws Exception If sniff registration fails.
	 *
	 * @exceptioncode EXCEPTION_SNIFF_REGISTRATION_SHOULD_PROVIDE_ARRAY
	 *
	 * @untranslatable register()
	 */

	private function _populateTokenListener($listenerClass, array $tokenizers)
	    {
		if (($this->listeners[$listenerClass] instanceof Sniff) === true)
		    {
			$tokens = $this->listeners[$listenerClass]->register();
			if (is_array($tokens) === false)
			    {
				throw new Exception(
				    _("Sniff") . " " . $listenerClass . " register() " . _("method must return an array"),
				    EXCEPTION_SNIFF_REGISTRATION_SHOULD_PROVIDE_ARRAY
				);
			    }

			foreach ($tokens as $token)
			    {
				if (isset($this->_tokenListeners["file"][$token]) === false)
				    {
					$this->_tokenListeners["file"][$token] = array();
				    }

				if (in_array($this->listeners[$listenerClass], $this->_tokenListeners["file"][$token], true) === false)
				    {
					$this->_tokenListeners["file"][$token][] = array(
										    "listener"   => $this->listeners[$listenerClass],
										    "class"      => $listenerClass,
										    "tokenizers" => $tokenizers,
										   );
				    }
			    }
		    }
		else if (($this->listeners[$listenerClass] instanceof MultiFileSniff) === true)
		    {
			$this->_tokenListeners["multifile"][] = array(
								 "listener"   => $this->listeners[$listenerClass],
								 "class"      => $listenerClass,
								 "tokenizers" => $tokenizers,
								);
		    } //end if
	    } //end _populateTokenListener()


	/**
	 * Set a single property for a sniff.
	 *
	 * @param string $sniff The class name of the sniff.
	 * @param string $name  The name of the property to change.
	 * @param string $value The new value of the property.
	 *
	 * @return void
	 *
	 * @untranslatable true
	 * @untranslatable false
	 */

	public function setSniffProperty($sniff, $name, $value)
	    {
		$className = $this->_getSniffClassName($sniff);

		// Setting a property for a sniff we are not using.
		if (isset($this->listeners[$className]) === true)
		    {
			$name = trim($name);
			if (is_string($value) === true)
			    {
				$value = trim($value);
			    }

			// Special case for booleans.
			if ($value === "true")
			    {
				$value = true;
			    }
			else if ($value === "false")
			    {
				$value = false;
			    }

			$listener        = $this->listeners[$className];
			$listener->$name = $value;
		    }
	    } //end setSniffProperty()


	/**
	 * Get a list of files that will be processed.
	 *
	 * If passed directories, this method will find all files within them.
	 * The method will also perform file extension and ignore pattern filtering.
	 *
	 * @param string $paths A list of file or directory paths to process.
	 * @param bool   $local If true, only process 1 level of files in directories
	 *
	 * @return array
	 *
	 * @see shouldProcessFile()
	 */

	public function getFilesToProcess($paths, $local = false)
	    {
		$files = array();

		foreach ($paths as $path)
		    {
			if (is_dir($path) === true)
			    {
				if ($local === true)
				    {
					$di = new DirectoryIterator($path);
				    }
				else
				    {
					$ri = new RecursiveDirectoryIterator($path);
					$di = new RecursiveIteratorIterator($ri);
				    }

				foreach ($di as $file)
				    {
					// Check if the file exists after all symlinks are resolved.
					$filePath = realpath($file->getPathname());
					if ($filePath !== false && is_dir($filePath) === false &&
					    $this->shouldProcessFile($file->getPathname(), $path) === true)
					    {
						$files[] = $file->getPathname();
					    }
				    } //end foreach
			    }
			else
			    {
				if ($this->shouldIgnoreFile($path, dirname($path)) === false)
				    {
					$files[] = $path;
				    }
			    } //end if
		    } //end foreach

		return $files;
	    } //end getFilesToProcess()


	/**
	 * Checks filtering rules to see if a file should be checked.
	 *
	 * Checks both file extension filters and path ignore filters.
	 *
	 * @param string $path    The path to the file being checked.
	 * @param string $basedir The directory to use for relative path checks.
	 *
	 * @return bool
	 */

	public function shouldProcessFile($path, $basedir)
	    {
		// Check that the file's extension is one we are checking.
		// We are strict about checking the extension and we don't
		// let files through with no extension or that start with a dot.
		$fileName  = basename($path);
		$fileParts = explode(".", $fileName);
		if ($fileParts[0] === $fileName || $fileParts[0] === "")
		    {
			return false;
		    }
		else
		    {
			// Checking multi-part file extensions, so need to create a
			// complete extension list and make sure one is allowed.
			$extensions = array();
			array_shift($fileParts);
			while (empty($fileParts) === false)
			    {
				$extensions[implode(".", $fileParts)] = 1;
				array_shift($fileParts);
			    }

			$matches = array_intersect_key($extensions, $this->allowedFileExtensions);
			if (empty($matches) === true)
			    {
				return false;
			    }
			else if ($this->shouldIgnoreFile($path, $basedir) === true)
			    {
				// If the file's path matches one of our ignore patterns, skip it.
				return false;
			    }
			else
			    {
				return true;
			    }
		    } //end if
	    } //end shouldProcessFile()


	/**
	 * Checks filtering rules to see if a file should be ignored.
	 *
	 * @param string $path    The path to the file being checked.
	 * @param string $basedir The directory to use for relative path checks.
	 *
	 * @return bool
	 *
	 * @untranslatable absolute
	 * @untranslatable relative
	 * @untranslatable /i
	 */

	public function shouldIgnoreFile($path, $basedir)
	    {
		$relativePath = $path;
		if (strpos($path, $basedir) === 0)
		    {
			// The +1 cuts off the directory separator as well.
			$relativePath = substr($path, (strlen($basedir) + 1));
		    }

		$ignore = false;
		foreach ($this->ignorePatterns as $pattern => $type)
		    {
			if (is_array($pattern) === false)
			    {
				// Maintains backwards compatibility in case the ignore pattern does
				// not have a relative/absolute value.
				if (is_int($pattern) === true)
				    {
					$pattern = $type;
					$type    = "absolute";
				    }

				$replacements = array(
						 "\\\\," => ",",
						 "*"     => ".*",
						);

				// We assume a / directory separator, as do the exclude rules
				// most developers write, so we need a special case for any system
				// that is different.
				if (DIRECTORY_SEPARATOR === "\\\\")
				    {
					$replacements["/"] = "\\\\\\\\";
				    }

				$pattern = strtr($pattern, $replacements);

				if ($type === "relative")
				    {
					$testPath = $relativePath;
				    }
				else
				    {
					$testPath = $path;
				    }

				if (preg_match("/" . preg_quote($pattern, "/") . "/i", $testPath) === 1)
				    {
					$ignore = true;
					break;
				    }
			    } //end if
		    } //end foreach

		return $ignore;
	    } //end shouldIgnoreFile()


	/**
	 * Run the code sniffs over a single given file.
	 *
	 * Processes the file and runs the CodeSniffer sniffs to verify that it
	 * conforms with the standard. Returns the processed file object, or NULL
	 * if no file was processed due to error.
	 *
	 * @param string $file     The file to process.
	 * @param string $contents The contents to parse. If NULL, the content is taken from the file system.
	 *
	 * @return File
	 *
	 * @throws Exception If the file could not be processed.
	 *
	 * @exceptioncode EXCEPTION_NO_SOURCE_FILE
	 *
	 * @see _processFile()
	 *
	 * @internalconst PHP_CODESNIFFER_INTERACTIVE Interactive
	 *
	 * @untranslatable codingStandardsIgnoreFile
	 */

	public function processFile($file, $contents = null)
	    {
		if ($contents === null && file_exists($file) === false)
		    {
			throw new Exception(_("Source file") . " " . $file . " " . _("does not exist"), EXCEPTION_NO_SOURCE_FILE);
		    }

		$filePath = realpath($file);
		if ($filePath === false)
		    {
			$filePath = $file;
		    }

		// Before we go and spend time tokenizing this file, just check
		// to see if there is a tag up top to indicate that the whole
		// file should be ignored. It must be on one of the first two lines.
		$firstContent = $contents;
		if ($contents === null && is_readable($filePath) === true)
		    {
			$firstContent = file_get_contents($filePath);
		    }

		if (strpos($firstContent, "@" . "codingStandardsIgnoreFile") !== false)
		    {
			// We are ignoring the whole file.
			Console::report(_("Ignoring") . " " . basename($filePath), 0, 0);

			return null;
		    }
		else
		    {
			$phpcsFile = $this->_safeProcessFile($file, $contents);
			if (PHP_CODESNIFFER_INTERACTIVE === true)
			    {
				$phpcsFile = $this->_runInteractively($phpcsFile, $file, $contents);
			    } //end if

			return $phpcsFile;
		    } //end if
	    } //end processFile()


	/**
	 * Running interactively.
	 * Print the error report for the current file and then wait for user input.
	 *
	 * @param File   $phpcsFile File to process
	 * @param string $file      The file to process.
	 * @param string $contents  The contents to parse. If NULL, the content is taken from the file system.
	 *
	 * @return File
	 *
	 * @untranslatable full
	 */

	private function _runInteractively(File $phpcsFile, $file, $contents)
	    {
		$reporting = new Reporting();
		$cliValues = $this->cli->getCommandLineValues();

		// Get current violations and then clear the list to make sure
		// we only print violations for a single file each time.
		$numErrors = null;
		while ($numErrors !== 0)
		    {
			$filesViolations = $this->getFilesErrors();
			$this->files     = array();

			$numErrors = $reporting->printReport("full", $filesViolations, $cliValues["showSources"], null, $cliValues["reportWidth"]);

			if ($numErrors !== 0)
			    {
				echo _("<ENTER> to recheck, [s] to skip or [q] to quit :") . " ";
				$input = trim(fgets(STDIN));

				switch ($input)
				    {
					case "s":
					    break;
					case "q":
					    exit(0);
					default:
						// Repopulate the sniffs because some of them save their state
						// and only clear it when the file changes, but we are rechecking
						// the same file.
						$this->populateTokenListeners();
						$phpcsFile = $this->_safeProcessFile($file, $contents);
					    break;
				    }
			    } //end if
		    } //end while

		return $phpcsFile;
	    } //end _runInteractively()


	/**
	 * Process the sniffs for a single file.
	 *
	 * Catch exceptions which may be generated by sniffs and add them to returned file object
	 *
	 * @param string $file     The file to process.
	 * @param string $contents The contents to parse. If NULL, the content
	 *                         is taken from the file system.
	 *
	 * @return File
	 * @see    processFile()
	 */

	private function _safeProcessFile($file, $contents)
	    {
		try
		    {
			$phpcsFile = $this->_processFile($file, $contents);
		    }
		catch (Exception $e)
		    {
			$phpcsFile = new File(
					$this->_traceFileName($e), $this->_tokenListeners["file"],
					$this->allowedFileExtensions, $this->ruleset, $this
				     );
			$this->addFile($phpcsFile);
			$phpcsFile->addError(_("An error occurred during processing; checking has been aborted. The error message was:") . " " . $e->getMessage(), null);
		    } //end try

		return $phpcsFile;
	    } //end _safeProcessFile()


	/**
	 * Get file name from exception trace
	 *
	 * @param Exception $e Exception from processing failure
	 *
	 * @return string File name
	 *
	 * @untranslatable Logics\BuildTools\CodeSniffer\File
	 */

	private function _traceFileName(Exception $e)
	    {
		$trace = $e->getTrace();

		$filename = $trace[0]["args"][0];
		if (is_object($filename) === true && get_class($filename) === "Logics\BuildTools\CodeSniffer\File")
		    {
			$filename = $filename->getFilename();
		    }
		else if (is_numeric($filename) === true)
		    {
			// See if we can find the File object.
			foreach ($trace as $data)
			    {
				if (isset($data["args"][0]) === true && ($data["args"][0] instanceof File) === true)
				    {
					$filename = $data["args"][0]->getFilename();
				    }
			    }
		    }
		else if (is_string($filename) === false)
		    {
			$filename = (string) $filename;
		    }

		return $filename;
	    } //end _traceFileName()


	/**
	 * Process the sniffs for a single file.
	 *
	 * Does raw processing only. No interactive support or error checking.
	 *
	 * @param string $file     The file to process.
	 * @param string $contents The contents to parse. If NULL, the content
	 *                         is taken from the file system.
	 *
	 * @return File
	 * @see    processFile()
	 *
	 * @internalconst PHP_CODESNIFFER_INTERACTIVE Interactive
	 * @internalconst PHP_CODESNIFFER_VERBOSITY   Verbosity
	 */

	private function _processFile($file, $contents)
	    {
		$startTime = time();
		Console::report(_("Processing") . " " . basename($file) . " ", 0, 0, "");
		Console::report("", 0, 1);

		$phpcsFile = new File($file, $this->_tokenListeners["file"], $this->allowedFileExtensions, $this->ruleset, $this);
		$this->addFile($phpcsFile);
		$phpcsFile->start($contents);

		// Clean up the test if we can to save memory. This can't be done if
		// we need to leave the files around for multi-file sniffs.
		if (PHP_CODESNIFFER_INTERACTIVE === false && empty($this->_tokenListeners["multifile"]) === true)
		    {
			$phpcsFile->cleanUp();
		    }

		if (PHP_CODESNIFFER_VERBOSITY > 0)
		    {
			$timeTaken = (time() - $startTime);
			if ($timeTaken === 0)
			    {
				Console::report(_("DONE in < 1 second"), 0, 0, "");
			    }
			else if ($timeTaken === 1)
			    {
				Console::report(_("DONE in 1 second"), 0, 0, "");
			    }
			else
			    {
				Console::report(_("DONE in") . " " . $timeTaken . " " . _("seconds"), 0, 0, "");
			    }

			$errors   = $phpcsFile->getErrorCount();
			$warnings = $phpcsFile->getWarningCount();
			Console::report(" (" . $errors . " " . _("errors") . ", " . $warnings . " " . _("warnings") . ")", 0, 0);
		    }

		return $phpcsFile;
	    } //end _processFile()


	/**
	 * Gives collected violations for reports.
	 *
	 * @return array
	 */

	public function getFilesErrors()
	    {
		$files = array();
		foreach ($this->files as $file)
		    {
			$files[$file->getFilename()] = array(
							"warnings"    => $file->getWarnings(),
							"errors"      => $file->getErrors(),
							"numWarnings" => $file->getWarningCount(),
							"numErrors"   => $file->getErrorCount(),
						       );
		    }

		return $files;
	    } //end getFilesErrors()


	/**
	 * Generates documentation for a coding standard.
	 *
	 * @param string $standard  The standard to generate docs for
	 * @param array  $sniffs    A list of sniffs to limit the docs to.
	 * @param string $generator The name of the generator class to use.
	 *
	 * @return void
	 *
	 * @throws Exception Documenatation generator is absent
	 *
	 * @exceptioncode EXCEPTION_CLASS_NOT_FOUND
	 *
	 * @untranslatable Text
	 * @untranslatable DocGenerator
	 */

	public function generateDocs($standard, array $sniffs = array(), $generator = "Text")
	    {
		$class = $generator . "DocGenerator";

		if (class_exists($generator . "DocGenerator", true) === false)
		    {
			throw new Exception(_("Class") . " " . $generator . "DocGenerator " . _("not found"), EXCEPTION_CLASS_NOT_FOUND);
		    }

		$generator = new $class($standard, $sniffs);
		$generator->generate();
	    } //end generateDocs()


	/**
	 * Returns the CodeSniffer file objects.
	 *
	 * @return array(Logics\BuildTools\CodeSniffer\File)
	 */

	public function getFiles()
	    {
		return $this->files;
	    } //end getFiles()


	/**
	 * Gets the array of Sniff's.
	 *
	 * @return array(Sniff)
	 */

	public function getSniffs()
	    {
		return $this->listeners;
	    } //end getSniffs()


	/**
	 * Gets the array of Sniff's indexed by token type.
	 *
	 * @return array
	 */

	public function getTokenSniffs()
	    {
		return $this->_tokenListeners;
	    } //end getTokenSniffs()


	/**
	 * Returns true if the specified string is in the camel caps format.
	 *
	 * @param string $string      The string the verify.
	 * @param bool   $classFormat If true, check to see if the string is in the
	 *                            class format. Class format strings must start
	 *                            with a capital letter and contain no
	 *                            underscores.
	 * @param bool   $public      If true, the first character in the string
	 *                            must be an a-z character. If false, the
	 *                            character must be an underscore. This
	 *                            argument is only applicable if $classFormat
	 *                            is false.
	 * @param bool   $strict      If true, the string must not have two capital
	 *                            letters next to each other. If false, a
	 *                            relaxed camel caps policy is used to allow
	 *                            for acronyms.
	 * @param bool   $underscore  If true, underscores are allowed in the middle
	 *
	 * @return bool
	 *
	 * @untranslatable [a-z]
	 * @untranslatable [A-Z]
	 */

	public static function isCamelCaps($string, $classFormat = false, $public = true, $strict = true, $underscore = false)
	    {
		if ($classFormat === false)
		    {
			$legalFirstChar = (($public === false) ? "_" : "") . "[a-z]";
		    }
		else
		    {
			$legalFirstChar = "[A-Z]";
		    }

		if (preg_match("/^" . $legalFirstChar . "[a-zA-Z0-9" . (($underscore === true) ? "_" : "") . "]*$/", $string) === 0)
		    {
			return false;
		    }
		else if ($strict === false)
		    {
			return true;
		    }
		else
		    {
			// Check that there are not two capital letters next to each other.
			return (preg_match("/[A-Z][A-Z]/", $string) === 0);
		    }
	    } //end isCamelCaps()


	/**
	 * Returns true if the specified string is in the underscore caps format.
	 *
	 * @param string $string The string to verify.
	 *
	 * @return bool
	 */

	public static function isUnderscoreName($string)
	    {
		if (strpos($string, " ") === false)
		    {
			$validName = true;
			$nameBits  = explode("_", $string);

			if (preg_match("|^[A-Z]|", $string) === 0)
			    {
				// Name does not begin with a capital letter.
				$validName = false;
			    }
			else
			    {
				foreach ($nameBits as $bit)
				    {
					if ($bit !== "")
					    {
						if ($bit{0} !== strtoupper($bit{0}))
						    {
							$validName = false;
							break;
						    }
					    }
				    }
			    }

			return $validName;
		    }
		else
		    {
			// If there are space in the name, it can't be valid.
			return false;
		    } //end if
	    } //end isUnderscoreName()


	/**
	 * Returns a valid variable type for param/var tag.
	 *
	 * If type is not one of the standard type, it must be a custom type.
	 * Returns the correct type name suggestion if type name is invalid.
	 *
	 * @param string $varType The variable type to process.
	 * @param bool   $short   Shorthand types are preferred
	 *
	 * @return string
	 *
	 * @untranslatable bool
	 * @untranslatable float
	 * @untranslatable int
	 * @untranslatable array
	 * @untranslatable array(
	 */

	public static function suggestType($varType, $short = false)
	    {
		if ($short === true)
		    {
			$types = array(
				  "boolean" => "bool",
				  "double"  => "float",
				  "real"    => "float",
				  "integer" => "int",
				  "array()" => "array",
				 );
		    }
		else
		    {
			$types = array(
				  "bool"    => "boolean",
				  "double"  => "float",
				  "real"    => "float",
				  "int"     => "integer",
				  "array()" => "array",
				 );
		    }

		$lowerVarType = strtolower($varType);

		if ($varType === "")
		    {
			return "";
		    }
		else if (isset($types[$lowerVarType]) === true)
		    {
			return $types[$lowerVarType];
		    }
		else if (strpos($lowerVarType, "array(") !== false)
		    {
			/*
			    Let's try to process types in array declaration. Following are valid array declarations:
			     1. array(type)
			     2. array(type1 => type2)
			*/

			if (preg_match("/^array\(\s*(?P<type1>[^\s^=^>]*)(\s*=>\s*(?P<type2>.*))?\s*\)/i", $varType, $matches) !== 0)
			    {
				$type1 = ((isset($matches["type1"]) === true) ? $matches["type1"] : "");
				$type2 = ((isset($matches["type2"]) === true) ? $matches["type2"] : "");

				$type1 = self::suggestType($type1, $short);
				$type2 = self::suggestType($type2, $short);
				if ($type2 !== "")
				    {
					$type2 = " => " . $type2;
				    }

				return "array(" . $type1 . $type2 . ")";
			    }
			else
			    {
				return "array";
			    } //end if
		    }
		else if (in_array($lowerVarType, self::$allowedTypes) === true)
		    {
			// A valid type, but not lower cased.
			return $lowerVarType;
		    }
		else
		    {
			// Must be a custom type name.
			return $varType;
		    } //end if
	    } //end suggestType()


	/**
	 * Get a list of all coding standards installed.
	 *
	 * Coding standards are directories located in the
	 * CodeSniffer/Standards directory. Valid coding standards
	 * include a Sniffs subdirectory.
	 *
	 * @param bool   $includeGeneric If true, the special "Generic"
	 *                               coding standard will be included
	 *                               if installed.
	 * @param string $standardsDir   A specific directory to look for standards
	 *                               in. If not specified, CodeSniffer will
	 *                               look in its default location.
	 *
	 * @return array
	 *
	 * @see isInstalledStandard()
	 *
	 * @untranslatable /CodeSniffer/Standards
	 * @untranslatable Generic
	 */

	public static function getInstalledStandards($includeGeneric = false, $standardsDir = "")
	    {
		$installedStandards = array();

		if ($standardsDir === "")
		    {
			$standardsDir = __DIR__ . "/CodeSniffer/Standards";
		    }

		$di = new DirectoryIterator($standardsDir);
		foreach ($di as $file)
		    {
			if ($file->isDir() === true && $file->isDot() === false)
			    {
				$filename = $file->getFilename();

				// Ignore the special "Generic" standard.
				if ($includeGeneric === true || $filename !== "Generic")
				    {
					// Valid coding standard dirs include a standard class.
					$csFile = $file->getPathname() . "/ruleset.xml";
					if (is_file($csFile) === true)
					    {
						// We found a coding standard directory.
						$installedStandards[] = $filename;
					    }
				    }
			    }
		    }

		return $installedStandards;
	    } //end getInstalledStandards()


	/**
	 * Determine if a standard is installed.
	 *
	 * Coding standards are directories located in the
	 * CodeSniffer/Standards directory. Valid coding standards
	 * include a Sniffs subdirectory.
	 *
	 * @param string $standard The name of the coding standard.
	 *
	 * @return bool
	 *
	 * @see getInstalledStandards()
	 *
	 * @untranslatable /CodeSniffer/Standards/
	 */

	public static function isInstalledStandard($standard)
	    {
		$standardDir = __DIR__ . "/CodeSniffer/Standards/" . $standard;
		if (is_file($standardDir . "/ruleset.xml") === true)
		    {
			return true;
		    }
		else
		    {
			if (is_file(rtrim($standard, " /\\") . DIRECTORY_SEPARATOR . "ruleset.xml") === true)
			    {
				// This could be a custom standard, installed outside our
				// standards directory.
				return true;
			    }
			else if (is_file($standard) === true && substr(strtolower($standard), -4) === ".xml")
			    {
				// Might also be an actual ruleset file itself.
				// If it has an XML extension, let's at least try it.
				return true;
			    }
			else
			    {
				return false;
			    }
		    } //end if
	    } //end isInstalledStandard()


    } //end class

?>
