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
 * InlineCommentSniff
 *
 * Checks that there is adequate spacing between comments.
 *
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @author    Marc McIntyre <mmcintyre@squiz.net>
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2006-2012 Squiz Pty Ltd (ABN 77 084 670 600)
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 * @version   SVN: $Date: 2016-08-16 23:39:01 +0900 (Tue, 16 Aug 2016) $ $Revision: 26 $
 * @link      $HeadURL: https://open.logics.net.au/buildtools/codesniffer/tags/0.1.3/src/CodeSniffer/Standards/Squiz/Sniffs/Commenting/InlineCommentSniff.php $
 *
 * @untranslatable PHP
 * @untranslatable JS
 */

class InlineCommentSniff implements Sniff
    {

	/**
	 * A list of tokenizers this sniff supports.
	 *
	 * @var array
	 */
	public $supportedTokenizers = array(
				       "PHP",
				       "JS",
				      );

	/**
	 * Returns an array of tokens this test wants to listen for.
	 *
	 * @return array
	 *
	 * @internalconst T_DOC_COMMENT_OPEN_TAG T_DOC_COMMENT_OPEN_TAG token
	 */

	public function register()
	    {
		return array(
			T_COMMENT,
			T_DOC_COMMENT_OPEN_TAG,
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
	 * @internalconst T_DOC_COMMENT_OPEN_TAG T_DOC_COMMENT_OPEN_TAG token
	 * @internalconst T_PROPERTY             T_PROPERTY token
	 * @internalconst T_EQUAL                T_EQUAL token
	 * @internalconst T_CLOSURE              T_CLOSURE token
	 * @internalconst T_OBJECT               T_OBJECT token
	 * @internalconst T_PROTOTYPE            T_PROTOTYPE token
	 *
	 * @untranslatable JS
	 * @untranslatable DocBlock
	 */

	public function process(File $phpcsFile, $stackPtr)
	    {
		$tokens = &$phpcsFile->tokens;

		$inlineComment = true;

		// If this is a function/class/interface doc block comment, skip it.
		// We are only interested in inline doc block comments, which are not allowed.
		if ($tokens[$stackPtr]["code"] === T_DOC_COMMENT_OPEN_TAG)
		    {
			$nextToken = $phpcsFile->findNext(Tokens::$emptyTokens, ($stackPtr + 1), null, true);

			$ignore = array(
				   T_CLASS,
				   T_INTERFACE,
				   T_TRAIT,
				   T_FUNCTION,
				   T_PUBLIC,
				   T_PRIVATE,
				   T_PROTECTED,
				   T_FINAL,
				   T_STATIC,
				   T_ABSTRACT,
				   T_CONST,
				   T_OBJECT,
				   T_PROPERTY,
				  );

			if (in_array($tokens[$nextToken]["code"], $ignore) === true)
			    {
				$inlineComment = false;
			    }
			else
			    {
				if ($phpcsFile->tokenizerType === "JS")
				    {
					// We allow block comments if a function is being assigned
					// to a variable.
					$ignore    = Tokens::$emptyTokens;
					$ignore[]  = T_EQUAL;
					$ignore[]  = T_STRING;
					$ignore[]  = T_OBJECT_OPERATOR;
					$nextToken = $phpcsFile->findNext($ignore, ($nextToken + 1), null, true);
					if ($tokens[$nextToken]["code"] === T_FUNCTION || $tokens[$nextToken]["code"] === T_CLOSURE
					|| $tokens[$nextToken]["code"] === T_OBJECT || $tokens[$nextToken]["code"] === T_PROTOTYPE)
					    {
						$inlineComment = false;
					    }
				    }

				if ($inlineComment === true)
				    {
					$prevToken = $phpcsFile->findPrevious(Tokens::$emptyTokens, ($stackPtr - 1), null, true);

					if ($tokens[$prevToken]["code"] === T_OPEN_TAG)
					    {
						$inlineComment = false;
					    }
					else if ($tokens[$stackPtr]["content"] === "/**")
					    {
						// Only error once per comment.
						$error = _("Inline doc block comments are not allowed; use \"/* Comment */\" or \"// Comment\" instead");
						$phpcsFile->addError($error, $stackPtr, "DocBlock");
					    }
				    }
			    } //end if
		    } //end if

		if ($inlineComment === true)
		    {
			$this->_checkInlineComment($phpcsFile, $tokens, $stackPtr);
		    } //end if
	    } //end process()


	/**
	 * Processes this test, when one of its tokens is encountered.
	 *
	 * @param File  $phpcsFile The file being scanned.
	 * @param array $tokens    All tokens
	 * @param int   $stackPtr  The position of the current token in the stack passed in $tokens.
	 *
	 * @return void
	 *
	 * @internalconst T_COMMA               T_COMMA token
	 * @internalconst T_SEMICOLON           T_SEMICOLON token
	 * @internalconst T_CLOSE_CURLY_BRACKET T_CLOSE_CURLY_BRACKET token
	 *
	 * @untranslatable WrongStyle
	 * @untranslatable \"// %s\"
	 * @untranslatable \"%s\"
	 * @untranslatable NoSpaceBefore
	 * @untranslatable %s
	 * @untranslatable TabBefore
	 * @untranslatable SpacingBefore
	 */

	private function _checkInlineComment(File &$phpcsFile, array &$tokens, $stackPtr)
	    {
		if ($tokens[$stackPtr]["content"]{0} === "#")
		    {
			$error = _("Perl-style comments are not allowed; use \"// Comment\" instead");
			$phpcsFile->addError($error, $stackPtr, "WrongStyle");
		    }

		// We don't want end of block comments. If the last comment is a closing
		// curly brace.
		$endOfBlockComment = false;
		$previousContent   = $phpcsFile->findPrevious(T_WHITESPACE, ($stackPtr - 1), null, true);
		if ($tokens[$previousContent]["line"] === $tokens[$stackPtr]["line"])
		    {
			if ($tokens[$previousContent]["code"] === T_CLOSE_CURLY_BRACKET)
			    {
				$endOfBlockComment = true;
			    }
			else if ($tokens[$previousContent]["code"] === T_COMMA || $tokens[$previousContent]["code"] === T_SEMICOLON)
			    {
				// Special case for JS files.
				$lastContent = $phpcsFile->findPrevious(T_WHITESPACE, ($previousContent - 1), null, true);
				if ($tokens[$lastContent]["code"] === T_CLOSE_CURLY_BRACKET)
				    {
					$endOfBlockComment = true;
				    }
			    }
		    }

		$comment = rtrim($tokens[$stackPtr]["content"]);

		// Only want inline comments.
		if ($endOfBlockComment === false && substr($comment, 0, 2) === "//")
		    {
			$spaceCount    = 0;
			$tabFound      = false;
			$commentLength = strlen($comment);
			for ($i = 2; $i < $commentLength && $comment[$i] === " "; $i++)
			    {
				if ($comment[$i] === "\t")
				    {
					$tabFound = true;
					break;
				    }

				if ($comment[$i] !== " ")
				    {
					break;
				    }

				$spaceCount++;
			    }

			if ($tabFound === true)
			    {
				$error = _("Tab found before comment text; expected \"// ") . "%s" . _(" but found ") . "\"%s\"";
				$data  = array(
					  ltrim(substr($comment, 2)),
					  $comment,
					 );
				$phpcsFile->addError($error, $stackPtr, "TabBefore", $data);
			    }
			else if ($spaceCount === 0)
			    {
				$error = _("No space found before comment text; expected") . " \"// %s\" " . _("but found") . " \"%s\"";
				$data  = array(
					  substr($comment, 2),
					  $comment,
					 );
				$phpcsFile->addError($error, $stackPtr, "NoSpaceBefore", $data);
			    }
			else if ($spaceCount > 1)
			    {
				$error = "%s " . _("spaces found before inline comment line; use block comment if you need indentation");
				$data  = array(
					  $spaceCount,
					  substr($comment, (2 + $spaceCount)),
					  $comment,
					 );
				$phpcsFile->addError($error, $stackPtr, "SpacingBefore", $data);
			    } //end if

			$this->_checkCommentContents($phpcsFile, $tokens, $stackPtr, $previousContent);
		    } //end if
	    } //end _checkInlineComment()


	/**
	 * Check comment text
	 *
	 * @param File  $phpcsFile       The file being scanned.
	 * @param array $tokens          All tokens
	 * @param int   $stackPtr        The position of the current token in the stack passed in $tokens.
	 * @param int   $previousContent The position of the previous content in the stack passed in $tokens.
	 *
	 * @return void
	 */

	private function _checkCommentContents(File &$phpcsFile, array &$tokens, $stackPtr, $previousContent)
	    {
		// The below section determines if a comment block is correctly capitalised,
		// and ends in a full-stop. It will find the last comment in a block, and
		// work its way up.
		$nextComment = $phpcsFile->findNext(array(T_COMMENT), ($stackPtr + 1), null, false);

		if ($nextComment === false || $tokens[$nextComment]["line"] !== ($tokens[$stackPtr]["line"] + 1))
		    {
			$lastComment = $stackPtr;
			$topComment  = $phpcsFile->findPrevious(array(T_COMMENT), ($lastComment - 1), null, false);
			while ($topComment !== false)
			    {
				if ($tokens[$topComment]["line"] !== ($tokens[$lastComment]["line"] - 1))
				    {
					break;
				    }

				$lastComment = $topComment;

				$topComment = $phpcsFile->findPrevious(array(T_COMMENT), ($lastComment - 1), null, false);
			    }

			$topComment  = $lastComment;
			$commentText = "";

			for ($i = $topComment; $i <= $stackPtr; $i++)
			    {
				if ($tokens[$i]["code"] === T_COMMENT)
				    {
					$commentText .= trim(substr($tokens[$i]["content"], 2));
				    }
			    }

			$this->_checkCommentText($phpcsFile, $tokens, $stackPtr, $commentText, $topComment, $previousContent);
		    } //end if
	    } //end _checkCommentContents()


	/**
	 * Check comment text
	 *
	 * @param File   $phpcsFile       The file being scanned.
	 * @param array  $tokens          All tokens
	 * @param int    $stackPtr        The position of the current token in the stack passed in $tokens.
	 * @param string $commentText     Comment text
	 * @param string $topComment      Top comment
	 * @param int    $previousContent The position of the previous content in the stack passed in $tokens.
	 *
	 * @return void
	 *
	 * @untranslatable Empty
	 * @untranslatable NotCapital
	 * @untranslatable %s
	 * @untranslatable InvalidEndChar
	 * @untranslatable SpacingAfter
	 */

	private function _checkCommentText(File &$phpcsFile, array &$tokens, $stackPtr, $commentText, $topComment, $previousContent)
	    {
		if ($commentText === "")
		    {
			$error = _("Blank comments are not allowed");
			$phpcsFile->addError($error, $stackPtr, "Empty");
		    }
		else
		    {
			if ($commentText[0] !== "@")
			    {
				if (preg_match("/\p{Lu}|\P{L}/u", $commentText[0]) === 0)
				    {
					$error = _("Inline comments must start with a capital letter");
					$phpcsFile->addError($error, $topComment, "NotCapital");
				    }

				$commentCloser   = $commentText[(strlen($commentText) - 1)];
				$acceptedClosers = array(
						    "full-stops"        => ".",
						    "exclamation marks" => "!",
						    "or question marks" => "?",
						   );

				if (in_array($commentCloser, $acceptedClosers) === false)
				    {
					$error = _("Inline comments must end in") . " %s";
					$ender = "";
					foreach ($acceptedClosers as $closerName => $symbol)
					    {
						$ender .= " " . $closerName . ",";
					    }

					$ender = rtrim($ender, ",");
					$data  = array($ender);
					$phpcsFile->addError($error, $stackPtr, "InvalidEndChar", $data);
				    }
			    } //end if

			// Finally, the line below the last comment cannot be empty if this inline comment is on a line by itself.
			if ($tokens[$previousContent]["line"] < $tokens[$stackPtr]["line"])
			    {
				// Finally, the line below the last comment cannot be empty.
				$start = false;
				for ($i = ($stackPtr + 1); $i < $phpcsFile->numTokens; $i++)
				    {
					if ($tokens[$i]["line"] === ($tokens[$stackPtr]["line"] + 1) && $tokens[$i]["code"] !== T_WHITESPACE)
					    {
						$start = true;
						break;
					    }
					else if ($tokens[$i]["line"] > ($tokens[$stackPtr]["line"] + 1))
					    {
						break;
					    }
				    }

				if ($start === false)
				    {
					$error = _("There must be no blank line following an inline comment");
					$phpcsFile->addError($error, $stackPtr, "SpacingAfter");
				    }
			    } //end if
		    } //end if
	    } //end _checkCommentText()


    } //end class

?>
