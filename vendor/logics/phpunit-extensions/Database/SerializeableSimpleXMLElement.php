<?php

/**
 * PHP version 5.6
 *
 * @package Logics\Tests
 */

namespace Logics\Tests;

use \ArrayAccess;
use \DOMElement;
use \DOMNamedNodeMap;
use \DOMXPath;
use \Iterator;

/**
 * SerializeableSimpleXMLElement serializing SimpleXMLElement
 *
 * @author    Anastasia Bashkirtseva <anastasia@logics.net.au>
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://opensource.org/licenses/MIT MIT License
 * @version   SVN: $Date: 2016-09-20 16:16:09 +0900 (Tue, 20 Sep 2016) $ $Revision: 241 $
 * @link      $HeadURL: https://open.logics.net.au/phpunit-extensions/tags/0.2.5/Database/SerializeableSimpleXMLElement.php $
 */

class SerializeableSimpleXMLElement implements Iterator, ArrayAccess
    {

	use SerializeableSimpleXMLElementAccessor;

	use SerializeableSimpleXMLElementArrayAccess;

	/**
	 * ELement
	 *
	 * @var DOMElement
	 */
	private $_element;

	/**
	 * Elements
	 *
	 * @var array
	 */
	private $_elements;

	/**
	 * Position of iterator
	 *
	 * @var int
	 */
	public $position = 0;

	/**
	 * Instance this class
	 *
	 * @param DOMElement|DOMNamedNodeMap $element Element DOMdocument
	 *
	 * @return void
	 */

	public function __construct($element)
	    {
		$this->_element  = $element;
		$this->_elements = array();
		if ($this->_element instanceof DOMElement)
		    {
			foreach ($this->_element->childNodes as $idx => $node)
			    {
				if ($node instanceof DOMElement)
				    {
					$this->_elements[] = $idx;
				    }
			    }
		    }

		$this->rewind();
	    } //end __construct()


	/**
	 * Gets the name of the XML element
	 *
	 * @return string
	 */

	public function getName()
	    {
		return $this->_element->nodeName;
	    } //end getName()


	/**
	 * Returns a string
	 *
	 * @return string
	 */

	public function __toString()
	    {
		return $this->_element->nodeValue;
	    } //end __toString()


	/**
	 * Finds children of given node
	 *
	 * @return SerializeableSimpleXMLElement
	 */

	public function children()
	    {
		return $this;
	    } //end children()


	/**
	 * Runs XPath query on XML data
	 *
	 * @param string $expression An XPath path
	 *
	 * @return array
	 */

	public function xpath($expression)
	    {
		$xpath = new DOMXPath($this->_element->ownerDocument);
		$list  = $xpath->query($this->_element->getNodePath() . "/" . $expression);
		$nodes = array();
		foreach ($list as $node)
		    {
			$nodes[] = $this->_newInstance($node);
		    }

		return $nodes;
	    } //end xpath()


	/**
	 * Returns the attributes
	 *
	 * @return SerializeableSimpleXMLElement | null
	 */

	public function attributes()
	    {
		if ($this->_element instanceof DOMElement)
		    {
			return $this->_newInstance($this->_element->attributes);
		    }
		else
		    {
			return null;
		    }
	    } //end attributes()


	/**
	 * Create new instance this class
	 *
	 * @param string $item Item instance
	 *
	 * @return SerializeableSimpleXMLElement
	 */

	private function _newInstance($item)
	    {
		return new SerializeableSimpleXMLElement($item);
	    } //end _newInstance()


	/**
	 * Rewind the Iterator to the first element
	 *
	 * @return void
	 */

	public function rewind()
	    {
		$this->position = 0;
	    } //end rewind()


	/**
	 * Return the current element
	 *
	 * @return SerializeableSimpleXMLElement
	 */

	public function current()
	    {
		if ($this->_element instanceof DOMNamedNodeMap)
		    {
			return $this->_newInstance($this->_element->item($this->position));
		    }
		else
		    {
			return $this->_newInstance($this->_element->childNodes->item($this->_elements[$this->position]));
		    }
	    } //end current()


	/**
	 * Return the key of the current element
	 *
	 * @return string
	 */

	public function key()
	    {
		if ($this->_element instanceof DOMNamedNodeMap)
		    {
			return $this->_element->item($this->position)->nodeName;
		    }
		else
		    {
			return $this->_element->childNodes->item($this->_elements[$this->position])->nodeName;
		    }
	    } //end key()


	/**
	 * Move forward to next element
	 *
	 * @return void
	 */

	public function next()
	    {
		++$this->position;
	    } //end next()


	/**
	 * Checks if current position is valid
	 *
	 * @return bool
	 */

	public function valid()
	    {
		if ($this->_element instanceof DOMNamedNodeMap)
		    {
			return ($this->_element->item($this->position) !== null);
		    }
		else
		    {
			return $this->position < count($this->_elements);
		    }
	    } //end valid()


    } //end class

?>