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

/**
 * Provides basic functionality for row based operations.
 *
 * To create a row based operation you must create two functions. The first
 * one, buildOperationQuery(), must return a query that will be used to create
 * a prepared statement. The second one, buildOperationArguments(), should
 * return an array containing arguments for each row.
 *
 * @author    Anastasia Bashkirtseva <anastasia@logics.net.au>
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://opensource.org/licenses/MIT MIT License
 * @version   SVN: $Date: 2016-08-17 22:45:36 +0900 (Wed, 17 Aug 2016) $ $Revision: 232 $
 * @link      $HeadURL: https://open.logics.net.au/phpunit-extensions/tags/0.2.5/Database/PHPUnit_Extensions_Database_Operation_RowBased.php $
 *
 * @donottranslate
 */

abstract class PHPUnit_Extensions_Database_Operation_RowBased implements PHPUnit_Extensions_Database_Operation_IDatabaseOperation
    {

	const ITERATOR_TYPE_FORWARD = 0;

	const ITERATOR_TYPE_REVERSE = 1;

	/**
	 * Operation name
	 *
	 * @var string
	 */
	protected $operationName;

	/**
	 * Iternation direction
	 *
	 * @var int
	 */
	protected $iteratorDirection = self::ITERATOR_TYPE_FORWARD;

	/**
	 * Build query
	 *
	 * @param PHPUnit_Extensions_Database_DataSet_ITableMetaData $databaseTableMetaData Dataset table meta data
	 * @param PHPUnit_Extensions_Database_DataSet_ITable         $table                 Table
	 * @param PHPUnit_Extensions_Database_DB_IDatabaseConnection $connection            SQLdatabase
	 *
	 * @return string|boolean String containing the query or FALSE if a valid query cannot be constructed
	 */

	protected abstract function buildOperationQuery(
	    PHPUnit_Extensions_Database_DataSet_ITableMetaData $databaseTableMetaData,
	    PHPUnit_Extensions_Database_DataSet_ITable $table,
	    PHPUnit_Extensions_Database_DB_IDatabaseConnection $connection
	);


	/**
	 * Build arguments array
	 *
	 * @param PHPUnit_Extensions_Database_DataSet_ITableMetaData $databaseTableMetaData Dataset table meta data
	 * @param PHPUnit_Extensions_Database_DataSet_ITable         $table                 Table
	 * @param int                                                $row                   Number row
	 *
	 * @return array
	 */

	protected abstract function buildOperationArguments(
	    PHPUnit_Extensions_Database_DataSet_ITableMetaData $databaseTableMetaData,
	    PHPUnit_Extensions_Database_DataSet_ITable $table, $row
	);


	/**
	 * Allows an operation to disable primary keys if necessary.
	 *
	 * @param PHPUnit_Extensions_Database_DataSet_ITableMetaData $databaseTableMetaData Dataset table meta data
	 * @param PHPUnit_Extensions_Database_DataSet_ITable         $table                 Table
	 * @param PHPUnit_Extensions_Database_DB_IDatabaseConnection $connection            SQLdatabase
	 *
	 * @return bool
	 */

	protected function disablePrimaryKeys(
	    PHPUnit_Extensions_Database_DataSet_ITableMetaData $databaseTableMetaData,
	    PHPUnit_Extensions_Database_DataSet_ITable $table,
	    PHPUnit_Extensions_Database_DB_IDatabaseConnection $connection)
	    {
		unset($databaseTableMetaData);
		unset($table);
		unset($connection);
		return false;
	    } //end disablePrimaryKeys()


	/**
	 * Set database encoding
	 *
	 * @param PHPUnit_Extensions_Database_DB_IDatabaseConnection $connection SQLdatabase
	 * @param PHPUnit_Extensions_Database_DataSet_IDataSet       $dataSet    DataSet of statement
	 *
	 * @return void
	 */

	private function _setEncoding(PHPUnit_Extensions_Database_DB_IDatabaseConnection $connection, PHPUnit_Extensions_Database_DataSet_IDataSet $dataSet)
	    {
		if (isset($dataSet->encoding) === true)
		    {
			$charactersets = array("utf-8" => "utf8");

			$encoding = (isset($charactersets[strtolower($dataSet->encoding)]) === true) ? $charactersets[strtolower($dataSet->encoding)] : $dataSet->encoding;
			$sql      = $connection->getConnection();

			$sql->exec("SET NAMES " . $sql->sqlText($encoding));
		    }
	    } //end _setEncoding()


	/**
	 * Executes a statement
	 *
	 * @param PHPUnit_Extensions_Database_DB_IDatabaseConnection $connection SQLdatabase
	 * @param PHPUnit_Extensions_Database_DataSet_IDataSet       $dataSet    DataSet of statement
	 *
	 * @return void
	 *
	 * @throws PHPUnit_Extensions_Database_Operation_Exception Rows requested for insert, but no columns provided
	 */

	public function execute(PHPUnit_Extensions_Database_DB_IDatabaseConnection $connection, PHPUnit_Extensions_Database_DataSet_IDataSet $dataSet)
	    {
		$this->_setEncoding($connection, $dataSet);
		$databaseDataSet = $connection->createDataSet();

		$dsIterator = ($this->iteratorDirection === self::ITERATOR_TYPE_REVERSE) ? $dataSet->getReverseIterator() : $dataSet->getIterator();

		foreach ($dsIterator as $table)
		    {
			$rowCount = $table->getRowCount();

			if ($rowCount > 0)
			    {
				$databaseTableMetaData = $databaseDataSet->getTableMetaData($table->getTableMetaData()->getTableName());
				$query                 = $this->buildOperationQuery($databaseTableMetaData, $table, $connection);
				$disablePrimaryKeys    = $this->disablePrimaryKeys($databaseTableMetaData, $table, $connection);

				if ($query === false)
				    {
					if ($table->getRowCount() > 0)
					    {
						throw new PHPUnit_Extensions_Database_Operation_Exception(
						    $this->operationName, "", array(), $table, "Rows requested for insert, but no columns provided!"
						);
					    }
				    }
				else
				    {
					if ($disablePrimaryKeys === true)
					    {
						$connection->disablePrimaryKeys($databaseTableMetaData->getTableName());
					    }

					$this->_execCorrectQuery($query, $connection, $databaseTableMetaData, $table, $rowCount);

					if ($disablePrimaryKeys === true)
					    {
						$connection->enablePrimaryKeys($databaseTableMetaData->getTableName());
					    }
				    } //end if
			    } //end if
		    } //end foreach
	    } //end execute()


	/**
	 * Correct query and run this query
	 *
	 * @param string                                             $query                 Query
	 * @param PHPUnit_Extensions_Database_DB_IDatabaseConnection $connection            SQLdatabase
	 * @param string                                             $databaseTableMetaData Meta data
	 * @param string                                             $table                 Table name
	 * @param string                                             $rowCount              Row count
	 *
	 * @return void
	 */

	private function _execCorrectQuery($query, PHPUnit_Extensions_Database_DB_IDatabaseConnection $connection, $databaseTableMetaData, $table, $rowCount)
	    {
		$strs = explode("?", $query);

		$db = $connection->getConnection();
		for ($i = 0; $i < $rowCount; $i++)
		    {
			$args         = $this->buildOperationArguments($databaseTableMetaData, $table, $i);
			$correctQuery = "";

			$count = (count($strs) - 1);
			foreach ($strs as $key => $str)
			    {
				$arg           = ((isset($args[$key]) === true) ? $args[$key] : "");
				$correctQuery .= $str . (($count > $key) ? $db->sqlText($arg) : "");
			    }

			$db->exec($correctQuery);
		    } //end for
	    } //end _execCorrectQuery()


	/**
	 * Builds column array
	 *
	 * @param array                                              $columns    Array columns
	 * @param PHPUnit_Extensions_Database_DB_IDatabaseConnection $connection SQLdatabase
	 *
	 * @return array
	 */

	protected function buildPreparedColumnArray(array $columns, PHPUnit_Extensions_Database_DB_IDatabaseConnection $connection)
	    {
		$columnArray = array();

		foreach ($columns as $columnName)
		    {
			$columnArray[] = $connection->quoteSchemaObject($columnName) . " = ?";
		    }

		return $columnArray;
	    } //end buildPreparedColumnArray()


    } //end class

?>
