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
 * Checks the nesting level for methods.
 *
 * @author    Johann-Peter Hartmann <hartmann@mayflower.de>
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2007 Mayflower GmbH
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 * @version   SVN: $Date: 2016-08-16 23:39:01 +0900 (Tue, 16 Aug 2016) $ $Revision: 26 $
 * @link      $HeadURL: https://open.logics.net.au/buildtools/codesniffer/tags/0.1.3/src/CodeSniffer/Standards/Generic/Sniffs/Metrics/NestingLevelSniff.php $
 */

class NestingLevelSniff implements Sniff
    {

	/**
	 * A nesting level than this value will throw a warning.
	 *
	 * @var int
	 */
	public $nestingLevel = 5;

	/**
	 * A nesting level than this value will throw an error.
	 *
	 * @var int
	 */
	public $absoluteNestingLevel = 10;

	/**
	 * Returns an array of tokens this test wants to listen for.
	 *
	 * @return array
	 */

	public function register()
	    {
		return array(T_FUNCTION);
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
	 * @untranslatable %s
	 * @untranslatable MaxExceeded
	 * @untranslatable %s;
	 * @untranslatable TooHigh
	 */

	public function process(File $phpcsFile, $stackPtr)
	    {
		$tokens = &$phpcsFile->tokens;

		// Ignore abstract methods.
		if (isset($tokens[$stackPtr]["scope_opener"]) === true)
		    {
			// Detect start and end of this function definition.
			$start = $tokens[$stackPtr]["scope_opener"];
			$end   = $tokens[$stackPtr]["scope_closer"];

			$nestingLevel = 0;

			// Find the maximum nesting level of any token in the function.
			for ($i = ($start + 1); $i < $end; $i++)
			    {
				$level = $tokens[$i]["level"];
				if ($nestingLevel < $level)
				    {
					$nestingLevel = $level;
				    }
			    }

			// We subtract the nesting level of the function itself.
			$nestingLevel = ($nestingLevel - $tokens[$stackPtr]["level"] - 1);

			if ($nestingLevel > $this->absoluteNestingLevel)
			    {
				$error = _("Function's nesting level") . " (%s) " . _("exceeds allowed maximum of") . " %s";
				$data  = array(
					  $nestingLevel,
					  $this->absoluteNestingLevel,
					 );
				$phpcsFile->addError($error, $stackPtr, "MaxExceeded", $data);
			    }
			else if ($nestingLevel > $this->nestingLevel)
			    {
				$warning = _("Function's nesting level") . " (%s) " . _("exceeds") . " %s; " . _("consider refactoring the function");
				$data    = array(
					    $nestingLevel,
					    $this->nestingLevel,
					   );
				$phpcsFile->addWarning($warning, $stackPtr, "TooHigh", $data);
			    }
		    } //end if
	    } //end process()


    } //end class

?>
