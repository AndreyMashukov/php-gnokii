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
 * Detects unconditional if- and elseif-statements.
 *
 * This rule is based on the PMD rule catalog. The Unconditional If Statement
 * sniff detects statement conditions that are only set to one of the constant
 * values <b>true</b> or <b>false</b>
 *
 * <code>
 * class Foo
 * {
 *     public function close()
 *     {
 *         if (true)
 *         {
 *             // ...
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
 * @link      $HeadURL: https://open.logics.net.au/buildtools/codesniffer/tags/0.1.3/src/CodeSniffer/Standards/Generic/Sniffs/CodeAnalysis/UnconditionalIfStatementSniff.php $
 */

class UnconditionalIfStatementSniff implements Sniff
    {

	/**
	 * Registers the tokens that this sniff wants to listen for.
	 *
	 * @return array(int)
	 */

	public function register()
	    {
		return array(
			T_IF,
			T_ELSEIF,
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
	 * @internalconst T_TRUE  T_TRUE token
	 * @internalconst T_FALSE T_FALSE token
	 *
	 * @untranslatable Found
	 */

	public function process(File $phpcsFile, $stackPtr)
	    {
		$tokens = &$phpcsFile->tokens;
		$token  = $tokens[$stackPtr];

		// Skip for-loop without body.
		if (isset($token["parenthesis_opener"]) === true)
		    {
			$next = ++$token["parenthesis_opener"];
			$end  = --$token["parenthesis_closer"];

			$goodCondition = false;
			for (; $next <= $end; ++$next)
			    {
				$code = $tokens[$next]["code"];

				if (in_array($code, Tokens::$emptyTokens) === false)
				    {
					if ($code !== T_TRUE && $code !== T_FALSE)
					    {
						$goodCondition = true;
					    }
				    }
			    }

			if ($goodCondition === false)
			    {
				$phpcsFile->addWarning(_("Avoid IF statements that are always true or false"), $stackPtr, "Found");
			    }
		    } //end if
	    } //end process()


    } //end class

?>