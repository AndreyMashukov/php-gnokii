<?php

/**
 * PHPUnit
 *
 * Copyright (c) 2002-2014, Sebastian Bergmann <sebastian@phpunit.de>.
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
 * @copyright 2002-2014 Sebastian Bergmann <sebastian@phpunit.de>
 * @license   http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 */

use \Logics\Tests\SerializeableSimpleXMLElement;

/**
 * Provides a basic functionality for dbunit tables
 *
 * @author    Mike Lively <m@digitalsandwich.com>
 * @copyright 2010-2014 Mike Lively <m@digitalsandwich.com>
 * @license   http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @version   SVN: $Date: 2016-04-29 20:36:03 +0900 (Fri, 29 Apr 2016) $ $Revision: 191 $
 * @link      $HeadURL: https://open.logics.net.au/phpunit-extensions/tags/0.2.5/Database/PHPUnit_Extensions_Database_DataSet_AbstractTable.php $
 *
 * @donottranslate
 */

class PHPUnit_Extensions_Database_DataSet_AbstractTable implements PHPUnit_Extensions_Database_DataSet_ITable
    {

	/**
	 * Table meta data
	 *
	 * @var PHPUnit_Extensions_Database_DataSet_ITableMetaData
	 */
	protected $tableMetaData;

	/**
	 * Data
	 *
	 * @var array
	 */
	protected $data;

	/**
	 * Other table
	 *
	 * @var PHPUnit_Extensions_Database_DataSet_ITable
	 */
	private $_other;

	/**
	 * Sets the metadata for this table.
	 *
	 * @param PHPUnit_Extensions_Database_DataSet_ITableMetaData $tableMetaData Table meta data
	 *
	 * @return void
	 */

	protected function setTableMetaData(PHPUnit_Extensions_Database_DataSet_ITableMetaData $tableMetaData)
	    {
		$this->tableMetaData = $tableMetaData;
	    } //end setTableMetaData()


	/**
	 * Returns the table's meta data.
	 *
	 * @return PHPUnit_Extensions_Database_DataSet_ITableMetaData
	 */

	public function getTableMetaData()
	    {
		return $this->tableMetaData;
	    } //end getTableMetaData()


	/**
	 * Returns the number of rows in this table.
	 *
	 * @return int
	 */

	public function getRowCount()
	    {
		return count($this->data);
	    } //end getRowCount()


	/**
	 * Returns the value for the given column on the given row.
	 *
	 * @param int $row    Row
	 * @param int $column Column
	 *
	 * @return mixed
	 *
	 * @throws InvalidArgumentException The given row and column do not exist in table
	 */

	public function getValue($row, $column)
	    {
		if (isset($this->data[$row][$column]) === true)
		    {
			$value = $this->data[$row][$column];
			return ($value instanceof SerializeableSimpleXMLElement) ? (string) $value : $value;
		    }
		else
		    {
			if (in_array($column, $this->getTableMetaData()->getColumns()) === false || $this->getRowCount() <= $row)
			    {
				throw new InvalidArgumentException(
				    "The given row ({" . $row . "}) and column ({" . $column . "}) do not exist in table {" . $this->getTableMetaData()->getTableName() . "}"
				);
			    }
			else
			    {
				return null;
			    }
		    }
	    } //end getValue()


	/**
	 * Returns the an associative array keyed by columns for the given row.
	 *
	 * @param int $row Row
	 *
	 * @return array
	 *
	 * @throws InvalidArgumentException The given row does not exist in table
	 */

	public function getRow($row)
	    {
		if (isset($this->data[$row]) === true)
		    {
			return $this->data[$row];
		    }
		else
		    {
			if ($this->getRowCount() <= $row)
			    {
				throw new InvalidArgumentException("The given row ({" . $row . "}) does not exist in table {" . $this->getTableMetaData()->getTableName() . "}");
			    }
			else
			    {
				return null;
			    }
		    }
	    } //end getRow()


	/**
	 * Asserts that the given table matches this table.
	 *
	 * @param PHPUnit_Extensions_Database_DataSet_ITable $other Other table
	 *
	 * @return bool
	 */

	public function matches(PHPUnit_Extensions_Database_DataSet_ITable $other)
	    {
		$thisMetaData  = $this->getTableMetaData();
		$otherMetaData = $other->getTableMetaData();
		$matchesResult = true;

		if ($thisMetaData->matches($otherMetaData) === false || $this->getRowCount() !== $other->getRowCount())
		    {
			$matchesResult = false;
		    }
		else
		    {
			$columns  = $thisMetaData->getColumns();
			$rowCount = $this->getRowCount();

			for ($i = 0; $i < $rowCount; $i++)
			    {
				foreach ($columns as $columnName)
				    {
					$thisValue  = $this->getValue($i, $columnName);
					$otherValue = $other->getValue($i, $columnName);
					if (is_numeric($thisValue) === true && is_numeric($otherValue) === true)
					    {
						if ($thisValue !== $otherValue)
						    {
							$this->_other  = $other;
							$matchesResult = false;
							break 2;
						    }
					    }
					else if ($thisValue !== $otherValue)
					    {
						$this->_other  = $other;
						$matchesResult = false;
						break 2;
					    }
				    } //end foreach
			    } //end for
		    } //end if

		return $matchesResult;
	    } //end matches()


	/**
	 * Checks if a given row is in the table
	 *
	 * @param array $row Row
	 *
	 * @return bool
	 */

	public function assertContainsRow(array $row)
	    {
		return in_array($row, $this->data);
	    } //end assertContainsRow()


	/**
	 * Represent as a string
	 *
	 * @return sting
	 */

	public function __toString()
	    {
		$columns       = $this->getTableMetaData()->getColumns();
		$lineSeperator = str_repeat("+----------------------", count($columns)) . "+\n";
		$lineLength    = (strlen($lineSeperator) - 1);

		$tableString  = $lineSeperator;
		$tableString .= "| " . str_pad($this->getTableMetaData()->getTableName(), ($lineLength - 4), " ", STR_PAD_RIGHT) . " |\n";
		$tableString .= $lineSeperator;
		$tableString .= $this->rowToString($columns);
		$tableString .= $lineSeperator;

		$rowCount = $this->getRowCount();

		for ($i = 0; $i < $rowCount; $i++)
		    {
			$values = array();

			foreach ($columns as $columnName)
			    {
				if ($this->_other !== null)
				    {
					try
					    {
						if ($this->getValue($i, $columnName) !== $this->_other->getValue($i, $columnName))
						    {
							$values[] = sprintf(
								     "%s != actual %s",
								     var_export($this->getValue($i, $columnName), true),
								     var_export($this->_other->getValue($i, $columnName), true)
								    );
						    }
						else
						    {
							$values[] = $this->getValue($i, $columnName);
						    }
					    }
					catch (\InvalidArgumentException $ex)
					    {
						$values[] = $this->getValue($i, $columnName) . ": no row";
					    }
				    }
				else
				    {
					$values[] = $this->getValue($i, $columnName);
				    } //end if
			    } //end foreach

			$tableString .= $this->rowToString($values) . $lineSeperator;
		    } //end for

		return (($this->_other !== false) ? "(table diff enabled)" : "") . "\n" . $tableString . "\n";
	    } //end __toString()


	/**
	 * Represent row as a sting
	 *
	 * @param array $row Original row
	 *
	 * @return string
	 */

	protected function rowToString(array $row)
	    {
		$rowString = "";

		foreach ($row as $value)
		    {
			if ($value === null)
			    {
				$value = "NULL";
			    }

			$rowString .= "| " . str_pad(substr($value, 0, 20), 20, " ", STR_PAD_BOTH) . " ";
		    }

		return $rowString . "|\n";
	    } //end rowToString()


    } //end class

?>
