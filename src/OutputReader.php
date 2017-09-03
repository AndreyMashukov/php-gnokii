<?php

namespace AM\Gnokii;

use \Exception;

class OutputReader
    {

	/**
	 * Output type
	 *
	 * @var string
	 */
	private $_type;

	/**
	 * Prepare to work
	 *
	 * @param string $type Type of output
	 *
	 * @return void
	 *
	 * @throws Exception Invalid output type
	 *
	 * @exceptioncode EXCEPTION_INVALID_OUTPUT_TYPE
	 */

	public function __construct(string $output)
	    {
		$validtypes = ["getsms"];

		if (in_array($output, $validtypes) === true)
		    {
			$this->_type = $output;
		    }
		else
		    {
			throw new Exception("Invalid output type", EXCEPTION_INVALID_OUTPUT_TYPE);
		    } //end if

	    } //end __construct()


	/**
	 * Read output
	 *
	 * @param string $output Output text to read
	 *
	 * @return array Result of read
	 */

	public function read(string $output):array
	    {
		$methods = [
		    "getsms" => "_readGetsmsOutput",
		];

		$method = $methods[$this->_type];

		return $this->$method($output);
	    } //end read()


	/**
	 * Read getsms gnokii output (gnokii --getsms (SM|ME){1} [0-9]+ [0-9]+)
	 *
	 * @param string $output Output text
	 *
	 * @return array Reading result
	 */

	private function _readGetsmsOutput(string $output):array
	    {
		$result = [];

		$pattern = "/(?P<start_loc>[0-9]+)\.\s+Inbox\s+Message\s+\((?P<read>(Read|Unread)+)\)\n" .
		    "Date\/time:\s+(?P<datetime>[0-9]{2}\/[0-9]{2}\/[0-9]{4}\s+[0-9]{2}:[0-9]{2}:[0-9]{2}\s+\+[0-9]{4})\n" .
		    "Sender:\s+(?P<sender>[+]?[A-Z-a-z0-9-_.]+)\s+Msg\s+Center:\s+[+][0-9]{11,15}\n" .
		    "((Linked|Text){1}:\n)?" .
		    "(Linked\s+\((?P<current_link>[0-9]+)\/(?P<total_links>[0-9]+)\):\n)?" .
		    "(?P<text>.*)/ui";

		if (preg_match($pattern, $output, $out) > 0)
		    {
			if ($out["current_link"] !== "" && $out["total_links"] !== "")
			    {
				if ($out["current_link"] === $out["total_links"])
				    {
					$start = (($out["start_loc"] - $out["total_links"]) + 1);
					$end   = $out["start_loc"];
				    }
				else
				    {
					$step_s = (1 - $out["current_link"]);
					$start  = ($out["start_loc"] + $step_s);
					$step_e = ($out["total_links"] - $out["current_link"]);
					$end    = ($out["start_loc"] + $step_e);
				    } //end if

				$result = [
				    "locations" => ["start" => (int) $start, "current" => (int) $out["start_loc"], "end" => (int) $end],
				    "sender"    => $out["sender"],
				    "linked"    => true,
				    "links"     => ["current" => (int) $out["current_link"], "total" => (int) $out["total_links"]],
				    "text"      => $out["text"],
				    "read"      => (($out["read"] === "Read") ? true : false),
				    "datetime"  => $out["datetime"],
				];
			    }
			else
			    {
				$result = [
				    "locations" => ["start" => (int) $out["start_loc"], "current" => (int) $out["start_loc"], "end" => (int) $out["start_loc"]],
				    "sender"   => $out["sender"],
				    "linked"   => false,
				    "text"     => $out["text"],
				    "read"     => (($out["read"] === "Read") ? true : false),
				    "datetime" => $out["datetime"],
				];
			    } //end if

		    } //end if

		return $result;
	    } //end _readGetsmsOutput()


    } //end class


?>
