<?php

/**
 * Summary report for CodeSniffer.
 *
 * PHP version 5.6
 *
 * @package Logics\BuildTools\CodeSniffer
 */

namespace Logics\BuildTools\CodeSniffer;

use \PHP_Timer;

/**
 * Summary report for CodeSniffer.
 *
 * @author    Gabriele Santini <gsantini@sqli.com>
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2009 SQLI <www.sqli.com>
 * @copyright 2006-2012 Squiz Pty Ltd (ABN 77 084 670 600)
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 * @version   SVN: $Date: 2016-08-16 23:39:01 +0900 (Tue, 16 Aug 2016) $ $Revision: 26 $
 * @link      $HeadURL: https://open.logics.net.au/buildtools/codesniffer/tags/0.1.3/src/CodeSniffer/Reports/SummaryReport.php $
 */

class SummaryReport implements Report
    {

	/**
	 * Generates a summary of errors and warnings for each file processed.
	 *
	 * If verbose output is enabled, results are shown for all files, even if
	 * they have no errors or warnings. If verbose output is disabled, we only
	 * show files that have at least one warning or error.
	 *
	 * @param array $report      Prepared report.
	 * @param bool  $showSources Show sources?
	 * @param int   $width       Maximum allowed lne width.
	 * @param bool  $toScreen    Is the report being printed to screen?
	 *
	 * @return string
	 *
	 * @internalconst PHP_CODESNIFFER_VERBOSITY   Verbosity
	 * @internalconst PHP_CODESNIFFER_INTERACTIVE Interactive
	 *
	 * @untranslatable PHP_Timer
	 */

	public function generate(array $report, $showSources = false, $width = 80, $toScreen = true)
	    {
		$errorFiles = array();
		$width      = max($width, 70);

		foreach ($report["files"] as $filename => $file)
		    {
			$numWarnings = $file["warnings"];
			$numErrors   = $file["errors"];

			// If verbose output is enabled, we show the results for all files,
			// but if not, we only show files that had errors or warnings.
			if (PHP_CODESNIFFER_VERBOSITY > 0 || $numErrors > 0 || $numWarnings > 0)
			    {
				$errorFiles[$filename] = array(
							  "warnings" => $numWarnings,
							  "errors"   => $numErrors,
							 );
			    } //end if
		    } //end foreach

		if (empty($errorFiles) === true)
		    {
			// Nothing to print.
			return 0;
		    }
		else
		    {
			echo PHP_EOL . _("PHP CODE SNIFFER REPORT SUMMARY") . PHP_EOL;
			echo str_repeat("-", $width) . PHP_EOL;
			echo _("FILE") . str_repeat(" ", ($width - 20)) . _("ERRORS  WARNINGS") . PHP_EOL;
			echo str_repeat("-", $width) . PHP_EOL;

			$totalErrors   = 0;
			$totalWarnings = 0;
			$totalFiles    = 0;

			foreach ($errorFiles as $file => $errors)
			    {
				$padding = ($width - 18 - strlen($file));
				if ($padding < 0)
				    {
					$file    = "..." . substr($file, (($padding * -1) + 3));
					$padding = 0;
				    }

				echo $file . str_repeat(" ", $padding) . "  ";
				echo $errors["errors"];
				echo str_repeat(" ", (8 - strlen((string) $errors["errors"])));
				echo $errors["warnings"];
				echo PHP_EOL;

				$totalFiles++;
			    } //end foreach

			echo str_repeat("-", $width) . PHP_EOL;
			echo _("A TOTAL OF") . " " . $report["totals"]["errors"] . " " . _("ERROR(S)") . " ";
			echo _("AND") . " " . $report["totals"]["warnings"] . " " . _("WARNING(S)") . " ";
			echo _("WERE FOUND IN") . " " . $totalFiles . " " . _("FILE(S)") . PHP_EOL;
			echo str_repeat("-", $width) . PHP_EOL . PHP_EOL;

			$sources = 0;
			if ($showSources === true)
			    {
				$source  = new SourceReport();
				$sources = $source->generate($report, $showSources, $width);
			    }

			if ($toScreen === true && PHP_CODESNIFFER_INTERACTIVE === false && $sources === 0 && class_exists("PHP_Timer", false) === true)
			    {
				echo PHP_Timer::resourceUsage() . PHP_EOL . PHP_EOL;
			    }

			return ($report["totals"]["errors"] + $report["totals"]["warnings"]);
		    } //end if
	    } //end generate()


    } //end class

?>
