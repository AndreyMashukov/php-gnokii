<?php

/**
 * Ensures that all action classes throw ChannelExceptions only.
 *
 * PHP version 5
 *
 * @package Logics\BuildTools\CodeSniffer\MySource
 */

namespace Logics\BuildTools\CodeSniffer\MySource;

use \Logics\BuildTools\CodeSniffer\File;
use \Logics\BuildTools\CodeSniffer\Sniff;

/**
 * Ensures that all action classes throw ChannelExceptions only.
 *
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2006-2012 Squiz Pty Ltd (ABN 77 084 670 600)
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 * @version   SVN: $Date: 2016-08-16 23:39:01 +0900 (Tue, 16 Aug 2016) $ $Revision: 26 $
 * @link      $HeadURL: https://open.logics.net.au/buildtools/codesniffer/tags/0.1.3/src/CodeSniffer/Standards/MySource/Sniffs/Channels/ChannelExceptionSniff.php $
 */

class ChannelExceptionSniff implements Sniff
    {

	/**
	 * Returns an array of tokens this test wants to listen for.
	 *
	 * @return array
	 */

	public function register()
	    {
		return array(T_THROW);
	    } //end register()


	/**
	 * Processes this sniff, when one of its tokens is encountered.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $stackPtr  The position of the current token in the stack passed in $tokens.
	 *
	 * @return void
	 *
	 * @untranslatable ChannelException
	 * @untranslatable \"%s\"
	 * @untranslatable WrongExceptionType
	 */

	public function process(File $phpcsFile, $stackPtr)
	    {
		$fileName = strtolower($phpcsFile->getFilename());
		$matches  = array();
		if (preg_match("/\/systems\/(.*)\/([^\/]+)?actions.inc$/", $fileName, $matches) > 0)
		    {
			// This is an actions.inc file.
			$tokens = &$phpcsFile->tokens;

			$exception     = $phpcsFile->findNext(array(T_STRING, T_VARIABLE), ($stackPtr + 1));
			$exceptionName = $tokens[$exception]["content"];

			if ($exceptionName !== "ChannelException")
			    {
				$data  = array($exceptionName);
				$error = _("Channel actions can only throw ChannelException; found") . " \"%s\"";
				$phpcsFile->addError($error, $exception, "WrongExceptionType", $data);
			    }
		    }
	    } //end process()


    } //end class

?>
