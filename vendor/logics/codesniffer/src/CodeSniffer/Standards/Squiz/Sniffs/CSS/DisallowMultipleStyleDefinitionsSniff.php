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
 * Squiz_Sniffs_CSS_DisallowMultipleStyleDefinitionsSniff.
 *
 * Ensure that each style definition is on a line by itself.
 *
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2006-2012 Squiz Pty Ltd (ABN 77 084 670 600)
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 * @version   SVN: $Date: 2016-08-16 23:39:01 +0900 (Tue, 16 Aug 2016) $ $Revision: 26 $
 * @link      $HeadURL: https://open.logics.net.au/buildtools/codesniffer/tags/0.1.3/src/CodeSniffer/Standards/Squiz/Sniffs/CSS/DisallowMultipleStyleDefinitionsSniff.php $
 *
 * @untranslatable CSS
 */

class DisallowMultipleStyleDefinitionsSniff implements Sniff
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
	 * @internalconst T_STYLE T_STYLE token
	 *
	 * @untranslatable Found
	 */

	public function process(File $phpcsFile, $stackPtr)
	    {
		$tokens = &$phpcsFile->tokens;
		$next   = $phpcsFile->findNext(T_STYLE, ($stackPtr + 1));
		if ($next !== false)
		    {
			if ($tokens[$next]["line"] === $tokens[$stackPtr]["line"])
			    {
				$error = _("Each style definition must be on a line by itself");
				$phpcsFile->addError($error, $next, "Found");
			    }
		    }
	    } //end process()


    } //end class

?>
