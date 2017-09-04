<?php

/**
 * PHP version 5
 *
 * @package Logics\BuildTools\CodeSniffer\Beauty
 */

namespace Logics\BuildTools\CodeSniffer\Beauty;

use \Logics\BuildTools\CodeSniffer\AbstractPatternSniff;

/**
 * Verifies that control statements conform to their coding standards.
 *
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @author    Marc McIntyre <mmcintyre@squiz.net>
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2006-2012 Squiz Pty Ltd (ABN 77 084 670 600)
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   http://matrix.squiz.net/developer/tools/php_cs/licence BSD Licence
 * @version   SVN: $Date: 2016-08-16 23:39:01 +0900 (Tue, 16 Aug 2016) $ $Revision: 26 $
 * @link      $HeadURL: https://open.logics.net.au/buildtools/codesniffer/tags/0.1.3/src/CodeSniffer/Standards/Beauty/Sniffs/ControlStructures/ControlSignatureSniff.php $
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
	 * @untranslatable tryEOL...{EOL...}EOL...catch (...)EOL...{EOL
	 * @untranslatable doEOL {EOL...EOL}EOL while (...);EOL
	 * @untranslatable while (...)EOL...{EOL
	 * @untranslatable for (...)EOL...{EOL
	 * @untranslatable if (...)EOL...{EOL
	 * @untranslatable foreach (...)EOL...{EOL
	 * @untranslatable }EOLelse if (...)EOL...{EOL
	 * @untranslatable }EOLelseEOL...{EOL
	 */

	protected function getPatterns()
	    {
		return array(
			"tryEOL...{EOL...}EOL...catch (...)EOL...{EOL",
			"doEOL {EOL...EOL}EOL while (...);EOL",
			"while (...)EOL...{EOL",
			"for (...)EOL...{EOL",
			"if (...)EOL...{EOL",
			"foreach (...)EOL...{EOL",
			"}EOLelse if (...)EOL...{EOL",
			"}EOLelseEOL...{EOL",
		       );
	    } //end getPatterns()


    } //end class

?>
