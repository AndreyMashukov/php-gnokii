<?php

/**
 * PHP version 5
 *
 * @package Logics\BuildTools\CodeSniffer\PEAR
 */

namespace Logics\BuildTools\CodeSniffer\PEAR;

use \Logics\BuildTools\CodeSniffer\File;
use \Logics\BuildTools\CodeSniffer\Sniff;
use \Logics\BuildTools\CodeSniffer\Tokens;

/**
 * IncludingFileSniff.
 *
 * Checks that the include_once is used in conditional situations, and
 * require_once is used elsewhere. Also checks that brackets do not surround
 * the file being included.
 *
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @author    Marc McIntyre <mmcintyre@squiz.net>
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2006-2012 Squiz Pty Ltd (ABN 77 084 670 600)
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 * @version   SVN: $Date: 2016-08-16 23:39:01 +0900 (Tue, 16 Aug 2016) $ $Revision: 26 $
 * @link      $HeadURL: https://open.logics.net.au/buildtools/codesniffer/tags/0.1.3/src/CodeSniffer/Standards/PEAR/Sniffs/Files/IncludingFileSniff.php $
 */

class IncludingFileSniff implements Sniff
    {

	/**
	 * Returns an array of tokens this test wants to listen for.
	 *
	 * @return array
	 */

	public function register()
	    {
		return array(
			T_INCLUDE_ONCE,
			T_REQUIRE_ONCE,
			T_REQUIRE,
			T_INCLUDE,
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
	 * @internalconst T_OPEN_PARENTHESIS T_OPEN_PARENTHESIS token
	 *
	 * @untranslatable \"%s\"
	 * @untranslatable BracketsNotRequired
	 * @untranslatable \"include_once\"
	 * @untranslatable UseIncludeOnce
	 * @untranslatable \"include\"
	 * @untranslatable UseInclude
	 * @untranslatable \"require_once\"
	 * @untranslatable UseRequireOnce
	 * @untranslatable \"require\"
	 * @untranslatable UseRequire
	 */

	public function process(File $phpcsFile, $stackPtr)
	    {
		$tokens = &$phpcsFile->tokens;

		$nextToken = $phpcsFile->findNext(Tokens::$emptyTokens, ($stackPtr + 1), null, true);
		if ($tokens[$nextToken]["code"] === T_OPEN_PARENTHESIS)
		    {
			$error = "\"%s\" " . _("is a statement not a function; no parentheses are required");
			$data  = array($tokens[$stackPtr]["content"]);
			$phpcsFile->addError($error, $stackPtr, "BracketsNotRequired", $data);
		    }

		$inCondition = (count($tokens[$stackPtr]["conditions"]) !== 0) ? true : false;

		// Check to see if this including statement is within the parenthesis
		// of a condition. If that's the case then we need to process it as being
		// within a condition, as they are checking the return value.
		if (isset($tokens[$stackPtr]["nested_parenthesis"]) === true)
		    {
			foreach ($tokens[$stackPtr]["nested_parenthesis"] as $left => $right)
			    {
				if (isset($tokens[$left]["parenthesis_owner"]) === true)
				    {
					$inCondition = true;
				    }
			    }
		    }

		// Check to see if they are assigning the return value of this
		// including call. If they are then they are probably checking it, so
		// it's conditional.
		$previous = $phpcsFile->findPrevious(Tokens::$emptyTokens, ($stackPtr - 1), null, true);
		// The have assigned the return value to it, so its conditional.
		$inCondition = in_array($tokens[$previous]["code"], Tokens::$assignmentTokens) || $inCondition;

		$tokenCode = $tokens[$stackPtr]["code"];
		if ($inCondition === true)
		    {
			// We are inside a conditional statement. We need an include_once.
			if ($tokenCode === T_REQUIRE_ONCE)
			    {
				$error = _("File is being conditionally included; use") . " \"include_once\" " . _("instead");
				$phpcsFile->addError($error, $stackPtr, "UseIncludeOnce");
			    }
			else if ($tokenCode === T_REQUIRE)
			    {
				$error = _("File is being conditionally included; use") . " \"include\" " . _("instead");
				$phpcsFile->addError($error, $stackPtr, "UseInclude");
			    }
		    }
		else
		    {
			// We are unconditionally including, we need a require_once.
			if ($tokenCode === T_INCLUDE_ONCE)
			    {
				$error = _("File is being unconditionally included; use") . " \"require_once\" " . _("instead");
				$phpcsFile->addError($error, $stackPtr, "UseRequireOnce");
			    }
			else if ($tokenCode === T_INCLUDE)
			    {
				$error = _("File is being unconditionally included; use") . " \"require\" " . _("instead");
				$phpcsFile->addError($error, $stackPtr, "UseRequire");
			    }
		    } //end if
	    } //end process()


    } //end class

?>
