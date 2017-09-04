<?php

/**
 * PHP version 5.6
 *
 * @package Logics\Tests
 */

namespace Logics\Tests;

use \ReflectionClass;

/**
 * InternalWebServer trait
 *
 * Starts up internal PHP web server for testing purposes. Shuts it down on tests completion.
 *
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://opensource.org/licenses/MIT MIT License
 * @version   SVN: $Date: 2016-10-01 21:42:56 +0800 (Sat, 01 Oct 2016) $ $Revision: 261 $
 * @link      $HeadURL: https://open.logics.net.au/phpunit-extensions/tags/0.2.5/InternalWebServer.php $
 *
 * @codeCoverageIgnore
 *
 * @donottranslate
 */

trait InternalWebServer
    {

	/**
	 * Web server host
	 *
	 * @var string
	 */
	static protected $webserverhost = "localhost";

	/**
	 * Web server port
	 *
	 * @var int
	 */
	static protected $webserverport = 6767;

	/**
	 * Web server PID
	 *
	 * @var mixed
	 */
	static protected $webserverpid = false;

	/**
	 * Webserver URL
	 *
	 * @return string Webserver URL
	 */

	public static function webserverURL()
	    {
		return "http://" . self::$webserverhost . ":" . self::$webserverport;
	    } //end webserverURL()


	/**
	 * Boot up built-in server
	 *
	 * @return void
	 *
	 * @beforeClass
	 *
	 * @throws Exception Internal webserver failed to start
	 */

	public static function bootUpBuiltInServer()
	    {
		if (self::$webserverpid === false)
		    {
			set_error_handler(
				function ()
				    {
					return true;
				    }
			);
			do
			    {
				self::$webserverport = rand(10000, 30000);
			    } while (fsockopen(self::$webserverhost, self::$webserverport) !== false);

			restore_error_handler();

			$class  = new ReflectionClass(__CLASS__);
			$root   = dirname($class->getFileName());
			$router = (file_exists($root . "/router.php") === true) ? " " . $root . "/router.php" : "";
			$log    = $root . "/webserver.log";
			$pid    = shell_exec("php -S " . self::$webserverhost . ":" . self::$webserverport . " -t " . $root . $router . " >> " . $log . " 2>&1 & echo $!");

			self::$webserverpid = trim($pid);

			set_error_handler(
				function ()
				    {
					return true;
				    }
			);
			$time = 0;
			while (fsockopen(self::$webserverhost, self::$webserverport) === false)
			    {
				if ($time >= 60)
				    {
					throw new Exception("Internal webserver failed to start in 60 seconds", 0);
				    }

				$time++;
				sleep(1);
			    }

			restore_error_handler();
		    } //end if
	    } //end bootUpBuiltInServer()


	/**
	 * Turn down built-in server
	 *
	 * @return void
	 *
	 * @afterClass
	 */

	public static function turnDownBuiltInServer()
	    {
		if (self::$webserverpid !== false)
		    {
			posix_kill(self::$webserverpid, 9);
		    }
	    } //end turnDownBuiltInServer()


    } //end trait

?>