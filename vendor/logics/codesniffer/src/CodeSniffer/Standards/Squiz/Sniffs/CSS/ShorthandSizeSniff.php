<?php

/**
 * PHP version 5
 *
 * @package Logics\BuildTools\CodeSniffer\Squiz
 */

namespace Logics\BuildTools\CodeSniffer\Squiz;

use \Logics\BuildTools\CodeSniffer\File;
use \Logics\BuildTools\CodeSniffer\Sniff;

/**
 * Squiz_Sniffs_CSS_ShorthandSizeSniff.
 *
 * Ensure sizes are defined using shorthand notation where possible, except in the
 * case where shorthand becomes 3 values.
 *
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2006-2012 Squiz Pty Ltd (ABN 77 084 670 600)
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 * @version   SVN: $Date: 2016-08-16 23:39:01 +0900 (Tue, 16 Aug 2016) $ $Revision: 26 $
 * @link      $HeadURL: https://open.logics.net.au/buildtools/codesniffer/tags/0.1.3/src/CodeSniffer/Standards/Squiz/Sniffs/CSS/ShorthandSizeSniff.php $
 *
 * @untranslatable CSS
 * @untranslatable background-position
 * @untranslatable box-shadow
 * @untranslatable transform-origin
 * @untranslatable -webkit-transform-origin
 * @untranslatable -ms-transform-origin
 */

class ShorthandSizeSniff implements Sniff
    {

	/**
	 * A list of tokenizers this sniff supports.
	 *
	 * @var array
	 */
	public $supportedTokenizers = array("CSS");

	/**
	 * A list of styles that we shouldn't check.
	 *
	 * These have values that looks like sizes, but are not.
	 *
	 * @var array
	 */
	public $excludeStyles = array(
				 "background-position"      => "background-position",
				 "box-shadow"               => "box-shadow",
				 "transform-origin"         => "transform-origin",
				 "-webkit-transform-origin" => "-webkit-transform-origin",
				 "-ms-transform-origin"     => "-ms-transform-origin",
				);

	/**
	 * Returns the token types that this sniff is interested in.
	 *
	 * @return int[]
	 *
	 * @internalconst T_STYLE T_STYLE token
	 */

	public function register()
	    {
		return array(T_STYLE);
	    } //end register()


	/**
	 * Processes the tokens that this sniff is interested in.
	 *
	 * @param File $phpcsFile The file where the token was found.
	 * @param int  $stackPtr  The position in the stack where the token was found.
	 *
	 * @return void
	 *
	 * @internalconst T_SEMICOLON T_SEMICOLON token
	 *
	 * @untranslatable !important
	 * @untranslatable %s
	 * @untranslatable NotAllowed
	 * @untranslatable \"%s\"
	 * @untranslatable NotUsed
	 */

	public function process(File $phpcsFile, $stackPtr)
	    {
		$tokens = &$phpcsFile->tokens;

		// Some styles look like shorthand but are not actually a set of 4 sizes.
		$style = strtolower($tokens[$stackPtr]["content"]);
		if (isset($this->excludeStyles[$style]) === true)
		    {
			return;
		    }

		// Get the whole style content.
		$end         = $phpcsFile->findNext(T_SEMICOLON, ($stackPtr + 1));
		$origContent = $phpcsFile->getTokensAsString(($stackPtr + 1), ($end - $stackPtr - 1));
		$origContent = trim($origContent, ": ");

		// Account for a !important annotation.
		$content = $origContent;
		if (substr($content, -10) === "!important")
		    {
			$content = substr($content, 0, -10);
			$content = trim($content);
		    }

		// Check if this style value is a set of numbers with optional prefixes.
		$content = preg_replace("/\s+/", " ", $content);
		$values  = array();
		$num     = preg_match_all("/([0-9]+)([a-zA-Z]{2}\s+|%\s+|\s+)/", $content . " ", $values, PREG_SET_ORDER);

		// Only interested in styles that have multiple sizes defined.
		if ($num < 2)
		    {
			return;
		    }

		// Rebuild the content we matched to ensure we got everything.
		$matched = "";
		foreach ($values as $value)
		    {
			$matched .= $value[0];
		    }

		if ($content !== trim($matched))
		    {
			return;
		    }

		if ($num === 3)
		    {
			$expected = trim($content . " " . $values[1][1] . $values[1][2]);
			$error    = _("Shorthand syntax not allowed here; use") . " %s " . _("instead");
			$data     = array($expected);
			$phpcsFile->addError($error, $stackPtr, "NotAllowed", $data);

			return;
		    } //end if

		if ($num === 2)
		    {
			if ($values[0][0] !== $values[1][0])
			    {
				// Both values are different, so it is already shorthand.
				return;
			    }
		    }
		else if ($values[0][0] !== $values[2][0] || $values[1][0] !== $values[3][0])
		    {
			// Can't shorthand this.
			return;
		    }

		if ($values[0][0] === $values[1][0])
		    {
			// All values are the same.
			$expected = $values[0][0];
		    }
		else
		    {
			$expected = $values[0][0] . " " . $values[1][0];
		    }

		$expected = preg_replace("/\s+/", " ", trim($expected));

		$error = _("Size definitions must use shorthand if available; expected") . " \"%s\" " . _("but found") . " \"%s\"";
		$data  = array(
			  $expected,
			  $content,
			 );

		$phpcsFile->addError($error, $stackPtr, "NotUsed", $data);
	    } //end process()


    } //end class

?>
