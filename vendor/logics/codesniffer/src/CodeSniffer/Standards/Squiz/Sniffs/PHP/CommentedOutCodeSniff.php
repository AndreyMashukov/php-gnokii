<?php

/**
 * PHP version 5
 *
 * @package Logics\BuildTools\CodeSniffer\Squiz
 */

namespace Logics\BuildTools\CodeSniffer\Squiz;

use \Exception;
use \Logics\BuildTools\CodeSniffer\File;
use \Logics\BuildTools\CodeSniffer\Sniff;
use \Logics\BuildTools\CodeSniffer\Tokens;

/**
 * Squiz_Sniffs_PHP_CommentedOutCodeSniff.
 *
 * Warn about commented out code.
 *
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @author    Marc McIntyre <mmcintyre@squiz.net>
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2006-2012 Squiz Pty Ltd (ABN 77 084 670 600)
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 * @version   SVN: $Date: 2016-08-16 23:39:01 +0900 (Tue, 16 Aug 2016) $ $Revision: 26 $
 * @link      $HeadURL: https://open.logics.net.au/buildtools/codesniffer/tags/0.1.3/src/CodeSniffer/Standards/Squiz/Sniffs/PHP/CommentedOutCodeSniff.php $
 *
 * @untranslatable PHP
 * @untranslatable CSS
 */

