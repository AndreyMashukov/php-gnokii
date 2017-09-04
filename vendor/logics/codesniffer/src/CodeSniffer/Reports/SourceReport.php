<?php

/**
 * Source report for CodeSniffer.
 *
 * PHP version 5.6
 *
 * @package Logics\BuildTools\CodeSniffer
 */

namespace Logics\BuildTools\CodeSniffer;

use \PHP_Timer;

/**
 * Source report for CodeSniffer.
 *
 * @author    Gabriele Santini <gsantini@sqli.com>
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2009 SQLI <www.sqli.com>
 * @copyright 2006-2012 Squiz Pty Ltd (ABN 77 084 670 600)
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 * @version   SVN: $Date: 2016-08-16 23:39:01 +0900 (Tue, 16 Aug 2016) $ $Revision: 26 $
 * @link      $HeadURL: https://open.logics.net.au/buildtools/codesniffer/tags/0.1.3/src/CodeSniffer/Reports/SourceReport.php $
 */

class SourceReport implements Report
    {

	/**
	 * Prints the source of all errors and warnings.
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
	 */

	public function generate(array $report, $showSources = false, $width = 80, $toScreen = true)
	    {
		$sources = array();
		$width   = max($width, 70);

		$errorsShown = 0;

		foreach ($report["files"] as $filename => $file)
		    {
			foreach ($file["messages"] as $line => $lineErrors)
			    {
				foreach ($lineErrors as $column => $colErrors)
				    {
					foreach ($colErrors as $error)
					    {
						$errorsShown++;

						$source           = $error["source"];
						$sources[$source] = (isset($sources[$source]) === false) ? 1 : ($sources[$source] + 1);
					    }
				    }
			    }
		    } //end foreach

		if ($errorsShown === 0)
		    {
			// Nothing to show.
			return 0;
		    }
		else
		    {
			asort($sources);
			$sources = array_reverse($sources);

			echo PHP_EOL . _("PHP CODE SNIFFER VIOLATION SOURCE SUMMARY") . PHP_EOL;
			echo str_repeat("-", $width) . PHP_EOL;
			echo ($showSources === true) ? _("SOURCE") . str_repeat(" ", ($width - 11)) : _("STANDARD  CATEGORY            SNIFF") . str_repeat(" ", ($width - 40));
			echo _("COUNT") . PHP_EOL;
			echo str_repeat("-", $width) . PHP_EOL;

			foreach ($sources as $source => $count)
			    {
				if ($showSources === true)
				    {
					echo $source . str_repeat(" ", ($width - 5 - strlen($source)));
				    }
				else
				    {
					$parts    = explode(".", $source);
					$parts[0] = (strlen($parts[0]) > 8) ? substr($parts[0], 0, ((strlen($parts[0]) - 8) * -1)) : $parts[0];
					echo $parts[0] . str_repeat(" ", (10 - strlen($parts[0])));

					$category = $this->makeFriendlyName($parts[1]);
					$category = (strlen($category) > 18) ? substr($category, 0, ((strlen($category) - 18) * -1)) : $category;
					echo $category . str_repeat(" ", (20 - strlen($category)));

					$sniff = $this->makeFriendlyName($parts[2]);
					if (isset($parts[3]) === true)
					    {
						$name    = $this->makeFriendlyName($parts[3]);
						$name[0] = strtolower($name[0]);
						$sniff  .= " " . $name;
					    }

					$sniff = (strlen($sniff) > ($width - 37)) ? substr($sniff, 0, ($width - 37 - strlen($sniff))) : $sniff;

					echo $sniff . str_repeat(" ", ($width - 35 - strlen($sniff)));
				    } //end if

				echo $count . PHP_EOL;
			    } //end foreach

			echo str_repeat("-", $width) . PHP_EOL;
			echo _("A TOTAL OF") . " " . $errorsShown . " " . _("SNIFF VIOLATION(S)") . " ";
			echo _("WERE FOUND IN") . " " . count($sources) . " " . _("SOURCE(S)") . PHP_EOL;
			echo str_repeat("-", $width) . PHP_EOL . PHP_EOL;

			if ($toScreen === true && PHP_CODESNIFFER_INTERACTIVE === false && class_exists("PHP_Timer", false) === true)
			    {
				echo PHP_Timer::resourceUsage() . PHP_EOL . PHP_EOL;
			    }

			return $errorsShown;
		    } //end if
	    } //end generate()


	/**
	 * Converts a camel caps name into a readable string.
	 *
	 * @param string $name The camel caps name to convert.
	 *
	 * @return string
	 */

	public function makeFriendlyName($name)
	    {
		$friendlyName = "";
		$length       = strlen($name);

		$lastWasUpper   = false;
		$lastWasNumeric = false;
		for ($i = 0; $i < $length; $i++)
		    {
			if (is_numeric($name[$i]) === true)
			    {
				if ($lastWasNumeric === false)
				    {
					$friendlyName .= " ";
				    }

				$lastWasUpper   = false;
				$lastWasNumeric = true;
			    }
			else
			    {
				$lastWasNumeric = false;

				$char = strtolower($name[$i]);
				if ($char === $name[$i])
				    {
					// Lowercase.
					$lastWasUpper = false;
				    }
				else
				    {
					// Uppercase.
					if ($lastWasUpper === false)
					    {
						$friendlyName .= " ";
						$next          = $name[($i + 1)];
						if (strtolower($next) === $next)
						    {
							// Next char is lowercase so it is a word boundary.
							$name[$i] = strtolower($name[$i]);
						    }
					    }

					$lastWasUpper = true;
				    }
			    } //end if

			$friendlyName .= $name[$i];
		    } //end for

		$friendlyName    = trim($friendlyName);
		$friendlyName[0] = strtoupper($friendlyName[0]);

		return $friendlyName;
	    } //end makeFriendlyName()


    } //end class

?>
