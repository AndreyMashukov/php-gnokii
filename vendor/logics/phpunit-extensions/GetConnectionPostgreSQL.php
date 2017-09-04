<?php

/**
 * PHP version 5.6
 *
 * @package Logics\Tests
 */

namespace Logics\Tests;

use \Logics\Foundation\SQL\PostgreSQLdatabase;

/**
 * GetConnection trait for PostgreSQL
 *
 * Provides PostgreSQL connection for tests
 *
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://opensource.org/licenses/MIT MIT License
 * @version   SVN: $Date: 2016-08-17 22:45:36 +0900 (Wed, 17 Aug 2016) $ $Revision: 232 $
 * @link      $HeadURL: https://open.logics.net.au/phpunit-extensions/tags/0.2.5/GetConnectionPostgreSQL.php $
 *
 * @codeCoverageIgnore
 */

trait GetConnectionPostgreSQL
    {
	use GetConnection;

	/**
	 * Get SQL database connection
	 *
	 * @return SQLdatabase
	 */

	final public function getSQLconnection()
	    {
		return new PostgreSQLdatabase($GLOBALS["DB_HOST"], $GLOBALS["DB_DBNAME"], $GLOBALS["DB_USER"], $GLOBALS["DB_PASSWD"]);
	    } //end getSQLconnection()


    } //end trait

?>
