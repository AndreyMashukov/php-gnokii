<?php

/**
 * Version control report base class for CodeSniffer.
 *
 * PHP version 5.6
 *
 * @package Logics\BuildTools\CodeSniffer
 */

namespace Logics\BuildTools\CodeSniffer;

use \PHP_Timer;

/**
 * Version control report base class for CodeSniffer.
 *
 * @author    Ben Selby <benmatselby@gmail.com>
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2009 SQLI <www.sqli.com>
 * @copyright 2006-2012 Squiz Pty Ltd (ABN 77 084 670 600)
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 * @version   SVN: $Date: 2016-08-16 23:39:01 +0900 (Tue, 16 Aug 2016) $ $Revision: 26 $
 * @link      $HeadURL: https://open.logics.net.au/buildtools/codesniffer/tags/0.1.3/src/CodeSniffer/Reports/VersionControlReports.php $
 *
 * @untranslatable VERSION CONTROL
 */

abstract class VersionControlReports implements Report
    {

	/**
	 * The name of the report we want in the output.
	 *
	 * @var string
	 */
	protected $reportName = "VERSION CONTROL";

	/**
	 * Prints the author of all errors and warnings, as given by "version control blame".
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
		$authors = array();
		$praise  = array();
		$sources = array();
		$width   = max($width, 70);

		$errors = 0;

		foreach ($report["files"] as $filename => $file)
		    {
			$this->_countBlamesAndPraises($file, $filename, $errors, $authors, $praise, $sources);
		    }

		return $this->_showErrors($errors, $authors, $praise, $showSources, $width, $toScreen);
	    } //end generate()


	/**
	 * Count all blames and praises for each author
	 *
	 * @param array  $file     File
	 * @param string $filename File name
	 * @param int    $errors   Total errors counter
	 * @param array  $authors  All authors
	 * @param array  $praise   Blames and praises
	 * @param array  $sources  Sources
	 *
	 * @return void
	 *
	 * @internalconst PHP_CODESNIFFER_VERBOSITY Verbosity
	 */

	private function _countBlamesAndPraises(array $file, $filename, &$errors, array &$authors, array &$praise, array &$sources)
	    {
		$blames = $this->getBlameContent($filename);

		foreach ($file["messages"] as $line => $lineErrors)
		    {
			$author = $this->getAuthor($blames[($line - 1)]);
			if ($author !== false)
			    {
				if (isset($authors[$author]) === false)
				    {
					$authors[$author] = 0;
					$praise[$author]  = array(
							     "good" => 0,
							     "bad"  => 1,
							    );
				    }
				else
				    {
					$praise[$author]["bad"]++;
				    }

				foreach ($lineErrors as $column => $colErrors)
				    {
					foreach ($colErrors as $error)
					    {
						$errors++;
						$authors[$author]++;

						$source                    = $error["source"];
						$sources[$author][$source] = (isset($sources[$author][$source]) === false) ? 1 : ($sources[$author][$source] + 1);
					    }
				    }

				unset($blames[($line - 1)]);
			    } //end if
		    } //end foreach

		// Now go through and give the authors some credit for
		// all the lines that do not have errors.
		foreach ($blames as $line)
		    {
			$author = $this->getAuthor($line);
			if ($author !== false)
			    {
				if (isset($authors[$author]) === false)
				    {
					// This author doesn't have any errors.
					if (PHP_CODESNIFFER_VERBOSITY > 0)
					    {
						$authors[$author] = 0;
						$praise[$author]  = array(
								     "good" => 1,
								     "bad"  => 0,
								    );
					    }
				    }
				else
				    {
					$praise[$author]["good"]++;
				    }
			    }
		    } //end foreach
	    } //end _countBlamesAndPraises()


	/**
	 * Show collected error report
	 *
	 * @param int   $errors      Total number of errors
	 * @param array $authors     List of all authors
	 * @param array $praise      Blames and praises
	 * @param bool  $showSources True is source should be shown
	 * @param int   $width       Report width
	 * @param bool  $toScreen    True if report should be printed to screen
	 *
	 * @return int Number of errors reported
	 *
	 * @internalconst PHP_CODESNIFFER_INTERACTIVE Interactive
	 *
	 * @untranslatable count
	 * @untranslatable PHP_Timer
	 */

	private function _showErrors($errors, array $authors, array $praise, $showSources, $width, $toScreen)
	    {
		if ($errors === 0)
		    {
			// Nothing to show.
			return 0;
		    }
		else
		    {
			arsort($authors);

			echo PHP_EOL . _("PHP CODE SNIFFER") . " " . $this->reportName . " " . _("BLAME SUMMARY") . PHP_EOL;
			echo str_repeat("-", $width) . PHP_EOL;
			if ($showSources === true)
			    {
				echo _("AUTHOR   SOURCE") . str_repeat(" ", ($width - 43)) . _("(Author %) (Overall %) COUNT") . PHP_EOL;
				echo str_repeat("-", $width) . PHP_EOL;
			    }
			else
			    {
				echo _("AUTHOR") . str_repeat(" ", ($width - 34)) . _("(Author %) (Overall %) COUNT") . PHP_EOL;
				echo str_repeat("-", $width) . PHP_EOL;
			    }

			foreach ($authors as $author => $count)
			    {
				if ($praise[$author]["good"] === 0)
				    {
					$percent = 0;
				    }
				else
				    {
					$total   = ($praise[$author]["bad"] + $praise[$author]["good"]);
					$percent = round(($praise[$author]["bad"] / $total * 100), 2);
				    }

				$overallPercent = "(" . round((($count / $errors) * 100), 2) . ")";
				$authorPercent  = "(" . $percent . ")";

				$line = str_repeat(" ", (6 - strlen($count))) . $count;
				$line = str_repeat(" ", (12 - strlen($overallPercent))) . $overallPercent . $line;
				$line = str_repeat(" ", (11 - strlen($authorPercent))) . $authorPercent . $line;
				$line = $author . str_repeat(" ", ($width - strlen($author) - strlen($line))) . $line;

				echo $line . PHP_EOL;

				if ($showSources === true && isset($sources[$author]) === true)
				    {
					$errors = $sources[$author];
					asort($errors);
					$errors = array_reverse($errors);

					foreach ($errors as $source => $count)
					    {
						if ($source !== "count")
						    {
							$line = str_repeat(" ", (5 - strlen($count))) . $count;
							echo "         " . $source . str_repeat(" ", ($width - 14 - strlen($source))) . $line . PHP_EOL;
						    }
					    }
				    }
			    } //end foreach

			echo str_repeat("-", $width) . PHP_EOL;
			echo _("A TOTAL OF") . " " . $errors . " " . _("SNIFF VIOLATION(S)") . " ";
			echo _("WERE COMMITTED BY") . " " . count($authors) . " " . _("AUTHOR(S)") . PHP_EOL;
			echo str_repeat("-", $width) . PHP_EOL . PHP_EOL;

			if ($toScreen === true && PHP_CODESNIFFER_INTERACTIVE === false && class_exists("PHP_Timer", false) === true)
			    {
				echo PHP_Timer::resourceUsage() . PHP_EOL . PHP_EOL;
			    }

			return $errors;
		    } //end if
	    } //end _showErrors()


	/**
	 * Extract the author from a blame line.
	 *
	 * @param string $line Line to parse.
	 *
	 * @return mixed string or false if impossible to recover.
	 */

	abstract protected function getAuthor($line);


	/**
	 * Gets the blame output.
	 *
	 * @param string $filename File to blame.
	 *
	 * @return array
	 */

	abstract protected function getBlameContent($filename);


    } //end class

?>
