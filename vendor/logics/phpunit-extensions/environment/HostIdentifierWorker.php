<?php

/**
 * PHP version 5.6
 *
 * @package Logics\Tests
 */

namespace Logics\Tests;

/**
 * Class identifies host
 *
 * @author    Vladimir Skorov <voroks@logics.net.au>
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://opensource.org/licenses/MIT MIT License
 * @version   SVN: $Date: 2016-08-17 22:45:36 +0900 (Wed, 17 Aug 2016) $ $Revision: 232 $
 * @link      $HeadURL: https://open.logics.net.au/phpunit-extensions/tags/0.2.5/environment/HostIdentifierWorker.php $
 *
 * @donottranslate
 */

class HostIdentifierWorker
    {

	const HOST_NAME = "dev.logics.net.au";

	/**
	 * Get host name and if it equals to needed host name return true
	 *
	 * @return bool
	 */

	static public function needSetUpEnvironment()
	    {
		if (gethostname() === self::HOST_NAME)
		    {
			return true;
		    }
		else
		    {
			return false;
		    }
	    } //end needSetUpEnvironment()


	/**
	 * Display header
	 *
	 * @return void
	 */

	static public function displayHeader()
	    {
		echo "Information about your PHPUnit environment.\n";
	    } //end displayHeader()


	/**
	 * Display footer
	 *
	 * @return void
	 */

	static public function displayFooter()
	    {
		echo "\n";
	    } //end displayFooter()


    } //end class

?>
