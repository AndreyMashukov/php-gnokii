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
 * Detects for-loops that can be simplified to a while-loop.
 *
 * This rule is based on the PMD rule catalog. Detects for-loops that can be
 * simplified as a while-loop.
 *
 * <code>
 * class Foo
 * {
 *     public function bar($x)
 *     {
 *         for (;true;) true; // No Init or Update part, may as well be: while (true)
 *     }
 * }
 * </code>
 *
 * @author    Manuel Pichler <mapi@manuel-pichler.de>
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2007-2008 Manuel Pichler. All rights reserved.
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version   SVN: $Date: 2016-08-16 23:39:01 +0900 (Tue, 16 Aug 2016) $ $Revision: 26 $
 * @link      $HeadURL: https://open.logics.net.au/buildtools/codesniffer/tags/0.1.3/src/CodeSniffer/Standards/Generic/Sniffs/CodeAnalysis/ForLoopShouldBeWhileLoopSniff.php $
 */

class ForLoopShouldBeWhileLoopSniff implements Sniff
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
	 * @internalconst T_SEMICOLON T_SEMICOLON token
	 *
	 * @untranslatable CanSimplify
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

			$parts = array(
				  0,
				  0,
				  0,
				 );
			$index = 0;

			for (; $next <= $end; ++$next)
			    {
				$code = $tokens[$next]["code"];
				if ($code === T_SEMICOLON)
				    {
					++$index;
				    }
				else if (in_array($code, Tokens::$emptyTokens) === false)
				    {
					++$parts[$index];
				    }
			    }

			if ($parts[0] === 0 && $parts[2] === 0 && $parts[1] > 0)
			    {
				$phpcsFile->addWarning(_("This FOR loop can be simplified to a WHILE loop"), $stackPtr, "CanSimplify");
			    }
		    } //end if
	    } //end process()


    } //end class

?>
