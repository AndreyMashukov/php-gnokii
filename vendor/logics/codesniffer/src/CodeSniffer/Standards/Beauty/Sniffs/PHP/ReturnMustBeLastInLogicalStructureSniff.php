<?php

/**
 * PHP version 5
 *
 * @package Logics\BuildTools\CodeSniffer\Beauty
 */

namespace Logics\BuildTools\CodeSniffer\Beauty;

use \Logics\BuildTools\CodeSniffer\File;
use \Logics\BuildTools\CodeSniffer\Sniff;

/**
 * ReturnMustBeLastInLogicalStructure
 *
 * Return must be last in logical structure
 *
 * @author    Anastasia Bashkirtseva <anastasia@logics.net.au>
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 * @version   SVN: $Date: 2016-08-16 23:39:01 +0900 (Tue, 16 Aug 2016) $ $Revision: 26 $
 * @link      $HeadURL: https://open.logics.net.au/buildtools/codesniffer/tags/0.1.3/src/CodeSniffer/Standards/Beauty/Sniffs/PHP/ReturnMustBeLastInLogicalStructureSniff.php $
 */

class ReturnMustBeLastInLogicalStructureSniff implements Sniff
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
	 * Processes this test, when used continue
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $stackPtr  The position of the current token in the stack passed in $tokens.
	 *
	 * @return void
	 *
	 * @internalconst T_CLOSE_CURLY_BRACKET T_CLOSE_CURLY_BRACKET token
	 * @internalconst T_CLOSURE             T_CLOSURE token
	 * @internalconst T_SEMICOLON           T_SEMICOLON token
	 * @internalconst T_OPEN_CURLY_BRACKET  T_OPEN_CURLY_BRACKET token
	 *
	 * @untranslatable ReturnMustBeLastInLogicalStructure
	 */

	public function process(File $phpcsFile, $stackPtr)
	    {
		$tokens      = &$phpcsFile->tokens;
		$error       = false;
		$validTokens = array(
				T_WHITESPACE,
				T_COMMENT,
				T_CLOSE_CURLY_BRACKET,
				T_DOC_COMMENT,
			       );

		$startFunction = $phpcsFile->findPrevious(array(T_FUNCTION, T_CLOSURE), $stackPtr);
		if (isset($tokens[$startFunction]["scope_closer"]) === true)
		    {
			$endFunction = $tokens[$startFunction]["scope_closer"];

			$nextLineAfterReturn = $phpcsFile->findNext(array(T_SEMICOLON), ($stackPtr + 1));
			$nextToken           = $phpcsFile->findNext($validTokens, ($nextLineAfterReturn + 1), null, true);
			while (($nextToken < $endFunction) && ($error === false))
			    {
				if ($tokens[$nextToken]["code"] === T_ELSE)
				    {
					$openCurlyBracket = $phpcsFile->findNext(array(T_OPEN_CURLY_BRACKET), ($nextToken + 1));
					$endElse          = $tokens[$openCurlyBracket]["scope_closer"];
					$nextToken        = $phpcsFile->findNext($validTokens, ($endElse + 1), null, true);
				    }
				else
				    {
					$error = true;
				    }
			    } //end while
		    }

		if ($error === true)
		    {
			$phpcsFile->addError(_("RETURN must be last in logical structure"), $stackPtr, "ReturnMustBeLastInLogicalStructure");
		    }
	    } //end process()


    } //end class

?>
