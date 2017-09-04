<?php

/**
 * PHP version 5.6
 *
 * @package Logics\Tests
 */

namespace Logics\Tests;

/**
 * Trait contain common methods for interface
 *
 * @author    Vladimir Skorov <voroks@logics.net.au>
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://opensource.org/licenses/MIT MIT License
 * @version   SVN: $Date: 2016-08-17 22:45:36 +0900 (Wed, 17 Aug 2016) $ $Revision: 232 $
 * @link      $HeadURL: https://open.logics.net.au/phpunit-extensions/tags/0.2.5/environment/DefaultMedium.php $
 */

trait DefaultMedium
    {

	/**
	 * Return array with values to redifine phpunit.xml variables
	 *
	 * @return array
	 */

	public function getInjectionVariables()
	    {
		return $this->_injections;
	    } //end getInjectionVariables()


	/**
	 * Review internal messages because of phpunit.xml variables replacemet fails
	 *
	 * @return void
	 */

	public function injectionFailed()
	    {
		$this->_injectionFailed = true;
	    } //end injectionFailed()


    } //end trait

?>
