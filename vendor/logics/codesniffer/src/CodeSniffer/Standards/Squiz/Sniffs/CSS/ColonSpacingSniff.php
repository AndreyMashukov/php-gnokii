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
 * Squiz_Sniffs_CSS_ColonSpacingSniff.
 *
 * Ensure there is no space before a colon and one space after it.
 *
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2006-2012 Squiz Pty Ltd (ABN 77 084 670 600)
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 * @version   SVN: $Date: 2016-08-16 23:39:01 +0900 (Tue, 16 Aug 2016) $ $Revision: 26 $
 * @link      $HeadURL: https://open.logics.net.au/buildtools/codesniffer/tags/0.1.3/src/CodeSniffer/Standards/Squiz/Sniffs/CSS/ColonSpacingSniff.php $
 *
 * @untranslatable CSS
 */

class ColonSpacingSniff implements Sniff
    {

	/**
	 * A list of tokenizers this sniff supports.
	 *
	 * @var array
	 */
	public $supportedTokenizers = array("CSS");

	/**
	 * Returns the token types that this sniff is interested in.
	 *
	 * @return array(int)
	 *
	 * @internalconst T_COLON T_COLON token
	 */

	public function register()
	    {
		return array(T_COLON);
	    } //end register()


	/**
	 * Processes the tokens that this sniff is interested in.
	 *
	 * @param File $phpcsFile The file where the token was found.
	 * @param int  $stackPtr  The position in the stack where the token was found.
	 *
	 * @return void
	 *
	 * @internalconst T_STYLE T_STYLE token
	 *
	 * @untranslatable Before
	 * @untranslatable NoneAfter
	 * @untranslatable After
	 * @untranslatable AfterNewline
	 * @untranslatable %s
	 */

	public function process(File $phpcsFile, $stackPtr)
	    {
		$tokens = &$phpcsFile->tokens;

		$prev = $phpcsFile->findPrevious(Tokens::$emptyTokens, ($stackPtr - 1), null, true);
		if ($tokens[$prev]["code"] === T_STYLE)
		    {
			if ($tokens[($stackPtr - 1)]["code"] === T_WHITESPACE)
			    {
				$error = _("There must be no space before a colon in a style definition");
				$phpcsFile->addError($error, $stackPtr, "Before");
			    }

			if ($tokens[($stackPtr + 1)]["code"] !== T_WHITESPACE)
			    {
				$error = _("Expected 1 space after colon in style definition; 0 found");
				$phpcsFile->addError($error, $stackPtr, "NoneAfter");
			    }
			else
			    {
				$content = $tokens[($stackPtr + 1)]["content"];
				if (strpos($content, $phpcsFile->eolChar) === false)
				    {
					$length = strlen($content);
					if ($length !== 1)
					    {
						$error = _("Expected 1 space after colon in style definition;") . " %s " . _("found");
						$data  = array($length);
						$phpcsFile->addError($error, $stackPtr, "After", $data);
					    }
				    }
				else
				    {
					$error = _("Expected 1 space after colon in style definition; newline found");
					$phpcsFile->addError($error, $stackPtr, "AfterNewline");
				    }
			    } //end if
		    } //end if
	    } //end process()


    } //end class

?>
