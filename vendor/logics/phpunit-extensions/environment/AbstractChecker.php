<?php

/**
 * PHP version 5.6
 *
 * @package Logics\Tests
 */

namespace Logics\Tests;

/**
 * Class implements useful methods and require
 * method to settin up different parameters.
 * Caches some values for all of the children
 *
 * @author    Vladimir Skorov <voroks@logics.net.au>
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://opensource.org/licenses/MIT MIT License
 * @version   SVN: $Date: 2016-08-17 22:45:36 +0900 (Wed, 17 Aug 2016) $ $Revision: 232 $
 * @link      $HeadURL: https://open.logics.net.au/phpunit-extensions/tags/0.2.5/environment/AbstractChecker.php $
 */

abstract class AbstractChecker
    {

	/**
	 * User ID
	 *
	 * @var string
	 */
	static private $_userID;

	/**
	 * User name
	 *
	 * @var string
	 */
	static private $_userName;

	/**
	 * Requested user name
	 *
	 * @var string
	 */
	static private $_requestedUserName;

	/**
	 * Initialize private variables
	 *
	 * @return void
	 */

	public function __construct()
	    {
		self::$_userID   = false;
		self::$_userName = false;

		self::$_requestedUserName = false;
	    } //end __construct()


	/**
	 * Check $_GET parameters to existence expected system user name (while under apache running)
	 *
	 * @return string
	 */

	public function getRequestedUserName()
	    {
		if (self::$_requestedUserName === false)
		    {
			if (isset($_SERVER["REQUEST_URI"]) === true)
			    {
				$components = explode("/", $_SERVER["REQUEST_URI"], 3);
				if ((count($components) === 3) && ($components[0] === ""))
				    {
					self::$_requestedUserName = $components[1];
				    }
			    }
		    }

		return self::$_requestedUserName;
	    } //end getRequestedUserName()


	/**
	 * Obtain, cache and then return sustem user name
	 *
	 * @return string or false
	 */

	protected function getUserName()
	    {
		if (self::$_userName === false)
		    {
			$attribute = posix_getpwuid($this->getUserID());
			if ($attribute !== false)
			    {
				self::$_userName = $attribute["name"];
			    }
		    }

		return self::$_userName;
	    } //end getUserName()


	/**
	 * Obtain, cache and then return system user ID
	 *
	 * @return int
	 */

	protected function getUserID()
	    {
		if (self::$_userID === false)
		    {
			self::$_userID = posix_geteuid();
		    }

		return self::$_userID;
	    } //end getUserID()


	/**
	 * Setting up different PHPUnit parameters
	 *
	 * @return void
	 */

	abstract public function setUp();


    } //end class

?>