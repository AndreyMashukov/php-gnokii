<?php

/**
 * PHP version 5.6
 *
 * @package Logics\Tests
 */

namespace Logics\Tests;

/**
 * ErrorHandler trait. Used to supress certain errors during testing
 *
 * Makes suppression of errors during testing possible.
 *
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://opensource.org/licenses/MIT MIT License
 * @version   SVN: $Date: 2016-11-02 15:13:16 +0800 (Wed, 02 Nov 2016) $ $Revision: 265 $
 * @link      $HeadURL: https://open.logics.net.au/phpunit-extensions/tags/0.2.5/ErrorHandler.php $
 *
 * @codeCoverageIgnore
 *
 * @donottranslate
 */

trait ErrorHandler
    {

	/**
	 * Ignore error
	 *
	 * @var string
	 */
	private $_ignoreerror;

	/**
	 * Old error handler
	 *
	 * @var mixed
	 */
	private $_olderrorhandler;

	/**
	 * Set error handler to our own
	 *
	 * @return void
	 */

	public function setErrorHandler()
	    {
		$this->_olderrorhandler = set_error_handler(array($this, "errorHandler"));
	    } //end setErrorHandler()


	/**
	 * Restore original error handler
	 *
	 * @return void
	 */

	public function restoreErrorHandler()
	    {
		restore_error_handler();
	    } //end restoreErrorHandler()


	/**
	 * Handler for PHP errors: we just need to suppress errors matching $this->_ignoreerror
	 *
	 * @param int    $errno      Contains the level of the error raised
	 * @param string $errstr     Contains the error message
	 * @param string $errfile    Contains the filename that the error was raised in
	 * @param int    $errline    Contains the line number the error was raised at
	 * @param array  $errcontext An array that points to the active symbol table at the point the error occurred
	 *
	 * @return boolean true if script should continue execution
	 */

	public function errorHandler($errno, $errstr, $errfile, $errline, array $errcontext)
	    {
		if (preg_match("/" . $this->_ignoreerror . "/", $errstr) > 0)
		    {
			$GLOBALS["errno"]      = $errno;
			$GLOBALS["errstr"]     = $errstr;
			$GLOBALS["errfile"]    = $errfile;
			$GLOBALS["errline"]    = $errline;
			$GLOBALS["errcontext"] = $errcontext;
			return true;
		    }
		else
		    {
			call_user_func_array($this->_olderrorhandler, array($errno, $errstr, $errfile, $errline, $errcontext));
		    }
	    } //end errorHandler()


    } //end trait

?>
