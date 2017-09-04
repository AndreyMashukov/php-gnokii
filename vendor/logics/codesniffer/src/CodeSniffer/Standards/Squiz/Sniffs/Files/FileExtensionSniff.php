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
 * Squiz_Sniffs_Files_FileExtensionSniff.
 *
 * Tests that the stars in a doc comment align correctly.
 *
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @author    Marc McIntyre <mmcintyre@squiz.net>
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2006-2012 Squiz Pty Ltd (ABN 77 084 670 600)
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 * @version   SVN: $Date: 2016-08-16 23:39:01 +0900 (Tue, 16 Aug 2016) $ $Revision: 26 $
 * @link      $HeadURL: https://open.logics.net.au/buildtools/codesniffer/tags/0.1.3/src/CodeSniffer/Standards/Squiz/Sniffs/Files/FileExtensionSniff.php $
 */

class FileExtensionSniff implements Sniff
    {

	/**
	 * Returns an array of tokens this test wants to listen for.
	 *
	 * @return array
	 */

	public function register()
	    {
		return array(T_OPEN_TAG);
	    } //end register()


	/**
	 * Processes this test, when one of its tokens is encountered.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $stackPtr  The position of the current token in the stack passed in $tokens.
	 *
	 * @return void
	 *
	 * @untranslatable %s
	 * @untranslatable ClassFound
	 * @untranslatable .inc
	 * @untranslatable NoClass
	 */

	public function process(File $phpcsFile, $stackPtr)
	    {
		$tokens = &$phpcsFile->tokens;

		// Make sure this is the first PHP open tag so we don't process
		// the same file twice.
		$prevOpenTag = $phpcsFile->findPrevious(T_OPEN_TAG, ($stackPtr - 1));
		if ($prevOpenTag === false)
		    {
			$fileName  = $phpcsFile->getFileName();
			$extension = substr($fileName, strrpos($fileName, "."));
			$nextClass = $phpcsFile->findNext(array(T_CLASS, T_INTERFACE), $stackPtr);

			if ($extension === ".php")
			    {
				if ($nextClass !== false)
				    {
					$error = "%s " . _("found in \".php\" file; use \".inc\" extension instead");
					$data  = array(ucfirst($tokens[$nextClass]["content"]));
					$phpcsFile->addError($error, $stackPtr, "ClassFound", $data);
				    }
			    }
			else if ($extension === ".inc")
			    {
				if ($nextClass === false)
				    {
					$error = _("No interface or class found in \".inc\" file; use \".php\" extension instead");
					$phpcsFile->addError($error, $stackPtr, "NoClass");
				    }
			    }
		    } //end if
	    } //end process()


    } //end class

?>