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
 * Squiz_Sniffs_CSS_ColourDefinitionSniff.
 *
 * Ensure colours are defined in upper-case and use shortcuts where possible.
 *
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2006-2012 Squiz Pty Ltd (ABN 77 084 670 600)
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 * @version   SVN: $Date: 2016-08-16 23:39:01 +0900 (Tue, 16 Aug 2016) $ $Revision: 26 $
 * @link      $HeadURL: https://open.logics.net.au/buildtools/codesniffer/tags/0.1.3/src/CodeSniffer/Standards/Squiz/Sniffs/CSS/ColourDefinitionSniff.php $
 *
 * @untranslatable CSS
 */

class ColourDefinitionSniff implements Sniff
    {

	/**
	 * A list of tokenizers this sniff supports.
	 *
	 * @var array
	 */
	public $supportedTokenizers = array("CSS");

	/**
	 * Returns the token types that this sniff is interested in.
	 *
	 * @return array(int)
	 *
	 * @internalconst T_COLOUR T_COLOUR token
	 */

	public function register()
	    {
		return array(T_COLOUR);
	    } //end register()


	/**
	 * Processes the tokens that this sniff is interested in.
	 *
	 * @param File $phpcsFile The file where the token was found.
	 * @param int  $stackPtr  The position in the stack where the token was found.
	 *
	 * @return void
	 *
	 * @untranslatable NotUpper
	 * @untranslatable Shorthand
	 * @untranslatable %s
	 */

	public function process(File $phpcsFile, $stackPtr)
	    {
		$tokens = &$phpcsFile->tokens;
		$colour = $tokens[$stackPtr]["content"];

		$expected = strtoupper($colour);
		if ($colour !== $expected)
		    {
			$error = _("CSS colours must be defined in uppercase; expected") . " %s " . _("but found") . " %s";
			$data  = array(
				  $expected,
				  $colour,
				 );
			$phpcsFile->addError($error, $stackPtr, "NotUpper", $data);
		    }

		// Now check if shorthand can be used.
		if (strlen($colour) === 7)
		    {
			if ($colour{1} === $colour{2} && $colour{3} === $colour{4} && $colour{5} === $colour{6})
			    {
				$expected = "#" . $colour{1} . $colour{3} . $colour{5};
				$error    = _("CSS colours must use shorthand if available; expected") . " %s " . _("but found") . " %s";
				$data     = array(
					     $expected,
					     $colour,
					    );
				$phpcsFile->addError($error, $stackPtr, "Shorthand", $data);
			    }
		    }
	    } //end process()


    } //end class

?>