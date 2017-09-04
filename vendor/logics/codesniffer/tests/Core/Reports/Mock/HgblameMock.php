<?php

/**
 * PHP version 5
 *
 * @package Logics\Tests\BuildTools\CodeSniffer
 */

namespace Logics\Tests\BuildTools\CodeSniffer;

use \Exception;
use \Logics\BuildTools\CodeSniffer\HgblameReport;

/**
 * Hgblame report mock class.
 *
 * @author    Ben Selby <benmatselby@gmail.com>
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2009 SQLI <www.sqli.com>
 * @copyright 2006-2012 Squiz Pty Ltd (ABN 77 084 670 600)
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 * @version   SVN: $Date: 2016-08-16 23:39:01 +0900 (Tue, 16 Aug 2016) $ $Revision: 26 $
 * @link      $HeadURL: https://open.logics.net.au/buildtools/codesniffer/tags/0.1.3/tests/Core/Reports/Mock/HgblameMock.php $
 *
 * @donottranslate
 */

class HgblameMock extends HgblameReport
    {

	/**
	 * Example Hgblame output.
	 *
	 * @var array
	 */
	protected $fooBlames = array(
				"Ben Selby <benmatselby@gmail.com> Sun May 29 00:05:15 2011 +0300:     /**",
				"Ben Selby <benmatselby@gmail.com> Sun May 29 00:05:15 2011 +0300:     /**",
				"Ben Selby <benmatselby@gmail.com> Sun May 29 00:05:15 2011 +0300:     /**",
				"Ben Selby <benmatselby@gmail.com> Sun May 29 00:05:15 2011 +0300:     /**",
				"Ben Selby <benmatselby@gmail.com> Sun May 29 00:05:15 2011 +0300:     /**",
				"Ben Selby <benmatselby@gmail.com> Sun May 29 00:05:15 2011 +0300:     /**",
				"Ben Selby <benmatselby@gmail.com> Sun May 29 00:05:15 2011 +0300:     /**",
				"Ben Selby <benmatselby@gmail.com> Sun May 29 00:05:15 2011 +0300:     /**",
				"Ben Selby <benmatselby@gmail.com> Sun May 29 00:05:15 2011 +0300:     /**",
				"Ben Selby <benmatselby@gmail.com> Sun May 29 00:05:15 2011 +0300:     /**",
			       );

	/**
	 * Example Hgblame output.
	 *
	 * @var array
	 */
	protected $barBlames = array(
				"Ben Selby <benmatselby@gmail.com> Sun May 29 00:05:15 2011 +0300:     /**",
				"Ben Selby <benmatselby@gmail.com> Sun May 29 00:05:15 2011 +0300:     /**",
				"Ben Selby <benmatselby@gmail.com> Sun May 29 00:05:15 2011 +0300:     /**",
				"Ben Selby <benmatselby@gmail.com> Sun May 29 00:05:15 2011 +0300:     /**",
				"Ben Selby <benmatselby@gmail.com> Sun May 29 00:05:15 2011 +0300:     /**",
				"Ben Selby <benmatselby@gmail.com> Sun May 29 00:05:15 2011 +0300:     /**",
				"Ben Selby <benmatselby@gmail.com> Sun May 29 00:05:15 2011 +0300:     /**",
				"Ben Selby <benmatselby@gmail.com> Sun May 29 00:05:15 2011 +0300:     /**",
				"Ben Selby <benmatselby@gmail.com> Sun May 29 00:05:15 2011 +0300:     /**",
				"Ben Selby <benmatselby@gmail.com> Sun May 29 00:05:15 2011 +0300:     /**",
			       );

	/**
	 * Example Hgblame output.
	 *
	 * @var array
	 */
	protected $bazBlames = array(
				"Ben Selby <benmatselby@gmail.com> Sun May 29 00:05:15 2011 +0300:     /**",
				"Ben Selby <benmatselby@gmail.com> Sun May 29 00:05:15 2011 +0300:     /**",
				"Ben Selby <benmatselby@gmail.com> Sun May 29 00:05:15 2011 +0300:     /**",
				"Ben Selby <benmatselby@gmail.com> Sun May 29 00:05:15 2011 +0300:     /**",
				"Ben Selby <benmatselby@gmail.com> Sun May 29 00:05:15 2011 +0300:     /**",
				"Ben Selby <benmatselby@gmail.com> Sun May 29 00:05:15 2011 +0300:     /**",
				"Ben Selby <benmatselby@gmail.com> Sun May 29 00:05:15 2011 +0300:     /**",
				"Ben Selby <benmatselby@gmail.com> Sun May 29 00:05:15 2011 +0300:     /**",
				"Ben Selby <benmatselby@gmail.com> Sun May 29 00:05:15 2011 +0300:     /**",
				"Ben Selby <benmatselby@gmail.com> Sun May 29 00:05:15 2011 +0300:     /**",
			       );

	/**
	 * Example Hgblame output with long revision numbers.
	 *
	 * @var array
	 */
	protected $bigRevisionNumberBlames = array(
					      "Ben Selby <benmatselby@gmail.com> Sun May 29 00:05:15 2011 +0300:     /**",
					      "Ben Selby <benmatselby@gmail.com> Sun May 29 00:05:15 2011 +0300:     /**",
					      "Ben Selby <benmatselby@gmail.com> Sun May 29 00:05:15 2011 +0300:     /**",
					      "Ben Selby <benmatselby@gmail.com> Sun May 29 00:05:15 2011 +0300:     /**",
					      "Ben Selby <benmatselby@gmail.com> Sun May 29 00:05:15 2011 +0300:     /**",
					      "Ben Selby <benmatselby@gmail.com> Sun May 29 00:05:15 2011 +0300:     /**",
					      "Ben Selby <benmatselby@gmail.com> Sun May 29 00:05:15 2011 +0300:     /**",
					      "Ben Selby <benmatselby@gmail.com> Sun May 29 00:05:15 2011 +0300:     /**",
					      "Ben Selby <benmatselby@gmail.com> Sun May 29 00:05:15 2011 +0300:     /**",
					      "Ben Selby <benmatselby@gmail.com> Sun May 29 00:05:15 2011 +0300:     /**",
					     );

	/**
	 * Mocks the Hgblame command.
	 *
	 * @param string $filename Filename (equals fixtures keys).
	 *
	 * @return string
	 *
	 * @throws Exception Unexpected filename
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
		    }

		return $blames;
	    } //end getBlameContent()


	/**
	 * Needed to test protected method.
	 *
	 * @param string $line Line to parse.
	 *
	 * @return string
	 */

	public function testGetHgAuthor($line)
	    {
		return $this->getAuthor($line);
	    } //end testGetHgAuthor()


    } //end class

?>
