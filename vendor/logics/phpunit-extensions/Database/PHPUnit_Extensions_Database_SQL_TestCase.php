<?php

/**
 * PHPUnit
 *
 * Copyright (c) 2002-2013, Sebastian Bergmann <sebastian@phpunit.de>.
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions
 * are met:
 *
 *   * Redistributions of source code must retain the above copyright
 *     notice, this list of conditions and the following disclaimer.
 *
 *   * Redistributions in binary form must reproduce the above copyright
 *     notice, this list of conditions and the following disclaimer in
 *     the documentation and/or other materials provided with the
 *     distribution.
 *
 *   * Neither the name of Sebastian Bergmann nor the names of his
 *     contributors may be used to endorse or promote products derived
 *     from this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS
 * FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE
 * COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT,
 * INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING,
 * BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT
 * LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN
 * ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 *
 * PHP version 5.6
 *
 * @package   Logics\Tests
 * @author    Mike Lively <m@digitalsandwich.com>
 * @copyright 2002-2013 Sebastian Bergmann <sebastian@phpunit.de>
 * @license   http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 */

namespace Logics\Tests;

use \Exception;
use \Logics\Foundation\SQL\SQLdatabase;
use \PHPUnit_Extensions_Database_Constraint_DataSetIsEqual;
use \PHPUnit_Extensions_Database_Constraint_TableIsEqual;
use \PHPUnit_Extensions_Database_Constraint_TableRowCount;
use \PHPUnit_Extensions_Database_DB_DataSet;
use \PHPUnit_Extensions_Database_DB_IDatabaseConnection;
use \PHPUnit_Extensions_Database_DataSet_IDataSet;
use \PHPUnit_Extensions_Database_DataSet_ITable;
use \PHPUnit_Extensions_Database_DefaultTester;
use \PHPUnit_Extensions_Database_Operation_Factory;
use \PHPUnit_Framework_TestCase;

/**
 * A TestCase extension that provides functionality for testing and asserting
 * against a real database.
 *
 * @author    Anastasia Bashkirtseva <anastasia@logics.net.au>
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://opensource.org/licenses/MIT MIT License
 * @version   SVN: $Date: 2016-08-17 22:45:36 +0900 (Wed, 17 Aug 2016) $ $Revision: 232 $
 * @link      $HeadURL: https://open.logics.net.au/phpunit-extensions/tags/0.2.5/Database/PHPUnit_Extensions_Database_SQL_TestCase.php $
 */

