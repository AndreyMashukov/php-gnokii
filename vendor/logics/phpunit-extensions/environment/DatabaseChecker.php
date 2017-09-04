<?php

/**
 * PHP version 5.6
 *
 * @package Logics\Tests
 */

namespace Logics\Tests;

require_once "AbstractChecker.php";
require_once "DefaultMedium.php";
require_once "InterfaceMedium.php";

use \mysqli;

/**
 * Class setting up personal database and test it
 *
 * @author    Vladimir Skorov <voroks@logics.net.au>
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://opensource.org/licenses/MIT MIT License
 * @version   SVN: $Date: 2016-08-17 22:45:36 +0900 (Wed, 17 Aug 2016) $ $Revision: 232 $
 * @link      $HeadURL: https://open.logics.net.au/phpunit-extensions/tags/0.2.5/environment/DatabaseChecker.php $
 *
 * @donottranslate
 */

class DatabaseChecker extends AbstractChecker implements InterfaceMedium
    {

	use DefaultMedium;

	/**
	 * Global mode
	 *
	 * @var bool
	 */
	private $_global;

	/**
	 * Need to drop
	 *
	 * @var bool
	 */
	private $_needToDrop;

	/**
	 * Injections
	 *
	 * @var array
	 */
	private $_injections;

	/**
	 * Real DB name
	 *
	 * @var string
	 */
	private $_realDBName;

	/**
	 * Injection failed
	 *
	 * @var bool
	 */
	private $_injectionFailed;

	/**
	 * Initialize private variables
	 *
	 * @param bool $global     True if we should initialize common database, false if one for user
	 * @param bool $needToDrop True if we should drop all tables in database
	 *
	 * @return void
	 */

	public function __construct($global, $needToDrop = true)
	    {
		$this->_global     = $global;
		$this->_needToDrop = $needToDrop;
		$this->_injections = array();
		$this->_realDBName = $GLOBALS["DB_DBNAME"];

		$this->_injectionFailed = false;
	    } //end __construct()


	/**
	 * Create MySQL database if needed
	 *
	 * @param string $dbName Expected database name
	 *
	 * @return bool
	 */

	private function _createMySQLdatabase($dbName)
	    {
		$created = false;

		$mysqli = new mysqli($GLOBALS["DB_HOST"], "root", "");
		$mysqli->query("GRANT ALL PRIVILEGES ON *.* TO '" . $GLOBALS["DB_USER"] . "'@'localhost' IDENTIFIED BY '" . $GLOBALS["DB_PASSWD"] . "' WITH GRANT OPTION");
		$mysqli->close();

		$mysqli = new mysqli($GLOBALS["DB_HOST"], $GLOBALS["DB_USER"], $GLOBALS["DB_PASSWD"]);
		if ($mysqli->connect_errno === 0)
		    {
			$result = $mysqli->query("CREATE DATABASE IF NOT EXISTS " . $mysqli->real_escape_string($dbName) . " DEFAULT CHARSET = 'utf8'");
			if ($result !== false)
			    {
				$created = true;
			    }

			if ($this->_needToDrop === true)
			    {
				$mysqli->select_db($dbName);
				$result = $mysqli->query("SHOW TABLES");

				if ($result->num_rows > 0)
				    {
					$tableNames = array();
					while ($row = $result->fetch_row())
					    {
						$tableNames[] = $row[0];
					    }

					$result = $mysqli->query("SET FOREIGN_KEY_CHECKS = 0");

					foreach ($tableNames as $table)
					    {
						$mysqli->query("DROP TABLE IF EXISTS " . $mysqli->real_escape_string($table));
					    }

					$result = $mysqli->query("SET FOREIGN_KEY_CHECKS = 1");
				    }
			    } //end if

			$mysqli->close();
		    } //end if

		return $created;
	    } //end _createMySQLdatabase()


	/**
	 * Create PosgreSQL database if needed
	 *
	 * @param string $dbName Expected database name
	 *
	 * @return bool
	 */

	private function _createPostgreSQLdatabase($dbName)
	    {
		$created = false;

		$parameters  = "host=" . $GLOBALS["DB_HOST"] . " dbname=postgres";
		$parameters .= " user=" . $GLOBALS["DB_USER"] . " password=" . $GLOBALS["DB_PASSWD"] . " options='--client_encoding=UTF8'";
		$connection  = pg_connect($parameters);
		if (is_resource($connection) === true)
		    {
			$result = pg_query(
			    $connection,
			    "SELECT datname FROM pg_database WHERE datistemplate = false AND datname = '" . pg_escape_string($connection, $dbName) . "'"
			);
			if ($result !== false)
			    {
				if (pg_fetch_row($result) !== false)
				    {
					$created = true;
				    }
			    }

			if ($created === false)
			    {
				$result = pg_query($connection, "CREATE DATABASE " . pg_escape_string($connection, $dbName));
				if ($result !== false)
				    {
					$created = true;
				    }
			    }

			pg_close($connection);

			if ($this->_needToDrop === true)
			    {
				$this->_dropPostreSQLTables($dbName);
			    } //end if
		    } //end if

		return $created;
	    } //end _createPostgreSQLdatabase()


	/**
	 * Drop all tables in PostgreSQL DB
	 *
	 * @param string $dbName Expected database name
	 *
	 * @return void
	 */

	private function _dropPostreSQLTables($dbName)
	    {
		$parameters  = "host=" . $GLOBALS["DB_HOST"] . " dbname=" . $dbName;
		$parameters .= " user=" . $GLOBALS["DB_USER"] . " password=" . $GLOBALS["DB_PASSWD"] . " options='--client_encoding=UTF8'";
		$connection  = pg_connect($parameters);

		$result = pg_query(
			   $connection,
			   "SELECT DISTINCT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES " .
			   "WHERE TABLE_TYPE = 'BASE TABLE' AND TABLE_SCHEMA = 'public' AND TABLE_CATALOG = '" . pg_escape_string($connection, $dbName) . "' " .
			   "ORDER BY TABLE_NAME"
			  );

		if (pg_num_rows($result) > 0)
		    {
			$tableNames = array();
			while ($row = pg_fetch_row($result))
			    {
				$tableNames[] = $row[0];
			    }

			foreach ($tableNames as $table)
			    {
				pg_query($connection, "DROP TABLE \"" . pg_escape_string($connection, $table) . "\"");
			    }
		    }

		pg_close($connection);
	    } //end _dropPostreSQLTables()


	/**
	 * Setting up personal database
	 *
	 * @return void
	 */

	public function setUp()
	    {
		if ($this->_global === true)
		    {
			if (($this->_createMySQLdatabase($GLOBALS["DB_DBNAME"]) === true) && ($this->_createPostgreSQLdatabase($GLOBALS["DB_DBNAME"]) === true))
			    {
				$this->_realDBName = $GLOBALS["DB_DBNAME"];
			    }
		    }
		else
		    {
			$userName = $this->getRequestedUserName();
			if ($userName === false)
			    {
				$userName = $this->getUserName();
			    }

			if ($userName !== false)
			    {
				$dbName = $GLOBALS["DB_DBNAME"] . "_" . $userName;

				if (($this->_createMySQLdatabase($dbName) === true) && ($this->_createPostgreSQLdatabase($dbName) === true))
				    {
					$this->_injections["DB_DBNAME"] = $dbName;
					$this->_realDBName              = $dbName;
				    }
			    } //end if
		    } //end if
	    } //end setUp()


	/**
	 * Display database info
	 *
	 * @return void
	 */

	public function displayMessages()
	    {
		if ($this->_injectionFailed === false)
		    {
			echo "Your personal database parameters:\n";
			echo " - host:          " . $GLOBALS["DB_HOST"] . "\n";
			echo " - username:      " . $GLOBALS["DB_USER"] . "\n";
			echo " - password:      " . $GLOBALS["DB_PASSWD"] . "\n";
			echo " - database name: " . $this->_realDBName . "\n";
		    }
		else
		    {
			echo "Database personalizing:";
			echo "Errors occurred while redefining phpunit.xml variables.";
		    }
	    } //end displayMessages()


    } //end class

?>
