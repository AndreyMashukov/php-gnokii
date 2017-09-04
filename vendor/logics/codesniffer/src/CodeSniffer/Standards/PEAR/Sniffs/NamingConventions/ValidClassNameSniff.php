<?php

/**
 * PHP version 5
 *
 * @package Logics\BuildTools\CodeSniffer\PEAR
 */

namespace Logics\BuildTools\CodeSniffer\PEAR;

use \Logics\BuildTools\CodeSniffer\File;
use \Logics\BuildTools\CodeSniffer\Sniff;

/**
 * ValidClassNameSniff
 *
 * Ensures class and interface names start with a capital letter
 * and use _ separators.
 *
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @author    Marc McIntyre <mmcintyre@squiz.net>
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2006-2012 Squiz Pty Ltd (ABN 77 084 670 600)
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 * @version   SVN: $Date: 2016-08-16 23:39:01 +0900 (Tue, 16 Aug 2016) $ $Revision: 26 $
 * @link      $HeadURL: https://open.logics.net.au/buildtools/codesniffer/tags/0.1.3/src/CodeSniffer/Standards/PEAR/Sniffs/NamingConventions/ValidClassNameSniff.php $
 */

class ValidClassNameSniff implements Sniff
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
	 * @param File $phpcsFile The current file being processed.
	 * @param int  $stackPtr  The position of the current token in the stack passed in $tokens.
	 *
	 * @return void
	 *
	 * @untranslatable StartWithCapital
	 * @untranslatable %s
	 * @untranslatable Invalid
	 */

	public function process(File $phpcsFile, $stackPtr)
	    {
		$tokens = &$phpcsFile->tokens;

		$className = $phpcsFile->findNext(T_STRING, $stackPtr);
		$name      = trim($tokens[$className]["content"]);
		$errorData = array(ucfirst($tokens[$stackPtr]["content"]));

		// Make sure the first letter is a capital.
		if (preg_match("/^[A-Z]/", $name) === 0)
		    {
			$error = "%s " . _("name must begin with a capital letter");
			$phpcsFile->addError($error, $stackPtr, "StartWithCapital", $errorData);
		    }

		// Check that each new word starts with a capital as well, but don't
		// check the first word, as it is checked above.
		$validName = true;
		$nameBits  = explode("_", $name);
		$firstBit  = array_shift($nameBits);
		foreach ($nameBits as $bit)
		    {
			if ($bit === "" || $bit{0} !== strtoupper($bit{0}))
			    {
				$validName = false;
				break;
			    }
		    }

		if ($validName === false)
		    {
			// Strip underscores because they cause the suggested name
			// to be incorrect.
			$nameBits = explode("_", trim($name, "_"));
			$firstBit = array_shift($nameBits);
			if ($firstBit === "")
			    {
				$error = "%s " . _("name is not valid");
				$phpcsFile->addError($error, $stackPtr, "Invalid", $errorData);
			    }
			else
			    {
				$newName = strtoupper($firstBit{0}) . substr($firstBit, 1) . "_";
				foreach ($nameBits as $bit)
				    {
					if ($bit !== "")
					    {
						$newName .= strtoupper($bit{0}) . substr($bit, 1) . "_";
					    }
				    }

				$newName = rtrim($newName, "_");
				$error   = "%s " . _("name is not valid; consider") . " %s " . _("instead");
				$data    = $errorData;
				$data[]  = $newName;
				$phpcsFile->addError($error, $stackPtr, "Invalid", $data);
			    } //end if
		    } //end if
	    } //end process()


    } //end class

?>
