<?php

/**
 * PHP version 5
 *
 * @package Logics\Tests\BuildTools\CodeSniffer
 */

namespace Logics\Tests\BuildTools\CodeSniffer;

use \Exception;
use \Logics\BuildTools\CodeSniffer\GitblameReport;

/**
 * Gitblame report mock class.
 *
 * @author    Ben Selby <benmatselby@gmail.com>
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2009 SQLI <www.sqli.com>
 * @copyright 2006-2012 Squiz Pty Ltd (ABN 77 084 670 600)
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 * @version   SVN: $Date: 2016-08-16 23:39:01 +0900 (Tue, 16 Aug 2016) $ $Revision: 26 $
 * @link      $HeadURL: https://open.logics.net.au/buildtools/codesniffer/tags/0.1.3/tests/Core/Reports/Mock/GitblameMock.php $
 *
 * @donottranslate
 */

class GitblameMock extends GitblameReport
    {

	/**
	 * Example gitblame output.
	 *
	 * @var array
	 */
	protected $fooBlames = array(
				"054e7580 (Ben Selby 1 2009-08-25  45)      * @return",
				"054e758a (Ben Selby 2 2009-08-25  45)      * @return",
				"054e758b (Ben Selby 3 2009-08-25  45)      * @return",
				"054e758c (Ben Selby 4 2009-08-25  45)      * @return",
				"054e758d (Ben Selby 5 2009-08-25  45)      * @return",
				"1ee0f411 (Ben Selby 6 2009-08-25  45)      * @return",
				"1ee0f41b (Ben Selby 7 2009-08-25  45)      * @return",
				"1ee0f41c (Ben Selby 8 2009-08-25  45)      * @return",
				"1ee0f41d (Ben Selby 9 2009-08-25  45)      * @return",
				"1ee0f41e (Ben Selby 10 2009-08-25  45)      * @return",
			       );

	/**
	 * Example gitblame output.
	 *
	 * @var array
	 */
	protected $barBlames = array(
				"054e7580 (Ben Selby 1 2009-08-25  45)      * @return",
				"054e758a (Ben Selby 2 2009-08-25  45)      * @return",
				"054e758b (Ben Selby 3 2009-08-25  45)      * @return",
				"054e758c (Ben Selby 4 2009-08-25  45)      * @return",
				"054e758d (Ben Selby 5 2009-08-25  45)      * @return",
				"1ee0f411 (Ben Selby 6 2009-08-25  45)      * @return",
				"1ee0f41b (Ben Selby 7 2009-08-25  45)      * @return",
				"1ee0f41c (Ben Selby 8 2009-08-25  45)      * @return",
				"1ee0f41d (Ben Selby 9 2009-08-25  45)      * @return",
				"1ee0f41e (Ben Selby 10 2009-08-25  45)      * @return",
			       );

	/**
	 * Example gitblame output.
	 *
	 * @var array
	 */
	protected $bazBlames = array(
				"054e7580 (Ben Selby 1 2009-08-25  45)      * @return",
				"054e758a (Ben Selby 2 2009-08-25  45)      * @return",
				"054e758b (Ben Selby 3 2009-08-25  45)      * @return",
				"054e758c (Ben Selby 4 2009-08-25  45)      * @return",
				"054e758d (Ben Selby 5 2009-08-25  45)      * @return",
				"1ee0f411 (Ben Selby 6 2009-08-25  45)      * @return",
				"1ee0f41b (Ben Selby 7 2009-08-25  45)      * @return",
				"1ee0f41c (Ben Selby 8 2009-08-25  45)      * @return",
				"1ee0f41d (Ben Selby 9 2009-08-25  45)      * @return",
				"1ee0f41e (Ben Selby 10 2009-08-25  45)      * @return",
			       );

	/**
	 * Example gitblame output with long revision numbers.
	 *
	 * @var array
	 */
	protected $bigRevisionNumberBlames = array(
					      "054e7580 (Ben Selby 1 2009-08-25  45)      * @return",
					      "054e758a (Ben Selby 2 2009-08-25  45)      * @return",
					      "054e758b (Ben Selby 3 2009-08-25  45)      * @return",
					      "054e758c (Ben Selby 4 2009-08-25  45)      * @return",
					      "054e758d (Ben Selby 5 2009-08-25  45)      * @return",
					      "1ee0f411 (Ben Selby 6 2009-08-25  45)      * @return",
					      "1ee0f41b (Ben Selby 7 2009-08-25  45)      * @return",
					      "1ee0f41c (Ben Selby 8 2009-08-25  45)      * @return",
					      "1ee0f41d (Ben Selby 9 2009-08-25  45)      * @return",
					      "1ee0f41e (Ben Selby 10 2009-08-25  45)      * @return",
					     );

	/**
	 * Mocks the gitblame command.
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

	public function testGetGitAuthor($line)
	    {
		return $this->getAuthor($line);
	    } //end testGetGitAuthor()


    } //end class

?>
