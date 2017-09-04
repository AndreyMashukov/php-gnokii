<?php

/**
 * PHP version 5.6
 *
 * @package Logics\Tests
 */

namespace Logics\Tests;

use \Exception;
use \Logics\Foundation\SmartyExtended\XHTMLvalidator;
use \Logics\Foundation\XML\XMLmapping;
use \Logics\Foundation\XML\XMLparser;
use \SimpleXMLElement;

/**
 * Basic test cases for ScenarioPlayer based scripts.
 *
 * Tests generation of robots.txt and sitemap.xml . Then tries to open all URIs listed in sitemap.xml - it is expected that all
 * pages in sitemap.xml can be generated without any hindrance by direct call.
 *
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://opensource.org/licenses/MIT MIT License
 * @version   SVN: $Date: 2017-01-21 00:00:17 +0800 (Sat, 21 Jan 2017) $ $Revision: 270 $
 * @link      $HeadURL: https://open.logics.net.au/phpunit-extensions/tags/0.2.5/PHPUnit_Extensions_ScenarioPlayer_TestCase.php $
 *
 * @donottranslate
 */

abstract class PHPUnit_Extensions_ScenarioPlayer_TestCase extends PHPUnit_Extensions_Script_TestCase
    {

	use ScriptExecutor;

	use XMLmapping;

	use XMLparser;

	/**
	 * Test robots.txt and sitemap.xml generation
	 *
	 * @return void
	 */

	public function testGeneratesProperRobotsAndSitemapXmlAndAllPagesListedInSitemapAreAccessible()
	    {
		$robots = $this->_execute($this->script, array(), "GET", array(), "..:", "/robots.txt");
		$this->assertRegExp("|Sitemap: http://.*/sitemap.xml|", $robots);

		$sitemap = $this->_execute($this->script, array(), "GET", array(), "..:", "/sitemap.xml");
		try
		    {
			$mapping = array("http://www.w3.org/2001/xml.xsd" => __DIR__ . "/externalschemas/www.w3.org/2001/03/xml.xsd");
			$this->_registerSchemaMappings($mapping);

			$type = $this->validateDocument(
				 $sitemap,
				 array(
				  "urlset"       => __DIR__ . "/multilanguagesitemap.xsd",
				  "sitemapindex" => __DIR__ . "/externalschemas/www.sitemaps.org/0.9/siteindex.xsd",
				 )
				);
			$this->assertEquals("urlset", $type);
		    }
		catch (Exception $e)
		    {
			$this->fail($e->getMessage());
		    }

		if (class_exists(XHTMLvalidator::CLASS) === true)
		    {
			$urls = new SimpleXMLElement($sitemap);
			foreach ($urls->url as $url)
			    {
				$path  = parse_url($url->loc, PHP_URL_PATH);
				$query = parse_url($url->loc, PHP_URL_QUERY);
				$uri   = $path . (($query === null) ? "" : "?" . $query);
				$html  = $this->_execute($this->script, array(), "GET", array(), "..:", $uri);
				$this->assertTrue(XHTMLvalidator::validate($html, true));
			    }
		    }
	    } //end testGeneratesProperRobotsAndSitemapXmlAndAllPagesListedInSitemapAreAccessible()


    } //end class

?>
