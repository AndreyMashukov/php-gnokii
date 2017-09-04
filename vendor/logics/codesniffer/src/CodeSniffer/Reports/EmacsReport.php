<?php

/**
 * Emacs report for CodeSniffer.
 *
 * PHP version 5.6
 *
 * @package Logics\BuildTools\CodeSniffer
 */

namespace Logics\BuildTools\CodeSniffer;

/**
 * Emacs report for CodeSniffer.
 *
 * @author    Gabriele Santini <gsantini@sqli.com>
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2009 SQLI <www.sqli.com>
 * @copyright 2006-2012 Squiz Pty Ltd (ABN 77 084 670 600)
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 * @version   SVN: $Date: 2016-08-16 23:39:01 +0900 (Tue, 16 Aug 2016) $ $Revision: 26 $
 * @link      $HeadURL: https://open.logics.net.au/buildtools/codesniffer/tags/0.1.3/src/CodeSniffer/Reports/EmacsReport.php $
 */

class EmacsReport implements Report
    {

	/**
	 * Generates an emacs report.
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
		unset($width);
		unset($toScreen);

		$errorsShown = 0;

		foreach ($report["files"] as $filename => $file)
		    {
			foreach ($file["messages"] as $line => $lineErrors)
			    {
				foreach ($lineErrors as $column => $colErrors)
				    {
					foreach ($colErrors as $error)
					    {
						$message = $error["message"];
						if ($showSources === true)
						    {
							$message .= " (" . $error["source"] . ")";
						    }

						$type = strtolower($error["type"]);
						echo $filename . ":" . $line . ":" . $column . ": " . $type . " - " . $message . PHP_EOL;
						$errorsShown++;
					    }
				    }
			    } //end foreach
		    } //end foreach

		return $errorsShown;
	    } //end generate()


    } //end class

?>
