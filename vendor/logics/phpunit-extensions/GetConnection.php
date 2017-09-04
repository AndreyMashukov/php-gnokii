<?php

/**
 * PHP version 5.6
 *
 * @package Logics\Tests
 */

namespace Logics\Tests;

/**
 * GetConnection trait
 *
 * Provides database connection to be used in tests
 *
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://opensource.org/licenses/MIT MIT License
 * @version   SVN: $Date: 2016-08-17 22:45:36 +0900 (Wed, 17 Aug 2016) $ $Revision: 232 $
 * @link      $HeadURL: https://open.logics.net.au/phpunit-extensions/tags/0.2.5/GetConnection.php $
 *
 * @codeCoverageIgnore
 */

trait GetConnection
    {

	/**
	 * Only instantiate sql once for test clean-up/fixture load.
	 *
	 * @var SQLdatabase
	 */
	static private $_sql = null;

	/**
	 * Only instantiate PHPUnit_Extensions_Database_DB_IDatabaseConnection once per test.
	 *
	 * @var PHPUnit_Extensions_Database_DB_SQL_DefaultDatabaseConnection
	 */
	private $_conn = null;

	/**
	 * Get database connection
	 *
	 * @return connection
	 */

	final public function getConnection()
	    {
		if ($this->_conn === null)
		    {
			if (self::$_sql === null)
			    {
				self::$_sql = $this->getSQLconnection();
			    }

			$this->_conn = $this->createDefaultDBConnection(self::$_sql, $GLOBALS["DB_DBNAME"]);
		    }

		return $this->_conn;
	    } //end getConnection()


    } //end trait

?>
