<?php

/**
 * PHP version 5.6
 *
 * @package Logics\Tests
 */

/**
 * Load vars, consts and includePaths from phpunit.xml for use outside of PHPUnit context
 *
 * @donottranslate
 */

$xml = new XMLReader;
$xml->open(stream_resolve_include_path("phpunit.xml"));

$xml->moveToElement("php");

while ($xml->read())
    {
	if (($xml->name === "php") && ($xml->nodeType === XMLReader::ELEMENT))
	    {
		while ($xml->read())
		    {
			if ($xml->name === "var")
			    {
				$GLOBALS[$xml->getAttribute("name")] = $xml->getAttribute("value");
			    }
			else if ($xml->name === "const")
			    {
				define($xml->getAttribute("name"), $xml->getAttribute("value"));
			    }
			else if ($xml->name === "includePath")
			    {
				$xml->read();
				ini_set("include_path", ini_get("include_path") . ":" . $xml->value);
				$xml->read();
			    }
			else if ($xml->nodeType === XMLReader::END_ELEMENT)
			    {
				break;
			    }
		    }
	    } //end if
    } //end while

require "environment/silent_init.php";

?>
