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
 * Squiz_Sniffs_ControlStructures_LowercaseDeclarationSniff.
 *
 * Ensures all control structure keywords are lowercase.
 *
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @author    Marc McIntyre <mmcintyre@squiz.net>
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2006-2012 Squiz Pty Ltd (ABN 77 084 670 600)
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 * @version   SVN: $Date: 2016-08-16 23:39:01 +0900 (Tue, 16 Aug 2016) $ $Revision: 26 $
 * @link      $HeadURL: https://open.logics.net.au/buildtools/codesniffer/tags/0.1.3/src/CodeSniffer/Standards/Squiz/Sniffs/ControlStructures/LowercaseDeclarationSniff.php $
 */

class LowercaseDeclarationSniff implements Sniff
    {

	/**
	 * Returns an array of tokens this test wants to listen for.
	 *
	 * @return array
	 */

	public function register()
	    {
		return array(
			T_IF,
			T_ELSE,
			T_ELSEIF,
			T_FOREACH,
			T_FOR,
			T_DO,
			T_SWITCH,
			T_WHILE,
			T_TRY,
			T_CATCH,
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
	 * @untranslatable FoundUppercase
	 * @untranslatable %s
	 * @untranslatable \"%s\"
	 */

	public function process(File $phpcsFile, $stackPtr)
	    {
		$tokens = &$phpcsFile->tokens;

		$content = $tokens[$stackPtr]["content"];
		if ($content !== strtolower($content))
		    {
			$error = "%s " . _("keyword must be lowercase; expected") . " \"%s\" " . _("but found") . " \"%s\"";
			$data  = array(
				  strtoupper($content),
				  strtolower($content),
				  $content,
				 );
			$phpcsFile->addError($error, $stackPtr, "FoundUppercase", $data);
		    }
	    } //end process()


    } //end class

?>
