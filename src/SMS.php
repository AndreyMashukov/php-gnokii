<?php

namespace AM\SMS;

use \DateTime;
use \DateTimeZone;

class SMS
    {

	/**
	 * Construct sms from array
	 *
	 * @param array $smsdata SMS data
	 *
	 * @return void
	 */

	public function __construct(array $smsdata)
	    {
		$datetime = new DateTime($smsdata["datetime"]["date"], new DateTimeZone($smsdata["datetime"]["timezone"]));

		$this->datetime = $datetime->setTimezone(new DateTimeZone("UTC"));
		$this->memory   = [
		    "slots"     => $smsdata["memory"]["slots"],
		    "type"      => $smsdata["memory"]["type"],
		    "intervals" => [
			    "start" => $smsdata["memory"]["slots"][0],
			    "end"   => $smsdata["memory"]["slots"][(count($smsdata["memory"]["slots"]) - 1)],
		    ],
		];

		unset($smsdata["memory"]);
		unset($smsdata["datetime"]);
		foreach ($smsdata as $property => $value)
		    {
			$this->$property = $value;
		    } //end foreach

	    } //end __construct()


	/**
	 * SMS to json string
	 *
	 * @return string Serialised SMS to Json
	 */

	public function __toString():string
	    {
		return json_encode([
		    "datetime"  => $this->datetime,
		    "text"      => $this->text,
		    "memory"    => ["type" => $this->memory["type"], "slots" => $this->memory["slots"]],
		    "sender"    => $this->sender,
		    "multipart" => $this->multipart,
		    "read"      => $this->read,
		]);
	    } //end __toString()


    } //end class


?>
