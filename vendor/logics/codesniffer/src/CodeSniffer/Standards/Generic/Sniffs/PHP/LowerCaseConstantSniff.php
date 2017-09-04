<?php

/**
 * PHP version 5
 *
 * @package Logics\BuildTools\CodeSniffer\Generic
 */

namespace Logics\BuildTools\CodeSniffer\Generic;

use \Logics\BuildTools\CodeSniffer\File;
use \Logics\BuildTools\CodeSniffer\Sniff;

/**
 * LowerCaseConstantSniff.
 *
 * Checks that all uses of true, false and null are lowercase.
 *
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @author    Marc McIntyre <mmcintyre@squiz.net>
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2006-2012 Squiz Pty Ltd (ABN 77 084 670 600)
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 * @version   SVN: $Date: 2016-08-16 23:39:01 +0900 (Tue, 16 Aug 2016) $ $Revision: 26 $
 * @link      $HeadURL: https://open.logics.net.au/buildtools/codesniffer/tags/0.1.3/src/CodeSniffer/Standards/Generic/Sniffs/PHP/LowerCaseConstantSniff.php $
 *
 * @untranslatable PHP
 * @untranslatable JS
 */

class LowerCaseConstantSniff implements Sniff
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
	 * Returns an array of tokens this test wants to listen for.
	 *
	 * @return array
	 *
	 * @internalconst T_TRUE  T_TRUE token
	 * @internalconst T_FALSE T_FALSE token
	 * @internalconst T_NULL  T_NULL token
	 */

	public function register()
	    {
		return array(
			T_TRUE,
			T_FALSE,
			T_NULL,
		       );
	    } //end register()


	/**
	 * Processes this sniff, when one of its tokens is encountered.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $stackPtr  The position of the current token in the stack passed in $tokens.
	 *
	 * @return void
	 *
	 * @untranslatable \"%s\"
	 * @untranslatable Found
	 */

	public function process(File $phpcsFile, $stackPtr)
	    {
		$tokens = &$phpcsFile->tokens;

		// Is this a member var name? Is this a class name? Class or namespace?
		$prevPtr = $phpcsFile->findPrevious(T_WHITESPACE, ($stackPtr - 1), null, true);
		if ($tokens[$prevPtr]["code"] !== T_OBJECT_OPERATOR &&
		    $tokens[$prevPtr]["code"] !== T_CLASS && $tokens[$prevPtr]["code"] !== T_EXTENDS && $tokens[$prevPtr]["code"] !== T_IMPLEMENTS &&
		    $tokens[($stackPtr - 1)]["code"] !== T_NS_SEPARATOR)
		    {
			$keyword = $tokens[$stackPtr]["content"];
			if (strtolower($keyword) !== $keyword)
			    {
				$error = _("TRUE, FALSE and NULL must be lowercase; expected") . " \"%s\" " . _("but found") . " \"%s\"";
				$data  = array(
					  strtolower($keyword),
					  $keyword,
					 );
				$phpcsFile->addError($error, $stackPtr, "Found", $data);
			    }
		    }
	    } //end process()


    } //end class

?>
