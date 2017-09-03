<?php

namespace Tests;

use \AM\Gnokii\OutputReader;
use \PHPUnit\Framework\TestCase;
use \Exception;

class OutputReaderTest extends TestCase
    {

	/**
	 * Should read cli gnokii output
	 *
	 * @return void
	 */

	public function testShouldReadCliGnokiiOutput()
	    {
		$reader = new OutputReader("getsms");
		$output = "1. Inbox Message (Read)\n" .
		    "Date/time: 02/09/2017 09:10:02 +0300\n" .
		    "Sender: 687 Msg Center: +79043490000\n" .
		    "Linked:\n" .
		    "Linked (1/2):\n" .
		    "Thanks for message";

		$expected = [
		    "locations" => ["start" => 1, "current" => 1, "end" => 2],
		    "sender"    => "687",
		    "linked"    => true,
		    "links"     => ["current" => 1, "total" => 2],
		    "text"      => "Thanks for message",
		    "read"      => true,
		    "datetime"  => "02/09/2017 09:10:02 +0300",
		];

		$result = $reader->read($output);
		$this->assertEquals($expected, $result);

		$output = "2. Inbox Message (Unread)\n" .
		    "Date/time: 02/09/2017 09:10:02 +0300\n" .
		    "Sender: 687 Msg Center: +79043490000\n" .
		    "Linked:\n" .
		    "Linked (3/3):\n" .
		    "Thanks for message";

		$expected = [
		    "locations" => ["start" => 0, "current" => 2, "end" => 2],
		    "sender"    => "687",
		    "linked"    => true,
		    "links"     => ["current" => 3, "total" => 3],
		    "text"      => "Thanks for message",
		    "read"      => false,
		    "datetime"  => "02/09/2017 09:10:02 +0300",
		];

		$result = $reader->read($output);
		$this->assertEquals($expected, $result);

		$output = "3. Inbox Message (Read)\n" .
		    "Date/time: 02/09/2017 09:10:02 +0300\n" .
		    "Sender: 687 Msg Center: +79043490000\n" .
		    "Linked:\n" .
		    "Linked (3/5):\n" .
		    "Thanks for message";

		$expected = [
		    "locations" => ["start" => 1, "current" => 3, "end" => 5],
		    "sender"    => "687",
		    "linked"    => true,
		    "links"     => ["current" => 3, "total" => 5],
		    "text"      => "Thanks for message",
		    "read"      => true,
		    "datetime"  => "02/09/2017 09:10:02 +0300",
		];

		$result = $reader->read($output);
		$this->assertEquals($expected, $result);

		$output = "6. Inbox Message (Unread)\n" .
		    "Date/time: 03/09/2017 18:13:49 +0700\n" .
		    "Sender: +79526191914 Msg Center: +79139869993\n" .
		    "Text:\n" .
		    "Hello";

		$expected = [
		    "locations" => ["start" => 6, "current" => 6, "end" => 6],
		    "sender"    => "+79526191914",
		    "linked"    => false,
		    "text"      => "Hello",
		    "read"      => false,
		    "datetime"  => "03/09/2017 18:13:49 +0700",
		];

		$result = $reader->read($output);
		$this->assertEquals($expected, $result);

	    } //end testShouldReadCliGnokiiOutput()


	/**
	 * Should allow create reader with only valid output types
	 *
	 * @return void
	 *
	 * @exceptioncode EXCEPTION_INVALID_OUTPUT_TYPE
	 */

	public function testShouldAllowCreateReaderWithOnlyValidOutputTypes()
	    {
		define("EXCEPTION_INVALID_OUTPUT_TYPE", 1);
		$this->expectException(Exception::class);
		$this->expectExceptionCode(EXCEPTION_INVALID_OUTPUT_TYPE);
		$reader = new OutputReader("invalid_param");
	    } //end testShouldAllowCreateReaderWithOnlyValidOutputTypes()


    } //end class

?>
