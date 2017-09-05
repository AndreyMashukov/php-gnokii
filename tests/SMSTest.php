<?php

namespace Tests;

use \AM\SMS\SMS;
use \PHPUnit\Framework\TestCase;
use \Exception;
use \DateTime;
use \DateTimeZone;

class SMSTest extends TestCase
    {

	/**
	 * Should can construct from array
	 *
	 * @return void
	 */

	public function testShouldCanConstructFromArray()
	    {
		for ($i = 1; $i <= 2; $i++)
		    {
			$array = json_decode(file_get_contents(__DIR__ . "/sms/" . $i . ".json"), true);
			$sms   = new SMS($array);

			$datetime = new DateTime($array["datetime"]["date"], new DateTimeZone($array["datetime"]["timezone"]));

			$this->assertEquals($sms->datetime, $datetime->setTimezone(new DateTimeZone("UTC")));
			$this->assertEquals($sms->text, $array["text"]);
			$this->assertEquals($sms->memory["type"], $array["memory"]["type"]);
			$this->assertEquals($sms->memory["slots"], $array["memory"]["slots"]);
			$this->assertEquals($sms->memory["intervals"], [
			    "start" => $array["memory"]["slots"][0],
			    "end" => $array["memory"]["slots"][(count($array["memory"]["slots"]) - 1)],
			    ]);
			$this->assertEquals($sms->sender, $array["sender"]);
			$this->assertEquals($sms->multipart, $array["multipart"]);
			$this->assertEquals($sms->read, $array["read"]);
		    } //end for

	    } //end testShouldCanConstructFromArray()


	/**
	 * Should convert to json if user use class as string
	 *
	 * @return void
	 */

	public function testShouldConvertToJsonIfUserUseClassAsString()
	    {
		for ($i = 1; $i <= 2; $i++)
		    {
			$array = json_decode(file_get_contents(__DIR__ . "/sms/" . $i . ".json"), true);
			$sms   = new SMS($array);
			$datetime = new DateTime($array["datetime"]["date"], new DateTimeZone($array["datetime"]["timezone"]));

			$array["datetime"] = $datetime->setTimezone(new DateTimeZone("UTC"));

			$this->assertEquals((string) $sms, json_encode($array));
		    } //end for

	    } //end testShouldConvertToJsonIfUserUseClassAsString()


    } //end class

?>
