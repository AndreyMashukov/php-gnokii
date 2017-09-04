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
 * Provides functionality to retrieve meta data from a MySQL database.
 *
 * @author    Anastasia Bashkirtseva <anastasia@logics.net.au>
 * @author    Mike Lively <m@digitalsandwich.com>
 * @copyright 2002-2013 Sebastian Bergmann <sebastian@phpunit.de>
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://opensource.org/licenses/MIT MIT License
 * @version   SVN: $Date: 2016-08-17 22:45:36 +0900 (Wed, 17 Aug 2016) $ $Revision: 232 $
 * @link      $HeadURL: https://open.logics.net.au/phpunit-extensions/tags/0.2.5/Database/PHPUnit_Extensions_Database_DB_SQL_MetaData_MySQL.php $
 *
 * @donottranslate
 */

class PHPUnit_Extensions_Database_DB_SQL_MetaData_MySQL extends PHPUnit_Extensions_Database_DB_SQL_MetaData
    {

	/**
	 * Schema object quote char
	 *
	 * @var string
	 */
	protected $schemaObjectQuoteChar = "`";

	/**
	 * Returns an array containing the names of all the tables in the database.
	 *
	 * @return array
	 */

	public function getTableNames()
	    {
		$query  = "SHOW TABLES";
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
		$result = $this->sql->fields($tableName);
		return (($result === false) ? array() : $result);
	    } //end getTableColumns()


	/**
	 * Returns an array containing the names of all the primary key columns in
	 * the $tableName table.
	 *
	 * @param string $tableName Bame of table
	 *
	 * @return array
	 */

	public function getTablePrimaryKeys($tableName)
	    {
		$query       = "SHOW INDEX FROM " . $this->quoteSchemaObject($tableName);
		$result      = $this->sql->exec($query);
		$columnNames = array();
		if ($result !== false)
		    {
			while ($column = $result->getRow())
			    {
				if ($column["Key_name"] === "PRIMARY")
				    {
					$columnNames[] = $column["Column_name"];
				    }
			    }
		    }

		return $columnNames;
	    } //end getTablePrimaryKeys()


    } //end class

?>