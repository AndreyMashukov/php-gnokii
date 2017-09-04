<?php

/**
 * PHP version 5.6
 *
 * @package Logics\Tests
 */

namespace Logics\Tests;

use \PHPUnit_Extensions_Database_Operation_Composite;
use \PHPUnit_Extensions_Database_Operation_Factory;

/**
 * GetSetUpOperation trait
 *
 * Provides getSetUpOperation() for tests
 *
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://opensource.org/licenses/MIT MIT License
 * @version   SVN: $Date: 2016-08-17 22:45:36 +0900 (Wed, 17 Aug 2016) $ $Revision: 232 $
 * @link      $HeadURL: https://open.logics.net.au/phpunit-extensions/tags/0.2.5/GetSetUpOperation.php $
 *
 * @codeCoverageIgnore
 */

trait GetSetUpOperation
    {

	/**
	 * Get setUp operation
	 *
	 * @return PHPUnit_Extensions_Database_Operation_Composite
	 */

	public function getSetUpOperation()
	    {
		// If you want cascading truncates, false otherwise. If unsure choose false.
		$cascadeTruncates = true;

		$truncateOperation = new TruncateOperation($cascadeTruncates);
		return new PHPUnit_Extensions_Database_Operation_Composite(
		    array(
		     $truncateOperation,
		     PHPUnit_Extensions_Database_Operation_Factory::INSERT(),
		    )
		);
	    } //end getSetUpOperation()


    } //end trait

?>
