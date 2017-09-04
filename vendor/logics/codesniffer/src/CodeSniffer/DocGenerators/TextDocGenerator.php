<?php

/**
 * A doc generator that outputs text-based documentation.
 *
 * PHP version 5.6
 *
 * @package Logics\BuildTools\CodeSniffer
 */

namespace Logics\BuildTools\CodeSniffer;

/**
 * A doc generator that outputs text-based documentation.
 *
 * Output is designed to be displayed in a terminal and is wrapped to 100 characters.
 *
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @author    Marc McIntyre <mmcintyre@squiz.net>
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2006-2012 Squiz Pty Ltd (ABN 77 084 670 600)
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 * @version   SVN: $Date: 2016-08-16 23:39:01 +0900 (Tue, 16 Aug 2016) $ $Revision: 26 $
 * @link      $HeadURL: https://open.logics.net.au/buildtools/codesniffer/tags/0.1.3/src/CodeSniffer/DocGenerators/TextDocGenerator.php $
 */

class TextDocGenerator extends DocGenerator
    {

	/**
	 * Process the documentation for a single sniff.
	 *
	 * @param DOMNode $doc The DOMNode object for the sniff.
	 *                     It represents the "documentation" tag in the XML
	 *                     standard file.
	 *
	 * @return void
	 *
	 * @untranslatable standard
	 * @untranslatable code_comparison
	 */

	public function processSniff(DOMNode $doc)
	    {
		$this->printTitle($doc);

		foreach ($doc->childNodes as $node)
		    {
			if ($node->nodeName === "standard")
			    {
				$this->printTextBlock($node);
			    }
			else if ($node->nodeName === "code_comparison")
			    {
				$this->printCodeComparisonBlock($node);
			    }
		    }
	    } //end processSniff()


	/**
	 * Prints the title area for a single sniff.
	 *
	 * @param DOMNode $doc The DOMNode object for the sniff.
	 *                     It represents the "documentation" tag in the XML
	 *                     standard file.
	 *
	 * @return void
	 */

	protected function printTitle(DOMNode $doc)
	    {
		$title    = $this->getTitle($doc);
		$standard = $this->getStandard();

		echo PHP_EOL;
		echo str_repeat("-", (strlen($standard . " " . _("CODING STANDARD") . ": " . $title) + 4));
		echo strtoupper(PHP_EOL . "| " . $standard . " " . _("CODING STANDARD") . ": " . $title . " |" . PHP_EOL);
		echo str_repeat("-", (strlen($standard . " " . _("CODING STANDARD") . ": " . $title) + 4));
		echo PHP_EOL . PHP_EOL;
	    } //end printTitle()


	/**
	 * Print a text block found in a standard.
	 *
	 * @param DOMNode $node The DOMNode object for the text block.
	 *
	 * @return void
	 *
	 * @untranslatable <em>
	 * @untranslatable </em>
	 */

	protected function printTextBlock(DOMNode $node)
	    {
		$text = trim($node->nodeValue);
		$text = str_replace("<em>", "*", $text);
		$text = str_replace("</em>", "*", $text);

		$lines    = array();
		$tempLine = "";
		$words    = explode(" ", $text);

		foreach ($words as $word)
		    {
			if (strlen($tempLine . $word) >= 99)
			    {
				if (strlen($tempLine . $word) === 99)
				    {
					// Adding the extra space will push us to the edge
					// so we are done.
					$lines[]  = $tempLine . $word;
					$tempLine = "";
				    }
				else if (strlen($tempLine . $word) === 100)
				    {
					// We are already at the edge, so we are done.
					$lines[]  = $tempLine . $word;
					$tempLine = "";
				    }
				else
				    {
					$lines[]  = rtrim($tempLine);
					$tempLine = $word . " ";
				    }
			    }
			else
			    {
				$tempLine .= $word . " ";
			    } //end if
		    } //end foreach

		if ($tempLine !== "")
		    {
			$lines[] = rtrim($tempLine);
		    }

		echo implode(PHP_EOL, $lines) . PHP_EOL . PHP_EOL;
	    } //end printTextBlock()


	/**
	 * Print a code comparison block found in a standard.
	 *
	 * @param DOMNode $node The DOMNode object for the code comparison block.
	 *
	 * @return void
	 *
	 * @untranslatable code
	 * @untranslatable title
	 * @untranslatable <em>
	 * @untranslatable </em>
	 */

	protected function printCodeComparisonBlock(DOMNode $node)
	    {
		$codeBlocks = $node->getElementsByTagName("code");
		$first      = trim($codeBlocks->item(0)->nodeValue);
		$firstTitle = $codeBlocks->item(0)->getAttribute("title");

		$firstTitleLines = $this->_getTitleLines($firstTitle);

		$first      = str_replace("<em>", "", $first);
		$first      = str_replace("</em>", "", $first);
		$firstLines = explode("\n", $first);

		$second      = trim($codeBlocks->item(1)->nodeValue);
		$secondTitle = $codeBlocks->item(1)->getAttribute("title");

		$secondTitleLines = $this->_getTitleLines($secondTitle);

		$second      = str_replace("<em>", "", $second);
		$second      = str_replace("</em>", "", $second);
		$secondLines = explode("\n", $second);

		$maxCodeLines  = max(count($firstLines), count($secondLines));
		$maxTitleLines = max(count($firstTitleLines), count($secondTitleLines));

		echo str_repeat("-", 41);
		echo " " . _("CODE COMPARISON") . " ";
		echo str_repeat("-", 42) . PHP_EOL;

		for ($i = 0; $i < $maxTitleLines; $i++)
		    {
			$firstLineText  = (isset($firstTitleLines[$i]) === true) ? $firstTitleLines[$i] : "";
			$secondLineText = (isset($secondTitleLines[$i]) === true) ? $secondTitleLines[$i] : "";

			echo "| ";
			echo $firstLineText . str_repeat(" ", (46 - strlen($firstLineText)));
			echo " | ";
			echo $secondLineText . str_repeat(" ", (47 - strlen($secondLineText)));
			echo " |" . PHP_EOL;
		    } //end for

		echo str_repeat("-", 100) . PHP_EOL;

		for ($i = 0; $i < $maxCodeLines; $i++)
		    {
			$firstLineText  = (isset($firstLines[$i]) === true) ? $firstLines[$i] : "";
			$secondLineText = (isset($secondLines[$i]) === true) ? $secondLines[$i] : "";

			echo "| ";
			echo $firstLineText . str_repeat(" ", (47 - strlen($firstLineText)));
			echo "| ";
			echo $secondLineText . str_repeat(" ", (48 - strlen($secondLineText)));
			echo "|" . PHP_EOL;
		    } //end for

		echo str_repeat("-", 100) . PHP_EOL . PHP_EOL;
	    } //end printCodeComparisonBlock()


	/**
	 * Get title lines as array
	 *
	 * @param string $title Title string
	 *
	 * @return array
	 */

	private function _getTitleLines($title)
	    {
		$titleLines = array();
		$tempTitle  = "";
		$words      = explode(" ", $title);

		foreach ($words as $word)
		    {
			if (strlen($tempTitle . $word) >= 45)
			    {
				if (strlen($tempTitle . $word) === 45)
				    {
					// Adding the extra space will push us to the edge
					// so we are done.
					$titleLines[] = $tempTitle . $word;
					$tempTitle    = "";
				    }
				else if (strlen($tempTitle . $word) === 46)
				    {
					// We are already at the edge, so we are done.
					$titleLines[] = $tempTitle . $word;
					$tempTitle    = "";
				    }
				else
				    {
					$titleLines[] = $tempTitle;
					$tempTitle    = $word;
				    }
			    }
			else
			    {
				$tempTitle .= $word . " ";
			    } //end if
		    } //end foreach

		if ($tempTitle !== "")
		    {
			$titleLines[] = $tempTitle;
		    }

		return $titleLines;
	    } //end _getTitleLines()


    } //end class

?>
