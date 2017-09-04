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

use \Logics\Foundation\SQL\MySQLdatabase;
use \Logics\Foundation\SQL\PostgreSQLdatabase;
use \Logics\Foundation\SQL\SQLdatabase;
use \Logics\Tests\PHPUnit_Extensions_Database_DB_SQL_MetaData_MySQL;
use \Logics\Tests\PHPUnit_Extensions_Database_DB_SQL_MetaData_PgSQL;
use \PHPUnit_Extensions_Database_DB_IMetaData;
use \PHPUnit_Extensions_Database_Exception;
use \ReflectionClass;

/**
 * Provides a basic constructor for all meta data classes and a factory for
 * generating the appropriate meta data class.
 *
 * @author    Anastasia Bashkirtseva <anastasia@logics.net.au>
 * @author    Mike Lively <m@digitalsandwich.com>
 * @copyright 2002-2013 Sebastian Bergmann <sebastian@phpunit.de>
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://opensource.org/licenses/MIT MIT License
 * @version   SVN: $Date: 2017-01-21 00:10:14 +0800 (Sat, 21 Jan 2017) $ $Revision: 273 $
 * @link      $HeadURL: https://open.logics.net.au/phpunit-extensions/tags/0.2.5/Database/PHPUnit_Extensions_Database_DB_SQL_MetaData.php $
 *
 * @donottranslate
 */

abstract class PHPUnit_Extensions_Database_DB_SQL_MetaData implements PHPUnit_Extensions_Database_DB_IMetaData
    {

	/**
	 * The SQLdatabase connection used to retreive database meta data.
	 *
	 * @var SQLdatabase
	 */
	protected $sql;

	/**
	 * The default schema name for the meta data object.
	 *
	 * @var string
	 */
	protected $schema;

	/**
	 * The character used to quote schema objects.
	 *
	 * @var string
	 */
	protected $schemaObjectQuoteChar = "\"";

	/**
	 * The command used to perform a TRUNCATE operation.
	 *
	 * @var string
	 */
	protected $truncateCommand = "TRUNCATE";

	/**
	 * Creates a new database meta data object using the given SQLdatabase connection
	 * and schema name.
	 *
	 * @param SQLdatabase $sql    Database
	 * @param string      $schema Schema name for the meta data object
	 *
	 * @return void
	 */

	public final function __construct(SQLdatabase $sql, $schema = "")
	    {
		$this->sql    = $sql;
		$this->schema = $schema;
	    } //end __construct()


	/**
	 * Creates a meta data object based on the driver of given $sql object and
	 * $schema name.
	 *
	 * @param SQLdatabase $sql    Database
	 * @param string      $schema Schema name for the meta data object
	 *
	 * @return PHPUnit_Extensions_Database_DB_MetaData Could not find a meta data driver for sql driver
	 *
	 * @throws PHPUnit_Extensions_Database_Exception Could not find a meta data driver for sql driver
	 */

	public static function createMetaData(SQLdatabase $sql, $schema = "")
	    {
		if ($sql instanceof MySQLdatabase)
		    {
			$className = PHPUnit_Extensions_Database_DB_SQL_MetaData_MySQL::CLASS;
			return self::registerClassWithDriver($className)->newInstance($sql, $schema);
		    }
		else if ($sql instanceof PostgreSQLdatabase)
		    {
			$className = PHPUnit_Extensions_Database_DB_SQL_MetaData_PgSQL::CLASS;
			return self::registerClassWithDriver($className)->newInstance($sql, $schema);
		    }
		else
		    {
			throw new PHPUnit_Extensions_Database_Exception("Could not find a meta data driver for sql driver");
		    }
	    } //end createMetaData()


	/**
	 * Validates and registers the given $className.
	 *
	 * A reflection of the $className is returned.
	 *
	 * @param string $className Class name database
	 *
	 * @return ReflectionClass
	 *
	 * @throws PHPUnit_Extensions_Database_Exception Specified class for drive not found
	 */

	public static function registerClassWithDriver($className)
	    {
		if (class_exists($className) === false)
		    {
			throw new PHPUnit_Extensions_Database_Exception(
			    "Specified class for driver (" . $className . ") does not exist."
			);
		    }

		$reflection = new ReflectionClass($className);
		if ($reflection->isSubclassOf(self::CLASS) === true)
		    {
			return $reflection;
		    }
		else
		    {
			throw new PHPUnit_Extensions_Database_Exception(
			    "Specified class for driver (" . $className . ") does not extend Logics\Tests\PHPUnit_Extensions_Database_DB_SQL_MetaData."
			);
		    }
	    } //end registerClassWithDriver()


	/**
	 * Returns the schema for the connection.
	 *
	 * @return string
	 */

	public function getSchema()
	    {
		return $this->schema;
	    } //end getSchema()


	/**
	 * Returns a quoted schema object. (table name, column name, etc)
	 *
	 * @param string $object Object database
	 *
	 * @return string
	 */

	public function quoteSchemaObject($object)
	    {
		$parts       = explode(".", $object);
		$quotedParts = array();

		foreach ($parts as $part)
		    {
			$quotedParts[] = $this->schemaObjectQuoteChar .
			str_replace($this->schemaObjectQuoteChar, $this->schemaObjectQuoteChar . $this->schemaObjectQuoteChar, $part) .
			$this->schemaObjectQuoteChar;
		    }

		return implode(".", $quotedParts);
	    } //end quoteSchemaObject()


	/**
	 * Seperates the schema and the table from a fully qualified table name.
	 * Returns an associative array containing the 'schema' and the 'table'.
	 *
	 * @param string $fullTableName A fully table name
	 *
	 * @return array
	 */

	public function splitTableName($fullTableName)
	    {
		$dot = strpos($fullTableName, ".");
		if ($dot !== false)
		    {
			return array(
				"schema" => substr($fullTableName, 0, $dot),
				"table"  => substr($fullTableName, ($dot + 1)),
			       );
		    }
		else
		    {
			return array(
				"schema" => null,
				"table"  => $fullTableName,
			       );
		    }
	    } //end splitTableName()


	/**
	 * Returns the command for the database to truncate a table.
	 *
	 * @return string
	 */

	public function getTruncateCommand()
	    {
		return $this->truncateCommand;
	    } //end getTruncateCommand()


	/**
	 * Returns true if the rdbms allows cascading
	 *
	 * @return bool
	 */

	public function allowsCascading()
	    {
		return false;
	    } //end allowsCascading()


	/**
	 * Disables primary keys if the rdbms does not allow setting them otherwise
	 *
	 * @param string $tableName Name of table
	 *
	 * @return void
	 */

	public function disablePrimaryKeys($tableName)
	    {
		unset($tableName);
	    } //end disablePrimaryKeys()


	/**
	 * Reenables primary keys after they have been disabled
	 *
	 * @param string $tableName Name of table
	 *
	 * @return void
	 */

	public function enablePrimaryKeys($tableName)
	    {
		unset($tableName);
	    } //end enablePrimaryKeys()


    } //end class

?>
