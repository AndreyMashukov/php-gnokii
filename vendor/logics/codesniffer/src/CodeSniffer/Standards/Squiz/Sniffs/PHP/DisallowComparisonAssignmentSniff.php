<?php

/**
 * PHP version 5
 *
 * @package Logics\BuildTools\CodeSniffer\Squiz
 */

namespace Logics\BuildTools\CodeSniffer\Squiz;

use \Logics\BuildTools\CodeSniffer\File;
use \Logics\BuildTools\CodeSniffer\Sniff;
use \Logics\BuildTools\CodeSniffer\Tokens;

/**
 * Squiz_Sniffs_PHP_DisallowComparisonAssignmentSniff.
 *
 * Ensures that the value of a comparison is not assigned to a variable.
 *
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2006-2012 Squiz Pty Ltd (ABN 77 084 670 600)
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 * @version   SVN: $Date: 2016-08-16 23:39:01 +0900 (Tue, 16 Aug 2016) $ $Revision: 26 $
 * @link      $HeadURL: https://open.logics.net.au/buildtools/codesniffer/tags/0.1.3/src/CodeSniffer/Standards/Squiz/Sniffs/PHP/DisallowComparisonAssignmentSniff.php $
 */

class DisallowComparisonAssignmentSniff implements Sniff
    {

	/**
	 * Returns an array of tokens this test wants to listen for.
	 *
	 * @return array
	 *
	 * @internalconst T_EQUAL T_EQUAL token
	 */

	public function register()
	    {
		return array(T_EQUAL);
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
	 * @internalconst T_SEMICOLON        T_SEMICOLON token
	 * @internalconst T_BOOLEAN_NOT      T_BOOLEAN_NOT token
	 *
	 * @untranslatable AssignedComparison
	 * @untranslatable AssignedBool
	 */

	public function process(File $phpcsFile, $stackPtr)
	    {
		$tokens = &$phpcsFile->tokens;

		// Ignore default value assignments in function definitions.
		$function = $phpcsFile->findPrevious(T_FUNCTION, ($stackPtr - 1));
		if ($function !== false)
		    {
			$opener = $tokens[$function]["parenthesis_opener"];
			$closer = $tokens[$function]["parenthesis_closer"];
		    }

		if ($function === false || ($function !== false && ($stackPtr <= $opener || $closer <= $stackPtr)))
		    {
			// Ignore values in array definitions.
			$array = $phpcsFile->findNext(T_ARRAY, ($stackPtr + 1), null, false, null, true);

			if ($array === false)
			    {
				// Ignore function calls.
				$ignore = array(
					   T_STRING,
					   T_WHITESPACE,
					   T_OBJECT_OPERATOR,
					  );

				$next = $phpcsFile->findNext($ignore, ($stackPtr + 1), null, true);
				// Code will look like: $var = myFunction(
				// and will be ignored.
				if ($tokens[$next]["code"] !== T_OPEN_PARENTHESIS || $tokens[($next - 1)]["code"] !== T_STRING)
				    {
					$endStatement = $phpcsFile->findNext(T_SEMICOLON, ($stackPtr + 1));
					for ($i = ($stackPtr + 1); $i < $endStatement; $i++)
					    {
						if (in_array($tokens[$i]["code"], Tokens::$comparisonTokens) === true)
						    {
							$error = _("The value of a comparison must not be assigned to a variable");
							$phpcsFile->addError($error, $stackPtr, "AssignedComparison");
							break;
						    }

						if (in_array($tokens[$i]["code"], Tokens::$booleanOperators) === true || $tokens[$i]["code"] === T_BOOLEAN_NOT)
						    {
							$error = _("The value of a boolean operation must not be assigned to a variable");
							$phpcsFile->addError($error, $stackPtr, "AssignedBool");
							break;
						    }
					    }
				    }
			    } //end if
		    } //end if
	    } //end process()


    } //end class

?>
