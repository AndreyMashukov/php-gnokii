<?php

namespace Tests;

use \AM\Gnokii\PhpGnokii;
use \PHPUnit\Framework\TestCase;
use \Exception;
use \DateTime;
use \DateTimeZone;
use \AM\SMS\SMS;

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

		define("EXCEPTION_GNOKII_COMMAND_IS_NOT_SET", 1);
		$this->expectException(Exception::class);
		$this->expectExceptionCode(EXCEPTION_GNOKII_COMMAND_IS_NOT_SET);
		$sender = new PhpGnokii();
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
		define("GNOKII_COMMAND", "sh " . __DIR__ . "/mock/mock.sh");
		$sender = new PhpGnokii("+79998887766", "+7", __DIR__ . "/datasets/valid.config");
		$this->assertTrue($sender->send("+79526191914", "test"));

		define("EXCEPTION_INVALID_CONFIG_FILE", 1);
		$this->expectException(Exception::class);
		$this->expectExceptionCode(EXCEPTION_INVALID_CONFIG_FILE);
		$sender = new PhpGnokii("+79998887766", "+7", __DIR__ . "/datasets/invalid.config");
	    } //end testShouldAllowToAddGnokiiConfigFilePath()


	/**
	 * Should get sms from device memory
	 *
	 * SM - Read SMS messages from the SIM card. This storage is supported
	 * on every GSM phone, because a SIM card should always be present.
	 * Usually a SIM card can store up to 15 messages.
	 *
	 * ME - Read SMS messages from the modem or mobile phone memory. The
	 * number of messages that can be stored here depends on the size of the phones memory.
	 *
	 * MT - Read SMS messages from all storages on the mobile phone. For instance when
	 * the phone supports "ME" and "SM", the "MT" memory combines the "ME" and "SM" memories as if it was a single storage.
	 *
	 * BM - This storage is only used to read stored incoming cell broadcast messages.
	 * It is normally not used to store SMS messages.
	 *
	 * SR - When you enable status reports when sending SMS messages, the status reports
	 * that are received are stored in this memory. These reports can read the same way as SMS messages.
	 *
	 * @return void
	 *
	 * @exceptioncode EXCEPTION_INVALID_MEMORY_TYPE
	 */

	public function testShouldGetSmsFromDeviceMemory()
	    {
		define("GNOKII_COMMAND", "sh " . __DIR__ . "/mock/mock.sh");
		define("SMSC_NUMBER", "+79043490000");

		$phpgnokii = new PhpGnokii();

		$expected   = [];
		$expected[] = new SMS([
		    "datetime"  => ["date" => "02/09/2017 08:40:07", "timezone" => "+0300"],
		    "text"      => "You balance is 200$",
		    "memory"    => ["type" => "ME", "slots" => [0]],
		    "sender"    => "Provider",
		    "multipart" => false,
		    "read"      => true,
		]);

		$expected[] = new SMS([
		    "datetime"  => ["date" => "04/09/2017 14:17:31", "timezone" => "+0700"],
		    "text"      => "Message part in first message and second part of message",
		    "memory"    => ["type" => "ME", "slots" => [8, 9]],
		    "sender"    => "+79526191914",
		    "multipart" => true,
		    "read"      => false,
		]);

		$messages = $phpgnokii->getSms("ME");
		$this->assertEquals($expected, $messages);
		define("EXCEPTION_INVALID_MEMORY_TYPE", 1);
		$this->expectException(Exception::class);
		$this->expectExceptionCode(EXCEPTION_INVALID_MEMORY_TYPE);
		$messages = $phpgnokii->getSms("INVALID");
	    } //end  testShouldGetSmsFromDeviceMemory()


    } //end class


?>
