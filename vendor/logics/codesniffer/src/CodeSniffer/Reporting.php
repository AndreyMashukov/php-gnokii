<?php

/**
 * A class to manage reporting.
 *
 * PHP version 5
 *
 * @package Logics\BuildTools\CodeSniffer
 */

namespace Logics\BuildTools\CodeSniffer;

use \Exception;

/**
 * A class to manage reporting.
 *
 * @author    Gabriele Santini <gsantini@sqli.com>
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2009 SQLI <www.sqli.com>
 * @copyright 2006-2012 Squiz Pty Ltd (ABN 77 084 670 600)
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 * @version   SVN: $Date: 2016-08-16 23:39:01 +0900 (Tue, 16 Aug 2016) $ $Revision: 26 $
 * @link      $HeadURL: https://open.logics.net.au/buildtools/codesniffer/tags/0.1.3/src/CodeSniffer/Reporting.php $
 */

class Reporting
    {

	/**
	 * Produce the appropriate report object based on $type parameter.
	 *
	 * @param string $type Demanded report type.
	 *
	 * @return Report
	 *
	 * @throws Exception If report is not available.
	 *
	 * @exceptioncode EXCEPTION_REPORT_TYPE_NOT_FOUND
	 * @exceptioncode EXCEPTION_CLASS_DOES_NOT_IMPLEMENT_REPORTING_INTERFACE
	 *
	 * @untranslatable CodeSniffer/Reports/
	 * @untranslatable \\Logics\\BuildTools\\CodeSniffer\\
	 * @untranslatable Report
	 */

	public function factory($type)
	    {
		$type            = ucfirst($type);
		$filename        = "CodeSniffer/Reports/" . $type . "Report.php";
		$reportClassName = "\\Logics\\BuildTools\\CodeSniffer\\" . $type . "Report";

		if (class_exists($reportClassName, true) === false)
		    {
			throw new Exception(
			    _("Report type") . " \"" . $type . "\" " . _("not found."),
			    EXCEPTION_REPORT_TYPE_NOT_FOUND
			);
		    }

		$reportClass = new $reportClassName();
		if (false === ($reportClass instanceof Report))
		    {
			throw new Exception(
			    _("Class") . " \"" . $reportClassName . "\" " . _("must implement the \"Logics\\BuildTools\\CodeSniffer\\Report\" interface."),
			    EXCEPTION_CLASS_DOES_NOT_IMPLEMENT_REPORTING_INTERFACE
			);
		    }

		return $reportClass;
	    } //end factory()


	/**
	 * Actually generates the report.
	 *
	 * @param string $report          Report type.
	 * @param array  $filesViolations Collected violations.
	 * @param bool   $showSources     Show sources?
	 * @param string $reportFile      Report file to generate.
	 * @param int    $reportWidth     Report max width.
	 *
	 * @return int
	 *
	 * @internalconst PHPCS_CWD Current working directory
	 */

	public function printReport($report, array $filesViolations, $showSources, $reportFile = "", $reportWidth = 80)
	    {
		if ($reportFile !== null)
		    {
			$reportDir = dirname($reportFile);
			if ($reportDir === ".")
			    {
				// Passed report file is a filename in the current directory.
				$reportFile = PHPCS_CWD . "/" . basename($reportFile);
			    }
			else
			    {
				$reportDir = realpath(PHPCS_CWD . "/" . $reportDir);
				if ($reportDir !== false)
				    {
					// Report file path is relative.
					$reportFile = $reportDir . "/" . basename($reportFile);
				    }
			    }
		    }

		$reportClass = self::factory($report);
		$reportData  = $this->prepare($filesViolations);

		$toScreen = true;
		if ($reportFile !== null)
		    {
			$toScreen = false;
			ob_start();
		    }

		$numErrors = $reportClass->generate($reportData, $showSources, $reportWidth, $toScreen);

		if ($reportFile !== null)
		    {
			$generatedReport = ob_get_contents();
			Console::report($generatedReport, 0, 0, "");
			ob_end_clean();

			$generatedReport = trim($generatedReport);
			file_put_contents($reportFile, $generatedReport . PHP_EOL);
		    }

		return $numErrors;
	    } //end printReport()


	/**
	 * Pre-process and package violations for all files.
	 *
	 * Used by error reports to get a packaged list of all errors in each file.
	 *
	 * @param array $filesViolations List of found violations.
	 *
	 * @return array
	 */

	public function prepare(array $filesViolations)
	    {
		$report = array(
			   "totals" => array(
					"warnings" => 0,
					"errors"   => 0,
				       ),
			   "files"  => array(),
			  );

		foreach ($filesViolations as $filename => $fileViolations)
		    {
			$warnings    = $fileViolations["warnings"];
			$errors      = $fileViolations["errors"];
			$numWarnings = $fileViolations["numWarnings"];
			$numErrors   = $fileViolations["numErrors"];

			$report["files"][$filename] = array(
						       "errors"   => 0,
						       "warnings" => 0,
						       "messages" => array(),
						      );

			if ($numErrors !== 0 || $numWarnings !== 0)
			    {
				$report["files"][$filename]["errors"]   = $numErrors;
				$report["files"][$filename]["warnings"] = $numWarnings;

				$report["totals"]["errors"]   += $numErrors;
				$report["totals"]["warnings"] += $numWarnings;

				// Merge errors and warnings.
				foreach ($errors as $line => $lineErrors)
				    {
					foreach ($lineErrors as $column => $colErrors)
					    {
						$newErrors = array();
						foreach ($colErrors as $data)
						    {
							$newErrors[] = array(
									"message"  => $data["message"],
									"source"   => $data["source"],
									"severity" => $data["severity"],
									"type"     => _("ERROR"),
								       );
						    } //end foreach

						$errors[$line][$column] = $newErrors;
					    } //end foreach

					ksort($errors[$line]);
				    } //end foreach

				foreach ($warnings as $line => $lineWarnings)
				    {
					foreach ($lineWarnings as $column => $colWarnings)
					    {
						$newWarnings = array();
						foreach ($colWarnings as $data)
						    {
							$newWarnings[] = array(
									  "message"  => $data["message"],
									  "source"   => $data["source"],
									  "severity" => $data["severity"],
									  "type"     => _("WARNING"),
									 );
						    } //end foreach

						if (isset($errors[$line][$column]) === true)
						    {
							$errors[$line][$column] = array_merge($newWarnings, $errors[$line][$column]);
						    }
						else
						    {
							$errors[$line][$column] = $newWarnings;
						    }
					    } //end foreach

					ksort($errors[$line]);
				    } //end foreach

				ksort($errors);

				$report["files"][$filename]["messages"] = $errors;
			    } //end if
		    } //end foreach

		ksort($report["files"]);

		return $report;
	    } //end prepare()


    } //end class

?>
