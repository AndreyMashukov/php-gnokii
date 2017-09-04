<?php

namespace Tests;

use \AM\SMS\PhpGnokii;
use \PHPUnit\Framework\TestCase;
use \Exception;

/**
 * @runTestsInSeparateProcesses
 */

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
		define("GNOKII_COMMAND", "sh " . __DIR__ . "/mock/mock.sh");
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
		define("GNOKII_COMMAND", "sh " . __DIR__ . "/mock/mock.sh");
		define("SMSC_NUMBER", "+79043490000");

		$sender = new PhpGnokii();
		$this->assertTrue($sender->send("+79526191914", "test"));
	    } //end testShouldAllowToSendSms()


	/**
	 * Should not allow to send sms if gnokii command is not defined
	 *
	 * @return void
	 *
	 * @exceptioncode EXCEPTION_GNOKII_COMMAND_IS_NOT_SET
	 */

	public function testShouldNotAllowToSendSmsIfGnokiiCommandIsNotDefined()
	    {
		define("SMSC_NUMBER", "+79043490000");

		$sender = new PhpGnokii();
		define("EXCEPTION_GNOKII_COMMAND_IS_NOT_SET", 1);
		$this->expectException(Exception::class);
		$this->expectExceptionCode(EXCEPTION_GNOKII_COMMAND_IS_NOT_SET);
		$sender->send("+79526191914", "test");
	    } //end testShouldNotAllowToSendSmsIfGnokiiCommandIsNotDefined()


	/**
	 * Should allow to add gnokii config file path
	 *
	 * @return void
	 *
	 * @exceptioncode EXCEPTION_INVALID_CONFIG_FILE
	 */

	public function testShouldAllowToAddGnokiiConfigFilePath()
	    {
		$sender = new PhpGnokii("+79998887766", "+7", __DIR__ . "/datasets/valid.config");
		define("GNOKII_COMMAND", "sh " . __DIR__ . "/mock/mock.sh");
		$this->assertTrue($sender->send("+79526191914", "test"));

		define("EXCEPTION_INVALID_CONFIG_FILE", 1);
		$this->expectException(Exception::class);
		$this->expectExceptionCode(EXCEPTION_INVALID_CONFIG_FILE);
		$sender = new PhpGnokii("+79998887766", "+7", __DIR__ . "/datasets/invalid.config");
	    } //end testShouldAllowToAddGnokiiConfigFilePath()


    } //end class

?>
