<?php

/**
 * Warns when function values are returned directly.
 *
 * PHP version 5
 *
 * @package Logics\BuildTools\CodeSniffer\MySource
 */

namespace Logics\BuildTools\CodeSniffer\MySource;

use \Logics\BuildTools\CodeSniffer\File;
use \Logics\BuildTools\CodeSniffer\Sniff;

/**
 * Warns when function values are returned directly.
 *
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2006-2012 Squiz Pty Ltd (ABN 77 084 670 600)
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 * @version   SVN: $Date: 2016-08-16 23:39:01 +0900 (Tue, 16 Aug 2016) $ $Revision: 26 $
 * @link      $HeadURL: https://open.logics.net.au/buildtools/codesniffer/tags/0.1.3/src/CodeSniffer/Standards/MySource/Sniffs/PHP/ReturnFunctionValueSniff.php $
 */

class ReturnFunctionValueSniff implements Sniff
    {

	/**
	 * Returns an array of tokens this test wants to listen for.
	 *
	 * @return array
	 */

	public function register()
	    {
		return array(T_RETURN);
	    } //end register()


	/**
	 * Processes this sniff, when one of its tokens is encountered.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $stackPtr  The position of the current token in the stack passed in $tokens.
	 *
	 * @return void
	 *
	 * @internalconst T_OPEN_PARENTHESIS T_OPEN_PARENTHESIS token
	 *
	 * @untranslatable NotAssigned
	 */

	public function process(File $phpcsFile, $stackPtr)
	    {
		$tokens = &$phpcsFile->tokens;

		$functionName = $phpcsFile->findNext(T_STRING, ($stackPtr + 1), null, false, null, true);

		while ($functionName !== false)
		    {
			// Check if this is really a function.
			$bracket = $phpcsFile->findNext(T_WHITESPACE, ($functionName + 1), null, true);
			if ($tokens[$bracket]["code"] !== T_OPEN_PARENTHESIS)
			    {
				// Not a function call.
				$functionName = $phpcsFile->findNext(T_STRING, ($functionName + 1), null, false, null, true);
			    }
			else
			    {
				$error = _("The result of a function call should be assigned to a variable before being returned");
				$phpcsFile->addWarning($error, $stackPtr, "NotAssigned");
				break;
			    }
		    }
	    } //end process()


    } //end class

?>
