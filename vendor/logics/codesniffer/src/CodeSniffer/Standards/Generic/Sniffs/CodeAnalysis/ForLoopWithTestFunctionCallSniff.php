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
 * Detects for-loops that use a function call in the test expression.
 *
 * This rule is based on the PMD rule catalog. Detects for-loops that use a
 * function call in the test expression.
 *
 * <code>
 * class Foo
 * {
 *     public function bar($x)
 *     {
 *         $a = array(1, 2, 3, 4);
 *         for ($i = 0; $i < count($a); $i++) {
 *              $a[$i] *= $i;
 *         }
 *     }
 * }
 * </code>
 *
 * @author    Manuel Pichler <mapi@manuel-pichler.de>
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2007-2008 Manuel Pichler. All rights reserved.
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version   SVN: $Date: 2016-08-16 23:39:01 +0900 (Tue, 16 Aug 2016) $ $Revision: 26 $
 * @link      $HeadURL: https://open.logics.net.au/buildtools/codesniffer/tags/0.1.3/src/CodeSniffer/Standards/Generic/Sniffs/CodeAnalysis/ForLoopWithTestFunctionCallSniff.php $
 */

class ForLoopWithTestFunctionCallSniff implements Sniff
    {

	/**
	 * Registers the tokens that this sniff wants to listen for.
	 *
	 * @return array(int)
	 */

	public function register()
	    {
		return array(T_FOR);
	    } //end register()


	/**
	 * Processes this test, when one of its tokens is encountered.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $stackPtr  The position of the current token in the stack passed in $tokens.
	 *
	 * @return void
	 *
	 * @internalconst T_SEMICOLON        T_SEMICOLON token
	 * @internalconst T_OPEN_PARENTHESIS T_OPEN_PARENTHESIS token
	 *
	 * @untranslatable NotAllowed
	 */

	public function process(File $phpcsFile, $stackPtr)
	    {
		$tokens = &$phpcsFile->tokens;
		$token  = $tokens[$stackPtr];

		// Skip invalid statement.
		if (isset($token["parenthesis_opener"]) === true)
		    {
			$next = ++$token["parenthesis_opener"];
			$end  = --$token["parenthesis_closer"];

			$position = 0;

			for (; $next <= $end; ++$next)
			    {
				$code = $tokens[$next]["code"];
				if ($code === T_SEMICOLON)
				    {
					++$position;
				    }

				if ($position > 1)
				    {
					break;
				    }
				else if ($position === 1 && ($code === T_VARIABLE || $code === T_STRING))
				    {
					// Find next non empty token, if it is a open curly brace we have a
					// function call.
					$index = $phpcsFile->findNext(Tokens::$emptyTokens, ($next + 1), null, true);

					if ($tokens[$index]["code"] === T_OPEN_PARENTHESIS)
					    {
						$phpcsFile->addWarning(_("Avoid function calls in a FOR loop test part"), $stackPtr, "NotAllowed");
						break;
					    }
				    }
			    } //end for
		    } //end if
	    } //end process()


    } //end class

?>