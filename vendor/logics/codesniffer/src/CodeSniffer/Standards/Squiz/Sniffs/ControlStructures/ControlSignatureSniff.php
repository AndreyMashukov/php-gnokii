<?php

/**
 * PHP version 5
 *
 * @package Logics\BuildTools\CodeSniffer\Squiz
 */

namespace Logics\BuildTools\CodeSniffer\Squiz;

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
 * @link      $HeadURL: https://open.logics.net.au/buildtools/codesniffer/tags/0.1.3/src/CodeSniffer/Standards/Squiz/Sniffs/ControlStructures/ControlSignatureSniff.php $
 *
 * @untranslatable PHP
 * @untranslatable JS
 */

class ControlSignatureSniff extends AbstractPatternSniff
    {

	/**
	 * A list of tokenizers this sniff supports.
	 *
	 * @var array
	 */
	public $supportedTokenizers = array(
				       "PHP",
				       "JS",
				      );

	/**
	 * Returns the patterns that this test wishes to verify.
	 *
	 * @return array(string)
	 *
	 * @untranslatable try {EOL...} catch (...) {EOL
	 * @untranslatable do {EOL...} while (...);EOL
	 * @untranslatable while (...) {EOL
	 * @untranslatable for (...) {EOL
	 * @untranslatable if (...) {EOL
	 * @untranslatable foreach (...) {EOL
	 * @untranslatable } else if (...) {EOL
	 * @untranslatable } elseif (...) {EOL
	 * @untranslatable } else {EOL
	 */

	protected function getPatterns()
	    {
		return array(
			"try {EOL...} catch (...) {EOL",
			"do {EOL...} while (...);EOL",
			"while (...) {EOL",
			"for (...) {EOL",
			"if (...) {EOL",
			"foreach (...) {EOL",
			"} else if (...) {EOL",
			"} elseif (...) {EOL",
			"} else {EOL",
		       );
	    } //end getPatterns()


    } //end class

?>
