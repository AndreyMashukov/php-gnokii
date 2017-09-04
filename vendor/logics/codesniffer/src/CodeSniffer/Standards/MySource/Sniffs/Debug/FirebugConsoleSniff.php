<?php

/**
 * Ensures that console is not used for function or var names.
 *
 * PHP version 5
 *
 * @package Logics\BuildTools\CodeSniffer\MySource
 */

namespace Logics\BuildTools\CodeSniffer\MySource;

use \Logics\BuildTools\CodeSniffer\File;
use \Logics\BuildTools\CodeSniffer\Sniff;

/**
 * Ensures that console is not used for function or var names.
 *
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2006-2012 Squiz Pty Ltd (ABN 77 084 670 600)
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 * @version   SVN: $Date: 2016-08-16 23:39:01 +0900 (Tue, 16 Aug 2016) $ $Revision: 26 $
 * @link      $HeadURL: https://open.logics.net.au/buildtools/codesniffer/tags/0.1.3/src/CodeSniffer/Standards/MySource/Sniffs/Debug/FirebugConsoleSniff.php $
 *
 * @untranslatable JS
 */

class FirebugConsoleSniff implements Sniff
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
	 * @internalconst T_OBJECT   T_OBJECT token
	 */

	public function register()
	    {
		return array(
			T_STRING,
			T_PROPERTY,
			T_LABEL,
			T_OBJECT,
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
	 * @untranslatable console
	 * @untranslatable ConflictFound
	 */

	public function process(File $phpcsFile, $stackPtr)
	    {
		$tokens = &$phpcsFile->tokens;

		if (strtolower($tokens[$stackPtr]["content"]) === "console")
		    {
			$error = _("Variables, functions and labels must not be named \"console\"; name may conflict with Firebug internal variable");
			$phpcsFile->addError($error, $stackPtr, "ConflictFound");
		    }
	    } //end process()


    } //end class

?>
