<?php

/**
 * Class Declaration Test.
 *
 * PHP version 5
 *
 * @package Logics\BuildTools\CodeSniffer\PSR1
 */

namespace Logics\BuildTools\CodeSniffer\PSR1;

use \Logics\BuildTools\CodeSniffer\File;
use \Logics\BuildTools\CodeSniffer\Sniff;

/**
 * Class Declaration Test.
 *
 * Checks the declaration of the class is correct.
 *
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2006-2012 Squiz Pty Ltd (ABN 77 084 670 600)
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 * @version   SVN: $Date: 2016-08-16 23:39:01 +0900 (Tue, 16 Aug 2016) $ $Revision: 26 $
 * @link      $HeadURL: https://open.logics.net.au/buildtools/codesniffer/tags/0.1.3/src/CodeSniffer/Standards/PSR1/Sniffs/Classes/ClassDeclarationSniff.php $
 */

class ClassDeclarationSniff implements Sniff
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
	 * @param int  $stackPtr  The position of the current token in the token stack.
	 *
	 * @return void
	 *
	 * @untranslatable MultipleClasses
	 * @untranslatable MissingNamespace
	 */

	public function process(File $phpcsFile, $stackPtr)
	    {
		$nextClass = $phpcsFile->findNext(array(T_CLASS, T_INTERFACE), ($stackPtr + 1));
		if ($nextClass !== false)
		    {
			$error = _("Each class must be in a file by itself");
			$phpcsFile->addError($error, $nextClass, "MultipleClasses");
		    }

		if (version_compare(PHP_VERSION, "5.3.0") >= 0)
		    {
			$namespace = $phpcsFile->findPrevious(T_NAMESPACE, ($stackPtr - 1));
			if ($namespace === false)
			    {
				$error = _("Each class must be in a namespace of at least one level (a top-level vendor name)");
				$phpcsFile->addError($error, $stackPtr, "MissingNamespace");
			    }
		    }
	    } //end process()


    } //end class

?>