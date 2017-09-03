<?php

namespace Tests;

use \AM\SMS\PhpGnokii;
use \PHPUnit\Framework\TestCase;
use \Exception;

class PhpGnokiiTest extends TestCase
    {

	/**
	 * Should not allow create sender without smsc number
	 *
	 * @return void
	 *
	 * @exceptioncode EXCEPTION_INVALID_SMSC_NUMBER
	 */

	public function testShouldNotAllowCreateSenderWithoutSmscNumber()
	    {
		define("EXCEPTION_INVALID_SMSC_NUMBER", 1);
		$this->expectException(Exception::class);
		$this->expectExceptionCode(EXCEPTION_INVALID_SMSC_NUMBER);
		$sender = new PhpGnokii();
	    } //end testShouldNotAllowCreateSenderWithoutSmscNumber()


	/**
	 * Should allow to send sms
	 *
	 * @return void
	 */

	public function testShouldAllowToSendSms()
	    {
		define("SMSC_NUMBER", "+79043490000");

		$sender = new PhpGnokii();
		$this->assertTrue($sender->send("+79526191914", "test"));
	    } //end testShouldAllowToSendSms()


    } //end class

?>
