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

use \Logics\Tests\SerializeableSimpleXMLElement;

/**
 * The default implementation of a data set.
 *
 * @author    Anastasia Bashkirtseva <anastasia@logics.net.au>
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://opensource.org/licenses/MIT MIT License
 * @version   SVN: $Date: 2016-08-17 22:45:36 +0900 (Wed, 17 Aug 2016) $ $Revision: 232 $
 * @link      $HeadURL: https://open.logics.net.au/phpunit-extensions/tags/0.2.5/Database/PHPUnit_Extensions_Database_DataSet_AbstractXmlDataSet.php $
 *
 * @donottranslate
 */

abstract class PHPUnit_Extensions_Database_DataSet_AbstractXmlDataSet extends PHPUnit_Extensions_Database_DataSet_AbstractDataSet
    {

	/**
	 * Tables
	 *
	 * @var array
	 */
	protected $tables;

	/**
	 * XML file contents
	 *
	 * @var string
	 */
	protected $xmlFileContents;

	/**
	 * Encoding
	 *
	 * @var string
	 */
	public $encoding;

	/**
	 * Creates a new dataset using the given tables.
	 *
	 * @param string $xmlFile The path to the XML document
	 *
	 * @return void
	 *
	 * @throws InvalidArgumentException Could not find xml file
	 * @throws RuntimeException         Errors
	 */

	public function __construct($xmlFile)
	    {
		if (is_file($xmlFile) === false)
		    {
			throw new InvalidArgumentException(
			    "Could not find xml file: " . $xmlFile
			);
		    }

		$libxmlErrorReporting = libxml_use_internal_errors(true);

		$dom = new DOMDocument();
		$dom->load($xmlFile);
		$this->xmlFileContents = new SerializeableSimpleXMLElement($dom->documentElement);
		$this->encoding        = $dom->encoding;

		if ($this->xmlFileContents === null)
		    {
			$message = "";

			foreach (libxml_get_errors() as $error)
			    {
				$message .= $error->message;
			    }

			throw new RuntimeException($message);
		    }

		libxml_clear_errors();
		libxml_use_internal_errors($libxmlErrorReporting);

		$tableColumns = array();
		$tableValues  = array();

		$this->getTableInfo($tableColumns, $tableValues);
		$this->createTables($tableColumns, $tableValues);
	    } //end __construct()


	/**
	 * Reads the simple xml object and extracts data: tableColumns - array containing table columns
	 * tableValues - array containing values for those columns
	 *
	 * @param array $tableColumns Table columns
	 * @param array $tableValues  Table values
	 *
	 * @return void
	 */

	protected abstract function getTableInfo(array &$tableColumns, array &$tableValues);


	/**
	 * Creates the appropriate tables and metadata for this dataset.
	 *
	 * @param array $tableColumns Table columns
	 * @param array $tableValues  Table values
	 *
	 * @return void
	 */

	protected function createTables(array &$tableColumns, array &$tableValues)
	    {
		foreach ($tableValues as $tableName => $values)
		    {
			$table = $this->getOrCreateTable($tableName, $tableColumns[$tableName]);
			foreach ($values as $value)
			    {
				$table->addRow($value);
			    }
		    }
	    } //end createTables()


	/**
	 * Returns the table with the matching name. If the table does not exist
	 * an empty one is created.
	 *
	 * @param string $tableName    Table name
	 * @param string $tableColumns Table columns
	 *
	 * @return PHPUnit_Extensions_Database_DataSet_ITable
	 */

	protected function getOrCreateTable($tableName, $tableColumns)
	    {
		if (empty($this->tables[$tableName]) === true)
		    {
			$tableMetaData            = new PHPUnit_Extensions_Database_DataSet_DefaultTableMetaData($tableName, $tableColumns);
			$this->tables[$tableName] = new PHPUnit_Extensions_Database_DataSet_DefaultTable($tableMetaData);
		    }

		return $this->tables[$tableName];
	    } //end getOrCreateTable()


	/**
	 * Creates an iterator over the tables in the data set. If $reverse is
	 * true a reverse iterator will be returned.
	 *
	 * @param bool $reverse Return reverse iterator
	 *
	 * @return PHPUnit_Extensions_Database_DataSet_ITableIterator
	 */

	protected function createIterator($reverse = false)
	    {
		return new PHPUnit_Extensions_Database_DataSet_DefaultTableIterator($this->tables, $reverse);
	    } //end createIterator()


    } //end class

?>