class CommentedOutCodeSniff implements Sniff
    {

	/**
	 * A list of tokenizers this sniff supports.
	 *
	 * @var array
	 */
	public $supportedTokenizers = array(
				       "PHP",
				       "CSS",
				      );

	/**
	 * If a comment is more than $maxPercentage% code, a warning will be shown.
	 *
	 * @var int
	 */
	public $maxPercentage = 35;

	/**
	 * Returns an array of tokens this test wants to listen for.
	 *
	 * @return array
	 */

	public function register()
	    {
		return array(T_COMMENT);
	    } //end register()


	/**
	 * Processes this test, when one of its tokens is encountered.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $stackPtr  The position of the current token in the stack passed in $tokens.
	 *
	 * @return void
	 *
	 * @internalconst T_STRING_CONCAT T_STRING_CONCAT token
	 * @internalconst T_NONE          T_NONE token
	 * @internalconst T_GOTO_LABEL    T_GOTO_LABEL token
	 *
	 * @untranslatable //end
	 * @untranslatable error_reporting
	 * @untranslatable Found
	 * @untranslatable %s%%
	 */

	public function process(File $phpcsFile, $stackPtr)
	    {
		$tokens = &$phpcsFile->tokens;

		// Process whole comment blocks at once, so skip all but the first token.
		if ($stackPtr > 0 && $tokens[$stackPtr]["code"] === $tokens[($stackPtr - 1)]["code"])
		    {
			return;
		    }

		// Ignore comments at the end of code blocks.
		if (substr($tokens[$stackPtr]["content"], 0, 6) === "//end ")
		    {
			return;
		    }

		$content = $this->_getCommentAsString($phpcsFile, $stackPtr, $tokens);

		// Because we are not really parsing code, the tokenizer can throw all sorts of errors that don't mean anything, so ignore them.
		$oldErrors = ini_get("error_reporting");
		ini_set("error_reporting", 0);
		try
		    {
			$stringTokens = File::tokenizeString($content, $phpcsFile->tokenizer, $phpcsFile->eolChar);
		    }
		catch (Exception $e)
		    {
			// We couldn't check the comment, so ignore it.
			ini_set("error_reporting", $oldErrors);
			return;
		    }

		ini_set("error_reporting", $oldErrors);

		$emptyTokens = array(
				T_WHITESPACE              => true,
				T_STRING                  => true,
				T_STRING_CONCAT           => true,
				T_ENCAPSED_AND_WHITESPACE => true,
				T_NONE                    => true,
				T_COMMENT                 => true,
			       );

		$numTokens = count($stringTokens);

		// We know what the first two and last two tokens should be (because we put them there) so ignore this comment.
		// If those tokens were not parsed correctly. It obviously means this is not valid code.
		// First token is always the opening PHP tag.
		if ($stringTokens[0]["code"] !== T_OPEN_TAG)
		    {
			return;
		    }

		// Last token is always the closing PHP tag, unless something went wrong.
		if (isset($stringTokens[($numTokens - 1)]) === false || $stringTokens[($numTokens - 1)]["code"] !== T_CLOSE_TAG)
		    {
			return;
		    }

		// Second last token is always whitespace or a comment, depending on the code inside the comment.
		if ($phpcsFile->tokenizerType === "PHP" && isset(Tokens::$emptyTokens[$stringTokens[($numTokens - 2)]["code"]]) === false)
		    {
			return;
		    }

		$numComment  = 0;
		$numPossible = 0;
		$numCode     = 0;

		for ($i = 0; $i < $numTokens; $i++)
		    {
			if (isset($emptyTokens[$stringTokens[$i]["code"]]) === true)
			    {
				// Looks like comment.
				$numComment++;
			    }
			else if (in_array($stringTokens[$i]["code"], Tokens::$comparisonTokens) === true
			|| in_array($stringTokens[$i]["code"], Tokens::$arithmeticTokens) === true || $stringTokens[$i]["code"] === T_GOTO_LABEL)
			    {
				// Commented out HTML/XML and other docs contain a lot of these characters, so it is best to not use them directly.
				$numPossible++;
			    }
			else
			    {
				// Looks like code.
				$numCode++;
			    }
		    }

		// We subtract 3 from the token number so we ignore the start/end tokens and their surrounding whitespace.
		// We take 2 off the number of code tokens so we ignore the start/end tokens.
		if ($numTokens > 3)
		    {
			$numTokens -= 3;
		    }

		if ($numCode >= 2)
		    {
			$numCode -= 2;
		    }

		$percentCode = ceil((($numCode / $numTokens) * 100));
		if ($percentCode > $this->maxPercentage)
		    {
			// Just in case.
			$percentCode = min(100, $percentCode);

			$error = _("This comment is ") . "%s%%" . _(" valid code; is this commented out code?");
			$data  = array($percentCode);
			$phpcsFile->addWarning($error, $stackPtr, "Found", $data);
		    }
	    } //end process()


	/**
	 * Get comment as tokenizeable string
	 *
	 * @param File  $phpcsFile The file being scanned.
	 * @param int   $stackPtr  The position of the current token in the stack passed in $tokens.
	 * @param array $tokens    All tokens
	 *
	 * @return string
	 *
	 * @untranslatable PHP
	 * @untranslatable <?php
	 */

	private function _getCommentAsString(File &$phpcsFile, $stackPtr, array &$tokens)
	    {
		$content = ($phpcsFile->tokenizerType === "PHP") ? "<?php " : "";

		for ($i = $stackPtr; $i < $phpcsFile->numTokens; $i++)
		    {
			if ($tokens[$stackPtr]["code"] !== $tokens[$i]["code"])
			    {
				break;
			    }

			/*
			    Trim as much off the comment as possible so we don't
			    have additional whitespace tokens or comment tokens
			*/

			$tokenContent = trim($tokens[$i]["content"]);

			if (substr($tokenContent, 0, 2) === "//")
			    {
				$tokenContent = substr($tokenContent, 2);
			    }

			if (substr($tokenContent, 0, 1) === "#")
			    {
				$tokenContent = substr($tokenContent, 1);
			    }

			if (substr($tokenContent, 0, 3) === "/**")
			    {
				$tokenContent = substr($tokenContent, 3);
			    }

			if (substr($tokenContent, 0, 2) === "/*")
			    {
				$tokenContent = substr($tokenContent, 2);
			    }

			if (substr($tokenContent, -2) === "*/")
			    {
				$tokenContent = substr($tokenContent, 0, -2);
			    }

			if (substr($tokenContent, 0, 1) === "*")
			    {
				$tokenContent = substr($tokenContent, 1);
			    }

			$content .= $tokenContent . $phpcsFile->eolChar;
		    } //end for

		$content = trim($content) . (($phpcsFile->tokenizerType === "PHP") ? " ?>" : "");

		// Quite a few comments use multiple dashes, equals signs etc
		// to frame comments and licence headers.
		$content = preg_replace("/[-=*]+/", "-", $content);

		return $content;
	    } //end _getCommentAsString()


    } //end class

?>
