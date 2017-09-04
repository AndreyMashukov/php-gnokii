<?php

/**
 * A File object represents a PHP source file and the tokens
 * associated with it.
 *
 * PHP version 5
 *
 * @package Logics\BuildTools\CodeSniffer
 */

namespace Logics\BuildTools\CodeSniffer;

use \Exception;

/**
 * A File object represents a PHP source file and the tokens
 * associated with it.
 *
 * It provides a means for traversing the token stack, along with
 * other token related operations. If a Sniff finds and error or
 *  warning within a File, you can raise an error using the
 *  addError() or addWarning() methods.
 *
 * <b>Token Information</b>
 *
 * Each token within the stack contains information about itself:
 *
 * <code>
 *   array(
 *    "code"       => 301,       // the token type code (see token_get_all())
 *    "content"    => "if",      // the token content
 *    "type"       => "T_IF",    // the token name
 *    "line"       => 56,        // the line number when the token is located
 *    "column"     => 12,        // the column in the line where this token
 *                               // starts (starts from 1)
 *    "level"      => 2          // the depth a token is within the scopes open
 *    "conditions" => array(     // a list of scope condition token
 *                               // positions => codes that
 *                     2 => 50,  // openened the scopes that this token exists
 *                     9 => 353, // in (see conditional tokens section below)
 *                    ),
 *   );
 * </code>
 *
 * <b>Conditional Tokens</b>
 *
 * In addition to the standard token fields, conditions contain information to
 * determine where their scope begins and ends:
 *
 * <code>
 *   array(
 *    "scope_condition" => 38, // the token position of the condition
 *    "scope_opener"    => 41, // the token position that started the scope
 *    "scope_closer"    => 70, // the token position that ended the scope
 *   );
 * </code>
 *
 * The condition, the scope opener and the scope closer each contain this
 * information.
 *
 * <b>Parenthesis Tokens</b>
 *
 * Each parenthesis token (T_OPEN_PARENTHESIS and T_CLOSE_PARENTHESIS) has a
 * reference to their opening and closing parenthesis, one being itself, the
 * other being its opposite.
 *
 * <code>
 *   array(
 *    "parenthesis_opener" => 34,
 *    "parenthesis_closer" => 40,
 *   );
 * </code>
 *
 * Some tokens can "own" a set of parenthesis. For example a T_FUNCTION token
 * has parenthesis around its argument list. These tokens also have the
 * parenthesis_opener and and parenthesis_closer indices. Not all parenthesis
 * have owners, for example parenthesis used for arithmetic operations and
 * function calls. The parenthesis tokens that have an owner have the following
 * auxiliary array indices.
 *
 * <code>
 *   array(
 *    "parenthesis_opener" => 34,
 *    "parenthesis_closer" => 40,
 *    "parenthesis_owner"  => 33,
 *   );
 * </code>
 *
 * Each token within a set of parenthesis also has an array indice
 * "nested_parenthesis" which is an array of the
 * left parenthesis => right parenthesis token positions.
 *
 * <code>
 *   "nested_parenthesis" => array(
 *                             12 => 15
 *                             11 => 14
 *                            );
 * </code>
 *
 * <b>Extended Tokens</b>
 *
 * CodeSniffer extends and augments some of the tokens created by
 * <i>token_get_all()</i>. A full list of these tokens can be seen in the
 * <i>Tokens.php</i> file.
 *
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @author    Marc McIntyre <mmcintyre@squiz.net>
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2006-2012 Squiz Pty Ltd (ABN 77 084 670 600)
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 * @version   SVN: $Date: 2016-10-03 22:42:55 +0800 (Mon, 03 Oct 2016) $ $Revision: 47 $
 * @link      $HeadURL: https://open.logics.net.au/buildtools/codesniffer/tags/0.1.3/src/CodeSniffer/File.php $
 *
 * @untranslatable PHP
 */

