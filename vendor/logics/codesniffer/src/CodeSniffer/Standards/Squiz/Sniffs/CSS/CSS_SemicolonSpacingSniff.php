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
 * CSS_SemicolonSpacingSniff
 *
 * Ensure each style definition has a semi-colon and it is spaced correctly.
 *
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2006-2012 Squiz Pty Ltd (ABN 77 084 670 600)
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 * @version   SVN: $Date: 2016-08-16 23:39:01 +0900 (Tue, 16 Aug 2016) $ $Revision: 26 $
 * @link      $HeadURL: https://open.logics.net.au/buildtools/codesniffer/tags/0.1.3/src/CodeSniffer/Standards/Squiz/Sniffs/CSS/CSS_SemicolonSpacingSniff.php $
 *
 * @untranslatable CSS
 */

class CSS_SemicolonSpacingSniff implements Sniff
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
	 * @internalconst T_SEMICOLON T_SEMICOLON token
	 *
	 * @untranslatable NotAtEnd
	 * @untranslatable SpaceFound
	 * @untranslatable %s
	 */

	public function process(File $phpcsFile, $stackPtr)
	    {
		$tokens = &$phpcsFile->tokens;

		$semicolon = $phpcsFile->findNext(T_SEMICOLON, ($stackPtr + 1));
		if ($semicolon === false || $tokens[$semicolon]["line"] !== $tokens[$stackPtr]["line"])
		    {
			$error = _("Style definitions must end with a semicolon");
			$phpcsFile->addError($error, $stackPtr, "NotAtEnd");
		    }
		else if ($tokens[($semicolon - 1)]["code"] === T_WHITESPACE)
		    {
			$length = strlen($tokens[($semicolon - 1)]["content"]);
			$error  = _("Expected 0 spaces before semicolon in style definition;") . " %s " . _("found");
			$data   = array($length);
			$phpcsFile->addError($error, $stackPtr, "SpaceFound", $data);
		    }
	    } //end process()


    } //end class

?>
