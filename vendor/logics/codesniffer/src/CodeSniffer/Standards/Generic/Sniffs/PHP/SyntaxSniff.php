<?php

/**
 * PHP version 5
 *
 * @package Logics\BuildTools\CodeSniffer\Generic
 */

namespace Logics\BuildTools\CodeSniffer\Generic;

use \Logics\BuildTools\CodeSniffer\Config;
use \Logics\BuildTools\CodeSniffer\File;
use \Logics\BuildTools\CodeSniffer\Sniff;

/**
 * Generic_Sniffs_PHP_SyntaxSniff.
 *
 * Ensures PHP believes the syntax is clean.
 *
 * @author    Blaine Schmeisser <blainesch@gmail.com>
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2006-2012 Squiz Pty Ltd (ABN 77 084 670 600)
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 * @version   SVN: $Date: 2016-08-16 23:39:01 +0900 (Tue, 16 Aug 2016) $ $Revision: 26 $
 * @link      $HeadURL: https://open.logics.net.au/buildtools/codesniffer/tags/0.1.3/src/CodeSniffer/Standards/Generic/Sniffs/PHP/SyntaxSniff.php $
 */

class SyntaxSniff implements Sniff
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
	 * @untranslatable php_path
	 * @untranslatable -l \"
	 * @untranslatable PHPSyntax
	 */

	public function process(File $phpcsFile, $stackPtr)
	    {
		unset($stackPtr);

		$phpPath = Config::getConfigData("php_path");
		if ($phpPath === null)
		    {
			return;
		    }

		$fileName = $phpcsFile->getFilename();
		$cmd      = $phpPath . " -l \"" . $fileName . "\" 2>&1";
		$output   = shell_exec($cmd);

		$matches = array();
		if (preg_match("/^.*error:(.*) in .* on line ([0-9]+)/", $output, $matches) === 1)
		    {
			$error = trim($matches[1]);
			$line  = (int) $matches[2];
			$phpcsFile->addErrorOnLine(_("PHP syntax error") . ": " . $error, $line, "PHPSyntax");
		    }

		// Ignore the rest of the file.
		return ($phpcsFile->numTokens + 1);
	    } //end process()


    } //end class

?>
