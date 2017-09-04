<?php

/**
 * PHP version 5.6
 *
 * @package Logics\Tests
 */

namespace Logics\Tests;

use \Exception;
use \Serializable;

/**
 * NoTraceException class. Same as Exception but without stack trace information: if trace contains unserializable data then serialization fails.
 *
 * Provides NoTraceException class: Exception without trace information
 *
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://opensource.org/licenses/MIT MIT License
 * @version   SVN: $Date: 2016-08-17 22:45:36 +0900 (Wed, 17 Aug 2016) $ $Revision: 232 $
 * @link      $HeadURL: https://open.logics.net.au/phpunit-extensions/tags/0.2.5/NoTraceException.php $
 *
 * @codeCoverageIgnore
 */

class NoTraceException extends Exception implements Serializable
    {

	/**
	 * Serialize Exception class excluding stack trace
	 *
	 * @return string Exception in serialized form excluding stack trace
	 */

	public function serialize()
	    {
		return serialize(array($this->message, $this->code, $this->file, $this->line));
	    } //end serialize()


	/**
	 * Unserialize Exception class excluding stack trace
	 *
	 * @param string $serialized Exception in serialized form
	 *
	 * @return void
	 */

	public function unserialize($serialized)
	    {
		list($this->message, $this->code, $this->file, $this->line) = unserialize($serialized);
	    } //end unserialize()


    } //end class

?>
