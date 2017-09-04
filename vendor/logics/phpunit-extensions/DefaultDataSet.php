<?php

/**
 * PHP version 5.6
 *
 * @package Logics\Tests
 */

namespace Logics\Tests;

use \PHPUnit_Extensions_Database_DataSet_DefaultDataSet;

/**
 * DefaultDataSet trait
 *
 * DefaultDataSet trait should be used in tests which do not have any test data
 *
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://opensource.org/licenses/MIT MIT License
 * @version   SVN: $Date: 2016-08-17 22:45:36 +0900 (Wed, 17 Aug 2016) $ $Revision: 232 $
 * @link      $HeadURL: https://open.logics.net.au/phpunit-extensions/tags/0.2.5/DefaultDataSet.php $
 *
 * @codeCoverageIgnore
 */

trait DefaultDataSet
    {

	/**
	 * Get test data set
	 *
	 * @return PHPUnit_Extensions_Database_DataSet_AbstractDataSet
	 */

	public function getDataSet()
	    {
		return new PHPUnit_Extensions_Database_DataSet_DefaultDataSet();
	    } //end getDataSet()


    } //end trait

?>
