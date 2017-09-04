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
 * Squiz_Sniffs_WhiteSpace_SuperfluousWhitespaceSniff.
 *
 * Checks that no whitespace proceeds the first content of the file, exists
 * after the last content of the file, resides after content on any line, or
 * are two empty lines in functions.
 *
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @author    Marc McIntyre <mmcintyre@squiz.net>
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2006-2012 Squiz Pty Ltd (ABN 77 084 670 600)
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 * @version   SVN: $Date: 2016-08-16 23:39:01 +0900 (Tue, 16 Aug 2016) $ $Revision: 26 $
 * @link      $HeadURL: https://open.logics.net.au/buildtools/codesniffer/tags/0.1.3/src/CodeSniffer/Standards/Squiz/Sniffs/WhiteSpace/SuperfluousWhitespaceSniff.php $
 *
 * @untranslatable PHP
 * @untranslatable JS
 * @untranslatable CSS
 */

class SuperfluousWhitespaceSniff implements Sniff
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
	 * If TRUE, whitespace rules are not checked for blank lines.
	 *
	 * Blank lines are those that contain only whitespace.
	 *
	 * @var bool
	 */
	public $ignoreBlankLines = false;

	/**
	 * Returns an array of tokens this test wants to listen for.
	 *
	 * @return array
	 *
	 * @internalconst T_DOC_COMMENT_WHITESPACE T_DOC_COMMENT_WHITESPACE token
	 * @internalconst T_CLOSURE                T_CLOSURE token
	 */

	public function register()
	    {
		return array(
			T_OPEN_TAG,
			T_CLOSE_TAG,
			T_WHITESPACE,
			T_COMMENT,
			T_DOC_COMMENT_WHITESPACE,
			T_CLOSURE,
		       );
	    } //end register()


	/**
	 * Processes this sniff, when one of its tokens is encountered.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $stackPtr  The position of the current token in the stack passed in $tokens.
	 *
	 * @return void
	 *
	 * @internalconst T_CLOSURE T_CLOSURE token
	 *
	 * @untranslatable EndLine
	 * @untranslatable EmptyLines
	 * @untranslatable T_INLINE_HTML
	 * @untranslatable EndFile
	 * @untranslatable StartFile
	 */

	public function process(File $phpcsFile, $stackPtr)
	    {
		$tokens = &$phpcsFile->tokens;

		if ($tokens[$stackPtr]["code"] === T_OPEN_TAG)
		    {
			// Check for start of file whitespace.
			if ($phpcsFile->tokenizerType !== "PHP")
			    {
				// The first token is always the open tag inserted when tokenizsed  and the second token is always the first piece of content in the file.
				// If the second token is whitespace, there was the start of the file.
				if ($tokens[($stackPtr + 1)]["code"] !== T_WHITESPACE)
				    {
					return;
				    }
			    }
			else
			    {
				// If it"s the first token, then there is no space.
				if ($stackPtr === 0)
				    {
					return;
				    }

				for ($i = ($stackPtr - 1); $i >= 0; $i--)
				    {
					// If we find something that isn't inline html then there is something previous in the file.
					if ($tokens[$i]["type"] !== "T_INLINE_HTML")
					    {
						return;
					    }

					// If we have ended up with inline html make sure it isn't just whitespace.
					$tokenContent = trim($tokens[$i]["content"]);
					if ($tokenContent !== "")
					    {
						return;
					    }
				    } //end for
			    } //end if

			$phpcsFile->addError(_("Additional whitespace found at start of file"), $stackPtr, "StartFile");
		    }
		else if ($tokens[$stackPtr]["code"] === T_CLOSE_TAG)
		    {
			// Check for end of file whitespace.
			if ($phpcsFile->tokenizerType === "PHP")
			    {
				if (isset($tokens[($stackPtr + 1)]) === false)
				    {
					// The close PHP token is the last in the file.
					return;
				    }

				for ($i = ($stackPtr + 1); $i < $phpcsFile->numTokens; $i++)
				    {
					// If we find something that isn't inline HTML then there is more to the file.
					if ($tokens[$i]["type"] !== "T_INLINE_HTML")
					    {
						return;
					    }

					// If we have ended up with inline html make sure it  isn't just whitespace.
					$tokenContent = trim($tokens[$i]["content"]);
					if (empty($tokenContent) === false)
					    {
						return;
					    }
				    } //end for
			    }
			else
			    {
				// The last token is always the close tag inserted when tokenized  and the second last token is always the last piece of content in the file.
				// If the second last token is whitespace, there was the end of the file.
				$stackPtr--;

				// The pointer is now looking at the last content in the file and not the fake PHP end tag the tokenizer inserted.
				if ($tokens[$stackPtr]["code"] !== T_WHITESPACE)
				    {
					return;
				    }

				// Allow a single newline at the end of the last line in the file.
				if ($tokens[($stackPtr - 1)]["code"] !== T_WHITESPACE && $tokens[$stackPtr]["content"] === $phpcsFile->eolChar)
				    {
					return;
				    }
			    } //end if

			$phpcsFile->addError(_("Additional whitespace found at end of file"), $stackPtr, "EndFile");
		    }
		else
		    {
			// Check for end of line whitespace.
			// Ignore whitespace that is not at the end of a line.
			if (isset($tokens[($stackPtr + 1)]["line"]) === true && $tokens[($stackPtr + 1)]["line"] === $tokens[$stackPtr]["line"])
			    {
				return;
			    }

			// Ignore blank lines if required.
			if ($this->ignoreBlankLines === true && $tokens[($stackPtr - 1)]["line"] !== $tokens[$stackPtr]["line"])
			    {
				return;
			    }

			$tokenContent = rtrim($tokens[$stackPtr]["content"], $phpcsFile->eolChar);
			if (empty($tokenContent) === false)
			    {
				if ($tokenContent !== rtrim($tokenContent))
				    {
					$phpcsFile->addError(_("Whitespace found at end of line"), $stackPtr, "EndLine");
				    }
			    }
			else if ($tokens[($stackPtr - 1)]["content"] !== rtrim($tokens[($stackPtr - 1)]["content"]) &&
			    $tokens[($stackPtr - 1)]["line"] === $tokens[$stackPtr]["line"])
			    {
				$phpcsFile->addError(_("Whitespace found at end of line"), ($stackPtr - 1), "EndLine");
			    }

			// Check for multiple blank lines in a function.
			if (($phpcsFile->hasCondition($stackPtr, T_FUNCTION) === true || $phpcsFile->hasCondition($stackPtr, T_CLOSURE) === true)
			    && $tokens[($stackPtr - 1)]["line"] < $tokens[$stackPtr]["line"] && $tokens[($stackPtr - 2)]["line"] === $tokens[($stackPtr - 1)]["line"])
			    {
				// This is an empty line and the line before this one is not  empty, so this could be the start of a multiple empty line block.
				$next  = $phpcsFile->findNext(T_WHITESPACE, $stackPtr, null, true);
				$lines = ($tokens[$next]["line"] - $tokens[$stackPtr]["line"]);
				if ($lines > 1)
				    {
					$error = _("Functions must not contain multiple empty lines in a row; found %s empty lines");
					$phpcsFile->addError($error, $stackPtr, "EmptyLines", array($lines));
				    }
			    } //end if
		    } //end if
	    } //end process()


	/**
	 * Check for start of file whitespace.
	 *
	 * @param File  $phpcsFile The file being scanned.
	 * @param int   $stackPtr  The position of the current token in the stack passed in $tokens.
	 * @param array $tokens    All tokens
	 *
	 * @return void
	 *
	 * @untranslatable StartFile
	 * @untranslatable PHP
	 * @untranslatable T_INLINE_HTML
	 */

	private function _checkForStartOfFileWhitespace(File &$phpcsFile, $stackPtr, array &$tokens)
	    {
		$whitespace = true;

		if ($phpcsFile->tokenizerType !== "PHP")
		    {
			// The first token is always the open tag inserted when tokenizsed
			// and the second token is always the first piece of content in
			// the file. If the second token is whitespace, there was
			// whitespace at the start of the file.
			if ($tokens[($stackPtr + 1)]["code"] !== T_WHITESPACE)
			    {
				$whitespace = false;
			    }
		    }
		else
		    {
			// If its the first token, then there is no space.
			if ($stackPtr === 0)
			    {
				$whitespace = false;
			    }

			for ($i = ($stackPtr - 1); $i >= 0; $i--)
			    {
				// If we find something that isn't inline html then there is something previous in the file.
				if ($tokens[$i]["type"] !== "T_INLINE_HTML")
				    {
					$whitespace = false;
				    }

				// If we have ended up with inline html make sure it isn't just whitespace.
				$tokenContent = trim($tokens[$i]["content"]);
				if ($tokenContent !== "")
				    {
					$whitespace = false;
				    }
			    }
		    } //end if

		if ($whitespace === true)
		    {
			$phpcsFile->addError(_("Additional whitespace found at start of file"), $stackPtr, "StartFile");
		    }
	    } //end _checkForStartOfFileWhitespace()


	/**
	 * Check for end of file whitespace.
	 *
	 * @param File  $phpcsFile The file being scanned.
	 * @param int   $stackPtr  The position of the current token in the stack passed in $tokens.
	 * @param array $tokens    All tokens.
	 *
	 * @return void
	 *
	 * @untranslatable JS
	 * @untranslatable CSS
	 * @untranslatable PHP
	 * @untranslatable T_INLINE_HTML
	 * @untranslatable EndFile
	 */

	private function _checkForEndOfFileWhitespace(File &$phpcsFile, $stackPtr, array &$tokens)
	    {
		$whitespace = true;

		if ($phpcsFile->tokenizerType === "JS")
		    {
			// The last token is always the close tag inserted when tokenized
			// and the second last token is always the last piece of content in
			// the file. If the second last token is whitespace, there was
			// whitespace at the end of the file.
			$stackPtr--;
		    }
		else if ($phpcsFile->tokenizerType === "CSS")
		    {
			// The last two tokens are always the close tag and whitespace
			// inserted when tokenizsed and the third last token is always the
			// last piece of content in the file. If the third last token is
			// whitespace, there was whitespace at the end of the file.
			$stackPtr -= 2;
		    }

		if ($phpcsFile->tokenizerType === "PHP")
		    {
			if (isset($tokens[($stackPtr + 1)]) === false)
			    {
				// The close PHP token is the last in the file.
				$whitespace = false;
			    }
			else
			    {
				for ($i = ($stackPtr + 1); $i < $phpcsFile->numTokens; $i++)
				    {
					if ($tokens[$i]["type"] !== "T_INLINE_HTML" || empty(trim($tokens[$i]["content"])) === false)
					    {
						// If we find something that isn't inline html then there
						// is more to the file.
						// If we have ended up with inline html make sure it
						// isn't just whitespace.
						$whitespace = false;
						break;
					    }
				    }
			    } //end if
		    }
		else
		    {
			if ($tokens[$stackPtr]["code"] !== T_WHITESPACE)
			    {
				// The pointer is now looking at the last content in the file and
				// not the fake PHP end tag the tokenizer inserted.
				$whitespace = false;
			    }
			else if ($tokens[($stackPtr - 1)]["code"] !== T_WHITESPACE && $tokens[$stackPtr]["content"] === $phpcsFile->eolChar)
			    {
				// Allow a single newline at the end of the last line in the file.
				$whitespace = false;
			    }
		    } //end if

		if ($whitespace === true)
		    {
			$phpcsFile->addError(_("Additional whitespace found at end of file"), $stackPtr, "EndFile");
		    }
	    } //end _checkForEndOfFileWhitespace()


    } //end class

?>
