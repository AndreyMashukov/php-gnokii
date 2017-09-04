<?php

/**
 * PHP version 5.6
 *
 * @package Logics\Tests
 */

namespace Logics\Tests;

use \PHPUnit_Extensions_Database_DB_IDatabaseConnection;
use \PHPUnit_Extensions_Database_DataSet_IDataSet;
use \PHPUnit_Extensions_Database_Operation_Truncate;

/**
 * TruncateOperation class. Disables foreign key checks temporarily.
 *
 * Truncate operation does not succeed if foreign key are on in MySQL. This class allows to force truncate on tables with foreign keys constraints.
 *
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://opensource.org/licenses/MIT MIT License
 * @version   SVN: $Date: 2016-08-17 22:45:36 +0900 (Wed, 17 Aug 2016) $ $Revision: 232 $
 * @link      $HeadURL: https://open.logics.net.au/phpunit-extensions/tags/0.2.5/TruncateOperation.php $
 *
 * @codeCoverageIgnore
 *
 * @donottranslate
 */

class TruncateOperation extends PHPUnit_Extensions_Database_Operation_Truncate
    {

	/**
	 * Execute truncate operation ignoring foreign keys constraints
	 *
	 * @param PHPUnit_Extensions_Database_DB_IDatabaseConnection $connection Database connection
	 * @param PHPUnit_Extensions_Database_DataSet_IDataSet       $dataSet    Dataset to load
	 *
	 * @return void
	 */

	public function execute(PHPUnit_Extensions_Database_DB_IDatabaseConnection $connection, PHPUnit_Extensions_Database_DataSet_IDataSet $dataSet)
	    {
		$connection->getConnection()->exec("SET foreign_key_checks = 0");
		parent::execute($connection, $dataSet);
		$connection->getConnection()->exec("SET foreign_key_checks = 1");
	    } //end execute()


    } //end class

?>
