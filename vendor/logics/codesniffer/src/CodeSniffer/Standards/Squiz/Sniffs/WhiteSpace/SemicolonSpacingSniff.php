<?php

/**
 * PHP version 5
 *
 * @package Logics\BuildTools\CodeSniffer\Squiz
 */

namespace Logics\BuildTools\CodeSniffer\Squiz;

use \Logics\BuildTools\CodeSniffer\File;
use \Logics\BuildTools\CodeSniffer\Sniff;
use \Logics\BuildTools\CodeSniffer\Tokens;

/**
 * SemicolonSpacingSniff
 *
 * Ensure there is no whitespace before a semicolon.
 *
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @author    Marc McIntyre <mmcintyre@squiz.net>
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2006-2012 Squiz Pty Ltd (ABN 77 084 670 600)
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 * @version   SVN: $Date: 2016-08-16 23:39:01 +0900 (Tue, 16 Aug 2016) $ $Revision: 26 $
 * @link      $HeadURL: https://open.logics.net.au/buildtools/codesniffer/tags/0.1.3/src/CodeSniffer/Standards/Squiz/Sniffs/WhiteSpace/SemicolonSpacingSniff.php $
 *
 * @untranslatable PHP
 * @untranslatable JS
 */

class SemicolonSpacingSniff implements Sniff
    {

	/**
	 * A list of tokenizers this sniff supports.
	 *
	 * @var array
	 */
	public $supportedTokenizers = array(
				       "PHP",
				       "JS",
				      );

	/**
	 * Returns an array of tokens this test wants to listen for.
	 *
	 * @return array
	 *
	 * @internalconst T_SEMICOLON T_SEMICOLON token
	 */

	public function register()
	    {
		return array(T_SEMICOLON);
	    } //end register()


	/**
	 * Processes this test, when one of its tokens is encountered.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $stackPtr  The position of the current token in the stack passed in $tokens.
	 *
	 * @return void
	 *
	 * @untranslatable Incorrect
	 * @untranslatable \"%s\"
	 */

	public function process(File $phpcsFile, $stackPtr)
	    {
		$tokens = &$phpcsFile->tokens;

		$prevType = $tokens[($stackPtr - 1)]["code"];
		if (in_array($prevType, Tokens::$emptyTokens) === true)
		    {
			$nonSpace = $phpcsFile->findPrevious(Tokens::$emptyTokens, ($stackPtr - 2), null, true);
			$expected = $tokens[$nonSpace]["content"] . ";";
			$found    = $phpcsFile->getTokensAsString($nonSpace, ($stackPtr - $nonSpace)) . ";";
			$error    = _("Space found before semicolon; expected") . " \"%s\" " . _("but found") . " \"%s\"";
			$data     = array(
				     $expected,
				     $found,
				    );
			$phpcsFile->addError($error, $stackPtr, "Incorrect", $data);
		    }
	    } //end process()


    } //end class

?>
