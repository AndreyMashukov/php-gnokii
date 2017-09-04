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
 * Generic_Sniffs_ControlStructures_InlineControlStructureSniff.
 *
 * Verifies that inline control statements are not present.
 *
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @author    Marc McIntyre <mmcintyre@squiz.net>
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2006-2012 Squiz Pty Ltd (ABN 77 084 670 600)
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 * @version   SVN: $Date: 2016-08-16 23:39:01 +0900 (Tue, 16 Aug 2016) $ $Revision: 26 $
 * @link      $HeadURL: https://open.logics.net.au/buildtools/codesniffer/tags/0.1.3/src/CodeSniffer/Standards/Generic/Sniffs/ControlStructures/InlineControlStructureSniff.php $
 *
 * @untranslatable PHP
 * @untranslatable JS
 */

class InlineControlStructureSniff implements Sniff
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
	 * If true, an error will be thrown; otherwise a warning.
	 *
	 * @var bool
	 */
	public $error = true;

	/**
	 * Returns an array of tokens this test wants to listen for.
	 *
	 * @return array
	 */

	public function register()
	    {
		return array(
			T_IF,
			T_ELSE,
			T_FOREACH,
			T_WHILE,
			T_DO,
			T_SWITCH,
			T_FOR,
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
	 * @internalconst T_CLOSE_CURLY_BRACKET T_CLOSE_CURLY_BRACKET token
	 *
	 * @untranslatable NotAllowed
	 * @untranslatable Discouraged
	 */

	public function process(File $phpcsFile, $stackPtr)
	    {
		$tokens = &$phpcsFile->tokens;

		// Ignore the ELSE in ELSE IF. We'll process the IF part later.
		if (isset($tokens[$stackPtr]["scope_opener"]) === false &&
		    (($tokens[$stackPtr]["code"] !== T_ELSE) || ($tokens[($stackPtr + 2)]["code"] !== T_IF)))
		    {
			$dowhile = false;
			if ($tokens[$stackPtr]["code"] === T_WHILE)
			    {
				// This could be from a DO WHILE, which doesn't have an opening brace.
				$lastContent = $phpcsFile->findPrevious(T_WHITESPACE, ($stackPtr - 1), null, true);
				if ($tokens[$lastContent]["code"] === T_CLOSE_CURLY_BRACKET)
				    {
					$brace = $tokens[$lastContent];
					if (isset($brace["scope_condition"]) === true)
					    {
						$condition = $tokens[$brace["scope_condition"]];
						if ($condition["code"] === T_DO)
						    {
							$dowhile = true;
						    }
					    }
				    }
			    }

			if ($dowhile === false)
			    {
				// This is a control structure without an opening brace,
				// so it is an inline statement.
				if ($this->error === true)
				    {
					$phpcsFile->addError(_("Inline control structures are not allowed"), $stackPtr, "NotAllowed");
				    }
				else
				    {
					$phpcsFile->addWarning(_("Inline control structures are discouraged"), $stackPtr, "Discouraged");
				    }
			    }
		    } //end if
	    } //end process()


    } //end class

?>
