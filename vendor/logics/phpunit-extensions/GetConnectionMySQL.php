<?php

/**
 * PHP version 5.6
 *
 * @package Logics\Tests
 */

namespace Logics\Tests;

use \Logics\Foundation\SQL\MySQLdatabase;

/**
 * GetConnection trait for MySQL
 *
 * Provides MySQL connection for tests
 *
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://opensource.org/licenses/MIT MIT License
 * @version   SVN: $Date: 2016-08-17 22:45:36 +0900 (Wed, 17 Aug 2016) $ $Revision: 232 $
 * @link      $HeadURL: https://open.logics.net.au/phpunit-extensions/tags/0.2.5/GetConnectionMySQL.php $
 *
 * @codeCoverageIgnore
 */

trait GetConnectionMySQL
    {
	use GetConnection;

	/**
	 * Get SQL database connection
	 *
	 * @return SQLdatabase
	 */

	final public function getSQLconnection()
	    {
		return new MySQLdatabase($GLOBALS["DB_HOST"], $GLOBALS["DB_DBNAME"], $GLOBALS["DB_USER"], $GLOBALS["DB_PASSWD"]);
	    } //end getSQLconnection()


    } //end trait

?>
