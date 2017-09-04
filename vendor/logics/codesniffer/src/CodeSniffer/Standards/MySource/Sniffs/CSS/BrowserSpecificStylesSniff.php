<?php

/**
 * BrowserSpecificStylesSniff.
 *
 * PHP version 5
 *
 * @package Logics\BuildTools\CodeSniffer\MySource
 */

namespace Logics\BuildTools\CodeSniffer\MySource;

use \Logics\BuildTools\CodeSniffer\File;
use \Logics\BuildTools\CodeSniffer\Sniff;

/**
 * BrowserSpecificStylesSniff.
 *
 * Ensure that browser-specific styles are not used.
 *
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2006-2012 Squiz Pty Ltd (ABN 77 084 670 600)
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 * @version   SVN: $Date: 2016-08-16 23:39:01 +0900 (Tue, 16 Aug 2016) $ $Revision: 26 $
 * @link      $HeadURL: https://open.logics.net.au/buildtools/codesniffer/tags/0.1.3/src/CodeSniffer/Standards/MySource/Sniffs/CSS/BrowserSpecificStylesSniff.php $
 *
 * @untranslatable CSS
 * @untranslatable moz
 * @untranslatable ie
 * @untranslatable ie7
 * @untranslatable ie8
 * @untranslatable webkit
 */

class BrowserSpecificStylesSniff implements Sniff
    {

	/**
	 * A list of tokenizers this sniff supports.
	 *
	 * @var array
	 */
	public $supportedTokenizers = array("CSS");

	/**
	 * A list of specific stylesheet suffixes we allow.
	 *
	 * These stylesheets contain browser specific styles
	 * so this sniff ignore them files in the form:
	 * *_moz.css and *_ie7.css etc.
	 *
	 * @var array
	 */
	protected $specificStylesheets = array(
					  "moz",
					  "ie",
					  "ie7",
					  "ie8",
					  "webkit",
					 );

	/**
	 * Returns the token types that this sniff is interested in.
	 *
	 * @return array(int)
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
	 * @untranslatable .css
	 * @untranslatable ForbiddenStyle
	 */

	public function process(File $phpcsFile, $stackPtr)
	    {
		// Ignore files with browser-specific suffixes.
		$filename  = $phpcsFile->getFilename();
		$breakChar = strrpos($filename, "_");
		if ($breakChar === false || substr($filename, -4) !== ".css" ||
		    in_array(substr($filename, ($breakChar + 1), -4), $this->specificStylesheets) === false)
		    {
			$tokens  = &$phpcsFile->tokens;
			$content = $tokens[$stackPtr]["content"];

			if ($content{0} === "-")
			    {
				$error = _("Browser-specific styles are not allowed");
				$phpcsFile->addError($error, $stackPtr, "ForbiddenStyle");
			    }
		    }
	    } //end process()


    } //end class

?>