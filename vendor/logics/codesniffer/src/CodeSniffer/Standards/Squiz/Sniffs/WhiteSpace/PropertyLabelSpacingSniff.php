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
 * Squiz_Sniffs_WhiteSpace_PropertyLabelSpacingSniff.
 *
 * Ensures that the colon in a property or label definition has a single
 * space after it and no space before it.
 *
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2006-2012 Squiz Pty Ltd (ABN 77 084 670 600)
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 * @version   SVN: $Date: 2016-08-16 23:39:01 +0900 (Tue, 16 Aug 2016) $ $Revision: 26 $
 * @link      $HeadURL: https://open.logics.net.au/buildtools/codesniffer/tags/0.1.3/src/CodeSniffer/Standards/Squiz/Sniffs/WhiteSpace/PropertyLabelSpacingSniff.php $
 *
 * @untranslatable JS
 */

class PropertyLabelSpacingSniff implements Sniff
    {

	/**
	 * A list of tokenizers this sniff supports.
	 *
	 * @var array
	 */
	public $supportedTokenizers = array("JS");

	/**
	 * Returns an array of tokens this test wants to listen for.
	 *
	 * @return array
	 *
	 * @internalconst T_PROPERTY T_PROPERTY token
	 * @internalconst T_LABEL    T_LABEL token
	 */

	public function register()
	    {
		return array(
			T_PROPERTY,
			T_LABEL,
		       );
	    } //end register()


	/**
	 * Processes this test, when one of its tokens is encountered.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $stackPtr  The position of the current token in the stack passed in $tokens.
	 *
	 * @return void
	 *
	 * @internalconst T_COLON T_COLON token
	 *
	 * @untranslatable Before
	 * @untranslatable After
	 */

	public function process(File $phpcsFile, $stackPtr)
	    {
		$tokens = &$phpcsFile->tokens;

		$colon = $phpcsFile->findNext(T_COLON, ($stackPtr + 1));

		if ($colon !== ($stackPtr + 1))
		    {
			$error = _("There must be no space before the colon in a property/label declaration");
			$phpcsFile->addError($error, $stackPtr, "Before");
		    }

		if ($tokens[($colon + 1)]["code"] !== T_WHITESPACE || $tokens[($colon + 1)]["content"] !== " ")
		    {
			$error = _("There must be a single space after the colon in a property/label declaration");
			$phpcsFile->addError($error, $stackPtr, "After");
		    }
	    } //end process()


    } //end class

?>
