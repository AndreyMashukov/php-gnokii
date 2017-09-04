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
 * PSR2_Sniffs_Namespaces_UseDeclarationSniff.
 *
 * Ensures USE blocks are declared correctly.
 *
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2006-2012 Squiz Pty Ltd (ABN 77 084 670 600)
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 * @version   SVN: $Date: 2016-08-16 23:39:01 +0900 (Tue, 16 Aug 2016) $ $Revision: 26 $
 * @link      $HeadURL: https://open.logics.net.au/buildtools/codesniffer/tags/0.1.3/src/CodeSniffer/Standards/PSR2/Sniffs/Namespaces/UseDeclarationSniff.php $
 */

class UseDeclarationSniff implements Sniff
    {

	/**
	 * Returns an array of tokens this test wants to listen for.
	 *
	 * @return array
	 */

	public function register()
	    {
		return array(T_USE);
	    } //end register()


	/**
	 * Processes this test, when one of its tokens is encountered.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $stackPtr  The position of the current token in the stack passed in $tokens.
	 *
	 * @return void
	 *
	 * @internalconst T_COMMA     T_COMMA token
	 * @internalconst T_SEMICOLON T_SEMICOLON token
	 *
	 * @untranslatable MultipleDeclarations
	 * @untranslatable UseAfterNamespace
	 * @untranslatable %s
	 * @untranslatable SpaceAfterLastUse
	 */

	public function process(File $phpcsFile, $stackPtr)
	    {
		if ($this->_shouldIgnoreUse($phpcsFile, $stackPtr) === false)
		    {
			$tokens = &$phpcsFile->tokens;

			// Only one USE declaration allowed per statement.
			$next = $phpcsFile->findNext(array(T_COMMA, T_SEMICOLON), ($stackPtr + 1));
			if ($tokens[$next]["code"] === T_COMMA)
			    {
				$error = _("There must be one USE keyword per declaration");
				$phpcsFile->addError($error, $stackPtr, "MultipleDeclarations");
			    }

			// Make sure this USE comes after the first namespace declaration.
			$prev = $phpcsFile->findPrevious(T_NAMESPACE, ($stackPtr - 1));
			if ($prev !== false)
			    {
				$first = $phpcsFile->findNext(T_NAMESPACE, 1);
				if ($prev !== $first)
				    {
					$error = _("USE declarations must go after the first namespace declaration");
					$phpcsFile->addError($error, $stackPtr, "UseAfterNamespace");
				    }
			    }

			// Only interested in the last USE statement from here onwards.
			$nextUse = $phpcsFile->findNext(T_USE, ($stackPtr + 1));
			while ($this->_shouldIgnoreUse($phpcsFile, $nextUse) === true)
			    {
				$nextUse = $phpcsFile->findNext(T_USE, ($nextUse + 1));
				if ($nextUse === false)
				    {
					break;
				    }
			    }

			if ($nextUse === false)
			    {
				$end  = $phpcsFile->findNext(T_SEMICOLON, ($stackPtr + 1));
				$next = $phpcsFile->findNext(T_WHITESPACE, ($end + 1), null, true);
				$diff = ($tokens[$next]["line"] - $tokens[$end]["line"] - 1);
				if ($diff !== 1)
				    {
					if ($diff < 0)
					    {
						$diff = 0;
					    }

					$error = _("There must be one blank line after the last USE statement;") . " %s " . _("found;");
					$data  = array($diff);
					$phpcsFile->addError($error, $stackPtr, "SpaceAfterLastUse", $data);
				    }
			    }
		    } //end if
	    } //end process()


	/**
	 * Check if this use statement is part of the namespace block.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $stackPtr  The position of the current token in the stack passed in $tokens.
	 *
	 * @return bool
	 *
	 * @internalconst T_OPEN_PARENTHESIS T_OPEN_PARENTHESIS token
	 */

	private function _shouldIgnoreUse(File &$phpcsFile, $stackPtr)
	    {
		$tokens = &$phpcsFile->tokens;

		// Ignore USE keywords inside closures.
		$next = $phpcsFile->findNext(T_WHITESPACE, ($stackPtr + 1), null, true);
		if ($tokens[$next]["code"] === T_OPEN_PARENTHESIS)
		    {
			return true;
		    }
		else if ($phpcsFile->hasCondition($stackPtr, T_CLASS) === true)
		    {
			// Ignore USE keywords for traits.
			return true;
		    }
		else
		    {
			return false;
		    }
	    } //end _shouldIgnoreUse()


    } //end class

?>