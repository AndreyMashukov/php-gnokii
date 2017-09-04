<?php

/**
 * PHP version 5
 *
 * @package Logics\BuildTools\CodeSniffer\Beauty
 */

namespace Logics\BuildTools\CodeSniffer\Beauty;

use \Exception;
use \Logics\BuildTools\CodeSniffer\File;
use \Logics\BuildTools\CodeSniffer\Sniff;

/**
 * AllowTabsOnlyInLineBeginningSniff.
 *
 * Throws errors if tabs are used for indentation.
 *
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2006-2012 Squiz Pty Ltd (ABN 77 084 670 600)
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 * @version   SVN: $Date: 2016-08-16 23:39:01 +0900 (Tue, 16 Aug 2016) $ $Revision: 26 $
 * @link      $HeadURL: https://open.logics.net.au/buildtools/codesniffer/tags/0.1.3/src/CodeSniffer/Standards/Beauty/Sniffs/WhiteSpace/AllowTabsOnlyInLineBeginningSniff.php $
 *
 * @untranslatable PHP
 * @untranslatable JS
 * @untranslatable CSS
 */

class AllowTabsOnlyInLineBeginningSniff implements Sniff
    {

	/**
	 * A list of tokenizers this sniff supports.
	 *
	 * @var array
	 */
	public $supportedTokenizers = array(
				       "PHP",
				       "JS",
				       "CSS",
				      );

	/**
	 * Returns an array of tokens this test wants to listen for.
	 *
	 * @return array
	 */

	public function register()
	    {
		return array(T_WHITESPACE);
	    } //end register()


	/**
	 * Processes this test, when one of its tokens is encountered.
	 *
	 * @param File $phpcsFile All the tokens found in the document.
	 * @param int  $stackPtr  The position of the current token in the stack passed in $tokens.
	 *
	 * @return void
	 *
	 * @throws Exception Tab width is zero
	 *
	 * @exceptioncode EXCEPTION_TAB_WIDTH_CANNOT_BE_ZERO
	 *
	 * @untranslatable TabsUsed
	 * @untranslatable \t
	 */

	public function process(File $phpcsFile, $stackPtr)
	    {
		$cliValues = $phpcsFile->phpcs->cli->getCommandLineValues();
		$tabWidth  = $cliValues["tabWidth"];
		if ($tabWidth === 0 || $tabWidth === null)
		    {
			throw new Exception(_("Tab width cannot be 0"), EXCEPTION_TAB_WIDTH_CANNOT_BE_ZERO);
		    }

		$tokens  = &$phpcsFile->tokens;
		$content = (isset($tokens[$stackPtr]["orig_content"]) === true) ? $tokens[$stackPtr]["orig_content"] : $tokens[$stackPtr]["content"];
		$tab     = str_repeat(" ", $tabWidth);

		if ($tokens[$stackPtr]["column"] === 1)
		    {
			if (strpos($content, $tab) !== false)
			    {
				$error = _("Tabs must be used to indent lines followed by spaces if needed; tabs must be used instead of long spaces");
				$phpcsFile->addError($error, $stackPtr, "TabsUsed");
			    }

			if (strpos($content, " \t") !== false)
			    {
				// Space are considered ok if they are preceeded by tabs and not followed
				// by tabs, as is the case with standard docblock comments.
				$error = _("Tabs must be used to indent lines followed by spaces if needed; no spaces allowed before tabs");
				$phpcsFile->addError($error, $stackPtr, "TabsUsed");
			    }
		    }
		else
		    {
			if (strpos($content, "\t") !== false)
			    {
				$error = _("Tabs must not be used to space out content");
				$phpcsFile->addError($error, $stackPtr, "TabsUsed");
			    }
		    } //end if
	    } //end process()


    } //end class

?>
