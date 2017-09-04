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

use \PHPUnit_Extensions_Database_DB_IDatabaseConnection;
use \PHPUnit_Extensions_Database_DataSet_AbstractTable;
use \PHPUnit_Extensions_Database_DataSet_DefaultTableMetaData;
use \PHPUnit_Extensions_Database_DataSet_ITable;

/**
 * Provides the functionality to represent a database table.
 *
 * @author    Anastasia Bashkirtseva <anastasia@logics.net.au>
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://opensource.org/licenses/MIT MIT License
 * @version   SVN: $Date: 2016-08-17 22:45:36 +0900 (Wed, 17 Aug 2016) $ $Revision: 232 $
 * @link      $HeadURL: https://open.logics.net.au/phpunit-extensions/tags/0.2.5/Database/PHPUnit_Extensions_Database_DataSet_SQL_QueryTable.php $
 */

class PHPUnit_Extensions_Database_DataSet_SQL_QueryTable extends PHPUnit_Extensions_Database_DataSet_AbstractTable
    {

	/**
	 * Statement
	 *
	 * @var string
	 */
	protected $query;

	/**
	 * Database
	 *
	 * @var PHPUnit_Extensions_Database_DB_IDatabaseConnection
	 */
	protected $databaseConnection;

	/**
	 * Table name
	 *
	 * @var string
	 */
	protected $tableName;

	/**
	 * Creates a new database query table object.
	 *
	 * @param string                                             $tableName          Name of table
	 * @param string                                             $query              Statement
	 * @param PHPUnit_Extensions_Database_DB_IDatabaseConnection $databaseConnection SQLdatabase
	 *
	 * @return void
	 */

	public function __construct($tableName, $query, PHPUnit_Extensions_Database_DB_IDatabaseConnection $databaseConnection)
	    {
		$this->query              = $query;
		$this->databaseConnection = $databaseConnection;
		$this->tableName          = $tableName;
	    } //end __construct()


	/**
	 * Returns the table's meta data.
	 *
	 * @return PHPUnit_Extensions_Database_DataSet_ITableMetaData
	 */

	public function getTableMetaData()
	    {
		$this->createTableMetaData();
		return parent::getTableMetaData();
	    } //end getTableMetaData()


	/**
	 * Checks if a given row is in the table
	 *
	 * @param array $row Array  table rows
	 *
	 * @return bool
	 */

	public function assertContainsRow(array $row)
	    {
		$this->loadData();
		return parent::assertContainsRow($row);
	    } //end assertContainsRow()


	/**
	 * Returns the number of rows in this table.
	 *
	 * @return int
	 */

	public function getRowCount()
	    {
		$this->loadData();
		return parent::getRowCount();
	    } //end getRowCount()


	/**
	 * Returns the value for the given column on the given row.
	 *
	 * @param int $row    Number row
	 * @param int $column Number column
	 *
	 * @return mixed
	 */

	public function getValue($row, $column)
	    {
		$this->loadData();
		return parent::getValue($row, $column);
	    } //end getValue()


	/**
	 * Returns the an associative array keyed by columns for the given row.
	 *
	 * @param int $row Number row
	 *
	 * @return array
	 */

	public function getRow($row)
	    {
		$this->loadData();
		return parent::getRow($row);
	    } //end getRow()


	/**
	 * Asserts that the given table matches this table.
	 *
	 * @param PHPUnit_Extensions_Database_DataSet_ITable $other DataSet matching
	 *
	 * @return bool
	 */

	public function matches(PHPUnit_Extensions_Database_DataSet_ITable $other)
	    {
		$this->loadData();
		return parent::matches($other);
	    } //end matches()


	/**
	 * Loads data
	 *
	 * @return void
	 */

	protected function loadData()
	    {
		if ($this->data === null)
		    {
			$result = $this->databaseConnection->getConnection()->exec($this->query);
			while ($row = $result->getRow())
			    {
				$this->data[] = $row;
			    }
		    }
	    } //end loadData()


	/**
	 * Creates table for meta data
	 *
	 * @return void
	 */

	protected function createTableMetaData()
	    {
		if ($this->tableMetaData === null)
		    {
			$this->loadData();

			$columns = array();
			if (isset($this->data[0]) === true)
			    {
				$columns = array_keys($this->data[0]);
			    }
			else
			    {
				$result  = $this->databaseConnection->getConnection()->fields($this->tableName);
				$columns = ($columns === false) ? array() : $result;
			    }

			$this->tableMetaData = new PHPUnit_Extensions_Database_DataSet_DefaultTableMetaData($this->tableName, $columns);
		    }
	    } //end createTableMetaData()


    } //end class

?>
