<?php

/**
 * PHP version 5
 *
 * @package Logics\BuildTools\CodeSniffer\PEAR
 */

namespace Logics\BuildTools\CodeSniffer\PEAR;

use \Logics\BuildTools\CodeSniffer\AbstractPatternSniff;

/**
 * Verifies that control statements conform to their coding standards.
 *
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @author    Marc McIntyre <mmcintyre@squiz.net>
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2006-2012 Squiz Pty Ltd (ABN 77 084 670 600)
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 * @version   SVN: $Date: 2016-08-16 23:39:01 +0900 (Tue, 16 Aug 2016) $ $Revision: 26 $
 * @link      $HeadURL: https://open.logics.net.au/buildtools/codesniffer/tags/0.1.3/src/CodeSniffer/Standards/PEAR/Sniffs/ControlStructures/ControlSignatureSniff.php $
 */

class ControlSignatureSniff extends AbstractPatternSniff
    {

	/**
	 * If true, comments will be ignored if they are found in the code.
	 *
	 * @var bool
	 */
	public $ignoreComments = true;

	/**
	 * Returns the patterns that this test wishes to verify.
	 *
	 * @return array(string)
	 *
	 * @untranslatable do {EOL...} while (...);EOL
	 * @untranslatable while (...) {EOL
	 * @untranslatable for (...) {EOL
	 * @untranslatable if (...) {EOL
	 * @untranslatable foreach (...) {EOL
	 * @untranslatable } else if (...) {EOL
	 * @untranslatable } elseif (...) {EOL
	 * @untranslatable } else {EOL
	 * @untranslatable do {EOL
	 */

	protected function getPatterns()
	    {
		return array(
			"do {EOL...} while (...);EOL",
			"while (...) {EOL",
			"for (...) {EOL",
			"if (...) {EOL",
			"foreach (...) {EOL",
			"} else if (...) {EOL",
			"} elseif (...) {EOL",
			"} else {EOL",
			"do {EOL",
		       );
	    } //end getPatterns()


    } //end class

?>
