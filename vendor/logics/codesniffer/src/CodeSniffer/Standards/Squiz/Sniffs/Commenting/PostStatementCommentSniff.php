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
 * Squiz_Sniffs_Commenting_PostStatementCommentSniff.
 *
 * Checks to ensure that there are no comments after statements.
 *
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @author    Marc McIntyre <mmcintyre@squiz.net>
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2006-2012 Squiz Pty Ltd (ABN 77 084 670 600)
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 * @version   SVN: $Date: 2016-08-16 23:39:01 +0900 (Tue, 16 Aug 2016) $ $Revision: 26 $
 * @link      $HeadURL: https://open.logics.net.au/buildtools/codesniffer/tags/0.1.3/src/CodeSniffer/Standards/Squiz/Sniffs/Commenting/PostStatementCommentSniff.php $
 *
 * @untranslatable PHP
 * @untranslatable JS
 */

class PostStatementCommentSniff implements Sniff
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
	 */

	public function register()
	    {
		return array(T_COMMENT);
	    } //end register()


	/**
	 * Processes this sniff, when one of its tokens is encountered.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $stackPtr  The position of the current token in the stack passed in $tokens.
	 *
	 * @return void
	 *
	 * @internalconst T_COMMA               T_COMMA token
	 * @internalconst T_SEMICOLON           T_SEMICOLON token
	 * @internalconst T_CLOSE_CURLY_BRACKET T_CLOSE_CURLY_BRACKET token
	 *
	 * @untranslatable Found
	 */

	public function process(File $phpcsFile, $stackPtr)
	    {
		$tokens = &$phpcsFile->tokens;

		if (substr($tokens[$stackPtr]["content"], 0, 2) === "//")
		    {
			$commentLine = $tokens[$stackPtr]["line"];
			$lastContent = $phpcsFile->findPrevious(T_WHITESPACE, ($stackPtr - 1), null, true);

			if ($tokens[$lastContent]["line"] === $commentLine && $tokens[$lastContent]["code"] !== T_CLOSE_CURLY_BRACKET)
			    {
				// Special case for JS files.
				$allowinjs = false;

				if ($tokens[$lastContent]["code"] === T_COMMA || $tokens[$lastContent]["code"] === T_SEMICOLON)
				    {
					$lastContent = $phpcsFile->findPrevious(T_WHITESPACE, ($lastContent - 1), null, true);
					if ($tokens[$lastContent]["code"] === T_CLOSE_CURLY_BRACKET)
					    {
						$allowinjs = true;
					    }
				    }

				if ($allowinjs === false)
				    {
					$error = _("Comments may not appear after statements.");
					$phpcsFile->addError($error, $stackPtr, "Found");
				    }
			    }
		    } //end if
	    } //end process()


    } //end class

?>
