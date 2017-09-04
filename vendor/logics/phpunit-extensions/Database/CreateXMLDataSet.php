<?php

/**
 * PHP version 5.6
 *
 * @package Logics\Tests
 */

namespace Logics\Tests;

use \PHPUnit_Extensions_Database_DataSet_FlatXmlDataSet;
use \PHPUnit_Extensions_Database_DataSet_MysqlXmlDataSet;
use \PHPUnit_Extensions_Database_DataSet_XmlDataSet;

/**
 * CreateXMLDataSet trait for PHPUnit_Extensions_Database_SQL_TestCase
 *
 * Trait to create data sets out of XML files
 *
 * @author    Anastasia Bashkirtseva <anastasia@logics.net.au>
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://opensource.org/licenses/MIT MIT License
 * @version   SVN: $Date: 2016-08-17 22:45:36 +0900 (Wed, 17 Aug 2016) $ $Revision: 232 $
 * @link      $HeadURL: https://open.logics.net.au/phpunit-extensions/tags/0.2.5/Database/CreateXMLDataSet.php $
 */

trait CreateXMLDataSet
    {

	/**
	 * Creates a new FlatXmlDataSet with the given $xmlFile. (absolute path.)
	 *
	 * @param string $xmlFile File XML
	 *
	 * @return PHPUnit_Extensions_Database_DataSet_FlatXmlDataSet
	 */

	protected function createFlatXMLDataSet($xmlFile)
	    {
		return new PHPUnit_Extensions_Database_DataSet_FlatXmlDataSet($xmlFile);
	    } //end createFlatXMLDataSet()


	/**
	 * Creates a new XMLDataSet with the given $xmlFile. (absolute path.)
	 *
	 * @param string $xmlFile File XML
	 *
	 * @return PHPUnit_Extensions_Database_DataSet_XmlDataSet
	 */

	protected function createXMLDataSet($xmlFile)
	    {
		return new PHPUnit_Extensions_Database_DataSet_XmlDataSet($xmlFile);
	    } //end createXMLDataSet()


	/**
	 * Create a a new MysqlXmlDataSet with the given $xmlFile. (absolute path.)
	 *
	 * @param string $xmlFile File XML
	 *
	 * @return PHPUnit_Extensions_Database_DataSet_MysqlXmlDataSet
	 */

	protected function createMySQLXMLDataSet($xmlFile)
	    {
		return new PHPUnit_Extensions_Database_DataSet_MysqlXmlDataSet($xmlFile);
	    } //end createMySQLXMLDataSet()


    } //end trait

?>
