<?php

/**
 * Csv report for CodeSniffer.
 *
 * PHP version 5.6
 *
 * @package Logics\BuildTools\CodeSniffer
 */

namespace Logics\BuildTools\CodeSniffer;

/**
 * Csv report for CodeSniffer.
 *
 * @author    Gabriele Santini <gsantini@sqli.com>
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2009 SQLI <www.sqli.com>
 * @copyright 2006-2012 Squiz Pty Ltd (ABN 77 084 670 600)
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 * @version   SVN: $Date: 2016-08-16 23:39:01 +0900 (Tue, 16 Aug 2016) $ $Revision: 26 $
 * @link      $HeadURL: https://open.logics.net.au/buildtools/codesniffer/tags/0.1.3/src/CodeSniffer/Reports/CsvReport.php $
 */

class CsvReport implements Report
    {

	/**
	 * Generates a csv report.
	 *
	 * @param array $report      Prepared report.
	 * @param bool  $showSources Show sources?
	 * @param int   $width       Maximum allowed lne width.
	 * @param bool  $toScreen    Is the report being printed to screen?
	 *
	 * @return string
	 */

	public function generate(array $report, $showSources = false, $width = 80, $toScreen = true)
	    {
		unset($showSources);
		unset($width);
		unset($toScreen);

		echo _("File,Line,Column,Type,Message,Source,Severity") . PHP_EOL;

		$errorsShown = 0;
		foreach ($report["files"] as $filename => $file)
		    {
			foreach ($file["messages"] as $line => $lineErrors)
			    {
				foreach ($lineErrors as $column => $colErrors)
				    {
					foreach ($colErrors as $error)
					    {
						$filename = str_replace("\"", '\"', $filename);
						$message  = str_replace("\"", '\"', $error["message"]);
						$type     = strtolower($error["type"]);
						$source   = $error["source"];
						$severity = $error["severity"];
						echo "\"" . $filename . "\"," . $line . "," . $column . "," .
						     $type . ",\"" . $message . "\"," . $source . "," . $severity . PHP_EOL;
						$errorsShown++;
					    }
				    }
			    } //end foreach
		    } //end foreach

		return $errorsShown;
	    } //end generate()


    } //end class

?>
