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
 * Squiz_Sniffs_CSS_DuplicateStyleDefinitionSniff.
 *
 * Check for duplicate style definitions in the same class.
 *
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2006-2012 Squiz Pty Ltd (ABN 77 084 670 600)
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 * @version   SVN: $Date: 2016-08-16 23:39:01 +0900 (Tue, 16 Aug 2016) $ $Revision: 26 $
 * @link      $HeadURL: https://open.logics.net.au/buildtools/codesniffer/tags/0.1.3/src/CodeSniffer/Standards/Squiz/Sniffs/CSS/DuplicateStyleDefinitionSniff.php $
 *
 * @untranslatable CSS
 */

class DuplicateStyleDefinitionSniff implements Sniff
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
	 * @untranslatable Found
	 * @untranslatable %s
	 */

	public function process(File $phpcsFile, $stackPtr)
	    {
		$tokens = &$phpcsFile->tokens;

		// Find the content of each style definition name.
		$end  = $tokens[$stackPtr]["bracket_closer"];
		$next = $phpcsFile->findNext(T_STYLE, ($stackPtr + 1), $end);
		if ($next !== false)
		    {
			$styleNames = array();

			while ($next !== false)
			    {
				$name = $tokens[$next]["content"];
				if (isset($styleNames[$name]) === true)
				    {
					$first = $styleNames[$name];
					$error = _("Duplicate style definition found; first defined on line") . " %s";
					$data  = array($tokens[$first]["line"]);
					$phpcsFile->addError($error, $next, "Found", $data);
				    }
				else
				    {
					$styleNames[$name] = $next;
				    }

				$next = $phpcsFile->findNext(T_STYLE, ($next + 1), $end);
			    } //end while
		    } //end if
	    } //end process()


    } //end class

?>
