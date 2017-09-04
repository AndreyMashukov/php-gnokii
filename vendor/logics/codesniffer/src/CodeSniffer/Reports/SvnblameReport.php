<?php

/**
 * Svnblame report for CodeSniffer.
 *
 * PHP version 5.6
 *
 * @package Logics\BuildTools\CodeSniffer
 */

namespace Logics\BuildTools\CodeSniffer;

/**
 * Svnblame report for CodeSniffer.
 *
 * @author    Gabriele Santini <gsantini@sqli.com>
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2009 SQLI <www.sqli.com>
 * @copyright 2006-2012 Squiz Pty Ltd (ABN 77 084 670 600)
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 * @version   SVN: $Date: 2016-08-16 23:39:01 +0900 (Tue, 16 Aug 2016) $ $Revision: 26 $
 * @link      $HeadURL: https://open.logics.net.au/buildtools/codesniffer/tags/0.1.3/src/CodeSniffer/Reports/SvnblameReport.php $
 *
 * @untranslatable SVN
 */

class SvnblameReport extends VersionControlReports
    {

	/**
	 * The name of the report we want in the output
	 *
	 * @var string
	 */
	protected $reportName = "SVN";

	/**
	 * Extract the author from a blame line.
	 *
	 * @param string $line Line to parse.
	 *
	 * @return mixed string or false if impossible to recover.
	 */

	protected function getAuthor($line)
	    {
		$blameParts = array();
		preg_match("/\s*([^\s]+)\s+([^\s]+)/", $line, $blameParts);

		if (isset($blameParts[2]) === false)
		    {
			return false;
		    }
		else
		    {
			return $blameParts[2];
		    }
	    } //end getAuthor()


	/**
	 * Gets the blame output.
	 *
	 * @param string $filename File to blame.
	 *
	 * @return array
	 *
	 * @untranslatable svn blame \"
	 * @untranslatable r
	 */

	protected function getBlameContent($filename)
	    {
		Console::report(_("Getting SVN blame info for") . " " . basename($filename) . "... ", 0, 0, "");

		$command = "svn blame \"" . $filename . "\"";
		$handle  = popen($command, "r");
		if ($handle === false)
		    {
			echo _("ERROR: Could not execute") . " \"" . $command . "\"" . PHP_EOL . PHP_EOL;
			exit(2);
		    }

		$rawContent = stream_get_contents($handle);
		fclose($handle);

		Console::report(_("DONE"), 0, 0);

		$blames = explode("\n", $rawContent);

		return $blames;
	    } //end getBlameContent()


    } //end class

?>