abstract class PHPUnit_Extensions_Database_SQL_TestCase extends PHPUnit_Framework_TestCase
    {

	use CreateXMLDataSet;

	/**
	 * Database tester
	 *
	 * @var PHPUnit_Extensions_Database_ITester
	 */
	protected $databaseTester;

	/**
	 * Flag of set up
	 *
	 * @var bool
	 */
	private $_setup;

	/**
	 * Instance of SQLdatabase
	 *
	 * @var SQLdatabase
	 */
	private static $_db;

	/**
	 * Construct the class
	 *
	 * @param string $name     Name
	 * @param array  $data     Data
	 * @param string $dataName Data name
	 *
	 * @return void
	 */

	public function __construct($name = null, array $data = array(), $dataName = "")
	    {
		parent::__construct($name, $data, $dataName);

		$this->_setup = false;
	    } //end __construct()


	/**
	 * Closes the specified connection.
	 *
	 * @param PHPUnit_Extensions_Database_DB_IDatabaseConnection $connection Database
	 *
	 * @return void
	 */

	protected function closeConnection(PHPUnit_Extensions_Database_DB_IDatabaseConnection $connection)
	    {
		$this->getDatabaseTester()->closeConnection($connection);
	    } //end closeConnection()


	/**
	 * Returns the test database connection.
	 *
	 * @return PHPUnit_Extensions_Database_DB_IDatabaseConnection
	 */

	protected abstract function getConnection();


	/**
	 * Gets the IDatabaseTester for this testCase. If the IDatabaseTester is
	 * not set yet, this method calls newDatabaseTester() to obtain a new
	 * instance.
	 *
	 * @return PHPUnit_Extensions_Database_ITester
	 */

	protected function getDatabaseTester()
	    {
		if (empty($this->databaseTester) === true)
		    {
			$this->databaseTester = $this->newDatabaseTester();
		    }

		return $this->databaseTester;
	    } //end getDatabaseTester()


	/**
	 * Returns the test dataset.
	 *
	 * @return PHPUnit_Extensions_Database_DataSet_AbstractDataSet
	 */

	protected abstract function getDataSet();


	/**
	 * Returns the database operation executed in test setup.
	 *
	 * @return PHPUnit_Extensions_Database_Operation_DatabaseOperation
	 */

	protected function getSetUpOperation()
	    {
		return PHPUnit_Extensions_Database_Operation_Factory::CLEAN_INSERT();
	    } //end getSetUpOperation()


	/**
	 * Returns the database operation executed in test cleanup.
	 *
	 * @return PHPUnit_Extensions_Database_Operation_DatabaseOperation
	 */

	protected function getTearDownOperation()
	    {
		return PHPUnit_Extensions_Database_Operation_Factory::NONE();
	    } //end getTearDownOperation()


	/**
	 * Creates a IDatabaseTester for this testCase.
	 *
	 * @return PHPUnit_Extensions_Database_ITester
	 */

	protected function newDatabaseTester()
	    {
		return new PHPUnit_Extensions_Database_DefaultTester($this->getConnection());
	    } //end newDatabaseTester()


	/**
	 * Creates a new DefaultDatabaseConnection using the given SQLdatabase connection
	 * and database schema name.
	 *
	 * @param SQLdatabase $connection Database
	 * @param string      $schema     Schema database
	 *
	 * @return PHPUnit_Extensions_Database_DB_DefaultDatabaseConnection
	 */

	protected function createDefaultDBConnection(SQLdatabase $connection, $schema = "")
	    {
		return new PHPUnit_Extensions_Database_DB_SQL_DefaultDatabaseConnection($connection, $schema);
	    } //end createDefaultDBConnection()


	/**
	 * Returns an operation factory instance that can be used to instantiate
	 * new operations.
	 *
	 * @return PHPUnit_Extensions_Database_Operation_Factory
	 */

	protected function getOperations()
	    {
		return new PHPUnit_Extensions_Database_Operation_Factory();
	    } //end getOperations()


	/**
	 * Performs operation returned by getSetUpOperation().
	 *
	 * @return void
	 */

	protected function setUp()
	    {
		parent::setUp();

		$this->databaseTester = null;

		$this->getDatabaseTester()->setSetUpOperation($this->getSetUpOperation());
		$this->getDatabaseTester()->setDataSet($this->getDataSet());
		$this->getDatabaseTester()->onSetUp();

		$this->_setup = true;

		self::$_db = $this->getConnection();
	    } //end setUp()


	/**
	 * Performs assertions shared by all tests of a test case.
	 *
	 * This method is called before the execution of a test starts
	 * and after setUp() is called.
	 *
	 * @since Method available since Release 3.2.8
	 *
	 * @return void
	 */

	protected function assertPreConditions()
	    {
		if ($this->_setup === false)
		    {
			$this->fail(_("parent::setUp() was not called in overloaded method"));
		    }
	    } //end assertPreConditions()


	/**
	 * Performs operation returned by getSetUpOperation().
	 *
	 * @return void
	 */

	protected function tearDown()
	    {
		$this->getDatabaseTester()->setTearDownOperation($this->getTearDownOperation());
		$this->getDatabaseTester()->setDataSet($this->getDataSet());
		$this->getDatabaseTester()->onTearDown();

		$this->databaseTester = null;
	    } //end tearDown()


	/**
	 * Tear down after class: make sure no tables are left behind
	 *
	 * @return void
	 *
	 * @throws Exception In case if we have messy database
	 */

	public static function tearDownAfterClass()
	    {
		if (self::$_db !== null)
		    {
			$dataset  = new PHPUnit_Extensions_Database_DB_DataSet(self::$_db);
			$leftover = implode(", ", $dataset->getTableNames());
			if ($leftover !== "")
			    {
				throw new Exception(_("Clean up your mess! Tables left behind after testing: ") . $leftover, 0);
			    }
		    }
	    } //end tearDownAfterClass()


	/**
	 * Asserts that two given tables are equal.
	 *
	 * @param PHPUnit_Extensions_Database_DataSet_ITable $expected Data set database
	 * @param PHPUnit_Extensions_Database_DataSet_ITable $actual   Data set database
	 * @param string                                     $message  Message for equal
	 *
	 * @return void
	 */

	public static function assertTablesEqual(PHPUnit_Extensions_Database_DataSet_ITable $expected, PHPUnit_Extensions_Database_DataSet_ITable $actual, $message = "")
	    {
		$constraint = new PHPUnit_Extensions_Database_Constraint_TableIsEqual($expected);

		self::assertThat($actual, $constraint, $message);
	    } //end assertTablesEqual()


	/**
	 * Asserts that two given datasets are equal.
	 *
	 * @param PHPUnit_Extensions_Database_DataSet_IDataSet $expected Data set database
	 * @param PHPUnit_Extensions_Database_DataSet_IDataSet $actual   Data set database
	 * @param string                                       $message  Message of equal
	 *
	 * @return void
	 */

	public static function assertDataSetsEqual(PHPUnit_Extensions_Database_DataSet_IDataSet $expected, PHPUnit_Extensions_Database_DataSet_IDataSet $actual, $message = "")
	    {
		$constraint = new PHPUnit_Extensions_Database_Constraint_DataSetIsEqual($expected);

		self::assertThat($actual, $constraint, $message);
	    } //end assertDataSetsEqual()


	/**
	 * Assert that a given table has a given amount of rows
	 *
	 * @param string $tableName Name of the table
	 * @param int    $expected  Expected amount of rows in the table
	 * @param string $message   Optional message
	 *
	 * @return void
	 */

	public function assertTableRowCount($tableName, $expected, $message = "")
	    {
		$constraint = new PHPUnit_Extensions_Database_Constraint_TableRowCount($tableName, $expected);
		$actual     = $this->getConnection()->getRowCount($tableName);

		self::assertThat($actual, $constraint, $message);
	    } //end assertTableRowCount()


	/**
	 * Asserts that a given table contains a given row
	 *
	 * @param array                                      $expectedRow Row expected to find
	 * @param PHPUnit_Extensions_Database_DataSet_ITable $table       Table to look into
	 * @param string                                     $message     Optional message
	 *
	 * @return void
	 */

	public function assertTableContains(array $expectedRow, PHPUnit_Extensions_Database_DataSet_ITable $table, $message = "")
	    {
		self::assertThat($table->assertContainsRow($expectedRow), self::isTrue(), $message);
	    } //end assertTableContains()


    } //end class

?>