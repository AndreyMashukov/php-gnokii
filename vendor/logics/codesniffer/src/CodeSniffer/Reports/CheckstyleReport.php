<?php

/**
 * Checkstyle report for CodeSniffer.
 *
 * PHP version 5.6
 *
 * @package Logics\BuildTools\CodeSniffer
 */

namespace Logics\BuildTools\CodeSniffer;

use \XMLWriter;

/**
 * Checkstyle report for CodeSniffer.
 *
 * @author    Gabriele Santini <gsantini@sqli.com>
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2009 SQLI <www.sqli.com>
 * @copyright 2006-2012 Squiz Pty Ltd (ABN 77 084 670 600)
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 * @version   SVN: $Date: 2016-08-16 23:39:01 +0900 (Tue, 16 Aug 2016) $ $Revision: 26 $
 * @link      $HeadURL: https://open.logics.net.au/buildtools/codesniffer/tags/0.1.3/src/CodeSniffer/Reports/CheckstyleReport.php $
 */

class CheckstyleReport implements Report
    {

	/**
	 * Prints all violations for processed files, in a Checkstyle format.
	 *
	 * Violations are grouped by file.
	 *
	 * @param array $report      Prepared report.
	 * @param bool  $showSources Show sources?
	 * @param int   $width       Maximum allowed lne width.
	 * @param bool  $toScreen    Is the report being printed to screen?
	 *
	 * @return string
	 *
	 * @internalconst PHP_CODESNIFFER_ENCODING Encoding
	 *
	 * @untranslatable UTF-8
	 * @untranslatable checkstyle
	 * @untranslatable version
	 * @untranslatable file
	 * @untranslatable name
	 * @untranslatable utf-8
	 * @untranslatable error
	 * @untranslatable line
	 * @untranslatable column
	 * @untranslatable severity
	 * @untranslatable message
	 * @untranslatable source
	 */

	public function generate(array $report, $showSources = false, $width = 80, $toScreen = true)
	    {
		unset($showSources);
		unset($width);
		unset($toScreen);

		$out = new XMLWriter;
		$out->openMemory();
		$out->setIndent(true);
		$out->startDocument("1.0", "UTF-8");
		$out->startElement("checkstyle");
		$out->writeAttribute("version", "1.4.5");

		$errorsShown = 0;
		foreach ($report["files"] as $filename => $file)
		    {
			if (count($file["messages"]) !== 0)
			    {
				$out->startElement("file");
				$out->writeAttribute("name", $filename);

				foreach ($file["messages"] as $line => $lineErrors)
				    {
					foreach ($lineErrors as $column => $colErrors)
					    {
						foreach ($colErrors as $error)
						    {
							$error["type"]    = strtolower($error["type"]);
							$error["message"] = iconv(PHP_CODESNIFFER_ENCODING, "utf-8", $error["message"]);

							$out->startElement("error");
							$out->writeAttribute("line", $line);
							$out->writeAttribute("column", $column);
							$out->writeAttribute("severity", $error["type"]);
							$out->writeAttribute("message", $error["message"]);
							$out->writeAttribute("source", $error["source"]);
							$out->endElement();

							$errorsShown++;
						    }
					    }
				    } //end foreach

				$out->endElement();
			    } //end if
		    } //end foreach

		$out->endElement();
		echo $out->flush();

		return $errorsShown;
	    } //end generate()


    } //end class

?>
