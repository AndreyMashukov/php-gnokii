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
 * DisallowSpaceIndentSniff.
 *
 * Throws errors if tabs are used for indentation.
 *
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2006-2012 Squiz Pty Ltd (ABN 77 084 670 600)
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 * @version   SVN: $Date: 2016-08-16 23:39:01 +0900 (Tue, 16 Aug 2016) $ $Revision: 26 $
 * @link      $HeadURL: https://open.logics.net.au/buildtools/codesniffer/tags/0.1.3/src/CodeSniffer/Standards/Generic/Sniffs/WhiteSpace/DisallowSpaceIndentSniff.php $
 *
 * @untranslatable PHP
 * @untranslatable JS
 * @untranslatable CSS
 */

class DisallowSpaceIndentSniff implements Sniff
    {

	/**
	 * A list of tokenizers this sniff supports.
	 *
	 * @var array
	 */
	public $supportedTokenizers = array(
				       "PHP",
				       "JS",
				       "CSS",
				      );

	/**
	 * Returns an array of tokens this test wants to listen for.
	 *
	 * @return array
	 */

	public function register()
	    {
		return array(T_WHITESPACE);
	    } //end register()


	/**
	 * Processes this test, when one of its tokens is encountered.
	 *
	 * @param File $phpcsFile All the tokens found in the document.
	 * @param int  $stackPtr  The position of the current token in the stack passed in $tokens.
	 *
	 * @return void
	 *
	 * @untranslatable TabsUsed
	 */

	public function process(File $phpcsFile, $stackPtr)
	    {
		$tokens = &$phpcsFile->tokens;

		// Make sure this is whitespace used for indentation.
		$line = $tokens[$stackPtr]["line"];
		if ($stackPtr <= 0 || $tokens[($stackPtr - 1)]["line"] !== $line)
		    {
			if (strpos($tokens[$stackPtr]["content"], " ") !== false)
			    {
				// Space are considered ok if they are proceeded by tabs and not followed
				// by tabs, as is the case with standard docblock comments.
				$error = _("Tabs must be used to indent lines; spaces are not allowed");
				$phpcsFile->addError($error, $stackPtr, "TabsUsed");
			    }
		    }
	    } //end process()


    } //end class

?>