class File
    {

	/**
	 * The absolute path to the file associated with this object.
	 *
	 * @var string
	 */
	private $_file = "";

	/**
	 * Contents of the file associated with this object.
	 *
	 * @var string
	 */
	private $_contents = "";

	/**
	 * The EOL character this file uses.
	 *
	 * @var string
	 */
	public $eolChar = "";

	/**
	 * The CodeSniffer object controlling this run.
	 *
	 * @var CodeSniffer
	 */
	public $phpcs = null;

	/**
	 * The tokenizer being used for this file.
	 *
	 * @var object
	 */
	public $tokenizer = null;

	/**
	 * The tokenizer being used for this file.
	 *
	 * @var string
	 */
	public $tokenizerType = "PHP";

	/**
	 * The number of tokens in this file.
	 *
	 * Stored here to save calling count() everywhere.
	 *
	 * @var int
	 */
	public $numTokens = 0;

	/**
	 * The tokens stack map.
	 *
	 * Note that the tokens in this array differ in format to the tokens
	 * produced by token_get_all(). Tokens are initially produced with
	 * token_get_all(), then augmented so that it's easier to process them.
	 *
	 * @var array
	 * @see Tokens.php
	 */
	public $tokens = array();

	/**
	 * The errors raised from Sniffs.
	 *
	 * @var array
	 * @see getErrors()
	 */
	private $_errors = array();

	/**
	 * The warnings raised form Sniffs.
	 *
	 * @var array
	 * @see getWarnings()
	 */
	private $_warnings = array();

	/**
	 * Record the errors and warnings raised.
	 *
	 * @var bool
	 */
	private $_recordErrors = true;

	/**
	 * And array of lines being ignored by CodeSniffer.
	 *
	 * @var array
	 */
	private $_ignoredLines = array();

	/**
	 * The total number of errors raised.
	 *
	 * @var int
	 */
	private $_errorCount = 0;

	/**
	 * The total number of warnings raised.
	 *
	 * @var int
	 */
	private $_warningCount = 0;

	/**
	 * An array of sniffs listening to this file's processing.
	 *
	 * @var array(Sniff)
	 */
	private $_listeners = array();

	/**
	 * The class name of the sniff currently processing the file.
	 *
	 * @var string
	 */
	private $_activeListener = "";

	/**
	 * An array of sniffs being processed and how long they took.
	 *
	 * @var array
	 */
	private $_listenerTimes = array();

	/**
	 * An array of extensions mapping to the tokenizer to use.
	 *
	 * This value gets set by CodeSniffer when the object is created.
	 *
	 * @var array
	 */
	protected $tokenizers = array();

	/**
	 * An array of rules from the ruleset.xml file.
	 *
	 * This value gets set by CodeSniffer when the object is created.
	 * It may be empty, indicating that the ruleset does not override
	 * any of the default sniff settings.
	 *
	 * @var array
	 */
	protected $ruleset = array();

	/**
	 * Constructs a File.
	 *
	 * @param string        $file       The absolute path to the file to process.
	 * @param array(string) $listeners  The initial listeners listening to processing of this file.
	 * @param array         $tokenizers An array of extensions mapping to the tokenizer to use.
	 * @param array         $ruleset    An array of rules from the ruleset.xml file.
	 * @param CodeSniffer   $phpcs      The CodeSniffer object controlling this run.
	 *
	 * @return void
	 *
	 * @untranslatable summary
	 */

	public function __construct($file, array $listeners, array $tokenizers, array $ruleset, CodeSniffer $phpcs)
	    {
		$this->_file      = trim($file);
		$this->_listeners = $listeners;
		$this->tokenizers = $tokenizers;
		$this->ruleset    = $ruleset;
		$this->phpcs      = $phpcs;

		$cliValues = $phpcs->cli->getCommandLineValues();
		if (isset($cliValues["showSources"]) === true && $cliValues["showSources"] !== true &&
		    array_key_exists("summary", $cliValues["reports"]) === true && count($cliValues["reports"]) === 1)
		    {
			$this->_recordErrors = false;
		    }
	    } //end __construct()


	/**
	 * Sets the name of the currently active sniff.
	 *
	 * @param string $activeListener The class name of the current sniff.
	 *
	 * @return void
	 */

	public function setActiveListener($activeListener)
	    {
		$this->_activeListener = $activeListener;
	    } //end setActiveListener()


	/**
	 * Adds a listener to the token stack that listens to the specific tokens.
	 *
	 * When CodeSniffer encounters on the the tokens specified in $tokens,
	 * it invokes the process method of the sniff.
	 *
	 * @param Sniff      $listener The listener to add to the listener stack.
	 * @param array(int) $tokens   The token types the listener wishes to listen to.
	 *
	 * @return void
	 */

	public function addTokenListener(Sniff $listener, array $tokens)
	    {
		foreach ($tokens as $token)
		    {
			if (isset($this->_listeners[$token]) === false)
			    {
				$this->_listeners[$token] = array();
			    }

			if (in_array($listener, $this->_listeners[$token], true) === false)
			    {
				$this->_listeners[$token][] = $listener;
			    }
		    }
	    } //end addTokenListener()


	/**
	 * Removes a listener from listening from the specified tokens.
	 *
	 * @param Sniff      $listener The listener to remove from the listener stack.
	 * @param array(int) $tokens   The token types the listener wishes to stop listen to.
	 *
	 * @return void
	 */

	public function removeTokenListener(Sniff $listener, array $tokens)
	    {
		foreach ($tokens as $token)
		    {
			if (isset($this->_listeners[$token]) === true)
			    {
				if (in_array($listener, $this->_listeners[$token]) === true)
				    {
					foreach ($this->_listeners[$token] as $pos => $value)
					    {
						if ($value === $listener)
						    {
							unset($this->_listeners[$token][$pos]);
						    }
					    }
				    }
			    }
		    }
	    } //end removeTokenListener()


	/**
	 * Starts the stack traversal and tells listeners when tokens are found.
	 *
	 * @param string $contents The contents to parse. If NULL, the content
	 *                         is taken from the file system.
	 *
	 * @return void
	 *
	 * @untranslatable codingStandardsIgnoreStart
	 * @untranslatable codingStandardsIgnoreEnd
	 * @untranslatable codingStandardsIgnoreFile
	 * @untranslatable codingStandardsChangeSetting
	 * @untranslatable _Sniffs_
	 * @untranslatable Sniff
	 */

	public function start($contents = null)
	    {
		$this->_parse($contents);

		Console::report(_("*** START TOKEN PROCESSING ***"), 1, 2);

		$foundCode        = false;
		$ignoring         = false;
		$fullignore       = false;
		$listenerIgnoreTo = array();

		// Foreach of the listeners that have registered to listen for this
		// token, get them to process it.
		foreach ($this->tokens as $stackPtr => $token)
		    {
			// Check for ignored lines.
			if ($token["code"] === T_COMMENT || $token["code"] === T_DOC_COMMENT || $token["code"] === T_INLINE_HTML)
			    {
				if (strpos($token["content"], "@" . "codingStandardsIgnoreStart") !== false)
				    {
					$ignoring = true;
				    }
				else if (strpos($token["content"], "@" . "codingStandardsIgnoreEnd") !== false)
				    {
					$ignoring = false;
					// Ignore this comment too.
					$this->_ignoredLines[$token["line"]] = true;
				    }
				else if (strpos($token["content"], "@" . "codingStandardsIgnoreFile") !== false)
				    {
					// Ignoring the whole file, just a little late.
					$this->_errors       = array();
					$this->_warnings     = array();
					$this->_errorCount   = 0;
					$this->_warningCount = 0;

					$fullignore = true;
					break;
				    }
				else if (strpos($token["content"], "@" . "codingStandardsChangeSetting") !== false)
				    {
					$start         = strpos($token["content"], "@" . "codingStandardsChangeSetting");
					$comment       = substr($token["content"], ($start + 30));
					$parts         = explode(" ", $comment);
					$sniffParts    = explode(".", $parts[0]);
					$listenerClass = $sniffParts[0] . "_Sniffs_" . $sniffParts[1] . "_" . $sniffParts[2] . "Sniff";
					$this->phpcs->setSniffProperty($listenerClass, $parts[1], $parts[2]);
				    } //end if
			    } //end if

			if ($ignoring === true)
			    {
				$this->_ignoredLines[$token["line"]] = true;
			    }
			else
			    {
				Console::report(
				    _("Process token") . " " . $stackPtr . ": " . $token["type"] . " => " . str_replace($this->eolChar, "\n", $token["content"]), 2, 2
				);

				$tokenType = $token["code"];
				$foundCode |= ($tokenType !== T_INLINE_HTML);

				self::_executeListeners($tokenType, $stackPtr, $listenerIgnoreTo);
			    } //end if
		    } //end foreach

		if ($fullignore === false)
		    {
			self::_cleanUpErrorsAndWarnings();
			self::_dropUnneededErrorsAndWarnings();
			self::_checkNoCode($foundCode);
		    }

		Console::report(_("*** END TOKEN PROCESSING ***"), 1, 2);

		if ($fullignore === false)
		    {
			self::_showSniffProcessingReport();
		    }
	    } //end start()


	/**
	 * Execute listeners for token
	 *
	 * @param int   $tokenType        Token type
	 * @param int   $stackPtr         The stack position
	 * @param array $listenerIgnoreTo Listener ignore to
	 *
	 * @return void
	 */

	private function _executeListeners($tokenType, $stackPtr, array &$listenerIgnoreTo)
	    {
		if (isset($this->_listeners[$tokenType]) === true)
		    {
			foreach ($this->_listeners[$tokenType] as $listenerData)
			    {
				if (isset($this->_ignoredListeners[$listenerData["class"]]) === true ||
				    (isset($listenerIgnoreTo[$listenerData["class"]]) === true &&
				    $listenerIgnoreTo[$listenerData["class"]] > $stackPtr))
				    {
					// This sniff is ignoring past this token, or the whole file.
					continue;
				    }

				// Make sure this sniff supports the tokenizer
				// we are currently using.
				$listener = $listenerData["listener"];
				$class    = $listenerData["class"];
				if (in_array($this->tokenizerType, $listenerData["tokenizers"]) === true)
				    {
					// If the file path matches one of our ignore patterns, skip it.
					$parts = explode("_", $class);
					if (isset($parts[3]) === true)
					    {
						$source = $parts[0] . "." . $parts[2] . "." . substr($parts[3], 0, -5);
						$ignore = self::_checkIgnore($source);
					    }
					else
					    {
						$ignore = false;
					    }

					if ($ignore === false)
					    {
						$this->setActiveListener($class);

						$startTime = microtime(true);
						Console::report(_("Processing") . " " . $this->_activeListener . "... ", 3, 2);

						$ignoreTo = $listener->process($this, $stackPtr);
						if ($ignoreTo !== null)
						    {
							$listenerIgnoreTo[$this->_activeListener] = $ignoreTo;
						    }

						$timeTaken = (microtime(true) - $startTime);
						if (isset($this->_listenerTimes[$this->_activeListener]) === false)
						    {
							$this->_listenerTimes[$this->_activeListener] = 0;
						    }

						$this->_listenerTimes[$this->_activeListener] += $timeTaken;

						Console::report(_("DONE in") . " " . round(($timeTaken), 4) . " " . _("seconds"), 0, 2);

						$this->_activeListener = "";
					    } //end if
				    } //end if
			    } //end foreach
		    } //end if
	    } //end _executeListeners()


	/**
	 * Drop errors and warnings generated for ignored lines
	 *
	 * @return void
	 */

	private function _cleanUpErrorsAndWarnings()
	    {
		// Remove errors and warnings for ignored lines.
		foreach ($this->_ignoredLines as $line => $ignore)
		    {
			if (isset($this->_errors[$line]) === true)
			    {
				if ($this->_recordErrors === false)
				    {
					$this->_errorCount -= $this->_errors[$line];
				    }
				else
				    {
					foreach ($this->_errors[$line] as $col => $errors)
					    {
						$this->_errorCount -= count($errors);
					    }
				    }

				unset($this->_errors[$line]);
			    }

			if (isset($this->_warnings[$line]) === true)
			    {
				if ($this->_recordErrors === false)
				    {
					$this->_errorCount -= $this->_warnings[$line];
				    }
				else
				    {
					foreach ($this->_warnings[$line] as $col => $warnings)
					    {
						$this->_warningCount -= count($warnings);
					    }
				    }

				unset($this->_warnings[$line]);
			    }
		    } //end foreach
	    } //end _cleanUpErrorsAndWarnings()


	/**
	 * Drop all errors and warinings messages if they are not needed
	 *
	 * @return void
	 */

	private function _dropUnneededErrorsAndWarnings()
	    {
		if ($this->_recordErrors === false)
		    {
			$this->_errors   = array();
			$this->_warnings = array();
		    }
	    } //end _dropUnneededErrorsAndWarnings()


	/**
	 * Record warning about no code found, perhaps because of short tags
	 *
	 * @param bool $foundCode True if any code tonkens are found
	 *
	 * @return void
	 *
	 * @untranslatable short_open_tag
	 * @untranslatable Internal.NoCodeFound
	 */

	private function _checkNoCode($foundCode)
	    {
		// If short open tags are off but the file being checked uses
		// short open tags, the whole content will be inline HTML
		// and nothing will be checked. So try and handle this case.
		if ($foundCode === false && (bool) ini_get("short_open_tag") === false)
		    {
			$error = _("No PHP code was found in this file and short open tags are not allowed by this install of PHP. ") .
				 _("This file may be using short open tags but PHP does not allow them.");
			$this->addWarning($error, null, "Internal.NoCodeFound");
		    }
	    } //end _checkNoCode()


	/**
	 * Show sniff processing report
	 *
	 * @return void
	 *
	 * @internalconst PHP_CODESNIFFER_VERBOSITY Verbosity
	 */

	private function _showSniffProcessingReport()
	    {
		if (PHP_CODESNIFFER_VERBOSITY > 2)
		    {
			Console::report(_("*** START SNIFF PROCESSING REPORT ***"), 1, 2);

			asort($this->_listenerTimes, SORT_NUMERIC);
			$this->_listenerTimes = array_reverse($this->_listenerTimes, true);
			foreach ($this->_listenerTimes as $listener => $timeTaken)
			    {
				Console::report($listener . ": " . round(($timeTaken), 4) . " " . _("secs"), 1, 2);
			    }

			Console::report(_("** END SNIFF PROCESSING REPORT ***"), 1, 2);
		    }
	    } //end _showSniffProcessingReport()


	/**
	 * Remove vars stored in this sniff that are no longer required.
	 *
	 * @return void
	 */

	public function cleanUp()
	    {
		$this->tokens     = null;
		$this->_listeners = null;
	    } //end cleanUp()


	/**
	 * Tokenizes the file and prepares it for the test run.
	 *
	 * @param string $contents The contents to parse. If NULL, the content
	 *                         is taken from the file system.
	 *
	 * @return void
	 *
	 * @untranslatable Tokenizer
	 * @untranslatable \\Logics\\BuildTools\\CodeSniffer\\
	 * @untranslatable \\n
	 * @untranslatable \\r
	 * @untranslatable Internal.Tokenizer.Exception
	 * @untranslatable Internal.LineEndings.Mixed
	 * @untranslatable Internal.DetectLineEndings
	 */

	private function _parse($contents = null)
	    {
		try
		    {
			$this->eolChar = self::detectLineEndings($this->_file, $contents);

			// Determine the tokenizer from the file extension.
			$fileParts = explode(".", $this->_file);

			$tokenizerClass = $this->tokenizerType . "Tokenizer";

			array_shift($fileParts);
			foreach ($fileParts as $part)
			    {
				$extension = implode(".", $fileParts);
				if (isset($this->tokenizers[$extension]) === true)
				    {
					$tokenizerClass      = $this->tokenizers[$extension] . "Tokenizer";
					$this->tokenizerType = $this->tokenizers[$extension];
					break;
				    }

				array_shift($fileParts);
			    }

			$tokenizerClass  = "\\Logics\\BuildTools\\CodeSniffer\\" . $tokenizerClass;
			$this->tokenizer = new $tokenizerClass();

			if ($contents === null)
			    {
				$contents = file_get_contents($this->_file);
			    }

			$this->_contents = $contents;

			try
			    {
				$cliValues = $this->phpcs->cli->getCommandLineValues();
				if (isset($cliValues["tabWidth"]) === true)
				    {
					$tabWidth = $cliValues["tabWidth"];
				    }
				else
				    {
					$tabWidth = null;
				    }

				$this->tokens = self::tokenizeString($contents, $this->tokenizer, $this->eolChar, $tabWidth);
			    }
			catch (Exception $e)
			    {
				$this->addWarning($e->getMessage(), null, "Internal.Tokenizer.Exception");
				Console::report("[" . $this->tokenizerType . " => " . _("tokenizer error") . "]... ", 0, 0, "");
				Console::report("", 0, 1);

				return;
			    } //end try

			$this->numTokens = count($this->tokens);

			// Check for mixed line endings as these can cause tokenizer errors and we
			// should let the user know that the results they get may be incorrect.
			// This is done by removing all backslashes, removing the newline char we
			// detected, then converting newlines chars into text. If any backslashes
			// are left at the end, we have additional newline chars in use.
			$contents = str_replace("\\\\", "", $contents);
			$contents = str_replace($this->eolChar, "", $contents);
			$contents = str_replace("\n", "\\n", $contents);
			$contents = str_replace("\r", "\\r", $contents);
			if (strpos($contents, "\\\\") !== false)
			    {
				$error = _("File has mixed line endings; this may cause incorrect results");
				$this->addWarning($error, 0, "Internal.LineEndings.Mixed");
			    }

			Console::report(
			    "[" . $this->numTokens . " " . _("tokens in") . " " .
			    (($this->numTokens === 0) ? 0 : $this->tokens[($this->numTokens - 1)]["line"]) . " " .
			    _("lines") . "]... ",
			    0, 0, ""
			);
			Console::report("", 0, 1);
		    }
		catch (Exception $e)
		    {
			$this->addWarning($e->getMessage(), null, "Internal.DetectLineEndings");
		    } //end try
	    } //end _parse()


	/**
	 * Opens a file and detects the EOL character being used.
	 *
	 * @param string $file     The full path to the file.
	 * @param string $contents The contents to parse. If NULL, the content
	 *                         is taken from the file system.
	 *
	 * @return string
	 *
	 * @throws Exception If $file could not be opened.
	 *
	 * @exceptioncode EXCEPTION_UNREADABLE_FILE
	 * @exceptioncode EXCEPTION_CANNOT_DETECT_LINE_ENDINGS
	 *
	 * @untranslatable r
	 * @untranslatable \r\n
	 */

	public static function detectLineEndings($file, $contents = null)
	    {
		if ($contents === null)
		    {
			// Determine the newline character being used in this file.
			// Will be either \r, \r\n or \n.
			if (is_readable($file) === false)
			    {
				throw new Exception(
				    _("Error opening file; file no longer exists or you do not have access to read the file"),
				    EXCEPTION_UNREADABLE_FILE
				);
			    }
			else
			    {
				$handle = fopen($file, "r");
				if ($handle === false)
				    {
					throw new Exception(_("Error opening file; could not auto-detect line endings"), EXCEPTION_CANNOT_DETECT_LINE_ENDINGS);
				    }
			    }

			$firstLine = fgets($handle);
			fclose($handle);

			$eolChar = substr($firstLine, -1);
			if ($eolChar === "\n")
			    {
				$secondLastChar = substr($firstLine, -2, 1);
				if ($secondLastChar === "\r")
				    {
					$eolChar = "\r\n";
				    }
			    }
			else if ($eolChar !== "\r")
			    {
				// Must not be an EOL char at the end of the line.
				// Probably a one-line file, so assume \n as it really
				// doesn't matter considering there are no newlines.
				$eolChar = "\n";
			    }
		    }
		else
		    {
			if (preg_match("/\r\n?|\n/", $contents, $matches) !== 1)
			    {
				// Assuming there are no newlines.
				$eolChar = "\n";
			    }
			else
			    {
				$eolChar = $matches[0];
			    }
		    } //end if

		return $eolChar;
	    } //end detectLineEndings()


	/**
	 * Adds an error to the error stack.
	 *
	 * @param string $error    The error message.
	 * @param int    $stackPtr The stack position where the error occurred.
	 * @param string $code     A violation code unique to the sniff message.
	 * @param array  $data     Replacements for the error message.
	 * @param int    $severity The severity level for this error. A value of 0
	 *                         will be converted into the default severity level.
	 *
	 * @return void
	 *
	 * @internalconst PHPCS_DEFAULT_ERROR_SEV Error severity
	 *
	 * @untranslatable warning
	 */

	public function addError($error, $stackPtr, $code = "", array $data = array(), $severity = 0)
	    {
		// Don't bother doing any processing if errors are just going to
		// be hidden in the reports anyway.
		if ($this->phpcs->cli->errorSeverity !== 0)
		    {
			$sniff = self::_getSniffByCode($code);

			// Make sure this message type has not been set to "warning".
			if (isset($this->ruleset[$sniff]["type"]) === true && $this->ruleset[$sniff]["type"] === "warning")
			    {
				// Pass this off to the warning handler.
				$this->addWarning($error, $stackPtr, $code, $data, $severity);
			    }
			else
			    {
				// Make sure we are interested in this severity level.
				if (isset($this->ruleset[$sniff]["severity"]) === true)
				    {
					$severity = $this->ruleset[$sniff]["severity"];
				    }
				else if ($severity === 0)
				    {
					$severity = PHPCS_DEFAULT_ERROR_SEV;
				    }

				if ($this->phpcs->cli->errorSeverity <= $severity)
				    {
					if (self::_checkIgnore($sniff) === false)
					    {
						$lineNum = ($stackPtr === null) ? 1 : $this->tokens[$stackPtr]["line"];
						$column  = ($stackPtr === null) ? 1 : $this->tokens[$stackPtr]["column"];

						$this->_errorCount++;
						if ($this->_recordErrors === false)
						    {
							$this->_errors[$lineNum] = (isset($this->_errors[$lineNum]) === false) ? 1 : ($this->_errors[$lineNum] + 1);
						    }
						else
						    {
							// Work out the warning message.
							$error   = (isset($this->ruleset[$sniff]["message"]) === true) ? $this->ruleset[$sniff]["message"] : $error;
							$message = (empty($data) === true) ? $error : vsprintf($error, $data);

							$this->_errors[$lineNum][$column][] = array(
											       "message"  => $message,
											       "source"   => $sniff,
											       "severity" => $severity,
											      );
						    }
					    } //end if
				    } //end if
			    } //end if
		    } //end if
	    } //end addError()


	/**
	 * Adds an warning to the warning stack.
	 *
	 * @param string $warning  The error message.
	 * @param int    $stackPtr The stack position where the error occurred.
	 * @param string $code     A violation code unique to the sniff message.
	 * @param array  $data     Replacements for the warning message.
	 * @param int    $severity The severity level for this warning. A value of 0
	 *                         will be converted into the default severity level.
	 *
	 * @return void
	 *
	 * @internalconst PHPCS_DEFAULT_WARN_SEV Warning severity
	 *
	 * @untranslatable error
	 */

	public function addWarning($warning, $stackPtr, $code = "", array $data = array(), $severity = 0)
	    {
		// Don't bother doing any processing if warnings are just going to
		// be hidden in the reports anyway.
		if ($this->phpcs->cli->warningSeverity !== 0)
		    {
			$sniff = self::_getSniffByCode($code);

			// Make sure this message type has not been set to "error".
			if (isset($this->ruleset[$sniff]["type"]) === true && $this->ruleset[$sniff]["type"] === "error")
			    {
				// Pass this off to the error handler.
				$this->addError($warning, $stackPtr, $code, $data, $severity);
			    }
			else
			    {
				// Make sure we are interested in this severity level.
				if (isset($this->ruleset[$sniff]["severity"]) === true)
				    {
					$severity = $this->ruleset[$sniff]["severity"];
				    }
				else if ($severity === 0)
				    {
					$severity = PHPCS_DEFAULT_WARN_SEV;
				    }

				if ($this->phpcs->cli->warningSeverity <= $severity)
				    {
					if (self::_checkIgnore($sniff) === false)
					    {
						$lineNum = ($stackPtr === null) ? 1 : $this->tokens[$stackPtr]["line"];
						$column  = ($stackPtr === null) ? 1 : $this->tokens[$stackPtr]["column"];

						$this->_warningCount++;
						if ($this->_recordErrors === false)
						    {
							$this->_warnings[$lineNum] = (isset($this->_warnings[$lineNum]) === false) ? 1 : ($this->_warnings[$lineNum] + 1);
						    }
						else
						    {
							// Work out the warning message.
							$warning = (isset($this->ruleset[$sniff]["message"]) === true) ? $this->ruleset[$sniff]["message"] : $warning;
							$message = (empty($data) === true) ? $warning : vsprintf($warning, $data);

							$this->_warnings[$lineNum][$column][] = array(
												 "message"  => $message,
												 "source"   => $sniff,
												 "severity" => $severity,
												);
						    }
					    } //end if
				    } //end if
			    } //end if
		    } //end if
	    } //end addWarning()


	/**
	 * Get sniff name by code
	 *
	 * @param string $code Sniff code
	 *
	 * @return string Sniff name
	 *
	 * @untranslatable Internal.
	 * @untranslatable unknownSniff
	 */

	private function _getSniffByCode($code)
	    {
		// Work out which sniff generated the warning.
		if (substr($code, 0, 9) === "Internal.")
		    {
			// Any internal message.
			$sniff = $code;
		    }
		else
		    {
			$parts = explode("_", $this->_activeListener);
			if (isset($parts[3]) === true)
			    {
				$sniff = $parts[0] . "." . $parts[2] . "." . $parts[3];

				// Remove "Sniff" from the end.
				$sniff = substr($sniff, 0, -5);
			    }
			else
			    {
				$sniff = "unknownSniff";
			    }

			if ($code !== "")
			    {
				$sniff .= "." . $code;
			    }
		    } //end if

		return $sniff;
	    } //end _getSniffByCode()


	/**
	 * Check if we should ignore this sniff
	 *
	 * @param string $sniff Sniff name
	 *
	 * @return boolean True if sniff should be ignored
	 */

	private function _checkIgnore($sniff)
	    {
		// Make sure we are not ignoring this file.
		$patterns = $this->phpcs->getIgnorePatterns($sniff);
		$ignore   = false;
		foreach ($patterns as $pattern => $type)
		    {
			// While there is support for a type of each pattern
			// (absolute or relative) we don't actually support it here.
			$replacements = array(
					 "\\\\," => ",",
					 "*"     => ".*",
					);

			$pattern = strtr($pattern, $replacements);
			if (preg_match("/{" . $pattern . "}/i", $this->_file) > 0)
			    {
				$ignore = true;
				break;
			    }
		    }

		return $ignore;
	    } //end _checkIgnore()


	/**
	 * Returns the number of errors raised.
	 *
	 * @return int
	 */

	public function getErrorCount()
	    {
		return $this->_errorCount;
	    } //end getErrorCount()


	/**
	 * Returns the number of warnings raised.
	 *
	 * @return int
	 */

	public function getWarningCount()
	    {
		return $this->_warningCount;
	    } //end getWarningCount()


	/**
	 * Returns the list of ignored lines.
	 *
	 * @return array
	 */

	public function getIgnoredLines()
	    {
		return $this->_ignoredLines;
	    } //end getIgnoredLines()


	/**
	 * Returns the errors raised from processing this file.
	 *
	 * @return array
	 */

	public function getErrors()
	    {
		return $this->_errors;
	    } //end getErrors()


	/**
	 * Returns the warnings raised from processing this file.
	 *
	 * @return array
	 */

	public function getWarnings()
	    {
		return $this->_warnings;
	    } //end getWarnings()


	/**
	 * Returns the absolute filename of this file.
	 *
	 * @return string
	 */

	public function getFilename()
	    {
		return $this->_file;
	    } //end getFilename()


	/**
	 * Returns the contents of this file.
	 *
	 * @return string
	 */

	public function getContents()
	    {
		return $this->_contents;
	    } //end getContents()


	/**
	 * Creates an array of tokens when given some PHP code.
	 *
	 * Starts by using token_get_all() but does a lot of extra processing
	 * to insert information about the context of the token.
	 *
	 * @param string $string    The string to tokenize.
	 * @param object $tokenizer A tokenizer class to use to tokenize the string.
	 * @param string $eolChar   The EOL character to use for splitting strings.
	 * @param int    $tabWidth  Tab width
	 * @param string $encoding  Encoding
	 *
	 * @return array
	 *
	 * @throws Exception If file appears to be minified
	 *
	 * @exceptioncode EXCEPTION_MINIFIED_FILE_ENCOUNTERED
	 *
	 * @internalconst PHP_CODESNIFFER_TAB_WIDTH Tab width
	 * @internalconst PHP_CODESNIFFER_ENCODING  Encoding
	 *
	 * @untranslatable Logics\BuildTools\CodeSniffer\Tokenizers\PHP
	 */

	public static function tokenizeString($string, $tokenizer, $eolChar = "\n", $tabWidth = null, $encoding = null)
	    {
		// Minified files often have a very large number of characters per line
		// and cause issues when tokenizing.
		if (get_class($tokenizer) !== "Logics\BuildTools\CodeSniffer\Tokenizers\PHP")
		    {
			$numChars = strlen($string);
			$numLines = (substr_count($string, $eolChar) + 1);
			$average  = ($numChars / $numLines);
			if ($average > 110)
			    {
				throw new Exception(_("File appears to be minified and cannot be processed"), EXCEPTION_MINIFIED_FILE_ENCOUNTERED);
			    }
		    }

		$tokens = $tokenizer->tokenizeString($string, $eolChar);

		if ($tabWidth === null)
		    {
			$tabWidth = PHP_CODESNIFFER_TAB_WIDTH;
		    }

		if ($encoding === null)
		    {
			$encoding = PHP_CODESNIFFER_ENCODING;
		    }

		self::_createPositionMap($tokens, $tokenizer, $eolChar, $encoding, $tabWidth);
		self::_createTokenMap($tokens);
		self::_createParenthesisNestingMap($tokens);
		self::_createScopeMap($tokens, $tokenizer, $eolChar);

		self::_createLevelMap($tokens, $tokenizer);

		// Allow the tokenizer to do additional processing if required.
		$tokenizer->processAdditional($tokens, $eolChar);

		return $tokens;
	    } //end tokenizeString()


	/**
	 * Sets token position information.
	 *
	 * Can also convert tabs into spaces. Each tab can represent between
	 * 1 and $width spaces, so this cannot be a straight string replace.
	 *
	 * @param array  $tokens    The array of tokens to process.
	 * @param object $tokenizer The tokenizer being used to process this file.
	 * @param string $eolChar   The EOL character to use for splitting strings.
	 * @param string $encoding  The charset of the sniffed file.
	 * @param int    $tabWidth  The number of spaces that each tab represents.
	 *                          Set to 0 to disable tab replacement.
	 *
	 * @return void
	 *
	 * @internalconst T_DOC_COMMENT_WHITESPACE T_DOC_COMMENT_WHITESPACE token
	 * @internalconst T_DOC_COMMENT_STRING     T_DOC_COMMENT_STRING token
	 * @internalconst T_DOUBLE_QUOTED_STRING   T_DOUBLE_QUOTED_STRING token
	 * @internalconst T_HEREDOC                T_HEREDOC token
	 * @internalconst T_NOWDOC                 T_NOWDOC token
	 *
	 * @untranslatable PHP_CODESNIFFER_IN_TESTS
	 * @untranslatable iso-8859-1
	 * @untranslatable iconv_strlen
	 * @untranslatable Logics\BuildTools\CodeSniffer\Tokenizers\PHP
	 * @untranslatable codingStandards
	 * @untranslatable codingStandardsIgnoreStart
	 * @untranslatable codingStandardsIgnoreEnd
	 * @untranslatable codingStandardsIgnoreLine
	 */

	private static function _createPositionMap(array &$tokens, $tokenizer, $eolChar, $encoding, $tabWidth)
	    {
		$currColumn    = 1;
		$lineNumber    = 1;
		$eolLen        = (strlen($eolChar) * -1);
		$tokenizerType = get_class($tokenizer);
		$ignoring      = false;
		$inTests       = defined("PHP_CODESNIFFER_IN_TESTS");

		$checkEncoding = false;
		if ($encoding !== "iso-8859-1" && function_exists("iconv_strlen") === true)
		    {
			$checkEncoding = true;
		    }

		$tokensWithTabs = array(
				   T_WHITESPACE               => true,
				   T_COMMENT                  => true,
				   T_DOC_COMMENT              => true,
				   T_DOC_COMMENT_WHITESPACE   => true,
				   T_DOC_COMMENT_STRING       => true,
				   T_CONSTANT_ENCAPSED_STRING => true,
				   T_DOUBLE_QUOTED_STRING     => true,
				   T_HEREDOC                  => true,
				   T_NOWDOC                   => true,
				   T_INLINE_HTML              => true,
				  );

		$numTokens = count($tokens);
		for ($i = 0; $i < $numTokens; $i++)
		    {
			$tokens[$i]["line"]   = $lineNumber;
			$tokens[$i]["column"] = $currColumn;

			if ($tokenizerType === "Logics\BuildTools\CodeSniffer\Tokenizers\PHP" && isset(Tokens::$knownLengths[$tokens[$i]["code"]]) === true)
			    {
				// There are no tabs in the tokens we know the length of.
				$length      = Tokens::$knownLengths[$tokens[$i]["code"]];
				$currColumn += $length;
			    }
			else if ($tabWidth === 0 || isset($tokensWithTabs[$tokens[$i]["code"]]) === false || strpos($tokens[$i]["content"], "\t") === false)
			    {
				// There are no tabs in this content, or we aren't replacing them.
				if ($checkEncoding === true)
				    {
					// Not using the default encoding, so take a bit more care.
					$length = iconv_strlen($tokens[$i]["content"], $encoding);
					if ($length === false)
					    {
						// String contained invalid characters, so revert to default.
						$length = strlen($tokens[$i]["content"]);
					    }
				    }
				else
				    {
					$length = strlen($tokens[$i]["content"]);
				    }

				$currColumn += $length;
			    }
			else
			    {
				if (str_replace("\t", "", $tokens[$i]["content"]) === "")
				    {
					// String only contains tabs, so we can shortcut the process.
					$numTabs = strlen($tokens[$i]["content"]);

					$newContent   = "";
					$firstTabSize = ($tabWidth - ($currColumn % $tabWidth) + 1);
					$length       = ($firstTabSize + ($tabWidth * ($numTabs - 1)));
					$currColumn  += $length;
					$newContent   = str_repeat(" ", $length);
				    }
				else
				    {
					// We need to determine the length of each tab.
					$tabs = explode("\t", $tokens[$i]["content"]);

					$numTabs    = (count($tabs) - 1);
					$tabNum     = 0;
					$newContent = "";
					$length     = 0;

					foreach ($tabs as $content)
					    {
						if ($content !== "")
						    {
							$newContent .= $content;
							if ($checkEncoding === true)
							    {
								// Not using the default encoding, so take a bit more care.
								$contentLength = iconv_strlen($content, $encoding);
								if ($contentLength === false)
								    {
									// String contained invalid characters, so revert to default.
									$contentLength = strlen($content);
								    }
							    }
							else
							    {
								$contentLength = strlen($content);
							    }

							$currColumn += $contentLength;
							$length     += $contentLength;
						    }

						// The last piece of content does not have a tab after it.
						if ($tabNum === $numTabs)
						    {
							break;
						    }

						// Process the tab that comes after the content.
						$lastCurrColumn = $currColumn;
						$tabNum++;

						// Move the pointer to the next tab stop.
						if (($currColumn % $tabWidth) === 0)
						    {
							// This is the first tab, and we are already at a
							// tab stop, so this tab counts as a single space.
							$currColumn++;
						    }
						else
						    {
							$currColumn++;
							while (($currColumn % $tabWidth) !== 0)
							    {
								$currColumn++;
							    }

							$currColumn++;
						    }

						$length     += ($currColumn - $lastCurrColumn);
						$newContent .= str_repeat(" ", ($currColumn - $lastCurrColumn));
					    } //end foreach
				    } //end if

				$tokens[$i]["orig_content"] = $tokens[$i]["content"];
				$tokens[$i]["content"]      = $newContent;
			    } //end if

			$tokens[$i]["length"] = $length;

			if (isset(Tokens::$knownLengths[$tokens[$i]["code"]]) === false && strpos($tokens[$i]["content"], $eolChar) !== false)
			    {
				$lineNumber++;
				$currColumn = 1;

				// Newline chars are not counted in the token length.
				$tokens[$i]["length"] += $eolLen;
			    }

			if ($tokens[$i]["code"] === T_COMMENT || $tokens[$i]["code"] === T_DOC_COMMENT || ($inTests === true && $tokens[$i]["code"] === T_INLINE_HTML))
			    {
				if (strpos($tokens[$i]["content"], "@" . "codingStandards") !== false)
				    {
					if ($ignoring === false && strpos($tokens[$i]["content"], "@" . "codingStandardsIgnoreStart") !== false)
					    {
						$ignoring = true;
					    }
					else if ($ignoring === true && strpos($tokens[$i]["content"], "@" . "codingStandardsIgnoreEnd") !== false)
					    {
						$ignoring = false;
						// Ignore this comment too.
//						self::$_ignoredLines[$tokens[$i]["line"]] = true;
					    }
					else if ($ignoring === false && strpos($tokens[$i]["content"], "@" . "codingStandardsIgnoreLine") !== false)
					    {
//						self::$_ignoredLines[($tokens[$i]["line"] + 1)] = true;
						// Ignore this comment too.
//						self::$_ignoredLines[$tokens[$i]["line"]] = true;
					    }
				    }
			    } //end if

			if ($ignoring === true)
			    {
//				self::$_ignoredLines[$tokens[$i]["line"]] = true;
			    }
		    } //end for
	    } //end _createPositionMap()


	/**
	 * Creates a map of brackets positions.
	 *
	 * @param array $tokens The array of tokens to process.
	 *
	 * @return void
	 *
	 * @internalconst T_OPEN_PARENTHESIS     T_OPEN_PARENTHESIS token
	 * @internalconst T_CLOSE_PARENTHESIS    T_CLOSE_PARENTHESIS token
	 * @internalconst T_OPEN_SQUARE_BRACKET  T_OPEN_SQUARE_BRACKET token
	 * @internalconst T_OPEN_CURLY_BRACKET   T_OPEN_CURLY_BRACKET token
	 * @internalconst T_CLOSE_SQUARE_BRACKET T_CLOSE_SQUARE_BRACKET token
	 * @internalconst T_CLOSE_CURLY_BRACKET  T_CLOSE_CURLY_BRACKET token
	 */

	private static function _createTokenMap(array &$tokens)
	    {
		Console::report("*** " . _("START TOKEN MAP") . " ***", 1, 1);

		$squareOpeners = array();
		$curlyOpeners  = array();
		$numTokens     = count($tokens);

		$openers   = array();
		$openOwner = null;

		for ($i = 0; $i < $numTokens; $i++)
		    {
			/*
			    Parenthesis mapping.
			*/

			if (isset(Tokens::$parenthesisOpeners[$tokens[$i]["code"]]) === true)
			    {
				$tokens[$i]["parenthesis_opener"] = null;
				$tokens[$i]["parenthesis_closer"] = null;
				$tokens[$i]["parenthesis_owner"]  = $i;
				$openOwner                        = $i;
			    }
			else if ($tokens[$i]["code"] === T_OPEN_PARENTHESIS)
			    {
				$openers[]                        = $i;
				$tokens[$i]["parenthesis_opener"] = $i;
				if ($openOwner !== null)
				    {
					$tokens[$openOwner]["parenthesis_opener"] = $i;
					$tokens[$i]["parenthesis_owner"]          = $openOwner;
					$openOwner = null;
				    }
			    }
			else if ($tokens[$i]["code"] === T_CLOSE_PARENTHESIS)
			    {
				// Did we set an owner for this set of parenthesis?
				$numOpeners = count($openers);
				if ($numOpeners !== 0)
				    {
					$opener = array_pop($openers);
					if (isset($tokens[$opener]["parenthesis_owner"]) === true)
					    {
						$owner = $tokens[$opener]["parenthesis_owner"];

						$tokens[$owner]["parenthesis_closer"] = $i;
						$tokens[$i]["parenthesis_owner"]      = $owner;
					    }

					$tokens[$i]["parenthesis_opener"]      = $opener;
					$tokens[$i]["parenthesis_closer"]      = $i;
					$tokens[$opener]["parenthesis_closer"] = $i;
				    }
			    } //end if

			/*
			    Bracket mapping.
			*/

			switch ($tokens[$i]["code"])
			    {
				case T_OPEN_SQUARE_BRACKET:
					$squareOpeners[] = $i;

					Console::report("=> " . _("Found square bracket opener at") . " " . $i, (count($squareOpeners) + count($curlyOpeners)), 1);
				    break;
				case T_OPEN_CURLY_BRACKET:
					if (isset($tokens[$i]["scope_closer"]) === false)
					    {
						$curlyOpeners[] = $i;

						Console::report("=> " . _("Found curly bracket opener at") . " " . $i, (count($squareOpeners) + count($curlyOpeners)), 1);
					    }
				    break;
				case T_CLOSE_SQUARE_BRACKET:
					if (empty($squareOpeners) === false)
					    {
						$opener                       = array_pop($squareOpeners);
						$tokens[$i]["bracket_opener"] = $opener;
						$tokens[$i]["bracket_closer"] = $i;
						$tokens[$opener]["bracket_opener"] = $opener;
						$tokens[$opener]["bracket_closer"] = $i;

						Console::report(
						    "=> " . _("Found square bracket closer at") . " " . $i . " " . _("for") . " " . $opener,
						    (count($squareOpeners) + count($curlyOpeners) + 1), 1
						);
					    }
				    break;
				case T_CLOSE_CURLY_BRACKET:
					if (empty($curlyOpeners) === false && isset($tokens[$i]["scope_opener"]) === false)
					    {
						$opener                       = array_pop($curlyOpeners);
						$tokens[$i]["bracket_opener"] = $opener;
						$tokens[$i]["bracket_closer"] = $i;
						$tokens[$opener]["bracket_opener"] = $opener;
						$tokens[$opener]["bracket_closer"] = $i;

						Console::report(
						    "=> " . _("Found curly bracket closer at") . " " . $i . " " . _("for") . " " . $opener,
						    (count($squareOpeners) + count($curlyOpeners) + 1), 1
						);
					    }
				    break;
				default:
				    continue;
			    } //end switch
		    } //end for

		Console::report("*** " . _("END TOKEN MAP") . " ***", 1, 1);
	    } //end _createTokenMap()


	/**
	 * Creates a map for the parenthesis tokens that surround other tokens.
	 *
	 * @param array $tokens The array of tokens to process.
	 *
	 * @return void
	 */

	private static function _createParenthesisNestingMap(array &$tokens)
	    {
		$numTokens = count($tokens);
		$map       = array();
		for ($i = 0; $i < $numTokens; $i++)
		    {
			if (isset($tokens[$i]["parenthesis_opener"]) === true && $i === $tokens[$i]["parenthesis_opener"])
			    {
				if (empty($map) === false)
				    {
					$tokens[$i]["nested_parenthesis"] = $map;
				    }

				if (isset($tokens[$i]["parenthesis_closer"]) === true)
				    {
					$map[$tokens[$i]["parenthesis_opener"]] = $tokens[$i]["parenthesis_closer"];
				    }
			    }
			else if (isset($tokens[$i]["parenthesis_closer"]) === true && $i === $tokens[$i]["parenthesis_closer"])
			    {
				array_pop($map);
				if (empty($map) === false)
				    {
					$tokens[$i]["nested_parenthesis"] = $map;
				    }
			    }
			else
			    {
				if (empty($map) === false)
				    {
					$tokens[$i]["nested_parenthesis"] = $map;
				    }
			    } //end if
		    } //end for
	    } //end _createParenthesisNestingMap()


	/**
	 * Creates a scope map of tokens that open scopes.
	 *
	 * @param array  $tokens    The array of tokens to process.
	 * @param object $tokenizer The tokenizer being used to process this file.
	 * @param string $eolChar   The EOL character to use for splitting strings.
	 *
	 * @return void
	 *
	 * @see _recurseScopeMap()
	 */

	private static function _createScopeMap(array &$tokens, $tokenizer, $eolChar)
	    {
		Console::report("*** " . _("START SCOPE MAP") . " ***", 1, 1);

		$numTokens = count($tokens);
		for ($i = 0; $i < $numTokens; $i++)
		    {
			// Check to see if the current token starts a new scope.
			if (isset($tokenizer->scopeOpeners[$tokens[$i]["code"]]) === true)
			    {
				Console::report(
				    _("Start scope map at") . " " . $i . ": " . $tokens[$i]["type"] . " => " . Console::prepareForOutput($tokens[$i]["content"]),
				    1, 1
				);

				if (isset($tokens[$i]["scope_condition"]) === true)
				    {
					Console::report("* " . _("already processed, skipping") . " *", 1, 1);

					continue;
				    }

				$i = self::_recurseScopeMap($tokens, $numTokens, $tokenizer, $eolChar, $i);
			    } //end if
		    } //end for

		Console::report("*** " . _("END SCOPE MAP") . " ***", 1, 1);
	    } //end _createScopeMap()


	/**
	 * Recurses though the scope openers to build a scope map.
	 *
	 * @param array  $tokens    The array of tokens to process.
	 * @param int    $numTokens The size of the tokens array.
	 * @param object $tokenizer The tokenizer being used to process this file.
	 * @param string $eolChar   The EOL character to use for splitting strings.
	 * @param int    $stackPtr  The position in the stack of the token that
	 *                          opened the scope (eg. an IF token or FOR token).
	 * @param int    $depth     How many scope levels down we are.
	 * @param int    $ignore    How many curly braces we are ignoring.
	 *
	 * @return int The position in the stack that closed the scope.
	 *
	 * @throws Exception If nesting maximum is reached
	 *
	 * @exceptioncode EXCEPTION_MAXIMUM_NESTING_LEVEL_REACHED
	 *
	 * @internalconst T_SEMICOLON           T_SEMICOLON token
	 * @internalconst T_OPEN_PARENTHESIS    T_OPEN_PARENTHESIS token
	 * @internalconst T_OPEN_CURLY_BRACKET  T_OPEN_CURLY_BRACKET token
	 * @internalconst T_CLOSE_CURLY_BRACKET T_CLOSE_CURLY_BRACKET token
	 */

	private static function _recurseScopeMap(array &$tokens, $numTokens, $tokenizer, $eolChar, $stackPtr, $depth = 1, &$ignore = 0)
	    {
		if ($depth === 1)
		    {
			Console::report("*** " . _("START SCOPE MAP") . " ***", 1, 1);
		    }

		$opener    = null;
		$currType  = $tokens[$stackPtr]["code"];
		$startLine = $tokens[$stackPtr]["line"];

		// We will need this to restore the value if we end up
		// returning a token ID that causes our calling function to go back
		// over already ignored braces.
		$originalIgnore = $ignore;

		// If the start token for this scope opener is the same as
		// the scope token, we have already found our opener.
		if (isset($tokenizer->scopeOpeners[$currType]["start"][$currType]) === true)
		    {
			$opener = $stackPtr;
		    }

		for ($i = ($stackPtr + 1); $i < $numTokens; $i++)
		    {
			$tokenType = $tokens[$i]["code"];

			Console::report(
			    _("Process token") . " " . $i . " " . _("on line") . " " . $tokens[$i]["line"] . " [" .
			    (($opener !== null) ? _("opener") . ":" . $opener . ";" : "") .
			    (($ignore > 0) ? _("ignore") . "=" . $ignore . ";" : "") .
			    "]: " . $tokens[$i]["type"] . " => " . Console::prepareForOutput($tokens[$i]["content"]),
			    $depth, 1
			);

			// Very special case for IF statements in PHP that can be defined without scope tokens. E.g., if (1) 1; 1 ? (1 ? 1 : 1) : 1;
			// If an IF statement below this one has an opener but no
			// keyword, the opener will be incorrectly assigned to this IF statement.
			if (($currType === T_IF || $currType === T_ELSE) && $opener === null && $tokens[$i]["code"] === T_SEMICOLON)
			    {
				Console::report(
				    "=> " . _("Found semicolon before scope opener for") . " " . $stackPtr . ": " . $tokens[$stackPtr]["type"] . ", " . _("bailing"),
				    $depth, 1
				);

				return $i;
			    }

			if ($opener !== null && (isset($tokens[$i]["scope_opener"]) === false ||
			    $tokenizer->scopeOpeners[$tokens[$stackPtr]["code"]]["shared"] === true) &&
			    isset($tokenizer->scopeOpeners[$currType]["end"][$tokenType]) === true)
			    {
				if ($ignore > 0 && $tokenType === T_CLOSE_CURLY_BRACKET)
				    {
					// The last opening bracket must have been for a string
					// offset or alike, so let"s ignore it.
					Console::report("* " . _("finished ignoring curly brace") . " *", $depth, 1);

					$ignore--;
					continue;
				    }
				else if ($tokens[$opener]["code"] === T_OPEN_CURLY_BRACKET && $tokenType !== T_CLOSE_CURLY_BRACKET)
				    {
					// The opener is a curly bracket so the closer must be a curly bracket as well.
					// We ignore this closer to handle cases such as T_ELSE or T_ELSEIF being considered
					// a closer of T_IF when it should not.
					Console::report("=> " . _("Ignoring non-curly scope closer for") . " " . $stackPtr . ":" . $tokens[$stackPtr]["type"], $depth, 1);
				    }
				else
				    {
					$scopeCloser = $i;
					$todo        = array(
							$stackPtr,
							$opener,
						       );

					Console::report(
					    "=> " . _("Found scope closer") . " (" . $scopeCloser . ": " . $tokens[$scopeCloser]["type"] . ") " .
					     _("for") . " " . $stackPtr . ": " . $tokens[$stackPtr]["type"],
					    $depth, 1
					);

					$validCloser = true;
					if (($tokens[$stackPtr]["code"] === T_IF || $tokens[$stackPtr]["code"] === T_ELSEIF) &&
					    ($tokenType === T_ELSE || $tokenType === T_ELSEIF))
					    {
						// To be a closer, this token must have an opener.
						Console::report("* " . _("closer needs to be tested") . " *", $depth, 1);

						$i = self::_recurseScopeMap($tokens, $numTokens, $tokenizer, $eolChar, $i, ($depth + 1), $ignore);

						if (isset($tokens[$scopeCloser]["scope_opener"]) === false)
						    {
							$validCloser = false;
							Console::report("* " . _("closer is not valid (no opener found)") . " *", $depth, 1);
						    }
						else if ($tokens[$tokens[$scopeCloser]["scope_opener"]]["code"] !== $tokens[$opener]["code"])
						    {
							$validCloser = false;
							Console::report(
							    "* " . _("closer is not valid (mismatched opener type; ") .
							    $tokens[$tokens[$scopeCloser]["scope_opener"]]["type"] . " != " . $tokens[$opener]["type"] . ") *",
							    $depth, 1
							);
						    }
						else
						    {
							Console::report("* " . _("closer was valid") . " *", $depth, 1);
						    } //end if
					    }
					else
					    {
						// The closer was not processed, so we need to
						// complete that token as well.
						$todo[] = $scopeCloser;
					    } //end if

					if ($validCloser === true)
					    {
						foreach ($todo as $token)
						    {
							$tokens[$token]["scope_condition"] = $stackPtr;
							$tokens[$token]["scope_opener"]    = $opener;
							$tokens[$token]["scope_closer"]    = $scopeCloser;
						    }

						if ($tokenizer->scopeOpeners[$tokens[$stackPtr]["code"]]["shared"] === true)
						    {
							// As we are going back to where we started originally, restore
							// the ignore value back to its original value.
							$ignore = $originalIgnore;
							return $opener;
						    }
						else if ($scopeCloser === $i && isset($tokenizer->scopeOpeners[$tokenType]) === true)
						    {
							// Unset scope_condition here or else the token will appear to have
							// already been processed, and it will be skipped. Normally we want that,
							// but in this case, the token is both a closer and an opener, so
							// it needs to act like an opener. This is also why we return the
							// token before this one; so the closer has a chance to be processed
							// a second time, but as an opener.
							unset($tokens[$scopeCloser]["scope_condition"]);
							return ($i - 1);
						    }
						else
						    {
							return $i;
						    } //end if
					    }
					else
					    {
						continue;
					    } //end if
				    } //end if
			    } //end if

			// Is this an opening condition ?
			if (isset($tokenizer->scopeOpeners[$tokenType]) === true)
			    {
				if ($opener === null)
				    {
					if ($tokenType === T_USE)
					    {
						// PHP use keywords are special because they can be
						// used as blocks but also inline in function definitions.
						// So if we find them nested inside another opener, just skip them.
						continue;
					    }

					if ($tokenType === T_FUNCTION && $tokens[$stackPtr]["code"] !== T_FUNCTION)
					    {
						// Probably a closure, so process it manually.
						Console::report(
						    "=> " . _("Found function before scope opener for") . " " . $stackPtr . ": " .
						    $tokens[$stackPtr]["type"] . ", " . _("processing manually"),
						    $depth, 1
						);

						$i = self::_recurseScopeMap($tokens, $numTokens, $tokenizer, $eolChar, $i, ($depth + 1), $ignore);

						continue;
					    } //end if

					// Found another opening condition but still haven't
					// found our opener, so we are never going to find one.
					Console::report(
					    "=> " . _("Found new opening condition before scope opener for") . " " . $stackPtr . ": " .
					    $tokens[$stackPtr]["type"] . ", " . _("bailing"),
					    $depth, 1
					);

					return $stackPtr;
				    } //end if

				Console::report("* " . _("token is an opening condition") . " *", $depth, 1);

				$isShared = ($tokenizer->scopeOpeners[$tokenType]["shared"] === true);

				if (isset($tokens[$i]["scope_condition"]) === true)
				    {
					// We've been here before.
					Console::report("* " . _("already processed, skipping") . " *", $depth, 1);

					if ($isShared === false && isset($tokens[$i]["scope_closer"]) === true)
					    {
						$i = $tokens[$i]["scope_closer"];
					    }

					continue;
				    }
				else if ($currType === $tokenType && $isShared === false && $opener === null)
				    {
					// We haven't yet found our opener, but we have found another
					// scope opener which is the same type as us, and we don't
					// share openers, so we will never find one.
					Console::report("* " . _("it was another token's opener, bailing") . " *", $depth, 1);

					return $stackPtr;
				    }
				else
				    {
					Console::report("* " . _("searching for opener") . " *", $depth, 1);

					if (isset($tokenizer->scopeOpeners[$tokenType]["end"][T_CLOSE_CURLY_BRACKET]) === true)
					    {
						$oldIgnore = $ignore;
						$ignore    = 0;
					    }

					// PHP has a max nesting level for functions. Stop before we hit that limit
					// because too many loops means we've run into trouble anyway.
					if ($depth > 50)
					    {
						Console::report("* " . _("reached maximum nesting level; aborting") . " *", $depth, 1);

						throw new Exception(
						    _("Maximum nesting level reached; file could not be processed"),
						    EXCEPTION_MAXIMUM_NESTING_LEVEL_REACHED
						);
					    }

					if ($isShared === true && isset($tokenizer->scopeOpeners[$tokenType]["with"][$currType]) === true)
					    {
						// Don't allow the depth to incremement because this is
						// possibly not a true nesting if we are sharing our closer.
						// This can happen, for example, when a SWITCH has a large
						// number of CASE statements with the same shared BREAK.
						$depth--;
					    }

					$i = self::_recurseScopeMap($tokens, $numTokens, $tokenizer, $eolChar, $i, ($depth + 1), $ignore);

					if (isset($tokenizer->scopeOpeners[$tokenType]["end"][T_CLOSE_CURLY_BRACKET]) === true)
					    {
						$ignore = $oldIgnore;
					    }
				    } //end if
			    } //end if

			if (isset($tokenizer->scopeOpeners[$currType]["start"][$tokenType]) === true && $opener === null)
			    {
				if ($tokenType === T_OPEN_CURLY_BRACKET)
				    {
					if (isset($tokens[$stackPtr]["parenthesis_closer"]) === true && $i < $tokens[$stackPtr]["parenthesis_closer"])
					    {
						// We found a curly brace inside the condition of the
						// current scope opener, so it must be a string offset.
						Console::report("* " . _("ignoring curly brace") . " *", $depth, 1);

						$ignore++;
					    }
					else
					    {
						// Make sure this is actually an opener and not a string offset (e.g., $var{0}).
						for ($x = ($i - 1); $x > 0; $x--)
						    {
							if (isset(Tokens::$emptyTokens[$tokens[$x]["code"]]) === true)
							    {
								continue;
							    }
							else
							    {
								// If the first non-whitespace/comment token is a
								// variable or object operator then this is an opener
								// for a string offset and not a scope.
								if ($tokens[$x]["code"] === T_VARIABLE || $tokens[$x]["code"] === T_OBJECT_OPERATOR)
								    {
									Console::report("* " . _("ignoring curly brace") . " *", $depth, 1);

									$ignore++;
								    } //end if

								break;
							    } //end if
						    } //end for
					    } //end if
				    } //end if

				if ($ignore === 0 || $tokenType !== T_OPEN_CURLY_BRACKET)
				    {
					// We found the opening scope token for $currType.
					Console::report("=> " . _("Found scope opener for") . " " . $stackPtr . ":" . $tokens[$stackPtr]["type"], $depth, 1);

					$opener = $i;
				    }
			    }
			else if ($tokenType === T_OPEN_PARENTHESIS)
			    {
				if (isset($tokens[$i]["parenthesis_owner"]) === true)
				    {
					$owner = $tokens[$i]["parenthesis_owner"];
					if (isset(Tokens::$scopeOpeners[$tokens[$owner]["code"]]) === true && isset($tokens[$i]["parenthesis_closer"]) === true)
					    {
						// If we get into here, then we opened a parenthesis for
						// a scope (eg. an if or else if) so we need to update the
						// start of the line so that when we check to see
						// if the closing parenthesis is more than 3 lines away from
						// the statement, we check from the closing parenthesis.
						$startLine = $tokens[$tokens[$i]["parenthesis_closer"]]["line"];
					    }
				    }
			    }
			else if ($tokenType === T_OPEN_CURLY_BRACKET && $opener !== null)
			    {
				// We opened something that we don't have a scope opener for.
				// Examples of this are curly brackets for string offsets etc.
				// We want to ignore this so that we don't have an invalid scope
				// map.
				Console::report("* " . _("ignoring curly brace") . " *", $depth, 1);

				$ignore++;
			    }
			else if ($tokenType === T_CLOSE_CURLY_BRACKET && $ignore > 0)
			    {
				// We found the end token for the opener we were ignoring.
				Console::report("* " . _("finished ignoring curly brace") . " *", $depth, 1);

				$ignore--;
			    }
			else if ($opener === null && isset($tokenizer->scopeOpeners[$currType]) === true)
			    {
				// If we still haven't found the opener after 3 lines,
				// we're not going to find it, unless we know it requires
				// an opener (in which case we better keep looking) or the last
				// token was empty (in which case we'll just confirm there is
				// more code in this file and not just a big comment).
				if ($tokens[$i]["line"] >= ($startLine + 3) && isset(Tokens::$emptyTokens[$tokens[($i - 1)]["code"]]) === false)
				    {
					if ($tokenizer->scopeOpeners[$currType]["strict"] === true)
					    {
						Console::report(
						    "=> " . _("Still looking for") . " " . $stackPtr . ": " . $tokens[$stackPtr]["type"] . " " .
						     _("scope opener after") . " " . ($tokens[$i]["line"] - $startLine) . " " . _("lines"),
						    $depth, 1
						);
					    }
					else
					    {
						Console::report(
						    "=> " . _("Couldn't find scope opener for") . " " . $stackPtr . ": " . $tokens[$stackPtr]["type"] . ", " . _("bailing"),
						    $depth, 1
						);

						return $stackPtr;
					    } //end if
				    } //end if
			    }
			else if ($opener !== null && $tokenType !== T_BREAK && isset($tokenizer->endScopeTokens[$tokenType]) === true)
			    {
				if (isset($tokens[$i]["scope_condition"]) === false)
				    {
					if ($ignore > 0)
					    {
						// We found the end token for the opener we were ignoring.
						Console::report("* " . _("finished ignoring curly brace") . " *", $depth, 1);

						$ignore--;
					    }
					else
					    {
						// We found a token that closes the scope but it doesn't
						// have a condition, so it belongs to another token and
						// our token doesn't have a closer, so pretend this is
						// the closer.
						Console::report("=> " . _("Found (unexpected) scope closer for") . $stackPtr . ":" . $tokens[$stackPtr]["type"], $depth, 1);

						foreach (array($stackPtr, $opener) as $token)
						    {
							$tokens[$token]["scope_condition"] = $stackPtr;
							$tokens[$token]["scope_opener"]    = $opener;
							$tokens[$token]["scope_closer"]    = $i;
						    }

						return ($i - 1);
					    } //end if
				    } //end if
			    } //end if
		    } //end for

		return $stackPtr;
	    } //end _recurseScopeMap()


	/**
	 * Constructs the level map.
	 *
	 * The level map adds a 'level' index to each token which indicates the
	 * depth that a token within a set of scope blocks. It also adds a
	 * 'condition' index which is an array of the scope conditions that opened
	 * each of the scopes - position 0 being the first scope opener.
	 *
	 * @param array  $tokens    The array of tokens to process.
	 * @param object $tokenizer The tokenizer being used to process this file.
	 *
	 * @return void
	 *
	 * @untranslatable [col:
	 * @untranslatable ;len:
	 * @untranslatable ;lvl:
	 * @untranslatable conds:
	 * @untranslatable token_name
	 */

	private static function _createLevelMap(array &$tokens, $tokenizer)
	    {
		Console::report("*** " . _("START LEVEL MAP") . " ***", 1, 1);

		$numTokens  = count($tokens);
		$level      = 0;
		$conditions = array();
		$lastOpener = null;
		$openers    = array();

		for ($i = 0; $i < $numTokens; $i++)
		    {
			Console::report(
			    _("Process token") . " " . $i . " " . _("on line") . " " . $tokens[$i]["line"] .
			    " [col:" . $tokens[$i]["length"] . ";len:" . $tokens[$i]["column"] . ";lvl:" . $level . ";" .
			    ((empty($conditions) === false) ? "conds:" . implode(",", array_map("token_name", $conditions)) . ";" : "") .
			    "]: " . $tokens[$i]["type"] . " => " . Console::prepareForOutput($tokens[$i]["content"]),
			    ($level + 1), 1
			);

			$tokens[$i]["level"]      = $level;
			$tokens[$i]["conditions"] = $conditions;

			if (isset($tokens[$i]["scope_condition"]) === true)
			    {
				// Check to see if this token opened the scope.
				if ($tokens[$i]["scope_opener"] === $i)
				    {
					$stackPtr = $tokens[$i]["scope_condition"];
					Console::report(
					    "=> " . _("Found scope opener for") . " " . $stackPtr . ": " . $tokens[$stackPtr]["type"],
					    ($level + 1), 1
					);

					$stackPtr = $tokens[$i]["scope_condition"];

					// If we find a scope opener that has a shared closer,
					// then we need to go back over the condition map that we
					// just created and fix ourselves as we just added some
					// conditions where there was none. This happens for T_CASE
					// statements that are using the same break statement.
					if ($lastOpener !== null && $tokens[$lastOpener]["scope_closer"] === $tokens[$i]["scope_closer"])
					    {
						// This opener shares its closer with the previous opener,
						// but we still need to check if the two openers share their
						// closer with each other directly (like CASE and DEFAULT)
						// or if they are just sharing because one doesn't have a
						// closer (like CASE with no BREAK using a SWITCHes closer).
						$thisType = $tokens[$tokens[$i]["scope_condition"]]["code"];
						$opener   = $tokens[$lastOpener]["scope_condition"];

						$isShared = isset($tokenizer->scopeOpeners[$thisType]["with"][$tokens[$opener]["code"]]);

						reset($tokenizer->scopeOpeners[$thisType]["end"]);
						$end = current($tokenizer->scopeOpeners[$thisType]["end"]);
						reset($tokenizer->scopeOpeners[$tokens[$opener]["code"]]["end"]);
						$sameEnd = ($end === current($tokenizer->scopeOpeners[$tokens[$opener]["code"]]["end"]));

						if ($isShared === true && $sameEnd === true)
						    {
							$badToken = $opener;
							Console::report(
							    "* " . _("shared closer, cleaning up") . " " . $badToken . ": " . $tokens[$badToken]["type"] . " *",
							    ($level + 1), 1
							);

							for ($x = $tokens[$i]["scope_condition"]; $x <= $i; $x++)
							    {
								$oldConditions = $tokens[$x]["conditions"];
								$oldLevel      = $tokens[$x]["level"];
								$tokens[$x]["level"]--;
								unset($tokens[$x]["conditions"][$badToken]);
								Console::report("* " . _("cleaned") . " " . $x . ":" . $tokens[$x]["type"] . " *", ($level + 1), 1);
								Console::report(
								    "=> " . _("level changed from") . " " . $oldLevel . " " . _("to") . " " . $tokens[$x]["level"],
								    ($level + 2), 1
								);
								Console::report(
								    "=> " . _("conditions changed from") . " " . implode(",", array_map("token_name", $oldConditions)) . " " .
								    _("to") . " " . implode(",", array_map("token_name", $tokens[$x]["conditions"])),
								    ($level + 2), 1
								);
							    } //end for

							unset($conditions[$badToken]);
							Console::report(
							    "* " . _("token") . " " . $badToken . ": " .
							    $tokens[$badToken]["type"] . " " . _("removed from conditions array") . " *",
							    ($level + 1), 1
							);

							unset($openers[$lastOpener]);

							$level--;
							Console::report("* " . _("level decreased") . " *", ($level + 2), 1);
						    } //end if
					    } //end if

					$level++;
					Console::report("* " . _("level increased") . " *", ($level + 1), 1);

					$conditions[$stackPtr] = $tokens[$stackPtr]["code"];
					Console::report(
					    "* " . _("token") . " " . $stackPtr . ":" . $tokens[$stackPtr]["type"] . " " . _("added to conditions array") . " *",
					    ($level + 1), 1
					);

					$lastOpener = $tokens[$i]["scope_opener"];
					if ($lastOpener !== null)
					    {
						$openers[$lastOpener] = $lastOpener;
					    }
				    }
				else if ($lastOpener !== null && $tokens[$lastOpener]["scope_closer"] === $i)
				    {
					foreach (array_reverse($openers) as $opener)
					    {
						if ($tokens[$opener]["scope_closer"] === $i)
						    {
							$oldOpener = array_pop($openers);
							if (empty($openers) === false)
							    {
								$lastOpener           = array_pop($openers);
								$openers[$lastOpener] = $lastOpener;
							    }
							else
							    {
								$lastOpener = null;
							    }

							Console::report(
							    "=> " . _("Found scope closer for") . " " . $oldOpener . ": " . $tokens[$oldOpener]["type"],
							    ($level + 1), 1
							);

							$oldCondition = array_pop($conditions);
							Console::report(
							    "* " . _("token") . " " . token_name($oldCondition) . " " . _("removed from conditions array") . " *",
							    ($level + 1), 1
							);

							// Make sure this closer actually belongs to us.
							// Either the condition also has to think this is the
							// closer, or it has to allow sharing with us.
							$condition = $tokens[$tokens[$i]["scope_condition"]]["code"];
							if ($condition !== $oldCondition)
							    {
								if (isset($tokenizer->scopeOpeners[$oldCondition]["with"][$condition]) === false)
								    {
									$badToken = $tokens[$oldOpener]["scope_condition"];

									Console::report(
									    "* " . _("scope closer was bad, cleaning up") . " " .
									    $badToken . ":" . token_name($oldCondition) . " *",
									    ($level + 1), 1
									);

									for ($x = ($oldOpener + 1); $x <= $i; $x++)
									    {
										$oldConditions = $tokens[$x]["conditions"];
										$oldLevel      = $tokens[$x]["level"];
										$tokens[$x]["level"]--;
										unset($tokens[$x]["conditions"][$badToken]);

										Console::report(
										    "* " . _("cleaned") . " " . $x . ":" . $tokens[$x]["type"] . " *",
										    ($level + 1), 1
										);
										Console::report(
										    "=> " . _("level changed from") . " " . $oldLevel . " " .
										    _("to") . " " . $tokens[$x]["level"],
										    ($level + 2), 1
										);
										Console::report(
										    "=> " . _("conditions changed from") . " " .
										    implode(",", array_map("token_name", $oldConditions)) . " " .
										    _("to") . " " .
										    implode(",", array_map("token_name", $tokens[$x]["conditions"])),
										    ($level + 2), 1
										);
									    } //end for
								    } //end if
							    } //end if

							$level--;
							Console::report("* " . _("level decreased") . " *", ($level + 2), 1);

							$tokens[$i]["level"]      = $level;
							$tokens[$i]["conditions"] = $conditions;
						    } //end if
					    } //end foreach
				    } //end if
			    } //end if
		    } //end for

		Console::report("*** " . _("END LEVEL MAP") . " ***", 1, 1);
	    } //end _createLevelMap()


	/**
	 * Returns the declaration names for T_CLASS, T_INTERFACE and T_FUNCTION tokens.
	 *
	 * @param int $stackPtr The position of the declaration token which declared the class, interface or function.
	 *
	 * @return string|null The name of the class, interface or function or NULL if the function is a closure.
	 *
	 * @throws Exception If the specified token is not of type T_FUNCTION, T_CLASS or T_INTERFACE.
	 *
	 * @exceptioncode EXCEPTION_TOKEN_TYPE_IS_INCORRECT
	 */

	public function getDeclarationName($stackPtr)
	    {
		$tokenCode = $this->tokens[$stackPtr]["code"];
		if ($tokenCode !== T_FUNCTION && $tokenCode !== T_CLASS && $tokenCode !== T_INTERFACE && $tokenCode !== T_TRAIT)
		    {
			throw new Exception(
			    _("Token type is not T_FUNCTION, T_CLASS, T_INTERFACE or T_TRAIT"),
			    EXCEPTION_TOKEN_TYPE_IS_INCORRECT
			);
		    }

		if ($tokenCode === T_FUNCTION && $this->isAnonymousFunction($stackPtr) === true)
		    {
			return null;
		    }
		else
		    {
			$token = $this->findNext(T_STRING, $stackPtr);
			return $this->tokens[$token]["content"];
		    }
	    } //end getDeclarationName()


	/**
	 * Check if the token at the specified position is a anonymous function.
	 *
	 * @param int $stackPtr The position of the declaration token which declared the class, interface or function.
	 *
	 * @return bool
	 *
	 * @throws Exception If the specified token is not of type T_FUNCTION
	 *
	 * @exceptioncode EXCEPTION_TOKEN_TYPE_IS_NOT_T_FUNCTION
	 */

	public function isAnonymousFunction($stackPtr)
	    {
		$tokenCode = $this->tokens[$stackPtr]["code"];
		if ($tokenCode !== T_FUNCTION)
		    {
			throw new Exception(_("Token type is not T_FUNCTION"), EXCEPTION_TOKEN_TYPE_IS_NOT_T_FUNCTION);
		    }

		if (isset($this->tokens[$stackPtr]["parenthesis_opener"]) === false)
		    {
			// Something is not right with this function.
			return false;
		    }
		else
		    {
			$name = $this->findNext(T_STRING, ($stackPtr + 1));
			if ($name === false)
			    {
				// No name found.
				return true;
			    }
			else
			    {
				$open = $this->tokens[$stackPtr]["parenthesis_opener"];
				return ($name > $open);
			    }
		    }
	    } //end isAnonymousFunction()


	/**
	 * Returns the method parameters for the specified T_FUNCTION token.
	 *
	 * Each parameter is in the following format:
	 *
	 * <code>
	 *   0 => array(
	 *         "name"              => "$var",  // The variable name.
	 *         "pass_by_reference" => false,   // Passed by reference.
	 *         "type_hint"         => string,  // Type hint for array or custom type
	 *        )
	 * </code>
	 *
	 * Parameters with default values have and additional array indice of "default" with the value of the default as a string.
	 *
	 * @param int $stackPtr The position in the stack of the T_FUNCTION token to acquire the parameters for.
	 *
	 * @return array
	 *
	 * @throws Exception If the specified $stackPtr is not of type T_FUNCTION.
	 *
	 * @exceptioncode EXCEPTION_STACKPTR_MUST_BE_T_FUNCTION
	 *
	 * @internalconst T_BITWISE_AND       T_BITWISE_AND token
	 * @internalconst T_ARRAY_HINT        T_ARRAY_HINT token
	 * @internalconst T_EQUAL             T_EQUAL token
	 * @internalconst T_CLOSE_PARENTHESIS T_CLOSE_PARENTHESIS token
	 * @internalconst T_COMMA             T_COMMA token
	 */

	public function getMethodParameters($stackPtr)
	    {
		if ($this->tokens[$stackPtr]["code"] !== T_FUNCTION)
		    {
			throw new Exception(_("\$stackPtr must be of type T_FUNCTION"), EXCEPTION_STACKPTR_MUST_BE_T_FUNCTION);
		    }

		$opener = $this->tokens[$stackPtr]["parenthesis_opener"];
		$closer = $this->tokens[$stackPtr]["parenthesis_closer"];

		$vars            = array();
		$currVar         = null;
		$defaultStart    = null;
		$paramCount      = 0;
		$passByReference = false;
		$typeHint        = "";

		for ($i = ($opener + 1); $i <= $closer; $i++)
		    {
			// Check to see if this token has a parenthesis opener. If it does
			// its likely to be an array, which might have arguments in it, which
			// we cause problems in our parsing below, so lets just skip to the
			// end of it.
			// Don't do this if it's the close parenthesis for the method.
			if (isset($this->tokens[$i]["parenthesis_opener"]) === true && $i !== $this->tokens[$i]["parenthesis_closer"])
			    {
				$i = ($this->tokens[$i]["parenthesis_closer"] + 1);
			    }

			if (isset($this->tokens[$i]["bracket_opener"]) === true)
			    {
				// Don't do this if it's the close parenthesis for the method.
				if ($i !== $this->tokens[$i]["bracket_closer"])
				    {
					$i = ($this->tokens[$i]["bracket_closer"] + 1);
				    }
			    }

			$token = $this->tokens[$i]["code"];

			$passByReference = ($token === T_BITWISE_AND) ? true : $passByReference;
			$currVar         = ($token === T_VARIABLE) ? $i : $currVar;
			$defaultStart    = ($token === T_EQUAL) ? ($i + 1) : $defaultStart;
			$typeHint        = ($token === T_ARRAY_HINT || $token === T_CALLABLE) ? $this->tokens[$i]["content"] : $typeHint;
			$typeHint       .= ($token === T_NS_SEPARATOR && $defaultStart === null) ? $this->tokens[$i]["content"] : "";

			if ($token === T_STRING)
			    {
				// This is a string, so it may be a type hint, but it could
				// also be a constant used as a default value.
				$prevComma = $this->findPrevious(T_COMMA, $i, $opener);
				if ($prevComma !== false)
				    {
					$nextEquals = $this->findNext(T_EQUAL, $prevComma, $i);
					if ($nextEquals !== false)
					    {
						break;
					    }
				    }

				$typeHint .= ($defaultStart === null) ? $this->tokens[$i]["content"] : "";
			    }

			if (($token === T_CLOSE_PARENTHESIS || $token === T_COMMA) && $currVar !== null)
			    {
				$vars[$paramCount]         = array();
				$vars[$paramCount]["name"] = $this->tokens[$currVar]["content"];

				if ($defaultStart !== null)
				    {
					$vars[$paramCount]["default"] = $this->getTokensAsString($defaultStart, ($i - $defaultStart));
				    }

				$vars[$paramCount]["pass_by_reference"] = $passByReference;
				$vars[$paramCount]["type_hint"]         = $typeHint;

				// Reset the vars, as we are about to process the next parameter.
				$defaultStart    = null;
				$passByReference = false;
				$typeHint        = "";

				$paramCount++;
			    }
		    } //end for

		return $vars;
	    } //end getMethodParameters()


	/**
	 * Returns the visibility and implementation properties of a method.
	 *
	 * The format of the array is:
	 * <code>
	 *   array(
	 *    "scope"           => "public", // public private or protected
	 *    "scope_specified" => true,     // true is scope keyword was found.
	 *    "is_abstract"     => false,    // true if the abstract keyword was found.
	 *    "is_final"        => false,    // true if the final keyword was found.
	 *    "is_static"       => false,    // true if the static keyword was found.
	 *    "is_closure"      => false,    // true if no name is found.
	 *   );
	 * </code>
	 *
	 * @param int $stackPtr The position in the stack of the T_FUNCTION token to acquire the properties for.
	 *
	 * @return array
	 *
	 * @throws Exception If the specified position is not a T_FUNCTION token.
	 *
	 * @exceptioncode EXCEPTION_STACKPTR_MUST_BE_T_FUNCTION
	 *
	 * @untranslatable public
	 * @untranslatable private
	 * @untranslatable protected
	 */

	public function getMethodProperties($stackPtr)
	    {
		if ($this->tokens[$stackPtr]["code"] !== T_FUNCTION)
		    {
			throw new Exception(_("\$stackPtr must be of type T_FUNCTION"), EXCEPTION_STACKPTR_MUST_BE_T_FUNCTION);
		    }

		$valid = array(
			  T_PUBLIC,
			  T_PRIVATE,
			  T_PROTECTED,
			  T_STATIC,
			  T_FINAL,
			  T_ABSTRACT,
			  T_WHITESPACE,
			  T_COMMENT,
			  T_DOC_COMMENT,
			 );

		$result = array(
			   "scope"           => "public",
			   "scope_specified" => false,
			   "is_abstract"     => false,
			   "is_final"        => false,
			   "is_static"       => false,
			   "is_closure"      => $this->isAnonymousFunction($stackPtr),
			  );

		for ($i = ($stackPtr - 1); $i > 0; $i--)
		    {
			if (in_array($this->tokens[$i]["code"], $valid) === false)
			    {
				break;
			    }

			$scopes = array(
				   T_PUBLIC    => "public",
				   T_PRIVATE   => "private",
				   T_PROTECTED => "protected",
				  );

			if (isset($scopes[$this->tokens[$i]["code"]]) === true)
			    {
				$result["scope"]           = $scopes[$this->tokens[$i]["code"]];
				$result["scope_specified"] = true;
			    }

			switch ($this->tokens[$i]["code"])
			    {
				case T_ABSTRACT:
					$result["is_abstract"] = true;
				    break;
				case T_FINAL:
					$result["is_final"] = true;
				    break;
				case T_STATIC:
					$result["is_static"] = true;
				    break;
			    } //end switch
		    } //end for

		return $result;
	    } //end getMethodProperties()


	/**
	 * Returns the visibility and implementation properties of the class member
	 * variable found at the specified position in the stack.
	 *
	 * The format of the array is:
	 *
	 * <code>
	 *   array(
	 *    "scope"           => "public", // public private or protected
	 *    "scope_specified" => true,     // true is scope keyword was found.
	 *    "is_static"       => false,    // true if the static keyword was found.
	 *   );
	 * </code>
	 *
	 * @param int $stackPtr The position in the stack of the T_VARIABLE token to acquire the properties for.
	 *
	 * @return array
	 *
	 * @throws Exception If the specified position is not a T_VARIABLE token, or if the position is not a class member variable.
	 *
	 * @exceptioncode EXCEPTION_STACKPTR_MUST_BE_T_VARIABLE
	 * @exceptioncode EXCEPTION_STACKPTR_IS_NOT_CLASS_MEMBER_VAR
	 *
	 * @internalconst T_COMMA T_COMMA token
	 *
	 * @untranslatable Internal.ParseError.InterfaceHasMemberVar
	 * @untranslatable public
	 * @untranslatable private
	 * @untranslatable protected
	 */

	public function getMemberProperties($stackPtr)
	    {
		if ($this->tokens[$stackPtr]["code"] !== T_VARIABLE)
		    {
			throw new Exception(_("\$stackPtr must be of type T_VARIABLE"), EXCEPTION_STACKPTR_MUST_BE_T_VARIABLE);
		    }

		$conditions = array_keys($this->tokens[$stackPtr]["conditions"]);
		$ptr        = array_pop($conditions);
		$fault      = false;
		if (isset($this->tokens[$ptr]) === false ||
		    ($this->tokens[$ptr]["code"] !== T_CLASS && $this->tokens[$ptr]["code"] !== T_TRAIT))
		    {
			if (isset($this->tokens[$ptr]) === true && $this->tokens[$ptr]["code"] === T_INTERFACE)
			    {
				// T_VARIABLEs in interfaces can actually be method arguments
				// but they wont be seen as being inside the method because there
				// are no scope openers and closers for abstract methods. If it is in
				// parentheses, we can be pretty sure it is a method argument.
				if (isset($this->tokens[$stackPtr]["nested_parenthesis"]) === false || empty($this->tokens[$stackPtr]["nested_parenthesis"]) === true)
				    {
					$error = _("Possible parse error: interfaces may not include member vars");
					$this->addWarning($error, $stackPtr, "Internal.ParseError.InterfaceHasMemberVar");
					$fault = true;
				    }
			    }
			else
			    {
				throw new Exception(_("\$stackPtr is not a class member var"), EXCEPTION_STACKPTR_IS_NOT_CLASS_MEMBER_VAR);
			    }
		    }

		if ($fault === false)
		    {
			$valid = array(
				  T_PUBLIC,
				  T_PRIVATE,
				  T_PROTECTED,
				  T_STATIC,
				  T_WHITESPACE,
				  T_COMMENT,
				  T_DOC_COMMENT,
				  T_VARIABLE,
				  T_COMMA,
				 );

			$result = array(
				   "scope"           => "public",
				   "scope_specified" => false,
				   "is_static"       => false,
				  );

			for ($i = ($stackPtr - 1); $i > 0; $i--)
			    {
				if (in_array($this->tokens[$i]["code"], $valid) === false)
				    {
					break;
				    }

				$scopes = array(
					   T_PUBLIC    => "public",
					   T_PRIVATE   => "private",
					   T_PROTECTED => "protected",
					  );

				if (isset($scopes[$this->tokens[$i]["code"]]) === true)
				    {
					$result["scope"]           = $scopes[$this->tokens[$i]["code"]];
					$result["scope_specified"] = true;
				    }

				if ($this->tokens[$i]["code"] === T_STATIC)
				    {
					$result["is_static"] = true;
				    }
			    } //end for
		    }
		else
		    {
			$result = array();
		    } //end if

		return $result;
	    } //end getMemberProperties()


	/**
	 * Returns the visibility and implementation properties of a class.
	 *
	 * The format of the array is:
	 * <code>
	 *   array(
	 *    "is_abstract" => false, // true if the abstract keyword was found.
	 *    "is_final"    => false, // true if the final keyword was found.
	 *   );
	 * </code>
	 *
	 * @param int $stackPtr The position in the stack of the T_CLASS token to acquire the properties for.
	 *
	 * @return array
	 *
	 * @throws Exception If the specified position is not a T_CLASS token.
	 *
	 * @exceptioncode EXCEPTION_STACKPTR_MUST_BE_T_CLASS
	 */

	public function getClassProperties($stackPtr)
	    {
		if ($this->tokens[$stackPtr]["code"] !== T_CLASS)
		    {
			throw new Exception(_("\$stackPtr must be of type T_CLASS"), EXCEPTION_STACKPTR_MUST_BE_T_CLASS);
		    }

		$valid = array(
			  T_FINAL,
			  T_ABSTRACT,
			  T_WHITESPACE,
			  T_COMMENT,
			  T_DOC_COMMENT,
			 );

		$isAbstract = false;
		$isFinal    = false;

		for ($i = ($stackPtr - 1); $i > 0; $i--)
		    {
			if (in_array($this->tokens[$i]["code"], $valid) === false)
			    {
				break;
			    }

			switch ($this->tokens[$i]["code"])
			    {
				case T_ABSTRACT:
					$isAbstract = true;
				    break;
				case T_FINAL:
					$isFinal = true;
				    break;
			    }
		    } //end for

		return array(
			"is_abstract" => $isAbstract,
			"is_final"    => $isFinal,
		       );
	    } //end getClassProperties()


	/**
	 * Determine if the passed token is a reference operator.
	 *
	 * Returns true if the specified token position represents a reference.
	 * Returns false if the token represents a bitwise operator.
	 *
	 * @param int $stackPtr The position of the T_BITWISE_AND token.
	 *
	 * @return bool
	 *
	 * @internalconst T_BITWISE_AND      T_BITWISE_AND token
	 * @internalconst T_CLOSURE          T_CLOSURE token
	 * @internalconst T_OPEN_PARENTHESIS T_OPEN_PARENTHESIS token
	 * @internalconst T_COMMA            T_COMMA token
	 */

	public function isReference($stackPtr)
	    {
		if ($this->tokens[$stackPtr]["code"] !== T_BITWISE_AND)
		    {
			return false;
		    }
		else
		    {
			$tokenBefore = $this->findPrevious(Tokens::$emptyTokens, ($stackPtr - 1), null, true);

			if ($this->tokens[$tokenBefore]["code"] === T_FUNCTION ||
			    $this->tokens[$tokenBefore]["code"] === T_DOUBLE_ARROW ||
			    $this->tokens[$tokenBefore]["code"] === T_AS ||
			    in_array($this->tokens[$tokenBefore]["code"], Tokens::$assignmentTokens) === true)
			    {
				return true;
			    }
			else
			    {
				$result = false;
				if (isset($this->tokens[$stackPtr]["nested_parenthesis"]) === true)
				    {
					$brackets    = $this->tokens[$stackPtr]["nested_parenthesis"];
					$lastBracket = array_pop($brackets);
					if (isset($this->tokens[$lastBracket]["parenthesis_owner"]) === true)
					    {
						$owner = $this->tokens[$this->tokens[$lastBracket]["parenthesis_owner"]];
						if ($owner["code"] === T_FUNCTION || $owner["code"] === T_CLOSURE || $owner["code"] === T_ARRAY)
						    {
							// Inside a function or array declaration, this is a reference.
							$result = true;
						    }
					    }
					else
					    {
						$prev = $this->findPrevious(array(T_WHITESPACE), ($this->tokens[$lastBracket]["parenthesis_opener"] - 1), null, true);

						if ($prev !== false && $this->tokens[$prev]["code"] === T_USE)
						    {
							$result = true;
						    }
					    }
				    } //end if

				if ($result === false)
				    {
					$tokenAfter = $this->findNext(Tokens::$emptyTokens, ($stackPtr + 1), null, true);

					return ($this->tokens[$tokenAfter]["code"] === T_VARIABLE &&
					       ($this->tokens[$tokenBefore]["code"] === T_OPEN_PARENTHESIS || $this->tokens[$tokenBefore]["code"] === T_COMMA));
				    }
				else
				    {
					return true;
				    }
			    } //end if
		    } //end if
	    } //end isReference()


	/**
	 * Returns the content of the tokens from the specified start position in
	 * the token stack for the specified length.
	 *
	 * @param int $start  The position to start from in the token stack.
	 * @param int $length The length of tokens to traverse from the start pos.
	 *
	 * @return string The token contents.
	 */

	public function getTokensAsString($start, $length)
	    {
		$str = "";
		$end = ($start + $length);
		if ($end > $this->numTokens)
		    {
			$end = $this->numTokens;
		    }

		for ($i = $start; $i < $end; $i++)
		    {
			$str .= $this->tokens[$i]["content"];
		    }

		return $str;
	    } //end getTokensAsString()


	/**
	 * Returns the position of the next specified token(s).
	 *
	 * If a value is specified, the next token of the specified type(s)
	 * containing the specified value will be returned.
	 *
	 * Returns false if no token can be found.
	 *
	 * @param int|array $types   The type(s) of tokens to search for.
	 * @param int       $start   The position to start searching from in the
	 *                           token stack.
	 * @param int       $end     The end position to fail if no token is found.
	 *                           if not specified or null, end will default to
	 *                           the start of the token stack.
	 * @param bool      $exclude If true, find the next token that are NOT of
	 *                           the types specified in $types.
	 * @param string    $value   The value that the token(s) must be equal to.
	 *                           If value is omitted, tokens with any value will
	 *                           be returned.
	 * @param bool      $local   If true, tokens outside the current statement
	 *                           will not be checked. IE. checking will stop
	 *                           at the next semi-colon found.
	 *
	 * @return int | bool
	 *
	 * @see findNext()
	 *
	 * @internalconst T_SEMICOLON T_SEMICOLON token
	 */

	public function findPrevious($types, $start, $end = null, $exclude = false, $value = null, $local = false)
	    {
		$types   = (array) $types;
		$exclude = (bool) $exclude;
		$end     = (int) $end;

		$result = false;
		for ($i = $start; $i >= $end; $i--)
		    {
			if (in_array($this->tokens[$i]["code"], $types) !== $exclude)
			    {
				if ($value === null)
				    {
					$result = $i;
					break;
				    }
				else if ($this->tokens[$i]["content"] === $value)
				    {
					$result = $i;
					break;
				    }
			    }

			if ($local === true)
			    {
				if (isset($this->tokens[$i]["scope_opener"]) === true && $i === $this->tokens[$i]["scope_closer"])
				    {
					$i = $this->tokens[$i]["scope_opener"];
				    }
				else if (isset($this->tokens[$i]["bracket_opener"]) === true && $i === $this->tokens[$i]["bracket_closer"])
				    {
					$i = $this->tokens[$i]["bracket_opener"];
				    }
				else if (isset($this->tokens[$i]["parenthesis_opener"]) === true && $i === $this->tokens[$i]["parenthesis_closer"])
				    {
					$i = $this->tokens[$i]["parenthesis_opener"];
				    }
				else if ($this->tokens[$i]["code"] === T_SEMICOLON)
				    {
					break;
				    }
			    }
		    } //end for

		return $result;
	    } //end findPrevious()


	/**
	 * Returns the position of the next specified token(s).
	 *
	 * If a value is specified, the next token of the specified type(s)
	 * containing the specified value will be returned.
	 *
	 * Returns false if no token can be found.
	 *
	 * @param int|array $types   The type(s) of tokens to search for.
	 * @param int       $start   The position to start searching from in the
	 *                           token stack.
	 * @param int       $end     The end position to fail if no token is found.
	 *                           if not specified or null, end will default to
	 *                           the end of the token stack.
	 * @param bool      $exclude If true, find the next token that is NOT of
	 *                           a type specified in $types.
	 * @param string    $value   The value that the token(s) must be equal to.
	 *                           If value is omitted, tokens with any value will
	 *                           be returned.
	 * @param bool      $local   If true, tokens outside the current statement
	 *                           will not be checked. i.e., checking will stop
	 *                           at the next semi-colon found.
	 *
	 * @return int | bool
	 *
	 * @see findPrevious()
	 *
	 * @internalconst T_SEMICOLON T_SEMICOLON token
	 */

	public function findNext($types, $start, $end = null, $exclude = false, $value = null, $local = false)
	    {
		$types   = (array) $types;
		$exclude = (bool) $exclude;

		if ($end === null || $end > $this->numTokens)
		    {
			$end = $this->numTokens;
		    }

		$result = false;
		for ($i = $start; $i < $end; $i++)
		    {
			$found = $exclude;
			foreach ($types as $type)
			    {
				if ($this->tokens[$i]["code"] === $type)
				    {
					$found = ($exclude === false);
					break;
				    }
			    }

			if ($found === true)
			    {
				if ($value === null)
				    {
					$result = $i;
					break;
				    }
				else if ($this->tokens[$i]["content"] === $value)
				    {
					$result = $i;
					break;
				    }
			    }

			if ($local === true && $this->tokens[$i]["code"] === T_SEMICOLON)
			    {
				break;
			    }
		    } //end for

		return $result;
	    } //end findNext()


	/**
	 * Returns the position of the first non-whitespace token in a statement.
	 *
	 * @param int $start The position to start searching from in the token stack.
	 *
	 * @return int
	 *
	 * @internalconst T_COLON            T_COLON token
	 * @internalconst T_COMMA            T_COMMA token
	 * @internalconst T_SEMICOLON        T_SEMICOLON token
	 * @internalconst T_OPEN_SHORT_ARRAY T_OPEN_SHORT_ARRAY token
	 */

	public function findStartOfStatement($start)
	    {
		$endTokens = Tokens::$blockOpeners;

		$endTokens[T_COLON]            = true;
		$endTokens[T_COMMA]            = true;
		$endTokens[T_DOUBLE_ARROW]     = true;
		$endTokens[T_SEMICOLON]        = true;
		$endTokens[T_OPEN_TAG]         = true;
		$endTokens[T_CLOSE_TAG]        = true;
		$endTokens[T_OPEN_SHORT_ARRAY] = true;

		$lastNotEmpty = $start;

		for ($i = $start; $i >= 0; $i--)
		    {
			if (isset($endTokens[$this->tokens[$i]["code"]]) === true)
			    {
				// Found the end of the previous statement.
				return $lastNotEmpty;
			    }

			// Skip nested statements.
			if (isset($this->tokens[$i]["scope_opener"]) === true && $i === $this->tokens[$i]["scope_closer"])
			    {
				$i = $this->tokens[$i]["scope_opener"];
			    }
			else if (isset($this->tokens[$i]["bracket_opener"]) === true && $i === $this->tokens[$i]["bracket_closer"])
			    {
				$i = $this->tokens[$i]["bracket_opener"];
			    }
			else if (isset($this->tokens[$i]["parenthesis_opener"]) === true && $i === $this->tokens[$i]["parenthesis_closer"])
			    {
				$i = $this->tokens[$i]["parenthesis_opener"];
			    }

			if (isset(Tokens::$emptyTokens[$this->tokens[$i]["code"]]) === false)
			    {
				$lastNotEmpty = $i;
			    }
		    } //end for

		return 0;
	    } //end findStartOfStatement()


	/**
	 * Returns the position of the last non-whitespace token in a statement.
	 *
	 * @param int $start The position to start searching from in the token stack.
	 *
	 * @return int
	 *
	 * @internalconst T_COLON                T_COLON token
	 * @internalconst T_COMMA                T_COMMA token
	 * @internalconst T_SEMICOLON            T_SEMICOLON token
	 * @internalconst T_CLOSE_SHORT_ARRAY    T_CLOSE_SHORT_ARRAY token
	 * @internalconst T_CLOSE_PARENTHESIS    T_CLOSE_PARENTHESIS token
	 * @internalconst T_CLOSE_SQUARE_BRACKET T_CLOSE_SQUARE_BRACKET token
	 * @internalconst T_CLOSE_CURLY_BRACKET  T_CLOSE_CURLY_BRACKET token
	 */

	public function findEndOfStatement($start)
	    {
		$endTokens = array(
			      T_COLON                => true,
			      T_COMMA                => true,
			      T_DOUBLE_ARROW         => true,
			      T_SEMICOLON            => true,
			      T_CLOSE_PARENTHESIS    => true,
			      T_CLOSE_SQUARE_BRACKET => true,
			      T_CLOSE_CURLY_BRACKET  => true,
			      T_CLOSE_SHORT_ARRAY    => true,
			      T_OPEN_TAG             => true,
			      T_CLOSE_TAG            => true,
			     );

		$lastNotEmpty = $start;

		for ($i = $start; $i < $this->numTokens; $i++)
		    {
			if ($i !== $start && isset($endTokens[$this->tokens[$i]["code"]]) === true)
			    {
				// Found the end of the statement.
				if ($this->tokens[$i]["code"] === T_CLOSE_PARENTHESIS ||
				    $this->tokens[$i]["code"] === T_CLOSE_SQUARE_BRACKET ||
				    $this->tokens[$i]["code"] === T_CLOSE_CURLY_BRACKET ||
				    $this->tokens[$i]["code"] === T_OPEN_TAG ||
				    $this->tokens[$i]["code"] === T_CLOSE_TAG)
				    {
					return $lastNotEmpty;
				    }

				return $i;
			    }

			// Skip nested statements.
			if (isset($this->tokens[$i]["scope_closer"]) === true && $i === $this->tokens[$i]["scope_opener"])
			    {
				$i = $this->tokens[$i]["scope_closer"];
			    }
			else if (isset($this->tokens[$i]["bracket_closer"]) === true && $i === $this->tokens[$i]["bracket_opener"])
			    {
				$i = $this->tokens[$i]["bracket_closer"];
			    }
			else if (isset($this->tokens[$i]["parenthesis_closer"]) === true && $i === $this->tokens[$i]["parenthesis_opener"])
			    {
				$i = $this->tokens[$i]["parenthesis_closer"];
			    }

			if (isset(Tokens::$emptyTokens[$this->tokens[$i]["code"]]) === false)
			    {
				$lastNotEmpty = $i;
			    }
		    } //end for

		return ($this->numTokens - 1);
	    } //end findEndOfStatement()


	/**
	 * Returns the position of the first token on a line, matching given type.
	 *
	 * Returns false if no token can be found.
	 *
	 * @param int|array $types   The type(s) of tokens to search for.
	 * @param int       $start   The position to start searching from in the
	 *                           token stack. The first token matching on
	 *                           this line before this token will be returned.
	 * @param bool      $exclude If true, find the token that is NOT of
	 *                           the types specified in $types.
	 * @param string    $value   The value that the token must be equal to.
	 *                           If value is omitted, tokens with any value will
	 *                           be returned.
	 *
	 * @return int | bool
	 */

	public function findFirstOnLine($types, $start, $exclude = false, $value = null)
	    {
		$types   = (array) $types;
		$exclude = (bool) $exclude;

		$foundToken = false;

		for ($i = $start; $i >= 0; $i--)
		    {
			if ($this->tokens[$i]["line"] < $this->tokens[$start]["line"])
			    {
				break;
			    }

			$found = $exclude;
			foreach ($types as $type)
			    {
				if ($this->tokens[$i]["code"] === $type)
				    {
					$found = ($exclude === false);
					break;
				    }
			    }

			if ($found === true)
			    {
				if ($value === null)
				    {
					$foundToken = $i;
				    }
				else if ($this->tokens[$i]["content"] === $value)
				    {
					$foundToken = $i;
				    }
			    }
		    } //end for

		return $foundToken;
	    } //end findFirstOnLine()


	/**
	 * Determine if the passed token has a condition of one of the passed types.
	 *
	 * @param int       $stackPtr The position of the token we are checking.
	 * @param int|array $types    The type(s) of tokens to search for.
	 *
	 * @return bool
	 */

	public function hasCondition($stackPtr, $types)
	    {
		// Check for the existence of the token. Make sure the token has conditions.
		if (isset($this->tokens[$stackPtr]) === false || isset($this->tokens[$stackPtr]["conditions"]) === false)
		    {
			return false;
		    }
		else
		    {
			$types      = (array) $types;
			$conditions = $this->tokens[$stackPtr]["conditions"];

			$result = false;
			foreach ($types as $type)
			    {
				if (in_array($type, $conditions) === true)
				    {
					// We found a token with the required type.
					$result = true;
					break;
				    }
			    }

			return $result;
		    } //end if
	    } //end hasCondition()


	/**
	 * Return the position of the condition for the passed token.
	 *
	 * Returns FALSE if the token does not have the condition.
	 *
	 * @param int $stackPtr The position of the token we are checking.
	 * @param int $type     The type of token to search for.
	 *
	 * @return int
	 */

	public function getCondition($stackPtr, $type)
	    {
		// Check for the existence of the token. Make sure the token has conditions.
		if (isset($this->tokens[$stackPtr]) === false || isset($this->tokens[$stackPtr]["conditions"]) === false)
		    {
			return false;
		    }
		else
		    {
			$result     = false;
			$conditions = $this->tokens[$stackPtr]["conditions"];
			foreach ($conditions as $token => $condition)
			    {
				if ($condition === $type)
				    {
					$result = $token;
					break;
				    }
			    }

			return $result;
		    }
	    } //end getCondition()


	/**
	 * Returns the name of the class that the specified class extends.
	 *
	 * Returns FALSE on error or if there is no extended class name.
	 *
	 * @param int $stackPtr The stack position of the class.
	 *
	 * @return string
	 */

	public function findExtendedClassName($stackPtr)
	    {
		// Check for the existence of the token.
		if (isset($this->tokens[$stackPtr]) === false || $this->tokens[$stackPtr]["code"] !== T_CLASS || isset($this->tokens[$stackPtr]["scope_closer"]) === false)
		    {
			return false;
		    }
		else
		    {
			$classCloserIndex = $this->tokens[$stackPtr]["scope_closer"];
			$extendsIndex     = $this->findNext(T_EXTENDS, $stackPtr, $classCloserIndex);
			if ($extendsIndex === false)
			    {
				return false;
			    }
			else
			    {
				$find = array(
					 T_NS_SEPARATOR,
					 T_STRING,
					 T_WHITESPACE,
					);

				$end  = $this->findNext($find, ($extendsIndex + 1), $classCloserIndex, true);
				$name = $this->getTokensAsString(($extendsIndex + 1), ($end - $extendsIndex - 1));
				$name = trim($name);

				if ($name === "")
				    {
					return false;
				    }
				else
				    {
					return $name;
				    }
			    } //end if
		    } //end if
	    } //end findExtendedClassName()


    } //end class

?>
