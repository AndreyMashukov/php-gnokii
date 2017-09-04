<?php

/**
 * PHP version 5
 *
 * @package Logics\BuildTools\CodeSniffer\Squiz
 */

namespace Logics\BuildTools\CodeSniffer\Squiz;

use \Logics\BuildTools\CodeSniffer\File;
use \Logics\BuildTools\CodeSniffer\Sniff;

/**
 * DoubleQuoteUsageSniff
 *
 * Makes sure that any use of Double Quotes ("") are warranted.
 *
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @author    Marc McIntyre <mmcintyre@squiz.net>
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2006-2012 Squiz Pty Ltd (ABN 77 084 670 600)
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 * @version   SVN: $Date: 2016-08-16 23:39:01 +0900 (Tue, 16 Aug 2016) $ $Revision: 26 $
 * @link      $HeadURL: https://open.logics.net.au/buildtools/codesniffer/tags/0.1.3/src/CodeSniffer/Standards/Squiz/Sniffs/Strings/DoubleQuoteUsageSniff.php $
 */

class DoubleQuoteUsageSniff implements Sniff
    {

	/**
	 * Returns an array of tokens this test wants to listen for.
	 *
	 * @return array
	 *
	 * @internalconst T_DOUBLE_QUOTED_STRING T_DOUBLE_QUOTED_STRING token
	 */

	public function register()
	    {
		return array(
			T_CONSTANT_ENCAPSED_STRING,
			T_DOUBLE_QUOTED_STRING,
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
	 * @internalconst T_DOUBLE_QUOTED_STRING T_DOUBLE_QUOTED_STRING token
	 *
	 * @untranslatable <?php
	 * @untranslatable ContainsVar
	 * @untranslatable T_CONSTANT_ENCAPSED_STRING
	 * @untranslatable NotRequired
	 * @untranslatable %s
	 * @untranslatable \"%s\"
	 */

	public function process(File $phpcsFile, $stackPtr)
	    {
		$tokens = &$phpcsFile->tokens;

		// We are only interested in the first token in a multi-line string.
		if ($tokens[$stackPtr]["code"] !== $tokens[($stackPtr - 1)]["code"])
		    {
			$workingString = $tokens[$stackPtr]["content"];
			$i             = ($stackPtr + 1);
			while ($tokens[$i]["code"] === $tokens[$stackPtr]["code"])
			    {
				$workingString .= $tokens[$i]["content"];
				$i++;
			    }

			// Check if it's a double quoted string.
			// Make sure it's not a part of a string started in a previous line.
			// If it is, then we have already checked it.
			if (strpos($workingString, "\"") !== false && $workingString[0] === "\"")
			    {
				// The use of variables in double quoted strings is not allowed.
				if ($tokens[$stackPtr]["code"] === T_DOUBLE_QUOTED_STRING)
				    {
					$stringTokens = token_get_all("<?php " . $workingString);
					foreach ($stringTokens as $token)
					    {
						if (is_array($token) === true && $token[0] === T_VARIABLE)
						    {
							$error = _("Variable") . " \"%s\" " . _("not allowed in double quoted string; use concatenation instead");
							$data  = array($token[1]);
							$phpcsFile->addError($error, $stackPtr, "ContainsVar", $data);
						    }
					    }
				    }
				else
				    {
					// Work through the following tokens, in case this string is stretched
					// over multiple Lines.
					for ($i = ($stackPtr + 1); $i < $phpcsFile->numTokens; $i++)
					    {
						if ($tokens[$i]["type"] !== "T_CONSTANT_ENCAPSED_STRING")
						    {
							break;
						    }

						$workingString .= $tokens[$i]["content"];
					    }

					if (preg_match("/(\\\\[0nrftvx]|')/", $workingString) === 0)
					    {
						$error = _("String") . " %s " . _("does not require double quotes; use single quotes instead");
						$data  = array($workingString);
						$phpcsFile->addError($error, $stackPtr, "NotRequired", $data);
					    }
				    } //end if
			    } //end if
		    } //end if
	    } //end process()


    } //end class

?>