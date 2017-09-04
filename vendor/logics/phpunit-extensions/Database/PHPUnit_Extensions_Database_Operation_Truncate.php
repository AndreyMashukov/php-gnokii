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
 * Executes a truncate against all tables in a dataset.
 *
 * @author    Anastasia Bashkirtseva <anastasia@logics.net.au>
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://opensource.org/licenses/MIT MIT License
 * @version   SVN: $Date: 2016-08-17 22:45:36 +0900 (Wed, 17 Aug 2016) $ $Revision: 232 $
 * @link      $HeadURL: https://open.logics.net.au/phpunit-extensions/tags/0.2.5/Database/PHPUnit_Extensions_Database_Operation_Truncate.php $
 *
 * @donottranslate
 */

class PHPUnit_Extensions_Database_Operation_Truncate implements PHPUnit_Extensions_Database_Operation_IDatabaseOperation
    {

	/**
	 * Flag of using cascade
	 *
	 * @var bool
	 */
	protected $useCascade = false;

	/**
	 * Set use cascade
	 *
	 * @param bool $cascade Using cascade
	 *
	 * @return void
	 */

	public function setCascade($cascade = true)
	    {
		$this->useCascade = $cascade;
	    } //end setCascade()


	/**
	 * Executes a statement
	 *
	 * @param PHPUnit_Extensions_Database_DB_IDatabaseConnection $connection SQLdatabase
	 * @param PHPUnit_Extensions_Database_DataSet_IDataSet       $dataSet    DataSet of statement
	 *
	 * @return void
	 *
	 * @throws PHPUnit_Extensions_Database_Operation_Exception Unable to truncate table
	 */

	public function execute(PHPUnit_Extensions_Database_DB_IDatabaseConnection $connection, PHPUnit_Extensions_Database_DataSet_IDataSet $dataSet)
	    {
		foreach ($dataSet->getReverseIterator() as $table)
		    {
			$query = $connection->getTruncateCommand() . " " . $connection->quoteSchemaObject($table->getTableMetaData()->getTableName());

			if (($this->useCascade === true) && ($connection->allowsCascading() === true))
			    {
				$query .= " CASCADE";
			    }

			if ($connection->getConnection()->exec($query) === false)
			    {
				throw new PHPUnit_Extensions_Database_Operation_Exception("TRUNCATE", $query, array(), $table, "Unable to truncate table");
			    }
		    }
	    } //end execute()


    } //end class

?>
