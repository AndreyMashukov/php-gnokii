<?php

/**
 * PHP version 5.6
 *
 * @package Logics\Tests
 */

namespace Logics\Tests;

/**
 * Interface require method to get redefining variables
 *
 * @author    Vladimir Skorov <voroks@logics.net.au>
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://opensource.org/licenses/MIT MIT License
 * @version   SVN: $Date: 2016-08-17 22:45:36 +0900 (Wed, 17 Aug 2016) $ $Revision: 232 $
 * @link      $HeadURL: https://open.logics.net.au/phpunit-extensions/tags/0.2.5/environment/InterfaceMedium.php $
 */

interface InterfaceMedium
    {

	/**
	 * Returns array to redefine variables from phpunit.xml
	 *
	 * @return mixed
	 */

	public function getInjectionVariables();


    } //end interface

?>
