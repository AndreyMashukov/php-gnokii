<?php

/**
 * PHP version 5.6
 *
 * @package Logics\Tests
 */

namespace Logics\Tests;

use \DOMNamedNodeMap;

/**
 * ArrayAccess trait for SerializeableSimpleXMLElement
 *
 * @author    Anastasia Bashkirtseva <anastasia@logics.net.au>
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://opensource.org/licenses/MIT MIT License
 * @version   SVN: $Date: 2016-08-17 22:45:36 +0900 (Wed, 17 Aug 2016) $ $Revision: 232 $
 * @link      $HeadURL: https://open.logics.net.au/phpunit-extensions/tags/0.2.5/Database/SerializeableSimpleXMLElementArrayAccess.php $
 */

trait SerializeableSimpleXMLElementArrayAccess
    {

	/**
	 * Assigns a value to the specified offset
	 *
	 * @param string $offset The offset to assign the value to
	 * @param string $value  The value to set
	 *
	 * @return void
	 */

	public function offsetSet($offset, $value)
	    {
		$this->_element->setAttribute($offset, $value);
	    } //end offsetSet()


	/**
	 * Whether offset exists
	 *
	 * @param string $offset An offset to check for
	 *
	 * @return bool
	 */

	public function offsetExists($offset)
	    {
		if ($this->_element instanceof DOMNamedNodeMap)
		    {
			return ($this->_element->getNamedItem($offset) !== null);
		    }
		else
		    {
			return $this->_element->hasAttribute($offset);
		    }
	    } //end offsetExists()


	/**
	 * Unsets an offset
	 *
	 * @param string $offset The offset to unset
	 *
	 * @return void
	 */

	public function offsetUnset($offset)
	    {
		$this->_element->removeAttribute($offset);
	    } //end offsetUnset()


	/**
	 * Returns the value at specified offset
	 *
	 * @param string $offset The offset to retrieve
	 *
	 * @return string
	 */

	public function offsetGet($offset)
	    {
		if ($this->_element instanceof DOMNamedNodeMap)
		    {
			return $this->_element->getNamedItem($offset)->nodeValue;
		    }
		else
		    {
			return $this->_element->getAttributeNode($offset)->value;
		    }
	    } //end offsetGet()


    } //end trait

?>