<?php

/**
 * Xml report for CodeSniffer.
 *
 * PHP version 5.6
 *
 * @package Logics\BuildTools\CodeSniffer
 */

namespace Logics\BuildTools\CodeSniffer;

use \XMLWriter;

/**
 * Xml report for CodeSniffer.
 *
 * @author    Gabriele Santini <gsantini@sqli.com>
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2009 SQLI <www.sqli.com>
 * @copyright 2006-2012 Squiz Pty Ltd (ABN 77 084 670 600)
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 * @version   SVN: $Date: 2016-08-16 23:39:01 +0900 (Tue, 16 Aug 2016) $ $Revision: 26 $
 * @link      $HeadURL: https://open.logics.net.au/buildtools/codesniffer/tags/0.1.3/src/CodeSniffer/Reports/XmlReport.php $
 */

class XmlReport implements Report
    {

	/**
	 * Prints all violations for processed files, in a proprietary XML format.
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
	 * @internalconst PHP_CODESNIFFER_ENCODING Encoding
	 *
	 * @untranslatable UTF-8
	 * @untranslatable phpcs
	 * @untranslatable version
	 * @untranslatable file
	 * @untranslatable name
	 * @untranslatable errors
	 * @untranslatable warnings
	 * @untranslatable utf-8
	 * @untranslatable line
	 * @untranslatable column
	 * @untranslatable source
	 * @untranslatable severity
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
		$out->startElement("phpcs");
		$out->writeAttribute("version", "1.4.5");

		$errorsShown = 0;

		foreach ($report["files"] as $filename => $file)
		    {
			if (empty($file["messages"]) === false)
			    {
				$out->startElement("file");
				$out->writeAttribute("name", $filename);
				$out->writeAttribute("errors", $file["errors"]);
				$out->writeAttribute("warnings", $file["warnings"]);

				foreach ($file["messages"] as $line => $lineErrors)
				    {
					foreach ($lineErrors as $column => $colErrors)
					    {
						foreach ($colErrors as $error)
						    {
							$error["type"]    = strtolower($error["type"]);
							$error["message"] = iconv(PHP_CODESNIFFER_ENCODING, "utf-8", $error["message"]);

							$out->startElement($error["type"]);
							$out->writeAttribute("line", $line);
							$out->writeAttribute("column", $column);
							$out->writeAttribute("source", $error["source"]);
							$out->writeAttribute("severity", $error["severity"]);
							$out->text($error["message"]);
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
