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
 * MultiLineAssignmentSniff.
 *
 * If an assignment goes over two lines, ensure the equal sign is indented.
 *
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2006-2012 Squiz Pty Ltd (ABN 77 084 670 600)
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 * @version   SVN: $Date: 2016-08-16 23:39:01 +0900 (Tue, 16 Aug 2016) $ $Revision: 26 $
 * @link      $HeadURL: https://open.logics.net.au/buildtools/codesniffer/tags/0.1.3/src/CodeSniffer/Standards/PEAR/Sniffs/Formatting/MultiLineAssignmentSniff.php $
 */

class MultiLineAssignmentSniff implements Sniff
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
	 * @untranslatable EqualSignLine
	 * @untranslatable %s
	 * @untranslatable Indent
	 */

	public function process(File $phpcsFile, $stackPtr)
	    {
		$tokens = &$phpcsFile->tokens;

		// Equal sign can't be the last thing on the line.
		$next = $phpcsFile->findNext(T_WHITESPACE, ($stackPtr + 1), null, true);
		if ($next !== false)
		    {
			if ($tokens[$next]["line"] !== $tokens[$stackPtr]["line"])
			    {
				$error = _("Multi-line assignments must have the equal sign on the second line");
				$phpcsFile->addError($error, $stackPtr, "EqualSignLine");
			    }
			else
			    {
				// Make sure it is the first thing on the line, otherwise we ignore it.
				$prev = $phpcsFile->findPrevious(T_WHITESPACE, ($stackPtr - 1), false, true);
				if ($prev !== false && $tokens[$prev]["line"] !== $tokens[$stackPtr]["line"])
				    {
					// Find the required indent based on the ident of the previous line.
					$assignmentIndent = 0;
					$prevLine         = $tokens[$prev]["line"];
					for ($i = ($prev - 1); $i >= 0; $i--)
					    {
						if ($tokens[$i]["line"] !== $prevLine)
						    {
							$i++;
							break;
						    }
					    }

					if ($tokens[$i]["code"] === T_WHITESPACE)
					    {
						$assignmentIndent = strlen($tokens[$i]["content"]);
					    }

					// Find the actual indent.
					$prev = $phpcsFile->findPrevious(T_WHITESPACE, ($stackPtr - 1));

					$expectedIndent = ($assignmentIndent + 4);
					$foundIndent    = strlen($tokens[$prev]["content"]);
					if ($foundIndent !== $expectedIndent)
					    {
						$error = _("Multi-line assignment not indented correctly; expected") . " %s " . _("spaces but found") . " %s";
						$data  = array(
							  $expectedIndent,
							  $foundIndent,
							 );
						$phpcsFile->addError($error, $stackPtr, "Indent", $data);
					    }
				    } //end if
			    } //end if
		    } //end if
	    } //end process()


    } //end class

?>