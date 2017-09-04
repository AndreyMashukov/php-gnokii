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
 * @package Logics\Tests
 */

namespace Logics\Tests;

/**
 * Provides functionality to retrieve meta data from a PostgreSQL database.
 *
 * @author    Anastasia Bashkirtseva <anastasia@logics.net.au>
 * @author    Mike Lively <m@digitalsandwich.com>
 * @copyright 2002-2013 Sebastian Bergmann <sebastian@phpunit.de>
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://opensource.org/licenses/MIT MIT License
 * @version   SVN: $Date: 2016-08-17 22:45:36 +0900 (Wed, 17 Aug 2016) $ $Revision: 232 $
 * @link      $HeadURL: https://open.logics.net.au/phpunit-extensions/tags/0.2.5/Database/PHPUnit_Extensions_Database_DB_SQL_MetaData_PgSQL.php $
 *
 * @donottranslate
 */

class PHPUnit_Extensions_Database_DB_SQL_MetaData_PgSQL extends PHPUnit_Extensions_Database_DB_SQL_MetaData
    {

	/**
	 * Returns an array containing the names of all the tables in the database.
	 *
	 * @return array
	 */

	public function getTableNames()
	    {
		$query = "
		    SELECT DISTINCT
		    TABLE_NAME
		    FROM INFORMATION_SCHEMA.TABLES
		    WHERE
		    TABLE_TYPE='BASE TABLE' AND
		    TABLE_SCHEMA = " . $this->sql->sqlText($this->getSchema()) . "
		    ORDER BY TABLE_NAME
		";

		$result = $this->sql->exec($query);

		$tableNames = array();
		while ($row = $result->getRow())
		    {
			$tmp          = array_values($row);
			$tableNames[] = $tmp[0];
		    }

		return $tableNames;
	    } //end getTableNames()


	/**
	 * Returns an array containing the names of all the columns in the
	 * $tableName table,
	 *
	 * @param string $tableName Name of table
	 *
	 * @return array
	 */

	public function getTableColumns($tableName)
	    {
		if (isset($this->columns[$tableName]) === false)
		    {
			$this->loadColumnInfo($tableName);
		    }

		return $this->columns[$tableName];
	    } //end getTableColumns()


	/**
	 * Returns an array containing the names of all the primary key columns in
	 * the $tableName table.
	 *
	 * @param string $tableName Name of table
	 *
	 * @return array
	 */

	public function getTablePrimaryKeys($tableName)
	    {
		if (isset($this->keys[$tableName]) === true)
		    {
			$this->loadColumnInfo($tableName);
		    }

		return $this->keys[$tableName];
	    } //end getTablePrimaryKeys()


	/**
	 * Loads column info from a database table.
	 *
	 * @param string $tableName Name of table
	 *
	 * @return void
	 */

	protected function loadColumnInfo($tableName)
	    {
		$this->columns[$tableName] = array();
		$this->keys[$tableName]    = array();

		$columnQuery = "
		    SELECT DISTINCT
		    COLUMN_NAME, ORDINAL_POSITION
		    FROM INFORMATION_SCHEMA.COLUMNS
		    WHERE
		    TABLE_NAME = " . $this->sql->sqlText($tableName) . " AND
		    TABLE_SCHEMA = " . $this->sql->sqlText($this->getSchema()) . "
		    ORDER BY ORDINAL_POSITION
		";

		$result = $this->sql->exec($columnQuery);

		$tableNames = array();
		while ($row = $result->getRow())
		    {
			$tmp = array_values($row);
			$this->columns[$tableNames][] = $tmp[0];
		    }

		$keyQuery = "
		    SELECT
		    KCU.COLUMN_NAME,
		    KCU.ORDINAL_POSITION
		    FROM
		    INFORMATION_SCHEMA.KEY_COLUMN_USAGE as KCU
		    LEFT JOIN INFORMATION_SCHEMA.TABLE_CONSTRAINTS as TC
		    ON TC.TABLE_NAME = KCU.TABLE_NAME AND
		    TC.CONSTRAINT_NAME = KCU.CONSTRAINT_NAME
		    WHERE
		    TC.CONSTRAINT_TYPE = 'PRIMARY KEY' AND
		    TC.TABLE_NAME = " . $this->sql->sqlText($tableName) . " AND
		    TC.TABLE_SCHEMA = " . $this->sql->sqlText($this->getSchema()) . "
		    ORDER BY
		    KCU.ORDINAL_POSITION ASC
		";

		$result = $this->sql->exec($keyQuery);

		$tableNames = array();
		while ($row = $result->getRow())
		    {
			$tmp                      = array_values($row);
			$this->keys[$tableName][] = $tmp[0];
		    }
	    } //end loadColumnInfo()


	/**
	 * Returns the schema for the connection.
	 *
	 * @return string
	 */

	public function getSchema()
	    {
		if (empty($this->schema) === true)
		    {
			return "public";
		    }
		else
		    {
			return $this->schema;
		    }
	    } //end getSchema()


	/**
	 * Returns true if the rdbms allows cascading
	 *
	 * @return bool
	 */

	public function allowsCascading()
	    {
		return true;
	    } //end allowsCascading()


    } //end class

?>