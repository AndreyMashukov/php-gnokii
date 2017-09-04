<?php

/**
 * PHP version 5
 *
 * @package Logics\BuildTools\CodeSniffer\PSR2
 */

namespace Logics\BuildTools\CodeSniffer\PSR2;

use \Logics\BuildTools\CodeSniffer\File;
use \Logics\BuildTools\CodeSniffer\Sniff;

/**
 * ControlStructureSpacingSniff.
 *
 * Checks that control structures have the correct spacing around brackets.
 *
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2006-2012 Squiz Pty Ltd (ABN 77 084 670 600)
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 * @version   SVN: $Date: 2016-08-16 23:39:01 +0900 (Tue, 16 Aug 2016) $ $Revision: 26 $
 * @link      $HeadURL: https://open.logics.net.au/buildtools/codesniffer/tags/0.1.3/src/CodeSniffer/Standards/PSR2/Sniffs/ControlStructures/ControlStructureSpacingSniff.php $
 */

class ControlStructureSpacingSniff implements Sniff
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
			T_WHILE,
			T_FOREACH,
			T_FOR,
			T_SWITCH,
			T_DO,
			T_ELSE,
			T_ELSEIF,
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
	 * @untranslatable %s
	 * @untranslatable SpacingAfterOpenBrace
	 * @untranslatable SpaceBeforeCloseBrace
	 */

	public function process(File $phpcsFile, $stackPtr)
	    {
		$tokens = &$phpcsFile->tokens;

		if (isset($tokens[$stackPtr]["parenthesis_opener"]) === true)
		    {
			$parenOpener = $tokens[$stackPtr]["parenthesis_opener"];
			$parenCloser = $tokens[$stackPtr]["parenthesis_closer"];
			if ($tokens[($parenOpener + 1)]["code"] === T_WHITESPACE)
			    {
				$gap   = strlen($tokens[($parenOpener + 1)]["content"]);
				$error = _("Expected 0 spaces after opening bracket;") . " %s " . _("found");
				$data  = array($gap);
				$phpcsFile->addError($error, ($parenOpener + 1), "SpacingAfterOpenBrace", $data);
			    }

			if ($tokens[$parenOpener]["line"] === $tokens[$parenCloser]["line"] && $tokens[($parenCloser - 1)]["code"] === T_WHITESPACE)
			    {
				$gap   = strlen($tokens[($parenCloser - 1)]["content"]);
				$error = _("Expected 0 spaces before closing bracket;") . " %s " . _("found");
				$data  = array($gap);
				$phpcsFile->addError($error, ($parenCloser - 1), "SpaceBeforeCloseBrace", $data);
			    }
		    } //end if
	    } //end process()


    } //end class

?>
