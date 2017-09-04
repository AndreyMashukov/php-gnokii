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
 * Squiz_Sniffs_CSS_NamedColoursSniff.
 *
 * Ensure colour names are not used.
 *
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2006-2012 Squiz Pty Ltd (ABN 77 084 670 600)
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 * @version   SVN: $Date: 2016-08-16 23:39:01 +0900 (Tue, 16 Aug 2016) $ $Revision: 26 $
 * @link      $HeadURL: https://open.logics.net.au/buildtools/codesniffer/tags/0.1.3/src/CodeSniffer/Standards/Squiz/Sniffs/CSS/NamedColoursSniff.php $
 *
 * @untranslatable CSS
 * @untranslatable aqua
 * @untranslatable black
 * @untranslatable blue
 * @untranslatable fuchsia
 * @untranslatable gray
 * @untranslatable green
 * @untranslatable lime
 * @untranslatable maroon
 * @untranslatable navy
 * @untranslatable olive
 * @untranslatable orange
 * @untranslatable purple
 * @untranslatable red
 * @untranslatable silver
 * @untranslatable teal
 * @untranslatable white
 * @untranslatable yellow
 */

class NamedColoursSniff implements Sniff
    {

	/**
	 * A list of tokenizers this sniff supports.
	 *
	 * @var array
	 */
	public $supportedTokenizers = array("CSS");

	/**
	 * A list of named colours.
	 *
	 * This is the list of standard colours defined in the CSS spec.
	 *
	 * @var array
	 */
	public $colourNames = array(
			       "aqua"    => "aqua",
			       "black"   => "black",
			       "blue"    => "blue",
			       "fuchsia" => "fuchsia",
			       "gray"    => "gray",
			       "green"   => "green",
			       "lime"    => "lime",
			       "maroon"  => "maroon",
			       "navy"    => "navy",
			       "olive"   => "olive",
			       "orange"  => "orange",
			       "purple"  => "purple",
			       "red"     => "red",
			       "silver"  => "silver",
			       "teal"    => "teal",
			       "white"   => "white",
			       "yellow"  => "yellow",
			      );

	/**
	 * Returns the token types that this sniff is interested in.
	 *
	 * @return int[]
	 */

	public function register()
	    {
		return array(T_STRING);
	    } //end register()


	/**
	 * Processes the tokens that this sniff is interested in.
	 *
	 * @param File $phpcsFile The file where the token was found.
	 * @param int  $stackPtr  The position in the stack where the token was found.
	 *
	 * @return void
	 *
	 * @internalconst T_HASH          T_HASH token
	 * @internalconst T_STRING_CONCAT T_STRING_CONCAT token
	 *
	 * @untranslatable Forbidden
	 */

	public function process(File $phpcsFile, $stackPtr)
	    {
		$tokens = &$phpcsFile->tokens;

		if ($tokens[($stackPtr - 1)]["code"] === T_HASH || $tokens[($stackPtr - 1)]["code"] === T_STRING_CONCAT)
		    {
			// Class name.
			return;
		    }

		if (isset($this->colourNames[strtolower($tokens[$stackPtr]["content"])]) === true)
		    {
			$error = _("Named colours are forbidden; use hex, rgb, or rgba values instead");
			$phpcsFile->addError($error, $stackPtr, "Forbidden");
		    }
	    } //end process()


    } //end class

?>