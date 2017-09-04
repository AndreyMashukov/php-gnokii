<?php

/**
 * A doc generator that outputs documentation in one big HTML file.
 *
 * PHP version 5.6
 *
 * @package Logics\BuildTools\CodeSniffer
 */

namespace Logics\BuildTools\CodeSniffer;

/**
 * A doc generator that outputs documentation in one big HTML file.
 *
 * Output is in one large HTML file and is designed for you to style with
 * your own stylesheet. It contains a table of contents at the top with anchors
 * to each sniff.
 *
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @author    Marc McIntyre <mmcintyre@squiz.net>
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2006-2012 Squiz Pty Ltd (ABN 77 084 670 600)
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 * @version   SVN: $Date: 2016-08-16 23:39:01 +0900 (Tue, 16 Aug 2016) $ $Revision: 26 $
 * @link      $HeadURL: https://open.logics.net.au/buildtools/codesniffer/tags/0.1.3/src/CodeSniffer/DocGenerators/HTMLDocGenerator.php $
 */

class HTMLDocGenerator extends DocGenerator
    {

	/**
	 * Generates the documentation for a standard.
	 *
	 * @return void
	 *
	 * @see processSniff()
	 *
	 * @untranslatable documentation
	 */

	public function generate()
	    {
		ob_start();
		$this->printHeader();

		$standardFiles = $this->getStandardFiles();
		$this->printToc($standardFiles);

		foreach ($standardFiles as $standard)
		    {
			$doc = new DOMDocument();
			$doc->load($standard);
			$documentation = $doc->getElementsByTagName("documentation")->item(0);
			$this->processSniff($documentation);
		    }

		$this->printFooter();

		$content = ob_get_contents();
		ob_end_clean();

		echo $content;
	    } //end generate()


	/**
	 * Print the header of the HTML page.
	 *
	 * @return void
	 *
	 * @untranslatable <html>
	 * @untranslatable <head>
	 * @untranslatable <title>
	 * @untranslatable </title>
	 * @untranslatable <style>
	 * @untranslatable body {
	 * @untranslatable background-color: #FFFFFF;
	 * @untranslatable font-size: 14px;
	 * @untranslatable font-family: Arial, Helvetica, sans-serif;
	 * @untranslatable color: #000000;
	 * @untranslatable h1 {
	 * @untranslatable color: #666666;
	 * @untranslatable font-size: 20px;
	 * @untranslatable font-weight: bold;
	 * @untranslatable margin-top: 0px;
	 * @untranslatable background-color: #E6E7E8;
	 * @untranslatable padding: 20px;
	 * @untranslatable border: 1px solid #BBBBBB;
	 * @untranslatable h2 {
	 * @untranslatable color: #00A5E3;
	 * @untranslatable font-size: 16px;
	 * @untranslatable font-weight: normal;
	 * @untranslatable margin-top: 50px;
	 * @untranslatable .code-comparison {
	 * @untranslatable width: 100%;
	 * @untranslatable .code-comparison td {
	 * @untranslatable border: 1px solid #CCCCCC;
	 * @untranslatable .code-comparison-title, .code-comparison-code {
	 * @untranslatable font-size: 12px;
	 * @untranslatable vertical-align: top;
	 * @untranslatable padding: 4px;
	 * @untranslatable width: 50%;
	 * @untranslatable background-color: #F1F1F1;
	 * @untranslatable line-height: 15px;
	 * @untranslatable .code-comparison-code {
	 * @untranslatable font-family: Courier;
	 * @untranslatable background-color: #F9F9F9;
	 * @untranslatable .code-comparison-highlight {
	 * @untranslatable background-color: #DDF1F7;
	 * @untranslatable border: 1px solid #00A5E3;
	 * @untranslatable .tag-line {
	 * @untranslatable text-align: center;
	 * @untranslatable margin-top: 30px;
	 * @untranslatable .tag-line a {
	 * @untranslatable </style>
	 * @untranslatable </head>
	 * @untranslatable <body>
	 * @untranslatable <h1>
	 * @untranslatable </h1>
	 */

	protected function printHeader()
	    {
		$standard = $this->getStandard();
		echo "<html>" . PHP_EOL;
		echo " <head>" . PHP_EOL;
		echo "  <title>" . $standard . " " . _("Coding Standards") . "</title>" . PHP_EOL;
		echo "  <style>" . PHP_EOL;
		echo "   body {" . PHP_EOL;
		echo "       background-color: #FFFFFF;" . PHP_EOL;
		echo "       font-size: 14px;" . PHP_EOL;
		echo "       font-family: Arial, Helvetica, sans-serif;" . PHP_EOL;
		echo "       color: #000000;" . PHP_EOL;
		echo "   }" . PHP_EOL;

		echo "   h1 {" . PHP_EOL;
		echo "       color: #666666;" . PHP_EOL;
		echo "       font-size: 20px;" . PHP_EOL;
		echo "       font-weight: bold;" . PHP_EOL;
		echo "       margin-top: 0px;" . PHP_EOL;
		echo "       background-color: #E6E7E8;" . PHP_EOL;
		echo "       padding: 20px;" . PHP_EOL;
		echo "       border: 1px solid #BBBBBB;" . PHP_EOL;
		echo "   }" . PHP_EOL;

		echo "   h2 {" . PHP_EOL;
		echo "       color: #00A5E3;" . PHP_EOL;
		echo "       font-size: 16px;" . PHP_EOL;
		echo "       font-weight: normal;" . PHP_EOL;
		echo "       margin-top: 50px;" . PHP_EOL;
		echo "   }" . PHP_EOL;

		echo "   .code-comparison {" . PHP_EOL;
		echo "       width: 100%;" . PHP_EOL;
		echo "   }" . PHP_EOL;

		echo "   .code-comparison td {" . PHP_EOL;
		echo "       border: 1px solid #CCCCCC;" . PHP_EOL;
		echo "   }" . PHP_EOL;

		echo "   .code-comparison-title, .code-comparison-code {" . PHP_EOL;
		echo "       font-family: Arial, Helvetica, sans-serif;" . PHP_EOL;
		echo "       font-size: 12px;" . PHP_EOL;
		echo "       color: #000000;" . PHP_EOL;
		echo "       vertical-align: top;" . PHP_EOL;
		echo "       padding: 4px;" . PHP_EOL;
		echo "       width: 50%;" . PHP_EOL;
		echo "       background-color: #F1F1F1;" . PHP_EOL;
		echo "       line-height: 15px;" . PHP_EOL;
		echo "   }" . PHP_EOL;

		echo "   .code-comparison-code {" . PHP_EOL;
		echo "       font-family: Courier;" . PHP_EOL;
		echo "       background-color: #F9F9F9;" . PHP_EOL;
		echo "   }" . PHP_EOL;

		echo "   .code-comparison-highlight {" . PHP_EOL;
		echo "       background-color: #DDF1F7;" . PHP_EOL;
		echo "       border: 1px solid #00A5E3;" . PHP_EOL;
		echo "       line-height: 15px;" . PHP_EOL;
		echo "   }" . PHP_EOL;

		echo "   .tag-line {" . PHP_EOL;
		echo "       text-align: center;" . PHP_EOL;
		echo "       width: 100%;" . PHP_EOL;
		echo "       margin-top: 30px;" . PHP_EOL;
		echo "       font-size: 12px;" . PHP_EOL;
		echo "   }" . PHP_EOL;

		echo "   .tag-line a {" . PHP_EOL;
		echo "       color: #000000;" . PHP_EOL;
		echo "   }" . PHP_EOL;
		echo "  </style>" . PHP_EOL;
		echo " </head>" . PHP_EOL;
		echo " <body>" . PHP_EOL;
		echo "  <h1>" . $standard . " " . _("Coding Standards") . "</h1>" . PHP_EOL;
	    } //end printHeader()


	/**
	 * Print the table of contents for the standard.
	 *
	 * The TOC is just an unordered list of bookmarks to sniffs on the page.
	 *
	 * @param array $standardFiles An array of paths to the XML standard files.
	 *
	 * @return void
	 *
	 * @untranslatable <h2>
	 * @untranslatable </h2>
	 * @untranslatable <ul class=\"toc\">
	 * @untranslatable documentation
	 * @untranslatable <li><a href=\"#
	 * @untranslatable </a></li>
	 * @untranslatable </ul>
	 */

	protected function printToc(array $standardFiles)
	    {
		echo "  <h2>" . _("Table of Contents") . "</h2>" . PHP_EOL;
		echo "  <ul class=\"toc\">" . PHP_EOL;

		foreach ($standardFiles as $standard)
		    {
			$doc = new DOMDocument();
			$doc->load($standard);
			$documentation = $doc->getElementsByTagName("documentation")->item(0);
			$title         = $this->getTitle($documentation);
			echo "   <li><a href=\"#" . str_replace(" ", "-", $title) . "\">" . $title . "</a></li>" . PHP_EOL;
		    }

		echo "  </ul>" . PHP_EOL;
	    } //end printToc()


	/**
	 * Print the footer of the HTML page.
	 *
	 * @return void
	 *
	 * @untranslatable <div class=\"tag-line\">
	 * @untranslatable r
	 * @untranslatable <a href=\"http://pear.php.net/package/PHP_CodeSniffer\">PHP_CodeSniffer 1.4.5</a>
	 * @untranslatable </div>
	 * @untranslatable </body>
	 * @untranslatable </html>
	 */

	protected function printFooter()
	    {
		// Turn off strict errors so we don't get timezone warnings if people
		// don't have their timezone set.
		error_reporting(E_ALL);
		echo "  <div class=\"tag-line\">";
		echo _("Documentation generated on") . " " . date("r");
		echo " " . _("by") . " <a href=\"http://pear.php.net/package/PHP_CodeSniffer\">PHP_CodeSniffer 1.4.5</a>";
		echo "</div>" . PHP_EOL;
		error_reporting(E_ALL | E_STRICT);

		echo " </body>" . PHP_EOL;
		echo "</html>" . PHP_EOL;
	    } //end printFooter()


	/**
	 * Process the documentation for a single sniff.
	 *
	 * @param DOMNode $doc The DOMNode object for the sniff.
	 *                     It represents the "documentation" tag in the XML
	 *                     standard file.
	 *
	 * @return void
	 *
	 * @untranslatable <a name=\"
	 * @untranslatable <h2>
	 * @untranslatable </h2>
	 * @untranslatable standard
	 * @untranslatable code_comparison
	 */

	public function processSniff(DOMNode $doc)
	    {
		$title = $this->getTitle($doc);
		echo "  <a name=\"" . str_replace(" ", "-", $title) . "\" />" . PHP_EOL;
		echo "  <h2>" . $title . "</h2>" . PHP_EOL;

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
	 * Print a text block found in a standard.
	 *
	 * @param DOMNode $node The DOMNode object for the text block.
	 *
	 * @return void
	 *
	 * @untranslatable &lt;em&gt;
	 * @untranslatable &lt;/em&gt;
	 * @untranslatable <em>
	 * @untranslatable </em>
	 * @untranslatable <p class=\"text\">
	 * @untranslatable </p>
	 */

	protected function printTextBlock(DOMNode $node)
	    {
		$content = trim($node->nodeValue);
		$content = htmlspecialchars($content);

		// Allow em tags only.
		$content = str_replace("&lt;em&gt;", "<em>", $content);
		$content = str_replace("&lt;/em&gt;", "</em>", $content);

		echo "  <p class=\"text\">" . $content . "</p>" . PHP_EOL;
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
	 * @untranslatable </br>
	 * @untranslatable &nbsp;
	 * @untranslatable <em>
	 * @untranslatable <span class=\"code-comparison-highlight\">
	 * @untranslatable </em>
	 * @untranslatable </span>
	 * @untranslatable <table class=\"code-comparison\">
	 * @untranslatable <td class=\"code-comparison-title\">
	 * @untranslatable <td class=\"code-comparison-code\">
	 * @untranslatable </td>
	 * @untranslatable <tr>
	 * @untranslatable </tr>
	 * @untranslatable </table>
	 */

	protected function printCodeComparisonBlock(DOMNode $node)
	    {
		$codeBlocks = $node->getElementsByTagName("code");

		$firstTitle = $codeBlocks->item(0)->getAttribute("title");
		$first      = trim($codeBlocks->item(0)->nodeValue);
		$first      = str_replace("\n", "</br>", $first);
		$first      = str_replace(" ", "&nbsp;", $first);
		$first      = str_replace("<em>", "<span class=\"code-comparison-highlight\">", $first);
		$first      = str_replace("</em>", "</span>", $first);

		$secondTitle = $codeBlocks->item(1)->getAttribute("title");
		$second      = trim($codeBlocks->item(1)->nodeValue);
		$second      = str_replace("\n", "</br>", $second);
		$second      = str_replace(" ", "&nbsp;", $second);
		$second      = str_replace("<em>", "<span class=\"code-comparison-highlight\">", $second);
		$second      = str_replace("</em>", "</span>", $second);

		echo "  <table class=\"code-comparison\">" . PHP_EOL;
		echo "   <tr>" . PHP_EOL;
		echo "    <td class=\"code-comparison-title\">" . $firstTitle . "</td>" . PHP_EOL;
		echo "    <td class=\"code-comparison-title\">" . $secondTitle . "</td>" . PHP_EOL;
		echo "   </tr>" . PHP_EOL;
		echo "   <tr>" . PHP_EOL;
		echo "    <td class=\"code-comparison-code\">" . $first . "</td>" . PHP_EOL;
		echo "    <td class=\"code-comparison-code\">" . $second . "</td>" . PHP_EOL;
		echo "   </tr>" . PHP_EOL;
		echo "  </table>" . PHP_EOL;
	    } //end printCodeComparisonBlock()


    } //end class

?>