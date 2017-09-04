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
 * Detects incrementer jumbling in for loops.
 *
 * This rule is based on the PMD rule catalog. The jumbling incrementer sniff
 * detects the usage of one and the same incrementer into an outer and an inner
 * loop. Even it is intended this is confusing code.
 *
 * <code>
 * class Foo
 * {
 *     public function bar($x)
 *     {
 *         for ($i = 0; $i < 10; $i++)
 *         {
 *             for ($k = 0; $k < 20; $i++)
 *             {
 *                 echo 'Hello';
 *             }
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
 * @link      $HeadURL: https://open.logics.net.au/buildtools/codesniffer/tags/0.1.3/src/CodeSniffer/Standards/Generic/Sniffs/CodeAnalysis/JumbledIncrementerSniff.php $
 */

class JumbledIncrementerSniff implements Sniff
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
	 * @untranslatable (%s)
	 * @untranslatable Found
	 */

	public function process(File $phpcsFile, $stackPtr)
	    {
		$tokens = &$phpcsFile->tokens;
		$token  = $tokens[$stackPtr];

		// Skip for-loop without body.
		if (isset($token["scope_opener"]) === true)
		    {
			// Find incrementors for outer loop.
			$outer = $this->findIncrementers($tokens, $token);

			// Skip if empty.
			if (count($outer) !== 0)
			    {
				// Find nested for loops.
				$start = ++$token["scope_opener"];
				$end   = --$token["scope_closer"];

				for (; $start <= $end; ++$start)
				    {
					if ($tokens[$start]["code"] === T_FOR)
					    {
						$inner = $this->findIncrementers($tokens, $tokens[$start]);
						$diff  = array_intersect($outer, $inner);

						if (count($diff) !== 0)
						    {
							$error = _("Loop incrementor") . " (%s) " . _("jumbling with inner loop");
							$data  = array(join(", ", $diff));
							$phpcsFile->addWarning($error, $stackPtr, "Found", $data);
						    }
					    }
				    }
			    } //end if
		    } //end if
	    } //end process()


	/**
	 * Get all used variables in the incrementer part of a for statement.
	 *
	 * @param array $tokens Array with all code sniffer tokens.
	 * @param array $token  Current for loop token
	 *
	 * @return array List of all found incrementer variables.
	 *
	 * @internalconst T_SEMICOLON T_SEMICOLON token
	 */

	protected function findIncrementers(array $tokens, array $token)
	    {
		// Skip invalid statement.
		if (isset($token["parenthesis_opener"]) === true)
		    {
			$start = ++$token["parenthesis_opener"];
			$end   = --$token["parenthesis_closer"];

			$incrementers = array();
			$semicolons   = 0;
			for ($next = $start; $next <= $end; ++$next)
			    {
				$code = $tokens[$next]["code"];
				if ($code === T_SEMICOLON)
				    {
					++$semicolons;
				    }
				else if ($semicolons === 2 && $code === T_VARIABLE)
				    {
					$incrementers[] = $tokens[$next]["content"];
				    }
			    }

			return $incrementers;
		    }
		else
		    {
			return array();
		    } //end if
	    } //end findIncrementers()


    } //end class

?>