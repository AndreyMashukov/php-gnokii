<?php

/**
 * PHPUnit bootstrap file. We use our own modifications on PHPUnit. We need to load them before PHPUnit starts to get them into effect.
 *
 * PHP version 5.6
 *
 * @package Logics\Tests
 */

require "Database/CreateXMLDataSet.php";
require "Database/PHPUnit_Extensions_Database_DataSet_AbstractTable.php";
require "Database/PHPUnit_Extensions_Database_Operation_Truncate.php";
require "Database/PHPUnit_Extensions_Database_Operation_RowBased.php";
require "Database/PHPUnit_Extensions_Database_DB_Table.php";
require "Database/PHPUnit_Extensions_Database_DataSet_AbstractXmlDataSet.php";
require "Database/PHPUnit_Extensions_Database_DataSet_Specs_DbQuery.php";
require "Database/PHPUnit_Extensions_Database_Operation_DeleteAll.php";
require "Database/PHPUnit_Extensions_Database_SQL_TestCase.php";
require "Database/PHPUnit_Extensions_Database_DB_SQL_DefaultDatabaseConnection.php";
require "Database/PHPUnit_Extensions_Database_DB_SQL_MetaData.php";
require "Database/PHPUnit_Extensions_Database_DB_SQL_MetaData_MySQL.php";
require "Database/PHPUnit_Extensions_Database_DB_SQL_MetaData_PgSQL.php";
require "Database/PHPUnit_Extensions_Database_DataSet_SQL_QueryTable.php";

require "environment/init.php";

if (file_exists(stream_resolve_include_path("autoload.php")) === true)
    {
	include_once "autoload.php";
    }
else if (file_exists(stream_resolve_include_path("vendor/autoload.php")) === true)
    {
	include_once "vendor/autoload.php";
    }

?>
