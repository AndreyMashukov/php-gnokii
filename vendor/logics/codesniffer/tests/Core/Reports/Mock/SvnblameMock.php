<?php

/**
 * PHP version 5
 *
 * @package Logics\Tests\BuildTools\CodeSniffer
 */

namespace Logics\Tests\BuildTools\CodeSniffer;

use \Exception;
use \Logics\BuildTools\CodeSniffer\SvnblameReport;

/**
 * Svnblame report mock class.
 *
 * @author    Gabriele Santini <gsantini@sqli.com>
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2009 SQLI <www.sqli.com>
 * @copyright 2006-2012 Squiz Pty Ltd (ABN 77 084 670 600)
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 * @version   SVN: $Date: 2016-08-16 23:39:01 +0900 (Tue, 16 Aug 2016) $ $Revision: 26 $
 * @link      $HeadURL: https://open.logics.net.au/buildtools/codesniffer/tags/0.1.3/tests/Core/Reports/Mock/SvnblameMock.php $
 *
 * @donottranslate
 */

class SvnblameMock extends SvnblameReport
    {

	/**
	 * Example svnblame output.
	 *
	 * @var array
	 */
	protected $fooBlames = array(
				"     4   devel1        * @return void",
				"     4   devel1        * @return void",
				"     5   devel2        * @return void",
				"     4   devel1        * @return void",
				"     4   devel1        * @return void",
				"     5   devel2        * @return void",
				"     5   devel2        * @return void",
				"     4   devel1        * @return void",
				"    10   devel3        * @return void",
				"    10   devel3        * @return void",
			       );

	/**
	 * Example svnblame output.
	 *
	 * @var array
	 */
	protected $barBlames = array(
				"     4   devel1        * @return void",
				"     4   devel1        * @return void",
				"     5   devel2        * @return void",
				"     4   devel1        * @return void",
				"     4   devel1        * @return void",
				"     5   devel2        * @return void",
				"     5   devel2        * @return void",
				"     4   devel1        * @return void",
				"    10   devel3        * @return void",
				"    10   devel3        * @return void",
			       );

	/**
	 * Example svnblame output.
	 *
	 * @var array
	 */
	protected $bazBlames = array(
				"     4   devel1        * @return void",
				"     4   devel1        * @return void",
				"     5   devel2        * @return void",
				"     4   devel1        * @return void",
				"     4   devel1        * @return void",
				"     5   devel2        * @return void",
				"     5   devel2        * @return void",
				"     4   devel1        * @return void",
				"    10   devel3        * @return void",
				"    10   devel3        * @return void",
			       );

	/**
	 * Example svnblame output with long revision numbers.
	 *
	 * @var array
	 */
	protected $bigRevisionNumberBlames = array(
					      "123456   devel1        * @return void",
					      "123456   devel1        * @return void",
					      "251897   devel3        * @return void",
					      "251897   devel3        * @return void",
					      " 12345   devel1        * @return void",
					      "220123   devel2        * @return void",
					      "220123   devel2        * @return void",
					      "220123   devel2        * @return void",
					      "219571   devel1        * @return void",
					      "219571   devel1        * @return void",
					     );

	/**
	 * Mocks the svnblame command.
	 *
	 * @param string $filename Filename (equals fixtures keys).
	 *
	 * @return string
	 *
	 * @throws Exception Unexpected file name
	 *
	 * @exceptioncode EXCEPTION_UNEXPECTED_FILENAME
	 */

	protected function getBlameContent($filename)
	    {
		switch ($filename)
		    {
			case "foo":
				$blames = $this->fooBlames;
			    break;
			case "bar":
				$blames = $this->barBlames;
			    break;
			case "baz":
				$blames = $this->bazBlames;
			    break;
			case "bigRevisionNumber":
				$blames = $this->bigRevisionNumberBlames;
			    break;

			default:
			    throw new Exception("Unexpected filename " . $filename, EXCEPTION_UNEXPECTED_FILENAME);
		    } //end switch

		return $blames;
	    } //end getBlameContent()


	/**
	 * Needed to test protected method.
	 *
	 * @param string $line Line to parse.
	 *
	 * @return string
	 */

	public function testGetSvnAuthor($line)
	    {
		return $this->getAuthor($line);
	    } //end testGetSvnAuthor()


    } //end class

?>
