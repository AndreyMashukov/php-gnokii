<?php

/**
 * PHP version 5
 *
 * @package Logics\BuildTools\CodeSniffer\Squiz
 */

namespace Logics\BuildTools\CodeSniffer\Squiz;

use \Logics\BuildTools\CodeSniffer\AbstractVariableSniff;
use \Logics\BuildTools\CodeSniffer\CodeSniffer;
use \Logics\BuildTools\CodeSniffer\File;

/**
 * Parses and verifies the variable doc comment.
 *
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @author    Marc McIntyre <mmcintyre@squiz.net>
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2006-2012 Squiz Pty Ltd (ABN 77 084 670 600)
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 * @version   SVN: $Date: 2016-08-16 23:39:01 +0900 (Tue, 16 Aug 2016) $ $Revision: 26 $
 * @link      $HeadURL: https://open.logics.net.au/buildtools/codesniffer/tags/0.1.3/src/CodeSniffer/Standards/Squiz/Sniffs/Commenting/VariableCommentSniff.php $
 */

class VariableCommentSniff extends AbstractVariableSniff
    {

	/**
	 * Called to process class member vars.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $stackPtr  The position of the current token in the stack passed in $tokens.
	 *
	 * @return void
	 *
	 * @internalconst T_DOC_COMMENT_CLOSE_TAG T_DOC_COMMENT_CLOSE_TAG token
	 *
	 * @untranslatable WrongStyle
	 * @untranslatable Missing
	 */

	public function processMemberVar(File &$phpcsFile, $stackPtr)
	    {
		$tokens       = &$phpcsFile->tokens;
		$commentToken = array(
				 T_COMMENT,
				 T_DOC_COMMENT_CLOSE_TAG,
				);

		$commentEnd = $phpcsFile->findPrevious($commentToken, $stackPtr);
		if ($commentEnd === false)
		    {
			$phpcsFile->addError(_("Missing member variable doc comment"), $stackPtr, "Missing");
		    }
		else if ($tokens[$commentEnd]["code"] === T_COMMENT)
		    {
			$phpcsFile->addError(_("You must use") . " \"/**\" " . _("style comments for a member variable comment"), $stackPtr, "WrongStyle");
		    }
		else if ($tokens[$commentEnd]["code"] !== T_DOC_COMMENT_CLOSE_TAG)
		    {
			$phpcsFile->addError(_("Missing member variable doc comment"), $stackPtr, "Missing");
		    }
		else
		    {
			// Make sure the comment we have found belongs to us.
			$commentFor = $phpcsFile->findNext(array(T_VARIABLE, T_CLASS, T_INTERFACE), ($commentEnd + 1));
			if ($commentFor !== $stackPtr)
			    {
				$phpcsFile->addError(_("Missing member variable doc comment"), $stackPtr, "Missing");
			    }
			else
			    {
				$this->_checkComment($phpcsFile, $tokens, $commentEnd);
			    }
		    } //end if
	    } //end processMemberVar()


	/**
	 * Check variable comment
	 *
	 * @param File  $phpcsFile  The file being scanned.
	 * @param array $tokens     Tokens array
	 * @param int   $commentEnd The position of the comment end token in the stack passed in $tokens.
	 *
	 * @return void
	 *
	 * @internalconst T_DOC_COMMENT_STRING T_DOC_COMMENT_STRING token
	 *
	 * @untranslatable DuplicateVar
	 * @untranslatable @see
	 * @untranslatable EmptySees
	 * @untranslatable %s
	 * @untranslatable TagNotAllowed
	 * @untranslatable MissingVar
	 * @untranslatable @var
	 */

	private function _checkComment(File &$phpcsFile, array &$tokens, $commentEnd)
	    {
		$commentStart = $tokens[$commentEnd]["comment_opener"];

		$foundVar = null;
		foreach ($tokens[$commentStart]["comment_tags"] as $tag)
		    {
			if ($tokens[$tag]["content"] === "@var")
			    {
				if ($foundVar !== null)
				    {
					$error = _("Only one") . " @var " . _("tag is allowed in a member variable comment");
					$phpcsFile->addError($error, $tag, "DuplicateVar");
				    }
				else
				    {
					$foundVar = $tag;
				    }
			    }
			else if ($tokens[$tag]["content"] === "@see")
			    {
				// Make sure the tag isn't empty.
				$string = $phpcsFile->findNext(T_DOC_COMMENT_STRING, $tag, $commentEnd);
				if ($string === false || $tokens[$string]["line"] !== $tokens[$tag]["line"])
				    {
					$error = _("Content missing for") . " @see " . _("tag in member variable comment");
					$phpcsFile->addError($error, $tag, "EmptySees");
				    }
			    }
			else
			    {
				$error = "%s " . _("tag is not allowed in member variable comment");
				$data  = array($tokens[$tag]["content"]);
				$phpcsFile->addWarning($error, $tag, "TagNotAllowed", $data);
			    } //end if
		    } //end foreach

		// The @var tag is the only one we require.
		if ($foundVar === null)
		    {
			$error = _("Missing") . " @var " . _("tag in member variable comment");
			$phpcsFile->addError($error, $commentEnd, "MissingVar");
		    }
		else
		    {
			$this->_checkVar($phpcsFile, $tokens, $commentStart, $commentEnd, $foundVar);
		    }
	    } //end _checkComment()


	/**
	 * Check variable comment
	 *
	 * @param File  $phpcsFile    The file being scanned.
	 * @param array $tokens       Tokens array
	 * @param int   $commentStart The position of the comment start token in the stack passed in $tokens.
	 * @param int   $commentEnd   The position of the comment end token in the stack passed in $tokens.
	 * @param int   $foundVar     The position of the @var token in the stack passed in $tokens.
	 *
	 * @return void
	 *
	 * @internalconst T_DOC_COMMENT_STRING T_DOC_COMMENT_STRING token
	 *
	 * @untranslatable VarOrder
	 * @untranslatable EmptyVar
	 * @untranslatable \"%s\"
	 * @untranslatable @var
	 * @untranslatable IncorrectVarType
	 */

	private function _checkVar(File &$phpcsFile, array &$tokens, $commentStart, $commentEnd, $foundVar)
	    {
		$firstTag = $tokens[$commentStart]["comment_tags"][0];
		if ($foundVar !== null && $tokens[$firstTag]["content"] !== "@var")
		    {
			$error = _("The") . " @var " . _("tag must be the first tag in a member variable comment");
			$phpcsFile->addError($error, $foundVar, "VarOrder");
		    }

		// Make sure the tag isn't empty and has the correct padding.
		$string = $phpcsFile->findNext(T_DOC_COMMENT_STRING, $foundVar, $commentEnd);
		if ($string === false || $tokens[$string]["line"] !== $tokens[$foundVar]["line"])
		    {
			$error = _("Content missing for") . " @var " . _("tag in member variable comment");
			$phpcsFile->addError($error, $foundVar, "EmptyVar");
		    }
		else
		    {
			$varType       = $tokens[($foundVar + 2)]["content"];
			$suggestedType = $this->getSuggestedType($varType);
			if ($varType !== $suggestedType)
			    {
				$error = _("Expected") . " \"%s\" " . _("but found") . " \"%s\" " . _("for") . " @var " . _("tag in member variable comment");
				$data  = array(
					  $suggestedType,
					  $varType,
					 );
				$phpcsFile->addError($error, ($foundVar + 2), "IncorrectVarType", $data);
			    }
		    }
	    } //end _checkVar()


	/**
	 * Suggest a type
	 *
	 * @param string $varType Variable type
	 *
	 * @return string Suggested type
	 */

	protected function getSuggestedType($varType)
	    {
		return CodeSniffer::suggestType($varType);
	    } //end getSuggestedType()


	/**
	 * Called to process a normal variable.
	 *
	 * Not required for this sniff.
	 *
	 * @param File $phpcsFile The PHP_CodeSniffer file where this token was found.
	 * @param int  $stackPtr  The position where the double quoted string was found.
	 *
	 * @return void
	 */

	protected function processVariable(File &$phpcsFile, $stackPtr)
	    {
		unset($phpcsFile);
		unset($stackPtr);
	    } //end processVariable()


	/**
	 * Called to process variables found in double quoted strings.
	 *
	 * Not required for this sniff.
	 *
	 * @param File $phpcsFile The CodeSniffer file where this token was found.
	 * @param int  $stackPtr  The position where the double quoted string was found.
	 *
	 * @return void
	 */

	protected function processVariableInString(File &$phpcsFile, $stackPtr)
	    {
		unset($phpcsFile);
		unset($stackPtr);
	    } //end processVariableInString()


    } //end class

?>
