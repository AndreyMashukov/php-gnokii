<?php

namespace AM\SMS;

use \Exception;

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
	 *
	 * @throws Exception Gnokii command is not set
	 *
	 * @exceptioncode EXCEPTION_GNOKII_COMMAND_IS_NOT_SET
	 */

	public function send(string $phone, string $message):bool
	    {
		if (defined("GNOKII_COMMAND") === false)
		    {
			throw new Exception("Gnokii command is not set", EXCEPTION_GNOKII_COMMAND_IS_NOT_SET);
		    } //end if

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

    } //end class


?>
