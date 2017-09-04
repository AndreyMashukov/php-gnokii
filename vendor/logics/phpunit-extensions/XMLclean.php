<?php

/**
 * PHP version 5.6
 *
 * @package Logics\Tests
 */

namespace Logics\Tests;

use \DOMAttr;
use \DOMDocument;
use \DOMNode;
use \DOMXPath;
use \DateTime;

/**
 * XMLclean trait.
 *
 * This trait is used to remove dynamic data from XML documents before calling assertXmlStringEqualsXmlFile()
 *
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://opensource.org/licenses/MIT MIT License
 * @version   SVN: $Date: 2016-08-17 22:45:36 +0900 (Wed, 17 Aug 2016) $ $Revision: 232 $
 * @link      $HeadURL: https://open.logics.net.au/phpunit-extensions/tags/0.2.5/XMLclean.php $
 *
 * @codeCoverageIgnore
 *
 * @donottranslate
 */

trait XMLclean
    {

	/**
	 * Reset time attributes in XML
	 *
	 * @param string $xml        Input XML
	 * @param string $xpathquery XPapth query addressing date/time to reset
	 * @param string $time       Time to reset to
	 * @param string $format     Time format to be stored
	 *
	 * @return string containing XML with time attributes reset
	 */

	private function _resetTime($xml, $xpathquery = "@time", $time = "2014-01-01T00:00:00Z", $format = false)
	    {
		$datetime = new DateTime($time);
		$value    = $datetime->format(($format === false) ? DateTime::W3C : $format);

		$dom = new DOMDocument("1.0", "utf-8");
		$dom->loadXML($xml, LIBXML_PARSEHUGE);
		$xpath = new DOMXPath($dom);
		$time  = $xpath->query("//" . $xpathquery);
		foreach ($time as $item)
		    {
			if ($item instanceof DOMAttr)
			    {
				$item->value = $value;
			    }
			else if ($item instanceof DOMNode)
			    {
				$item->nodeValue = $value;
			    }
		    }

		return $dom->saveXML();
	    } //end _resetTime()


    } //end trait

?>
