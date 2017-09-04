<?php

/**
 * Mercurial report for CodeSniffer.
 *
 * PHP version 5.6
 *
 * @package Logics\BuildTools\CodeSniffer
 */

namespace Logics\BuildTools\CodeSniffer;

/**
 * Mercurial report for CodeSniffer.
 *
 * @author    Ben Selby <benmatselby@gmail.com>
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2009 SQLI <www.sqli.com>
 * @copyright 2006-2012 Squiz Pty Ltd (ABN 77 084 670 600)
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 * @version   SVN: $Date: 2016-08-16 23:39:01 +0900 (Tue, 16 Aug 2016) $ $Revision: 26 $
 * @link      $HeadURL: https://open.logics.net.au/buildtools/codesniffer/tags/0.1.3/src/CodeSniffer/Reports/HgblameReport.php $
 *
 * @untranslatable MERCURIAL
 */

class HgblameReport extends VersionControlReports
    {

	/**
	 * The name of the report we want in the output
	 *
	 * @var string
	 */
	protected $reportName = "MERCURIAL";

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
		$line       = preg_replace("/\s+/", " ", $line);

		preg_match("/(.+[0-9]{2}:[0-9]{2}:[0-9]{2}\s[0-9]{4}\s.[0-9]{4}:)/", $line, $blameParts);

		if (isset($blameParts[0]) === false)
		    {
			return false;
		    }
		else
		    {
			$parts = explode(" ", $blameParts[0]);

			if (count($parts) < 6)
			    {
				return false;
			    }
			else
			    {
				$parts = array_slice($parts, 0, (count($parts) - 6));

				return trim(preg_replace("/<.+>/", "", implode($parts, " ")));
			    }
		    }
	    } //end getAuthor()


	/**
	 * Gets the blame output.
	 *
	 * @param string $filename File to blame.
	 *
	 * @return array
	 *
	 * @untranslatable .hg
	 * @untranslatable hg blame -u -d -v \"
	 * @untranslatable r
	 */

	protected function getBlameContent($filename)
	    {
		$cwd = getcwd();

		Console::report(_("Getting MERCURIAL blame info for") . " " . basename($filename) . "... ", 0, 0, "");

		$fileParts = explode(DIRECTORY_SEPARATOR, $filename);
		$found     = false;
		$location  = "";
		while (empty($fileParts) === false)
		    {
			array_pop($fileParts);
			$location = implode($fileParts, DIRECTORY_SEPARATOR);
			if (is_dir($location . DIRECTORY_SEPARATOR . ".hg") === true)
			    {
				$found = true;
				break;
			    }
		    }

		if ($found === true)
		    {
			chdir($location);
		    }
		else
		    {
			echo _("ERROR: Could not locate .hg directory") . " " . PHP_EOL . PHP_EOL;
			exit(2);
		    }

		$command = "hg blame -u -d -v \"" . $filename . "\"";
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
		chdir($cwd);

		return $blames;
	    } //end getBlameContent()


    } //end class

?>