<?php

/**
 * Full report for CodeSniffer.
 *
 * PHP version 5.6
 *
 * @package Logics\BuildTools\CodeSniffer
 */

namespace Logics\BuildTools\CodeSniffer;

use \PHP_Timer;

/**
 * Full report for CodeSniffer.
 *
 * @author    Gabriele Santini <gsantini@sqli.com>
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2009 SQLI <www.sqli.com>
 * @copyright 2006-2012 Squiz Pty Ltd (ABN 77 084 670 600)
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 * @version   SVN: $Date: 2016-08-16 23:39:01 +0900 (Tue, 16 Aug 2016) $ $Revision: 26 $
 * @link      $HeadURL: https://open.logics.net.au/buildtools/codesniffer/tags/0.1.3/src/CodeSniffer/Reports/FullReport.php $
 */

class FullReport implements Report
    {

	/**
	 * Prints all errors and warnings for each file processed.
	 *
	 * Errors and warnings are displayed together, grouped by file.
	 *
	 * @param array $report      Prepared report.
	 * @param bool  $showSources Show sources?
	 * @param int   $width       Maximum allowed lne width.
	 * @param bool  $toScreen    Is the report being printed to screen?
	 *
	 * @return string
	 *
	 * @internalconst PHP_CODESNIFFER_INTERACTIVE Interactive
	 *
	 * @untranslatable PHP_Timer
	 * @untranslatable ERROR
	 */

	public function generate(array $report, $showSources = false, $width = 80, $toScreen = true)
	    {
		$errorsShown = 0;
		$width       = max($width, 70);

		foreach ($report["files"] as $filename => $file)
		    {
			if (empty($file["messages"]) === false)
			    {
				echo PHP_EOL . _("FILE") . ": ";
				if (strlen($filename) <= ($width - 9))
				    {
					echo $filename;
				    }
				else
				    {
					echo "..." . substr($filename, (strlen($filename) - ($width - 9)));
				    }

				echo PHP_EOL;
				echo str_repeat("-", $width) . PHP_EOL;

				echo _("FOUND") . " " . $file["errors"] . " " . _("ERROR(S)") . " " .
				     (($file["warnings"] > 0) ? _("AND") . " " . $file["warnings"] . " " . _("WARNING(S)") . " " : "");
				echo _("AFFECTING") . " " . count($file["messages"]) . " " . _("LINE(S)") . PHP_EOL;
				echo str_repeat("-", $width) . PHP_EOL;

				// Work out the max line number for formatting.
				$maxLine = 0;
				foreach ($file["messages"] as $line => $lineErrors)
				    {
					if ($line > $maxLine)
					    {
						$maxLine = $line;
					    }
				    }

				$maxLineLength = strlen($maxLine);

				// The length of the word ERROR or WARNING; used for padding.
				$typeLength = ($file["warnings"] > 0) ? 7 : 5;

				// The padding that all lines will require that are
				// printing an error message overflow.
				$paddingLine2  = str_repeat(" ", ($maxLineLength + 1));
				$paddingLine2 .= " | ";
				$paddingLine2 .= str_repeat(" ", $typeLength);
				$paddingLine2 .= " | ";

				// The maximum amount of space an error message can use.
				$maxErrorSpace = ($width - strlen($paddingLine2) - 1);

				foreach ($file["messages"] as $line => $lineErrors)
				    {
					foreach ($lineErrors as $column => $colErrors)
					    {
						foreach ($colErrors as $error)
						    {
							$message = $error["message"] . (($showSources === true) ? " (" . $error["source"] . ")" : "");

							// The padding that goes on the front of the line.
							$padding  = ($maxLineLength - strlen($line));
							$errorMsg = wordwrap($message, $maxErrorSpace, PHP_EOL . $paddingLine2);

							echo " " . str_repeat(" ", $padding) . $line . " | " . $error["type"];
							echo ($error["type"] === "ERROR" && $file["warnings"] > 0) ? "  " : "";
							echo " | " . $errorMsg . PHP_EOL;
							$errorsShown++;
						    } //end foreach
					    } //end foreach
				    } //end foreach

				echo str_repeat("-", $width) . PHP_EOL . PHP_EOL;
			    } //end if
		    } //end foreach

		if ($toScreen === true && PHP_CODESNIFFER_INTERACTIVE === false && class_exists("PHP_Timer", false) === true)
		    {
			echo PHP_Timer::resourceUsage() . PHP_EOL . PHP_EOL;
		    }

		return $errorsShown;
	    } //end generate()


    } //end class

?>