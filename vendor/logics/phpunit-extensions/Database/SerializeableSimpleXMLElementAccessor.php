<?php

/**
 * PHP version 5.6
 *
 * @package Logics\Tests
 */

namespace Logics\Tests;

/**
 * SerializeableSimpleXMLElement serializing SimpleXMLElement
 *
 * @author    Anastasia Bashkirtseva <anastasia@logics.net.au>
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://opensource.org/licenses/MIT MIT License
 * @version   SVN: $Date: 2016-08-17 23:15:36 +0930 (Wed, 17 Aug 2016) $ $Revision: 232 $
 * @link      $HeadURL: https://svn.logics.net.au/phpunit-extensions/trunk/Database/SerializeableSimpleXMLElement.php $
 */

trait SerializeableSimpleXMLElementAccessor
    {

	/**
	 * Reads data from node
	 *
	 * @param string $name Node name
	 *
	 * @return string
	 */

	public function __get($name)
	    {
		return $this->xpath("./" . $name);
	    } //end __get()


    } //end trait

?>
