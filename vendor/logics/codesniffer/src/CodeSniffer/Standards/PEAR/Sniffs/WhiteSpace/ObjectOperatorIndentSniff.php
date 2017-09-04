<?php

/**
 * PHP version 5
 *
 * @package Logics\BuildTools\CodeSniffer\PEAR
 */

namespace Logics\BuildTools\CodeSniffer\PEAR;

use \Logics\BuildTools\CodeSniffer\File;
use \Logics\BuildTools\CodeSniffer\Sniff;

/**
 * ObjectOperatorIndentSniff
 *
 * Checks that object operators are indented 4 spaces if they are the first
 * thing on a line.
 *
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2006-2012 Squiz Pty Ltd (ABN 77 084 670 600)
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 * @version   SVN: $Date: 2016-08-16 23:39:01 +0900 (Tue, 16 Aug 2016) $ $Revision: 26 $
 * @link      $HeadURL: https://open.logics.net.au/buildtools/codesniffer/tags/0.1.3/src/CodeSniffer/Standards/PEAR/Sniffs/WhiteSpace/ObjectOperatorIndentSniff.php $
 */

class ObjectOperatorIndentSniff implements Sniff
    {

	/**
	 * Returns an array of tokens this test wants to listen for.
	 *
	 * @return array
	 */

	public function register()
	    {
		return array(T_OBJECT_OPERATOR);
	    } //end register()


	/**
	 * Processes this test, when one of its tokens is encountered.
	 *
	 * @param File $phpcsFile All the tokens found in the document.
	 * @param int  $stackPtr  The position of the current token in the stack passed in $tokens.
	 *
	 * @return void
	 *
	 * @untranslatable %s
	 * @untranslatable Incorrect
	 * @untranslatable StartOfLine
	 */

	public function process(File $phpcsFile, $stackPtr)
	    {
		$tokens = &$phpcsFile->tokens;

		// Make sure this is the first object operator in a chain of them.
		$varToken = $phpcsFile->findPrevious(T_WHITESPACE, ($stackPtr - 1), null, true);
		if ($varToken !== false && $tokens[$varToken]["code"] === T_VARIABLE)
		    {
			// Make sure this is a chained call.
			$next = $phpcsFile->findNext(T_OBJECT_OPERATOR, ($stackPtr + 1), null, false, null, true);

			// Chained call.
			if ($next !== false)
			    {
				// Determine correct indent.
				for ($i = ($varToken - 1); $i >= 0; $i--)
				    {
					if ($tokens[$i]["line"] !== $tokens[$varToken]["line"])
					    {
						$i++;
						break;
					    }
				    }

				$requiredIndent  = ($i >= 0 && $tokens[$i]["code"] === T_WHITESPACE) ? strlen($tokens[$i]["content"]) : 0;
				$requiredIndent += 4;

				// Determine the scope of the original object operator.
				$origBrackets = (isset($tokens[$stackPtr]["nested_parenthesis"]) === true) ? $tokens[$stackPtr]["nested_parenthesis"] : null;

				$origConditions = (isset($tokens[$stackPtr]["conditions"]) === true) ? $tokens[$stackPtr]["conditions"] : null;

				// Check indentation of each object operator in the chain.
				// If the first object operator is on a different line than
				// the variable, make sure we check its indentation too.
				$next = ($tokens[$stackPtr]["line"] > $tokens[$varToken]["line"]) ? $stackPtr : $next;

				while ($next !== false)
				    {
					// Make sure it is in the same scope, otherwise don't check indent.
					$brackets = (isset($tokens[$next]["nested_parenthesis"]) === true) ? $tokens[$next]["nested_parenthesis"] : null;

					$conditions = (isset($tokens[$next]["conditions"]) === true) ? $tokens[$next]["conditions"] : null;

					if ($origBrackets === $brackets && $origConditions === $conditions)
					    {
						// Make sure it starts a line, otherwise dont check indent.
						$indent      = $tokens[($next - 1)];
						$foundIndent = ($indent["line"] === $tokens[$next]["line"]) ? strlen($indent["content"]) : 0;
						if ($indent["code"] === T_WHITESPACE && $foundIndent !== $requiredIndent)
						    {
							$error = _("Object operator not indented correctly; expected") . " %s " . _("spaces but found") . " %s";
							$data  = array(
								  $requiredIndent,
								  $foundIndent,
								 );
							$phpcsFile->addError($error, $next, "Incorrect", $data);
						    }

						// It cant be the last thing on the line either.
						$content = $phpcsFile->findNext(T_WHITESPACE, ($next + 1), null, true);
						if ($tokens[$content]["line"] !== $tokens[$next]["line"])
						    {
							$error = _("Object operator must be at the start of the line, not the end");
							$phpcsFile->addError($error, $next, "StartOfLine");
						    }
					    } //end if

					$next = $phpcsFile->findNext(T_OBJECT_OPERATOR, ($next + 1), null, false, null, true);
				    } //end while
			    } //end if
		    } //end if
	    } //end process()


    } //end class

?>
