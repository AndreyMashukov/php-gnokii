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
 * LineLengthSniff.
 *
 * Checks all lines in the file, and throws warnings if they are over 80
 * characters in length and errors if they are over 100. Both these
 * figures can be changed by extending this sniff in your own standard.
 *
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @author    Marc McIntyre <mmcintyre@squiz.net>
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2006-2012 Squiz Pty Ltd (ABN 77 084 670 600)
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 * @version   SVN: $Date: 2016-08-16 23:39:01 +0900 (Tue, 16 Aug 2016) $ $Revision: 26 $
 * @link      $HeadURL: https://open.logics.net.au/buildtools/codesniffer/tags/0.1.3/src/CodeSniffer/Standards/Generic/Sniffs/Files/LineLengthSniff.php $
 */

class LineLengthSniff implements Sniff
    {

	/**
	 * The limit that the length of a line should not exceed.
	 *
	 * @var int
	 */
	public $lineLimit = 80;

	/**
	 * The limit that the length of a line must not exceed.
	 *
	 * Set to zero (0) to disable.
	 *
	 * @var int
	 */
	public $absoluteLineLimit = 100;

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
	 * Processes this test, when one of its tokens is encountered.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $stackPtr  The position of the current token in the stack passed in $tokens.
	 *
	 * @return void
	 */

	public function process(File $phpcsFile, $stackPtr)
	    {
		$tokens = &$phpcsFile->tokens;

		// Make sure this is the first open tag.
		$previousOpenTag = $phpcsFile->findPrevious(T_OPEN_TAG, ($stackPtr - 1));
		if ($previousOpenTag === false)
		    {
			$tokenCount         = 0;
			$currentLineContent = "";
			$currentLine        = 1;

			$trim = (strlen($phpcsFile->eolChar) * -1);
			for (; $tokenCount < $phpcsFile->numTokens; $tokenCount++)
			    {
				if ($tokens[$tokenCount]["line"] === $currentLine)
				    {
					$currentLineContent .= $tokens[$tokenCount]["content"];
				    }
				else
				    {
					$currentLineContent = substr($currentLineContent, 0, $trim);
					$this->checkLineLength($phpcsFile, ($tokenCount - 1), $currentLineContent);
					$currentLineContent = $tokens[$tokenCount]["content"];
					$currentLine++;
				    }
			    }

			$currentLineContent = substr($currentLineContent, 0, $trim);
			$this->checkLineLength($phpcsFile, ($tokenCount - 1), $currentLineContent);
		    } //end if
	    } //end process()


	/**
	 * Checks if a line is too long.
	 *
	 * @param File   $phpcsFile   The file being scanned.
	 * @param int    $stackPtr    The token at the end of the line.
	 * @param string $lineContent The content of the line.
	 *
	 * @return void
	 *
	 * @internalconst PHP_CODESNIFFER_ENCODING Encoding
	 *
	 * @untranslatable iso-8859-1
	 * @untranslatable %s
	 * @untranslatable MaxExceeded
	 * @untranslatable TooLong
	 */

	protected function checkLineLength(File &$phpcsFile, $stackPtr, $lineContent)
	    {
		// If the content is a CVS or SVN id in a version tag, or it is
		// a license tag with a name and URL, there is nothing the
		// developer can do to shorten the line, so don't throw errors.
		if (preg_match("/(@version[^\$]+\$Id|@license)/", $lineContent) === 0)
		    {
			if (PHP_CODESNIFFER_ENCODING !== "iso-8859-1")
			    {
				// Not using the detault encoding, so take a bit more care.
				$lineLength = iconv_strlen($lineContent, PHP_CODESNIFFER_ENCODING);
				if ($lineLength === false)
				    {
					// String contained invalid characters, so revert to default.
					$lineLength = strlen($lineContent);
				    }
			    }
			else
			    {
				$lineLength = strlen($lineContent);
			    }

			if ($this->absoluteLineLimit > 0 && $lineLength > $this->absoluteLineLimit)
			    {
				$data = array(
					 $this->absoluteLineLimit,
					 $lineLength,
					);

				$error = _("Line exceeds maximum limit of") . " %s " . _("characters; contains") . " %s " . _("characters");
				$phpcsFile->addError($error, $stackPtr, "MaxExceeded", $data);
			    }
			else if ($lineLength > $this->lineLimit)
			    {
				$data = array(
					 $this->lineLimit,
					 $lineLength,
					);

				$warning = _("Line exceeds") . " %s " . _("characters; contains") . " %s " . _("characters");
				$phpcsFile->addWarning($warning, $stackPtr, "TooLong", $data);
			    }
		    } //end if
	    } //end checkLineLength()


    } //end class

?>
