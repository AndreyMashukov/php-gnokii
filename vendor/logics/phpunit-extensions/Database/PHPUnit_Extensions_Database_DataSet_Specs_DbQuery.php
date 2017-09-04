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
 * Creates DefaultDataSets based off of a spec string.
 *
 * This spec class requires a list of databases to be set to the object before
 * it can return a list of databases.
 *
 * The format of the spec string is as follows:
 *
 * <db label>:<schema>:<table name>:<sql>
 *
 * The db label should be equal to one of the keys in the array of databases
 * passed to setDatabases().
 *
 * The schema should be the primary schema you will be running the sql query
 * against.
 *
 * The table name should be set to what you would like the table name in the
 * dataset to be.
 *
 * The sql is the query you want to use to generate the table columns and data.
 * The column names in the table will be identical to the column aliases in the
 * query.
 *
 * @author    Anastasia Bashkirtseva <anastasia@logics.net.au>
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://opensource.org/licenses/MIT MIT License
 * @version   SVN: $Date: 2016-08-17 22:45:36 +0900 (Wed, 17 Aug 2016) $ $Revision: 232 $
 * @link      $HeadURL: https://open.logics.net.au/phpunit-extensions/tags/0.2.5/Database/PHPUnit_Extensions_Database_DataSet_Specs_DbQuery.php $
 *
 * @donottranslate
 */

class PHPUnit_Extensions_Database_DataSet_Specs_DbQuery implements PHPUnit_Extensions_Database_DataSet_ISpec, PHPUnit_Extensions_Database_IDatabaseListConsumer
    {

	/**
	 * Databases
	 *
	 * @var array
	 */
	protected $databases = array();

	/**
	 * Sets the database for the spec
	 *
	 * @param array $databases Databases
	 *
	 * @return void
	 */

	public function setDatabases(array $databases)
	    {
		$this->databases = $databases;
	    } //end setDatabases()


	/**
	 * Gets dataset
	 *
	 * @param string $dataSetSpec DataSet for the spec
	 *
	 * @return PHPUnit_Extensions_Database_DataSet_AbstractDataSet
	 */

	public function getDataSet($dataSetSpec)
	    {
		list($dbLabel, $schema, $table, $sql) = explode(":", $dataSetSpec, 4);
		$databaseInfo = $this->databases[$dbLabel];
		$sqlRflc      = new ReflectionClass("SQLdatabase");
		$sql          = $sqlRflc->newInstanceArgs(explode("|", $databaseInfo));
		$dbConnection = new PHPUnit_Extensions_Database_DB_DefaultDatabaseConnection($sql, $schema);
		$table        = $dbConnection->createQueryTable($table, $sql);

		return new PHPUnit_Extensions_Database_DataSet_DefaultDataSet(array($table));
	    } //end getDataSet()


    } //end class

?>
