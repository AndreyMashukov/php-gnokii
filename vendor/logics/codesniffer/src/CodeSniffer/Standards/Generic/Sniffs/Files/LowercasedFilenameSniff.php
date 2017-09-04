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
 * Checks that all file names are lowercased.
 *
 * @author    Andy Grunwald <andygrunwald@gmail.com>
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2010-2014 Andy Grunwald
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 * @version   SVN: $Date: 2016-08-16 23:39:01 +0900 (Tue, 16 Aug 2016) $ $Revision: 26 $
 * @link      $HeadURL: https://open.logics.net.au/buildtools/codesniffer/tags/0.1.3/src/CodeSniffer/Standards/Generic/Sniffs/Files/LowercasedFilenameSniff.php $
 */

class LowercasedFilenameSniff implements Sniff
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
	 * Processes this sniff, when one of its tokens is encountered.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $stackPtr  The position of the current token in the stack passed in $tokens.
	 *
	 * @return int
	 *
	 * @untranslatable \"%s\"
	 * @untranslatable NotFound
	 */

	public function process(File $phpcsFile, $stackPtr)
	    {
		$fileName          = basename($phpcsFile->getFilename());
		$lowercaseFileName = strtolower($fileName);
		if ($fileName !== $lowercaseFileName)
		    {
			$data  = array(
				  $fileName,
				  $lowercaseFileName,
				 );
			$error = _("Filename") . " \"%s\" " . _("doesn't match the expected filename") . " \"%s\"";
			$phpcsFile->addError($error, $stackPtr, "NotFound", $data);
		    }

		// Ignore the rest of the file.
		return ($phpcsFile->numTokens + 1);
	    } //end process()


    } //end class

?>
