<?php

/**
 * PHP version 5.6
 *
 * @package Logics\Tests
 */

namespace Logics\Tests;

use \Logics\Tests\InterfaceMedium;

/**
 * Prepare and return values to redefine variables from phpunit.xml
 *
 * @author    Vladimir Skorov <voroks@logics.net.au>
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://opensource.org/licenses/MIT MIT License
 * @version   SVN: $Date: 2017-01-21 00:10:14 +0800 (Sat, 21 Jan 2017) $ $Revision: 273 $
 * @link      $HeadURL: https://open.logics.net.au/phpunit-extensions/tags/0.2.5/environment/ConfigurationMedium.php $
 *
 * @donottranslate
 */

class ConfigurationMedium
    {

	/**
	 * Injections
	 *
	 * @var array
	 */
	static private $_injections = false;

	/**
	 * Prepare values to redefine phpunit.xml variables
	 *
	 * @param array $objects Some checkers which can provide values to redefining
	 *
	 * @return array
	 */

	static private function _prepareValues(array $objects)
	    {
		$injections = array();
		foreach ($objects as $object)
		    {
			if (in_array(InterfaceMedium::CLASS, class_implements($object)) === false)
			    {
				$injections = false;
				break;
			    }

			$injectionVariables = $object->getInjectionVariables();
			if (is_array($injectionVariables) === true)
			    {
				foreach ($injectionVariables as $key => $value)
				    {
					if ((is_string($key) === true) && (is_string($value) === true))
					    {
						if (((isset($injections[$key]) === true) && ($injections[$key] === $value)) || (isset($injections[$key]) === false))
						    {
							$injections[$key] = $value;
						    }
						else
						    {
							$injections = false;
							break 1;
						    }
					    }
				    }
			    }
		    } //end foreach

		return $injections;
	    } //end _prepareValues()


	/**
	 * Prepare values to redefine phpunit.xml variables via modifying PHPunit method
	 *
	 * @param array $objects Some checkers which can provide values to redefining
	 *
	 * @return void
	 */

	static public function makeInjection(array $objects)
	    {
		$injections = self::_prepareValues($objects);

		if ($injections !== false)
		    {
			self::$_injections = $injections;
		    }

		runkit_method_redefine(
		    "PHPUnit_Util_Configuration",
		    "getPHPConfiguration",
		    "",
		    file_get_contents(__DIR__ . "/getPHPConfiguration")
		);
	    } //end makeInjection()


	/**
	 * Prepare values to redefine phpunit.xml variables directly in $GLOBALS
	 *
	 * @param array $objects Some checkers which can provide values to redefining
	 *
	 * @return void
	 */

	static public function redefineVariables(array $objects)
	    {
		$injections = self::_prepareValues($objects);

		if ($injections !== false)
		    {
			foreach ($injections as $key => $value)
			    {
				$GLOBALS[$key] = $value;
			    }
		    }
	    } //end redefineVariables()


	/**
	 * Return array to redefine variables from phpunit.xml
	 * Used by redefined in runkit method
	 *
	 * @return array
	 */

	static public function getInjections()
	    {
		if (self::$_injections === false)
		    {
			return array();
		    }
		else
		    {
			return self::$_injections;
		    }
	    } //end getInjections()


    } //end class

?>
