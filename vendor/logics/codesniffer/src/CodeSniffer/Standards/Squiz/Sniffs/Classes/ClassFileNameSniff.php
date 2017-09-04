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
 * Squiz_Sniffs_Classes_ClassFileNameSniff.
 *
 * Tests that the file name and the name of the class contained within the file
 * match.
 *
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @author    Marc McIntyre <mmcintyre@squiz.net>
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2006-2012 Squiz Pty Ltd (ABN 77 084 670 600)
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 * @version   SVN: $Date: 2016-08-16 23:39:01 +0900 (Tue, 16 Aug 2016) $ $Revision: 26 $
 * @link      $HeadURL: https://open.logics.net.au/buildtools/codesniffer/tags/0.1.3/src/CodeSniffer/Standards/Squiz/Sniffs/Classes/ClassFileNameSniff.php $
 */

class ClassFileNameSniff implements Sniff
    {

	/**
	 * Returns an array of tokens this test wants to listen for.
	 *
	 * @return array
	 */

	public function register()
	    {
		return array(
			T_CLASS,
			T_INTERFACE,
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
	 * @untranslatable NoMatch
	 * @untranslatable %s
	 * @untranslatable \"%s %s\"
	 */

	public function process(File $phpcsFile, $stackPtr)
	    {
		$tokens   = &$phpcsFile->tokens;
		$decName  = $phpcsFile->findNext(T_STRING, $stackPtr);
		$fullPath = basename($phpcsFile->getFilename());
		$fileName = substr($fullPath, 0, strpos($fullPath, "."));

		if ($tokens[$decName]["content"] !== $fileName)
		    {
			$error = "%s " . _("name doesn't match filename; expected") . " \"%s %s\"";
			$data  = array(
				  ucfirst($tokens[$stackPtr]["content"]),
				  $tokens[$stackPtr]["content"],
				  $fileName,
				 );
			$phpcsFile->addError($error, $stackPtr, "NoMatch", $data);
		    }
	    } //end process()


    } //end class

?>
