<?php

namespace AM\Gnokii;

use \Exception;
use \AM\Gnokii\OutputReader;
use \DateTime;
use \AM\SMS\SMS;

class PhpGnokii
    {

	/**
	 * Sms center number
	 *
	 * @var string
	 */
	private $_smsc;

	/**
	 * Country code
	 *
	 * @var string
	 */
	private $_code;

	/**
	 * Config file
	 *
	 * @var string
	 */
	private $_config;

	/**
	 * Prepare php gnokii to work
	 *
	 * @param string $smsc        Sms center number
	 * @param string $countrycode Country phone code
	 * @param string $config      Gnokii config
	 *
	 * @return void
	 *
	 * @throws Exception Invalid SMSC number
	 *
	 * @exceptioncode EXCEPTION_INVALID_SMSC_NUMBER
	 */

	public function __construct(string $smsc = "", string $countrycode = "+7", string $config = "/etc/gnokiirc")
	    {
		$this->_checkGnokii();

		if ($this->_validateConfig($config) === true)
		    {
			$this->_config = $config;
		    } //end if

		$this->_code = $countrycode;

		if ($smsc === "" && defined("SMSC_NUMBER") === true)
		    {
			$smsc = SMSC_NUMBER;
		    } //end if

		if ($this->_validatePhoneNumber($smsc) === true)
		    {
			$this->_smsc = $smsc;
		    }
		else
		    {
			throw new Exception("Invalid SMSC number", EXCEPTION_INVALID_SMSC_NUMBER);
		    } //end if

	    } //end __construct()


	/**
	 * Validate config file
	 *
	 * @param string $config Config file path
	 *
	 * @return bool Validate result
	 *
	 * @throws Exception Invalid config file
	 *
	 * @exceptioncode EXCEPTION_INVALID_CONFIG_FILE
	 */

	private function _validateConfig(string $config):bool
	    {
		$content = file_get_contents($config);

		$pattern = "/\[global\]\n" .
		    "port\s?=\s?\/dev\/ttyUSB[0-9]{1,2}\n" .
		    "model\s?=\s?(AT|AT-HW|Series60){1}\n" .
		    "(#?initlength\s?=\s?default\n)?" .
		    "connection\s?=\s?serial\n" .
		    "(#?use_locking\s?=\s?no\n)?" .
		    "(#?serial_baudrate\s?=\s?115200\n)?" .
		    "(#?handshake\s?=\s?hardware\n)?/ui";

		if (preg_match($pattern, $content) > 0)
		    {
			return true;
		    }
		else
		    {
			throw new Exception("Invalid config file", EXCEPTION_INVALID_CONFIG_FILE);
		    } //end if

	    } //end _validateConfig()


	/**
	 * Validate phone number
	 *
	 * @param string $phonenumber Mobile phone number
	 *
	 * @return bool Validate result
	 */

	private function _validatePhoneNumber(string $phonenumber):bool
	    {
		return ((preg_match("/^" . str_replace("+", "\+", $this->_code) . "[0-9]{10}$/ui", $phonenumber) > 0) ? true : false);
	    } //end _validatePhoneNumber()


	/**
	 * Send message
	 *
	 * @param string $phone   Phone number to send
	 * @param string $message Message to send
	 *
	 * @return bool Send result
	 */

	public function send(string $phone, string $message):bool
	    {

		if ($this->_validatePhoneNumber($phone) === true)
		    {
			exec("echo '" . $message . "' | " . GNOKII_COMMAND . " --config " . $this->_config . " --sendsms '" . $phone . "' --smsc '" . $this->_smsc . "'", $output, $result);
			if ($result === 0)
			    {
				return true;
			    }
			else
			    {
				return false;
			    } //end if

		    }
		else
		    {
			return false;
		    } //end if

	    } //end send()


	/**
	 * Check gnokii to ready
	 *
	 * @return void
	 *
	 * @throws Exception Gnokii command is not set
	 *
	 * @exceptioncode EXCEPTION_GNOKII_COMMAND_IS_NOT_SET
	 */

	private function _checkGnokii()
	    {
		if (defined("GNOKII_COMMAND") === false)
		    {
			throw new Exception("Gnokii command is not set", EXCEPTION_GNOKII_COMMAND_IS_NOT_SET);
		    } //end if

	    } //end _checkGnokii()


	/**
	 * Get sms from device
	 *
	 * @param string $memorytype Type of device memory
	 *
	 * @return array Messages
	 *
	 * @throws Exception Invalid memory type
	 *
	 * @exceptioncode EXCEPTION_INVALID_MEMORY_TYPE
	 */

	public function getSms(string $memorytype, int $count = 30):array
	    {
		$smsarray = [];
		$types    = ["SM", "ME", "MT", "BM", "SR"];

		if (in_array($memorytype, $types) === true)
		    {
			for ($i = 0; $i <= $count; $i++)
			    {
				$message = $this->_getMessage($memorytype, $i);
				if ($message !== false)
				    {
					if ($message["linked"] === true && $message["locations"]["start"] === $message["locations"]["current"])
					    {
						$messages = [$message];
						$slots = $this->_getSlots($message["locations"]);
						unset($slots[0]);
						foreach ($slots as $slot)
						    {
							$messages[] = $this->_getMessage($memorytype, $slot);
						    } //end foreach

						$sms        = $this->_combineMessages($messages, $memorytype);
						$smsarray[] = new SMS($sms);
					    }
					else if ($message["linked"] === false)
					    {
						$sms        = $this->_createSms($message, $memorytype);
						$smsarray[] = new SMS($sms);
					    } //end if

				    } //end if

			    } //end for

		    }
		else
		    {
			throw new Exception("Invalid memory type", EXCEPTION_INVALID_MEMORY_TYPE);
		    } //end if

		return $smsarray;
	    } //end if


	/**
	 * Combine messages
	 *
	 * @param array  $messages   Messages to combine
	 * @param string $memorytype Memory type of device
	 *
	 * @return array Sms
	 */

	private function _combineMessages(array $messages, string $memorytype):array
	    {
		$sms = $this->_createSms($messages[0], $memorytype);
		unset($messages[0]);

		foreach ($messages as $message)
		    {
			if ($message !== false)
			    {
				$part = $this->_createSms($message, $memorytype);
				$sms["text"]     .= $part["text"];
				unset($part["text"]);
				foreach ($part as $key => $value)
				    {
					$sms[$key] = $value;
				    } //end foreach

			    } //end if

		    } //end foreach

		return $sms;
	    } //end _combineMessages()


	/**
	 * Get message
	 *
	 * @param string $memorytype Memory type of device
	 * @param int    $position   Message position in memory
	 *
	 * @return mixed: string Message or false
	 */

	private function _getMessage(string $memorytype, $position)
	    {
		$output = [];
		exec(GNOKII_COMMAND . " --config " . $this->_config . " --getsms " . $memorytype . " " . $position, $output, $result);
		if ($result === 0)
		    {
			$reader  = new OutputReader("getsms");
			$message = $reader->read(implode("\n", $output));
			if (count($message) > 0)
			    {
				return $message;
			    }
			else
			    {
				return false;
			    } //end if

		    }
		else
		    {
			return false;
		    } //end if

	    } //end _getMessage()


	/**
	 * Create sms
	 *
	 * @param array  $message    Sms data
	 * @param string $memorytype Memory type
	 *
	 * @return array SMS
	 */

	private function _createSms(array $message, string $memorytype):array
	    {
		$datetime = new DateTime($message["datetime"]);

		return [
		    "datetime"  => ["date" => $datetime->format("Y-m-d H:i:s.u"), "timezone" => $datetime->format("P")],
		    "text"      => $message["text"],
		    "memory"    => ["type" => $memorytype, "slots" => $this->_getSlots($message["locations"])],
		    "sender"    => $message["sender"],
		    "multipart" => $message["linked"],
		    "read"      => $message["read"],
		];
	    } //end _createSms()


	/**
	 * Get slots positions
	 *
	 * @param array $location Message locations 
	 *
	 * @return array Slots
	 */

	private function _getSlots(array $locations):array
	    {
		$slots = [];

		$i = $locations["start"];
		for ($i; $i <= $locations["end"]; $i++)
		    {
			$slots[] = $i;
		    } //end for

		return $slots;
	    } //end _getSlots()


	/**
	 * Delete sms
	 *
	 * @param SMS $sms SMS to delete
	 *
	 * @return bool True if gnokii give correct answer
	 */

	public function deleteSMS(SMS $sms):bool
	    {
		exec(GNOKII_COMMAND . " --config " . $this->_config . " --deletesms " . $sms->memory["type"] . " " .
		    $sms->memory["intervals"]["start"] . " " . $sms->memory["intervals"]["end"], $output, $result);

		$expected = [
		    "/GNOKII\s+Version\s+[0-9]{1}\.[0-9]{1,2}\.[0-9]{1,2}/ui",
		];

		foreach ($sms->memory["slots"] as $slot)
		    {
			$expected[] = "/Deleted\s+SMS\s+\(location\s+" . $slot . "\s+from\s+memory\s+" . $sms->memory["type"] . "\)/ui";
		    } //end foreach

		for ($i = 0; $i <= (count($expected) - 1); $i++)
		    {
			if (isset($output[$i]) === true)
			    {
				if (preg_match($expected[$i], trim($output[$i])) === 0)
				    {
					return false;
				    } //end if

			    }
			else
			    {
				return false;
			    } //end if

		    } //end for

		return true;
	    } //end deleteSMS()


    } //end class


?>
