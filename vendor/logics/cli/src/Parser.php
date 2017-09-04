<?php

/**
 * PHP version 5.6
 *
 * @package Logics\Foundation\CLI
 */

namespace Logics\Foundation\CLI;

use \Closure;
use \Exception;

/**
 * Class Parser
 *
 * Parses command line options passed to the CLI script. Allows CLI scripts to easily register all accepted options and
 * commands and even generates a help text from this setup.
 *
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://opensource.org/licenses/MIT MIT License
 * @version   SVN: $Date: 2016-08-17 23:19:29 +0930 (Wed, 17 Aug 2016) $ $Revision: 63 $
 * @link      $HeadURL: http://svn.logics.net.au/foundation/CLI/trunk/src/CLI.php $
 */

class Parser
    {

	/**
	 * Keeps help introduction
	 *
	 * @var string
	 */
	private $_intro;

	/**
	 * Keeps the current parser configuration
	 *
	 * @var array
	 */
	private $_setup;

	/**
	 * Passed arguments
	 *
	 * @var array
	 */
	private $_args = array();

	/**
	 * The executed script
	 *
	 * @var string
	 */
	private $_bin;

	/**
	 * Current parsed command if any
	 *
	 * @var string
	 */
	private $_command = "";

	/**
	 * Store parsed options
	 *
	 * @var array
	 */
	private $_options = array();

	/**
	 * Store parsed arguments
	 *
	 * @var array
	 */
	private $_arguments = array();

	/**
	 * Constructor
	 *
	 * @return void
	 */

	public function __construct()
	    {
		$this->_intro = "";
		$this->_setup = array(
				 "" => array(
					"opts" => array(),
					"args" => array(),
					"help" => "",
				       )
				);

		$this->_options   = array();
		$this->_arguments = array();
	    } //end __construct()


	/**
	 * Get argv either from command line or from server request
	 *
	 * @return void
	 *
	 * @throws Exception Cannot read command line arguments
	 *
	 * @exceptioncode EXCEPTION_CANNOT_READ_ARGS
	 */

	private function _getArgv()
	    {
		if (isset($GLOBALS["argv"]) === true && is_array($GLOBALS["argv"]) === true)
		    {
			$this->_args = $GLOBALS["argv"];
		    }
		else if (isset($_SERVER["argv"]) === true && is_array($_SERVER["argv"]) === true)
		    {
			$this->_args = $_SERVER["argv"];
		    }
		else
		    {
			throw new Exception(_("Could not read command line arguments"), EXCEPTION_CANNOT_READ_ARGS);
		    }

		$this->_bin = basename(array_shift($this->_args));
	    } //end _getArgv()


	/**
	 * Sets the introduction text for the tool itself
	 *
	 * @param string $help Help text
	 *
	 * @return void
	 */

	public function setIntro($help)
	    {
		$this->_intro = $help;
	    } //end setIntro()


	/**
	 * Sets the help text for the tool itself
	 *
	 * @param string $help Help text
	 *
	 * @return void
	 */

	public function setHelp($help)
	    {
		$this->_setup[""]["help"] = $help;
	    } //end setHelp()


	/**
	 * This registers a sub command
	 *
	 * Sub commands may have their own options and arguments. Multilevel sub commands may be specified as:
	 *
	 *   subcommand/subsubcommand
	 *
	 * @param string $command Command
	 * @param string $help    Command help
	 *
	 * @return void
	 *
	 * @throws Exception Command already registered
	 *
	 * @exceptioncode EXCEPTION_COMMAND_ALREADY_REGISTERED
	 */

	public function registerCommand($command, $help)
	    {
		if (isset($this->_setup[$command]) === true)
		    {
			throw new Exception(_("Command ") . $command . _(" already registered"), EXCEPTION_COMMAND_ALREADY_REGISTERED);
		    }

		$blank = array(
			  "opts" => array(),
			  "args" => array(),
			  "help" => $help,
			 );

		$this->_setup[$command] = $blank;

		$commands = explode("/", $command);
		array_pop($commands);
		$command = "";
		foreach ($commands as $subcommand)
		    {
			$command .= (($command !== "") ? "/" : "") . $subcommand;

			$this->_setup[$command] = $blank;
		    }

		ksort($this->_setup);
	    } //end registerCommand()


	/**
	 * Register an option for option parsing and help generation
	 *
	 * Spec as follows:
	 *
	 * * v|verbose   Flag option (with boolean value true)<br>
	 * * h|help!     Special option<br>
	 * * v|verbose*  Cumulative option<br>
	 * * d|dir:      Option require a value (MUST require)<br>
	 * * d|dir+      Option with multiple values.<br>
	 * * d|dir?      Option with optional value<br>
	 * * d?/default  Option with default value<br>
	 * * d?<name>    Option with value name<br>
	 * * dir:=string Option with type constraint of string
	 * * dir:=int    Option with type constraint of number
	 * * dir:=file   Option with type constraint of file
	 * * dir:=dir    Option with type constraint of dir
	 * * dir:=bool   Option with type constraint of boolean
	 * * d           Single character only option
	 * * dir         Long option name
	 *
	 * @param string  $spec      Option specification
	 * @param string  $help      Help text for this option
	 * @param string  $command   What subcommand does this option apply to
	 * @param Closure $validator Optional closure to validate the argument
	 *
	 * @return void
	 *
	 * @throws Exception Command not registered
	 *
	 * @exceptioncode EXCEPTION_ATTEMPT_TO_REDEFINE_OPTION
	 * @exceptioncode EXCEPTION_MUST_BE_LAST_ARGUMENT
	 * @exceptioncode EXCEPTION_COMMAND_NOT_REGISTERED
	 * @exceptioncode EXCEPTION_CONSTRAINT_IS_NOT_ALLOWED
	 * @exceptioncode EXCEPTION_NAME_IS_NOT_ALLOWED
	 * @exceptioncode EXCEPTION_DEFAULT_NOT_ALLOWED
	 * @exceptioncode EXCEPTION_WRONG_OPTION_SPEC
	 *
	 * @untranslatable (?P<option>([\w\d][-\w\d]*\|)*([\w\d][-\w\d]*))
	 * @untranslatable (?P<modifier>[!*:+?])?
	 * @untranslatable (<(?P<name>[\w\d][\w\d-]*)>)?
	 * @untranslatable (=(?P<constraint>string|int|file|dir|bool))?
	 * @untranslatable (\/(?P<default>.*))?
	 * @untranslatable cumulative
	 * @untranslatable flag
	 * @untranslatable special
	 * @untranslatable required
	 * @untranslatable multiple
	 * @untranslatable optional
	 * @untranslatable constraint
	 * @untranslatable name
	 * @untranslatable default
	 */

	public function registerOption($spec, $help, $command = "", Closure $validator = null)
	    {
		if (isset($this->_setup[$command]) === false)
		    {
			throw new Exception(_("Command ") . $command . _(" not registered"), EXCEPTION_COMMAND_NOT_REGISTERED);
		    }

		$optionre     = "(?P<option>([\w\d][-\w\d]*\|)*([\w\d][-\w\d]*))";
		$modifierre   = "(?P<modifier>[!*:+?])?";
		$namere       = "(<(?P<name>[\w\d][\w\d-]*)>)?";
		$constraintre = "(=(?P<constraint>string|int|file|dir|bool))?";
		$defaultre    = "(\/(?P<default>.*))?";
		if (preg_match("/^" . $optionre . $modifierre . $namere . $constraintre . $defaultre . "$/", $spec, $m) > 0)
		    {
			$modifiers = array(
				      ""  => "flag",
				      "!" => "special",
				      "*" => "cumulative",
				      ":" => "required",
				      "+" => "multiple",
				      "?" => "optional",
				     );
			$type      = (isset($m["modifier"]) === true) ? $modifiers[$m["modifier"]] : "flag";

			$options = explode("|", $m["option"]);

			$constrained = array(
					"required",
					"multiple",
					"optional",
				       );
			$constraint  = $this->_checkAllowed($type, $constrained, $m, "constraint");
			if ($constraint === null)
			    {
				throw new Exception(_("Constraint is not allowed on") . " " . $type . " " . _("option"), EXCEPTION_CONSTRAINT_IS_NOT_ALLOWED);
			    }

			$named = array(
				  "required",
				  "multiple",
				  "optional",
				 );
			$name  = $this->_checkAllowed($type, $named, $m, "name");
			if ($name === null)
			    {
				throw new Exception(_("Name is not allowed on") . " " . $type . " " . _("option"), EXCEPTION_NAME_IS_NOT_ALLOWED);
			    }

			$default = $this->_checkAllowed($type, array("optional"), $m, "default");
			if ($default === null)
			    {
				throw new Exception(_("Default is not allowed on") . " " . $type . " " . _("option"), EXCEPTION_DEFAULT_NOT_ALLOWED);
			    }
		    }
		else
		    {
			throw new Exception(_("Wrong option spec") . ": " . $spec, EXCEPTION_WRONG_OPTION_SPEC);
		    } //end if

		foreach ($this->_setup[$command]["opts"] as $specs)
		    {
			foreach ($specs as $spec)
			    {
				if (empty(array_intersect($options, $spec["options"])) === false)
				    {
					throw new Exception(_("Attempt to redefine an option specification"), EXCEPTION_ATTEMPT_TO_REDEFINE_OPTION);
				    }
			    }
		    }

		$this->_setup[$command]["opts"][$type][] = array(
							    "options"    => $options,
							    "constraint" => $constraint,
							    "name"       => $name,
							    "default"    => $default,
							    "help"       => $help,
							    "validator"  => $validator,
							   );
	    } //end registerOption()


	/**
	 * Register the arguments for help generation and validation
	 *
	 * This has to be called in the order arguments are expected
	 *
	 * Spec as follows:
	 *
	 * * name:         Required argument with a name
	 * * name+         Required argiment with multiple values (can be last only)
	 * * name?/default Optional argument with default value
	 * * name:=string  Option with type constraint of string
	 * * name:=int     Option with type constraint of number
	 * * name:=file    Option with type constraint of file
	 * * name:=dir     Option with type constraint of dir
	 * * name:=boolean Option with type constraint of boolean
	 *
	 * @param string  $spec      Argument specification
	 * @param string  $help      Help text
	 * @param string  $command   What subcommand does this option apply to
	 * @param Closure $validator Optional closure to validate the argument
	 *
	 * @return void
	 *
	 * @throws Exception Command not registered
	 *
	 * @exceptioncode EXCEPTION_MUST_BE_LAST_ARGUMENT
	 * @exceptioncode EXCEPTION_OPTIONAL_ARGUMENT_EXCEPCTED
	 * @exceptioncode EXCEPTION_COMMAND_NOT_REGISTERED
	 * @exceptioncode EXCEPTION_DEFAULT_NOT_ALLOWED
	 * @exceptioncode EXCEPTION_WRONG_ARGUMENT_SPEC
	 *
	 * @untranslatable (?P<name>[\w\d][\w\d-]*)
	 * @untranslatable (?P<modifier>[:+?])?
	 * @untranslatable (=(?P<constraint>string|int|file|dir|bool))?
	 * @untranslatable (\/(?P<default>.*))?
	 * @untranslatable multiple
	 * @untranslatable required
	 * @untranslatable optional
	 * @untranslatable default
	 */

	public function registerArgument($spec, $help, $command = "", Closure $validator = null)
	    {
		if (isset($this->_setup[$command]) === false)
		    {
			throw new Exception(_("Command ") . $command . _(" not registered"), EXCEPTION_COMMAND_NOT_REGISTERED);
		    }

		$namere       = "(?P<name>[\w\d][\w\d-]*)";
		$modifierre   = "(?P<modifier>[:+?])?";
		$constraintre = "(=(?P<constraint>string|int|file|dir|bool))?";
		$defaultre    = "(\/(?P<default>.*))?";
		if (preg_match("/^" . $namere . $modifierre . $constraintre . $defaultre . "$/", $spec, $m) > 0)
		    {
			$name = $m["name"];

			$modifiers = array(
				      ""  => "required",
				      ":" => "required",
				      "+" => "multiple",
				      "?" => "optional",
				     );
			$type      = (isset($m["modifier"]) === true) ? $modifiers[$m["modifier"]] : "required";

			$constraint = (isset($m["constraint"]) === true) ? $m["constraint"] : "";

			$default = $this->_checkAllowed($type, array("optional"), $m, "default");
			if ($default === null)
			    {
				throw new Exception(_("Default is not allowed on") . " " . $type . " " . _("argument"), EXCEPTION_DEFAULT_NOT_ALLOWED);
			    }
		    }
		else
		    {
			throw new Exception(_("Wrong argument spec") . ": " . $spec, EXCEPTION_WRONG_ARGUMENT_SPEC);
		    } //end if

		$last = (count($this->_setup[$command]["args"]) - 1);
		if ($last > 0)
		    {
			if ($this->_setup[$command]["args"][$last]["type"] === "multiple")
			    {
				throw new Exception(_("Argument of multiple type must be last argument"), EXCEPTION_MUST_BE_LAST_ARGUMENT);
			    }
			else if ($this->_setup[$command]["args"][$last]["type"] === "optional" && $type !== "optional")
			    {
				throw new Exception(_("Optional argument may be followed only by another optional argument"), EXCEPTION_OPTIONAL_ARGUMENT_EXCEPCTED);
			    }
		    }

		$this->_setup[$command]["args"][] = array(
						     "type"       => $type,
						     "name"       => $name,
						     "constraint" => $constraint,
						     "default"    => $default,
						     "help"       => $help,
						     "validator"  => $validator,
						    );
	    } //end registerArgument()


	/**
	 * Check if value is acceptable for the option type
	 *
	 * @param string $type    Value type
	 * @param array  $allowed Allowed types
	 * @param array  $match   Matches
	 * @param string $index   Value index
	 *
	 * @return mixed Value or null if not allowed
	 */

	private function _checkAllowed($type, array $allowed, array $match, $index)
	    {
		if (isset($match[$index]) === true && $match[$index] !== "")
		    {
			if (in_array($type, $allowed) === true)
			    {
				return $match[$index];
			    }
			else
			    {
				return null;
			    }
		    }
		else
		    {
			return "";
		    }
	    } //end _checkAllowed()


	/**
	 * Parses the given arguments for known options and command
	 *
	 * The given $args array should NOT contain the executed file as first item anymore! The $args
	 * array is stripped from any options and possible command. All found options can be accessed via the
	 * getOpt() function
	 *
	 * Note that command options will overwrite any global options with the same name
	 *
	 * This is run from CLI automatically and usually does not need to be called directly
	 *
	 * @return void
	 *
	 * @throws Exception Unknown option encountered or option argument is required
	 *
	 * @exceptioncode EXCEPTION_MISSING_VALUE
	 * @exceptioncode EXCEPTION_CANNOT_BE_COMBINED
	 *
	 * @untranslatable special
	 * @untranslatable flag
	 * @untranslatable cumulative
	 */

	public function parse()
	    {
		$this->_options   = array();
		$this->_arguments = array();

		$this->_getArgv();

		$command = "";
		$special = false;
		$argpos  = 0;
		$argonly = false;
		$args    = $this->_args;
		$this->_resetCumulativeOptions($command);
		while ($arg = array_shift($args))
		    {
			if ($arg === "--")
			    {
				$argonly = true;
			    }
			else if ($argonly === false && preg_match("/^(-(?P<short>[\w\d])|--(?P<long>[\w\d][\w\d-]*)(=(?P<value>.*))?)$/", $arg, $m) > 0)
			    {
				$option            = (isset($m["short"]) === true && $m["short"] !== "") ? $m["short"] : $m["long"];
				list($type, $spec) = $this->_optionSpec($option, $command);
				if ($type === "special")
				    {
					$this->_processOption($spec, $type, $option);
					$special = true;
				    }
				else if ($type === "flag" || $type === "cumulative")
				    {
					$this->_processOption($spec, $type, $option);
				    }
				else
				    {
					if (isset($m["value"]) === true)
					    {
						$this->_processOption($spec, $type, $option, $m["value"]);
					    }
					else if (empty($args) === false)
					    {
						$this->_processOption($spec, $type, $option, array_shift($args));
					    }
					else
					    {
						throw new Exception(_("Option") . " '" . $option . "' " . _("missing a value"), EXCEPTION_MISSING_VALUE);
					    }
				    } //end if
			    }
			else if ($argonly === false && preg_match("/^-(?P<flags>[\w\d]+)$/m", $arg, $m) > 0)
			    {
				$flags = str_split($m["flags"], 1);
				foreach ($flags as $flag)
				    {
					list($type, $spec) = $this->_optionSpec($flag, $command);
					if ($type === "flag" || $type === "cumulative")
					    {
						$this->_processOption($spec, $type, $flag);
					    }
					else
					    {
						throw new Exception(
						    _("Option") . " '" . $flag . "' " . _("cannot be used together with other options"),
						    EXCEPTION_CANNOT_BE_COMBINED
						);
					    }
				    }
			    }
			else if ($argonly === false && isset($this->_setup[(($command === "") ? "" : $command . "/") . $arg]) === true)
			    {
				$this->_setDefaults($command);
				if ($special === false)
				    {
					$this->_checkRequired($command);
				    }

				$command = (($command === "") ? "" : $command . "/") . $arg;
				$this->_resetCumulativeOptions($command);
				$argpos = 0;
			    }
			else
			    {
				$this->_processArgument($arg, $command, $argpos);
				$argpos++;
			    } //end if
		    } //end while

		$this->_setDefaults($command);

		if ($special === false)
		    {
			$this->_checkRequired($command);
		    }

		$this->_command = $command;
	    } //end parse()


	/**
	 * Process option
	 *
	 * @param array  $spec   Option specification
	 * @param string $type   Option type
	 * @param string $option Option name
	 * @param string $value  Option value
	 *
	 * @return void
	 *
	 * @throws Exception Ambiguous option
	 *
	 * @exceptioncode EXCEPTION_AMBIGUOUS_OPTION
	 */

	private function _processOption(array $spec, $type, $option, $value = null)
	    {
		switch ($type)
		    {
			case "special":
			case "flag":
				if (isset($this->_options[$spec["options"][0]]) === true)
				    {
					throw new Exception(_("Flag") . " '" . $option . "' " . _("is already set"), EXCEPTION_AMBIGUOUS_OPTION);
				    }

				$this->_options[$spec["options"][0]] = true;
			    break;
			case "cumulative":
				$this->_options[$spec["options"][0]]++;
			    break;
			case "required":
			case "optional":
				if (isset($this->_options[$spec["options"][0]]) === true)
				    {
					throw new Exception(_("Option") . " '" . $option . "' " . _("is already set"), EXCEPTION_AMBIGUOUS_OPTION);
				    }

				$value = $this->_validate(_("option"), $option, $value, $spec["constraint"], $spec["validator"]);
				$this->_options[$spec["options"][0]] = $value;
			    break;
			case "multiple":
				$value = $this->_validate(_("option"), $option, $value, $spec["constraint"], $spec["validator"]);
				$this->_options[$spec["options"][0]][] = $value;
			    break;
		    } //end switch
	    } //end _processOption()


	/**
	 * Validate value against constraints
	 *
	 * @param string $type       Value type name: "option" or "argument"
	 * @param string $name       Value name
	 * @param string $value      Value
	 * @param string $constraint Constraint type
	 * @param string $validator  Validator closure
	 *
	 * @return mixed Validated value
	 *
	 * @throws Exception Invalid value
	 *
	 * @exceptioncode EXCEPTION_NOT_AN_INTEGER
	 * @exceptioncode EXCEPTION_NOT_A_FILE
	 * @exceptioncode EXCEPTION_NOT_A_DIRECTORY
	 * @exceptioncode EXCEPTION_NOT_A_BOOLEAN
	 * @exceptioncode EXCEPTION_NOT_VALID
	 */

	private function _validate($type, $name, $value, $constraint, $validator)
	    {
		switch ($constraint)
		    {
			case "int":
				if (preg_match("/^\d+$/", $value) === 0)
				    {
					throw new Exception(
					    _("Value of") . " '" . $value . "' " . _("for") . " " . $type . " '" . $name . "' " . _("is not valid integer"),
					    EXCEPTION_NOT_AN_INTEGER
					);
				    }
			    break;
			case "file":
				if (is_file($value) === false)
				    {
					throw new Exception(
					    _("Option value of") . " '" . $value . "' " . _("for") . " " . $type . " '" . $name . "' " . _("is not a file"),
					    EXCEPTION_NOT_A_FILE
					);
				    }
			    break;
			case "dir":
				if (is_dir($value) === false)
				    {
					throw new Exception(
					    _("Option value of") . " '" . $value . "' " . _("for") . " " . $type . " '" . $name . "' " . _("is not a directory"),
					    EXCEPTION_NOT_A_DIRECTORY
					);
				    }
			    break;
			case "bool":
				$map = array(
					"0"     => false,
					"1"     => true,
					"off"   => false,
					"on"    => true,
					"false" => false,
					"true"  => true,
				       );
				if (isset($map[strtolower($value)]) === false)
				    {
					throw new Exception(
					    _("Option value of") . " '" . $value . "' " . _("for") . " " . $type . " '" . $name . "' " . _("is not a valid boolean"),
					    EXCEPTION_NOT_A_BOOLEAN
					);
				    }

				$value = $map[strtolower($value)];
			    break;
		    } //end switch

		if ($validator instanceof Closure && $validator($value) === false)
		    {
			throw new Exception(_("Option value of") . " '" . $value . "' " . _("for") . " " . $type . " '" . $name . "' " . _("is not valid"), EXCEPTION_NOT_VALID);
		    }

		return $value;
	    } //end _validate()


	/**
	 * Get specification for the option
	 *
	 * @param string $option  Option name
	 * @param string $command Command name to fetch option for
	 *
	 * @return array Option specification
	 *
	 * @throws Exception Unknown option
	 *
	 * @exceptioncode EXCEPTION_UNKNOWN_OPTION
	 */

	private function _optionSpec($option, $command)
	    {
		$result = false;

		foreach ($this->_setup[$command]["opts"] as $type => $specs)
		    {
			foreach ($specs as $spec)
			    {
				if (in_array($option, $spec["options"]) === true)
				    {
					$result = array(
						   $type,
						   $spec,
						  );
				    }
			    }
		    }

		if ($result === false)
		    {
			throw new Exception(_("Uknown option") . " '" . $option . "' " . _("is given"), EXCEPTION_UNKNOWN_OPTION);
		    }

		return $result;
	    } //end _optionSpec()


	/**
	 * Reset cumulative options for the command
	 *
	 * @param string $command Command to reset cumulative options for
	 *
	 * @return void
	 */

	private function _resetCumulativeOptions($command)
	    {
		if (isset($this->_setup[$command]["opts"]["cumulative"]) === true)
		    {
			foreach ($this->_setup[$command]["opts"]["cumulative"] as $spec)
			    {
				$this->_options[$spec["options"][0]] = 0;
			    }
		    }
	    } //end _resetCumulativeOptions()


	/**
	 * Set default options and arguments for the command
	 *
	 * @param string $command Command to set defaults for
	 *
	 * @return void
	 *
	 * @untranslatable optional
	 * @untranslatable multiple
	 */

	private function _setDefaults($command)
	    {
		if (isset($this->_setup[$command]["opts"]["optional"]) === true)
		    {
			foreach ($this->_setup[$command]["opts"]["optional"] as $spec)
			    {
				if (isset($this->_options[$spec["options"][0]]) === false)
				    {
					$this->_options[$spec["options"][0]] = $spec["default"];
				    }
			    }
		    }

		if (isset($this->_setup[$command]["opts"]["multiple"]) === true)
		    {
			foreach ($this->_setup[$command]["opts"]["multiple"] as $spec)
			    {
				if (isset($this->_options[$spec["options"][0]]) === false)
				    {
					$this->_options[$spec["options"][0]] = array();
				    }
			    }
		    }

		if (isset($this->_setup[$command]["args"]) === true)
		    {
			foreach ($this->_setup[$command]["args"] as $spec)
			    {
				$default = ($spec["type"] === "multiple") ? array() : $spec["default"];
				if (($spec["type"] === "optional" || $spec["type"] === "multiple") && isset($this->_arguments[$spec["name"]]) === false)
				    {
					$this->_arguments[$spec["name"]] = $default;
				    }
			    }
		    }
	    } //end _setDefaults()


	/**
	 * Process argument
	 *
	 * @param string $argument Argument value
	 * @param string $command  Command to which the argument belongs
	 * @param int    $argpos   Argument position
	 *
	 * @return void
	 *
	 * @throws Exception Superfluous argument
	 *
	 * @exceptioncode EXCEPTION_SUPERFLUOUS_ARGUMENT
	 *
	 * @untranslatable multiple
	 */

	private function _processArgument($argument, $command, $argpos)
	    {
		if (isset($this->_setup[$command]["args"][$argpos]) === true)
		    {
			$spec = $this->_setup[$command]["args"][$argpos];
		    }
		else
		    {
			$pos = (count($this->_setup[$command]["args"]) - 1);

			if ($pos < 0 || isset($this->_setup[$command]["args"][$pos]) === false || $this->_setup[$command]["args"][$pos]["type"] !== "multiple")
			    {
				throw new Exception(_("Superfluous argument") . " '" . $argument . "' " . _("provided"), EXCEPTION_SUPERFLUOUS_ARGUMENT);
			    }

			$spec = $this->_setup[$command]["args"][$pos];
		    }

		$this->_validate(_("argument"), $spec["name"], $argument, $spec["constraint"], $spec["validator"]);

		if ($spec["type"] === "multiple")
		    {
			$this->_arguments[$spec["name"]][] = $argument;
		    }
		else
		    {
			$this->_arguments[$spec["name"]] = $argument;
		    }
	    } //end _processArgument()


	/**
	 * Check presence of required options and arguments
	 *
	 * @param string $command Command to check options and arguments for
	 *
	 * @return void
	 *
	 * @throws Exception Missing required option or argument
	 *
	 * @exceptioncode EXCEPTION_MISSING_REQUIRED_OPTION
	 * @exceptioncode EXCEPTION_MISSING_REQUIRED_ARGUMENT
	 *
	 * @untranslatable required
	 */

	private function _checkRequired($command)
	    {
		if (isset($this->_setup[$command]["opts"]["required"]) === true)
		    {
			foreach ($this->_setup[$command]["opts"]["required"] as $spec)
			    {
				if (isset($this->_options[$spec["options"][0]]) === false)
				    {
					throw new Exception(_("Required option") . " '" . $spec["options"][0] . "' " . _("is missing"), EXCEPTION_MISSING_REQUIRED_OPTION);
				    }
			    }
		    }

		if (isset($this->_setup[$command]["args"]) === true)
		    {
			foreach ($this->_setup[$command]["args"] as $spec)
			    {
				if ($spec["type"] === "required" && isset($this->_arguments[$spec["name"]]) === false)
				    {
					throw new Exception(_("Required argument") . " '" . $spec["name"] . "' " . _("is missing"), EXCEPTION_MISSING_REQUIRED_ARGUMENT);
				    }
			    }
		    }
	    } //end _checkRequired()


	/**
	 * Return the found command if any
	 *
	 * @return string
	 */

	public function getCommand()
	    {
		return $this->_command;
	    } //end getCommand()


	/**
	 * Get the value of the given option
	 *
	 * Please note that all options are accessed by their long option names regardless of how they were
	 * specified on commandline.
	 *
	 * Can only be used after parseOptions() has been run
	 *
	 * @param string $option Option name
	 *
	 * @return bool|array|null
	 */

	public function getOption($option)
	    {
		return (isset($this->_options[$option]) === true) ? $this->_options[$option] : null;
	    } //end getOption()


	/**
	 * Get the argument passed to the script
	 *
	 * @param string $argument Argument name
	 *
	 * @return null|array
	 */

	public function getArgument($argument)
	    {
		return (isset($this->_arguments[$argument]) === true) ? $this->_arguments[$argument] : null;
	    } //end getArgument()


	/**
	 * Builds a help screen from the available options. You may want to call it from -h or on error
	 *
	 * @return string
	 *
	 * @untranslatable green
	 * @untranslatable cyan
	 * @untranslatable yellow
	 */

	public function help()
	    {
		$tf = new TableFormatter(array("*"));
		$tf->setColumns(array(0, "*"));
		$text = "";

		if (empty($this->_intro) === false)
		    {
			$tf->setSeparator("");
			$text  = $tf->format(array("", $this->_intro));
			$text .= "\n";
			$tf->setSeparator(" ");
		    }

		foreach ($this->_setup as $command => $config)
		    {
			if ($command === "")
			    {
				$margin = 2;
				$text  .= Colorize::text(_("USAGE:"), "yellow") . "\n";
			    }
			else
			    {
				$margin = 4;
				$text  .= str_repeat(" ", $margin) . Colorize::text(_("SUBCOMMAND:"), "yellow") . "\n";
			    }

			$tf->setMargin($margin + 1);

			$text .= "\n";

			$cmds = array("");
			if ($command !== "")
			    {
				$cmds = array_merge($cmds, explode("/", $command));
			    }

			$text .= str_repeat(" ", $margin);
			$cmd   = "";
			foreach ($cmds as $subcmd)
			    {
				$cmd  .= (($cmd === "") ? "" : "/") . $subcmd;
				$text .= (($cmd === "") ? "" : " ") . $this->_usage($cmd);
			    }

			$text .= "\n";
			$text .= "\n";

			if ($config["help"] !== "")
			    {
				$tf->setColumns(array("*"));
				$text .= $tf->format(array($config["help"])) . "\n";
			    }

			if (empty($config["opts"]) === false)
			    {
				$text .= str_repeat(" ", $margin) . Colorize::text(_("OPTIONS:"), "yellow") . "\n";
				$table = array();
				$max   = 0;
				foreach ($config["opts"] as $type)
				    {
					foreach ($type as $option)
					    {
						$s = "";
						foreach ($option["options"] as $name)
						    {
							$s .= (($s === "") ? "" : ", ") . ((strlen($name) === 1) ? "-" : "--") . $name;
							$s .= ($option["name"] !== "") ? " <" . $option["name"] . ">" : "";
						    }

						$table[] = (array(Colorize::text($s, "green"), $option["help"]));
						$max     = max($max, strlen($s));
					    }
				    } //end foreach

				$tf->setColumns(array($max . "<30%", "*"));
				$text .= $tf->table($table);
				$text .= "\n";
			    } //end if

			if (empty($config["args"]) === false)
			    {
				$text .= str_repeat(" ", $margin) . Colorize::text(_("ARGUMENTS:"), "yellow") . "\n";
				$table = array();
				$max   = 0;
				foreach ($config["args"] as $arg)
				    {
					$name = "<" . $arg["name"] . ">";

					$table[] = (array(Colorize::text($name, "cyan"), $arg["help"]));
					$max     = max($max, strlen($name));
				    }

				$tf->setColumns(array($max . "<30%", "*"));
				$text .= $tf->table($table);
				$text .= "\n";
			    }
		    } //end foreach

		return $text;
	    } //end help()


	/**
	 * Get command usage
	 *
	 * @param string $command Command for which usage should be generated
	 *
	 * @return string Usage string
	 *
	 * @untranslatable green
	 * @untranslatable cyan
	 * @untranslatable required
	 * @untranslatable multiple
	 */

	private function _usage($command)
	    {
		$s = "";

		if ($command === "")
		    {
			$s .= $this->_bin;
		    }
		else
		    {
			$s .= array_pop(explode("/", $command));
		    }

		if (empty($this->_setup[$command]["opts"]) === false)
		    {
			$s .= " " . Colorize::text(_("<OPTIONS>"), "green");
		    }

		foreach ($this->_setup[$command]["args"] as $arg)
		    {
			$out = Colorize::text("<" . $arg["name"] . ">", "cyan");

			if ($arg["type"] !== "required")
			    {
				$out = "[" . $out . (($arg["type"] === "multiple") ? ", ..." : "") . "]";
			    }

			$s .= " " . $out;
		    }

		return $s;
	    } //end _usage()


    } //end class

?>
