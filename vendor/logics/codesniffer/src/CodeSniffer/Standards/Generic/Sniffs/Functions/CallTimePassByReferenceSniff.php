<?php

/**
 * PHP version 5
 *
 * @package Logics\BuildTools\CodeSniffer\Generic
 */

namespace Logics\BuildTools\CodeSniffer\Generic;

use \Logics\BuildTools\CodeSniffer\File;
use \Logics\BuildTools\CodeSniffer\Sniff;
use \Logics\BuildTools\CodeSniffer\Tokens;

/**
 * Generic_Sniffs_Functions_CallTimePassByReferenceSniff.
 *
 * Ensures that variables are not passed by reference when calling a function.
 *
 * @author    Florian Grandel <jerico.dev@gmail.com>
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2009 Florian Grandel
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 * @version   SVN: $Date: 2016-08-16 23:39:01 +0900 (Tue, 16 Aug 2016) $ $Revision: 26 $
 * @link      $HeadURL: https://open.logics.net.au/buildtools/codesniffer/tags/0.1.3/src/CodeSniffer/Standards/Generic/Sniffs/Functions/CallTimePassByReferenceSniff.php $
 */

class CallTimePassByReferenceSniff implements Sniff
    {

	/**
	 * Returns an array of tokens this test wants to listen for.
	 *
	 * @return array
	 */

	public function register()
	    {
		return array(T_STRING);
	    } //end register()


	/**
	 * Processes this test, when one of its tokens is encountered.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $stackPtr  The position of the current token in the stack passed in $tokens.
	 *
	 * @return void
	 *
	 * @internalconst T_OPEN_PARENTHESIS  T_OPEN_PARENTHESIS token
	 * @internalconst T_BITWISE_AND       T_BITWISE_AND token
	 * @internalconst T_CLOSE_PARENTHESIS T_CLOSE_PARENTHESIS token
	 *
	 * @untranslatable NotAllowed
	 */

	public function process(File $phpcsFile, $stackPtr)
	    {
		$tokens = &$phpcsFile->tokens;

		// Skip tokens that are the names of functions or classes
		// within their definitions. For example: function myFunction...
		// "myFunction" is T_STRING but we should skip because it is not a function or method *call*.
		$functionName = $stackPtr;
		$findTokens   = array_merge(Tokens::$emptyTokens, array(T_BITWISE_AND));

		$functionKeyword = $phpcsFile->findPrevious($findTokens, ($stackPtr - 1), null, true);

		// If the next non-whitespace token after the function or method call
		// is not an opening parenthesis then it cant really be a *call*.
		$openBracket = $phpcsFile->findNext(Tokens::$emptyTokens, ($functionName + 1), null, true);

		if ($tokens[$functionKeyword]["code"] !== T_FUNCTION && $tokens[$functionKeyword]["code"] !== T_CLASS &&
		    $tokens[$openBracket]["code"] === T_OPEN_PARENTHESIS)
		    {
			$closeBracket = $tokens[$openBracket]["parenthesis_closer"];

			$nextSeparator = $phpcsFile->findNext(T_VARIABLE, ($openBracket + 1), $closeBracket);
			while ($nextSeparator !== false)
			    {
				// Make sure the variable belongs directly to this function call
				// and is not inside a nested function call or array.
				$brackets    = $tokens[$nextSeparator]["nested_parenthesis"];
				$lastBracket = array_pop($brackets);
				if ($lastBracket === $closeBracket)
				    {
					// We should check the following construction: $value = my_function(...[*]$arg...).
					$tokenBefore = $phpcsFile->findPrevious(Tokens::$emptyTokens, ($nextSeparator - 1), null, true);

					if ($tokens[$tokenBefore]["code"] === T_BITWISE_AND)
					    {
						// We should check the following construction: $value = my_function(...[*]&$arg...).
						$tokenBefore = $phpcsFile->findPrevious(Tokens::$emptyTokens, ($tokenBefore - 1), null, true);

						// We have to exclude all uses of T_BITWISE_AND that are not
						// references. We use a blacklist approach as we prefer false
						// positives to not identifying a pass-by-reference call at all.
						// The blacklist may not yet be complete.
						if ($tokens[$tokenBefore]["code"] !== T_VARIABLE &&
						    $tokens[$tokenBefore]["code"] !== T_CLOSE_PARENTHESIS &&
						    $tokens[$tokenBefore]["code"] !== T_LNUMBER)
						    {
							$phpcsFile->addError(_("Call-time pass-by-reference calls are prohibited"), $tokenBefore, "NotAllowed");
						    }
					    } //end if
				    } //end if

				$nextSeparator = $phpcsFile->findNext(T_VARIABLE, ($nextSeparator + 1), $closeBracket);
			    } //end while
		    } //end if
	    } //end process()


    } //end class

?>
