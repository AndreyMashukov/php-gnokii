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
	 * Prepare php gnokii to work
	 *
	 * @param string $smsc        Sms center number
	 * @param string $countrycode Country phone code
	 *
	 * @return void
	 *
	 * @throws Exception Invalid SMSC number
	 *
	 * @exceptioncode EXCEPTION_INVALID_SMSC_NUMBER
	 */

	public function __construct(string $smsc = "", $countrycode = "+7")
	    {
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
			exec("echo '" . $message . "' | " . GNOKII_COMMAND . " --sendsms '" . $phone . "' --smsc '" . $this->_smsc . "'", $output, $result);
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
