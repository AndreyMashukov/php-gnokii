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
 * LineEndingsSniff.
 *
 * Checks that end of line characters are correct.
 *
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @author    Marc McIntyre <mmcintyre@squiz.net>
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2006-2012 Squiz Pty Ltd (ABN 77 084 670 600)
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 * @version   SVN: $Date: 2016-08-16 23:39:01 +0900 (Tue, 16 Aug 2016) $ $Revision: 26 $
 * @link      $HeadURL: https://open.logics.net.au/buildtools/codesniffer/tags/0.1.3/src/CodeSniffer/Standards/Generic/Sniffs/Files/LineEndingsSniff.php $
 *
 * @untranslatable PHP
 * @untranslatable JS
 * @untranslatable CSS
 */

class LineEndingsSniff implements Sniff
    {

	/**
	 * A list of tokenizers this sniff supports.
	 *
	 * @var array
	 */
	public $supportedTokenizers = array(
				       "PHP",
				       "JS",
				       "CSS",
				      );

	/**
	 * The valid EOL character.
	 *
	 * @var string
	 */
	public $eolChar = '\n';

	/**
	 * Returns an array of tokens this test wants to listen for.
	 *
	 * @return array
	 */

	public function register()
	    {
		return array(T_OPEN_TAG);
	    } //end register()


	/**
	 * Processes this sniff, when one of its tokens is encountered.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $stackPtr  The position of the current token in the stack passed in $tokens.
	 *
	 * @return void
	 *
	 * @untranslatable \"%s\"
	 * @untranslatable InvalidEOLChar
	 */

	public function process(File $phpcsFile, $stackPtr)
	    {
		// We are only interested if this is the first open tag.
		if ($stackPtr === 0 || $phpcsFile->findPrevious(T_OPEN_TAG, ($stackPtr - 1)) === false)
		    {
			$found = $phpcsFile->eolChar;
			$found = str_replace("\n", '\n', $found);
			$found = str_replace("\r", '\r', $found);

			if ($found !== $this->eolChar)
			    {
				// Check for single line files without an EOL. This is a very special
				// case and the EOL char is set to \n when this happens.
				$tokens = &$phpcsFile->tokens;
				if ($found !== '\n' || $tokens[($phpcsFile->numTokens - 1)]["line"] !== 1 || $tokens[($phpcsFile->numTokens - 1)]["content"] === "\n")
				    {
					$error    = _("End of line character is invalid; expected") . " \"%s\" " . _("but found") . " \"%s\"";
					$expected = $this->eolChar;
					$expected = str_replace("\n", '\n', $expected);
					$expected = str_replace("\r", '\r', $expected);
					$data     = array(
						     $expected,
						     $found,
						    );
					$phpcsFile->addError($error, $stackPtr, "InvalidEOLChar", $data);
				    }
			    } //end if
		    } //end if
	    } //end process()


    } //end class

?>
