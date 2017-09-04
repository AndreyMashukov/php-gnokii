<?php

/**
 * PHP version 5.6
 *
 * @package Logics\Tests
 */

namespace Logics\Tests;

use \Logics\Foundation\BaseLib\ReadOnlyProperties;
use \Logics\Foundation\BaseLib\Request;

/**
 * Abbreviated RequestValidator for test purposes - does not really performs any validation
 *
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://opensource.org/licenses/MIT MIT License
 * @version   SVN: $Date: 2016-09-06 23:08:32 +0900 (Tue, 06 Sep 2016) $ $Revision: 236 $
 * @link      $HeadURL: https://open.logics.net.au/phpunit-extensions/tags/0.2.5/RequestValidator.php $
 */

class RequestValidator extends Request
    {

	use ReadOnlyProperties;

	/**
	 * Validate run time variables and store them in local array
	 *
	 * @return void
	 */

	public function __construct()
	    {
		if (isset($_GET) === true)
		    {
			foreach ($_GET as $name => $value)
			    {
				$this->readonlyproperties[$name] = $value;
			    }
		    }

		if (isset($_POST) === true)
		    {
			foreach ($_POST as $name => $value)
			    {
				$this->readonlyproperties[$name] = $value;
			    }
		    }

		if (isset($_COOKIE) === true)
		    {
			foreach ($_COOKIE as $name => $value)
			    {
				$this->readonlyproperties[$name] = $value;
			    }
		    }
	    } //end __construct()


	/**
	 * Getting the validated list of uploaded files.
	 *
	 * @return array
	 *
	 * @untranslatable tmp_name
	 * @untranslatable file
	 */

	public function getFiles()
	    {
		$result = array();
		foreach ($_FILES as $idx => $file)
		    {
			foreach ($file as $type => $record)
			    {
				if ($type === "tmp_name")
				    {
					$type = "file";
				    }

				foreach ($record as $hash => $value)
				    {
					$result[$idx][$hash][$type] = $value;
				    }
			    }
		    }

		return $result;
	    } //end getFiles()


    } //end class

?>
