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
 * Squiz_Sniffs_CSS_LowercaseStyleDefinitionSniff.
 *
 * Ensure that all style definitions are in lowercase.
 *
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2006-2012 Squiz Pty Ltd (ABN 77 084 670 600)
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 * @version   SVN: $Date: 2016-08-16 23:39:01 +0900 (Tue, 16 Aug 2016) $ $Revision: 26 $
 * @link      $HeadURL: https://open.logics.net.au/buildtools/codesniffer/tags/0.1.3/src/CodeSniffer/Standards/Squiz/Sniffs/CSS/LowercaseStyleDefinitionSniff.php $
 *
 * @untranslatable CSS
 */

class LowercaseStyleDefinitionSniff implements Sniff
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
	 * @internalconst T_OPEN_CURLY_BRACKET T_OPEN_CURLY_BRACKET token
	 */

	public function register()
	    {
		return array(T_OPEN_CURLY_BRACKET);
	    } //end register()


	/**
	 * Processes the tokens that this sniff is interested in.
	 *
	 * @param File $phpcsFile The file where the token was found.
	 * @param int  $stackPtr  The position in the stack where the token was found.
	 *
	 * @return void
	 *
	 * @internalconst T_STYLE T_STYLE token
	 *
	 * @untranslatable FoundUpper
	 * @untranslatable %s
	 */

	public function process(File $phpcsFile, $stackPtr)
	    {
		$tokens = &$phpcsFile->tokens;
		$start  = ($stackPtr + 1);
		$end    = ($tokens[$stackPtr]["bracket_closer"] - 1);

		for ($i = $start; $i <= $end; $i++)
		    {
			if ($tokens[$i]["code"] === T_STRING || $tokens[$i]["code"] === T_STYLE)
			    {
				$expected = strtolower($tokens[$i]["content"]);
				if ($expected !== $tokens[$i]["content"])
				    {
					$error = _("Style definitions must be lowercase; expected") . " %s " . _("but found") . " %s";
					$data  = array(
						  $expected,
						  $tokens[$i]["content"],
						 );
					$phpcsFile->addError($error, $i, "FoundUpper", $data);
				    }
			    }
		    }
	    } //end process()


    } //end class

?>
