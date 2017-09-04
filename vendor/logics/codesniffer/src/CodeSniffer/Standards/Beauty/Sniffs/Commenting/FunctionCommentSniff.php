<?php

/**
 * PHP version 5
 *
 * @package Logics\BuildTools\CodeSniffer\Beauty
 */

namespace Logics\BuildTools\CodeSniffer\Beauty;

use \Logics\BuildTools\CodeSniffer\CodeSniffer;
use \Logics\BuildTools\CodeSniffer\File;
use \Logics\BuildTools\CodeSniffer\Squiz\FunctionCommentSniff as Squiz_FunctionCommentSniff;
use \Logics\BuildTools\CodeSniffer\Tokens;

/**
 * Parses and verifies the doc comments for functions.
 *
 * Verifies that :
 * <ul>
 *  <li>A comment exists</li>
 *  <li>There is a blank newline after the short description.</li>
 *  <li>There is a blank newline between the long and short description.</li>
 *  <li>There is a blank newline between the long description and tags.</li>
 *  <li>Parameter names represent those in the method.</li>
 *  <li>Parameter comments are in the correct order</li>
 *  <li>Parameter comments are complete</li>
 *  <li>A space is present before the first and after the last parameter</li>
 *  <li>A return type exists</li>
 *  <li>There must be one blank line between body and headline comments.</li>
 *  <li>Any throw tag must have an exception class.</li>
 * </ul>
 *
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @author    Marc McIntyre <mmcintyre@squiz.net>
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2006-2012 Squiz Pty Ltd (ABN 77 084 670 600)
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 * @version   SVN: $Date: 2016-08-16 23:39:01 +0900 (Tue, 16 Aug 2016) $ $Revision: 26 $
 * @link      $HeadURL: https://open.logics.net.au/buildtools/codesniffer/tags/0.1.3/src/CodeSniffer/Standards/Beauty/Sniffs/Commenting/FunctionCommentSniff.php $
 */

class FunctionCommentSniff extends Squiz_FunctionCommentSniff
    {

	/**
	 * Tags in correct order and related info.
	 *
	 * @var array
	 */
	protected $tags = array(
			   "@param"              => array(
						     "required"       => false,
						     "allow_multiple" => true,
						    ),
			   "@return"             => array(
						     "required"       => true,
						     "allow_multiple" => false,
						    ),
			   "@throws"             => array(
						     "required"       => false,
						     "allow_multiple" => true,
						    ),
			   "@exceptioncode"      => array(
						     "required"       => false,
						     "allow_multiple" => true,
						    ),
			   "@see"                => array(
						     "required"       => false,
						     "allow_multiple" => true,
						    ),
			   "@soap"               => array(
						     "required"       => false,
						     "allow_multiple" => false,
						     "allow_empty"    => true,
						    ),
			   "@soap-indicator"     => array(
						     "required"       => false,
						     "allow_multiple" => false,
						    ),
			   "@soap-header-input"  => array(
						     "required"       => false,
						     "allow_multiple" => true,
						    ),
			   "@soap-header-output" => array(
						     "required"       => false,
						     "allow_multiple" => true,
						    ),
			   "@soap-wsdl"          => array(
						     "required"       => false,
						     "allow_multiple" => true,
						    ),
			   "@requiredconst"      => array(
						     "required"       => false,
						     "allow_multiple" => true,
						    ),
			   "@optionalconst"      => array(
						     "required"       => false,
						     "allow_multiple" => true,
						    ),
			   "@internalconst"      => array(
						     "required"       => false,
						     "allow_multiple" => true,
						    ),
			   "@untranslatable"     => array(
						     "required"       => false,
						     "allow_multiple" => true,
						    ),
			  );

	/**
	 * Processes this test, when one of its tokens is encountered.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $stackPtr  The position of the current token in the stack passed in $tokens.
	 *
	 * @return void
	 *
	 * @internalconst T_DOC_COMMENT_CLOSE_TAG T_DOC_COMMENT_CLOSE_TAG token
	 *
	 * @untranslatable Missing
	 * @untranslatable WrongStyle
	 * @untranslatable SpacingAfter
	 */

	public function process(File $phpcsFile, $stackPtr)
	    {
		$tokens = &$phpcsFile->tokens;
		$find   = Tokens::$methodPrefixes;
		$find[] = T_WHITESPACE;

		$commentEnd = $phpcsFile->findPrevious($find, ($stackPtr - 1), null, true);
		if ($tokens[$commentEnd]["code"] === T_COMMENT)
		    {
			// Inline comments might just be closing comments for
			// control structures or functions instead of function comments
			// using the wrong comment type. If there is other code on the line,
			// assume they relate to that code.
			$prev = $phpcsFile->findPrevious($find, ($commentEnd - 1), null, true);
			if ($prev !== false && $tokens[$prev]["line"] === $tokens[$commentEnd]["line"])
			    {
				$commentEnd = $prev;
			    }
		    }

		if ($tokens[$commentEnd]["code"] !== T_DOC_COMMENT_CLOSE_TAG && $tokens[$commentEnd]["code"] !== T_COMMENT)
		    {
			$phpcsFile->addError(_("Missing function doc comment"), $stackPtr, "Missing");
		    }
		else if ($tokens[$commentEnd]["code"] === T_COMMENT)
		    {
			$phpcsFile->addError(_("You must use \"/**\" style comments for a function comment"), $stackPtr, "WrongStyle");
		    }
		else
		    {
			if ($tokens[$commentEnd]["line"] !== ($tokens[$stackPtr]["line"] - 2))
			    {
				$error = _("There must be one blank line after the function comment");
				$phpcsFile->addError($error, $commentEnd, "SpacingAfter");
			    }

			$commentStart = $tokens[$commentEnd]["comment_opener"];

			$this->processTags($phpcsFile, $stackPtr, $commentStart);

			$this->processThrows($phpcsFile, $stackPtr, $commentStart);
			$this->processParams($phpcsFile, $stackPtr, $commentStart);
		    } //end if
	    } //end process()


	/**
	 * Processes each required or optional tag.
	 *
	 * @param File $phpcsFile    The file being scanned.
	 * @param int  $stackPtr     The position of the current token in the stack passed in $tokens.
	 * @param int  $commentStart Position in the stack where the comment started.
	 *
	 * @return void
	 *
	 * @internalconst T_DOC_COMMENT_STRING T_DOC_COMMENT_STRING token
	 *
	 * @untranslatable Duplicate
	 * @untranslatable Empty
	 * @untranslatable @return
	 * @untranslatable Missing
	 * @untranslatable Tag
	 * @untranslatable %s
	 * @untranslatable TagOrder
	 */

	protected function processTags(File &$phpcsFile, $stackPtr, $commentStart)
	    {
		$tokens = &$phpcsFile->tokens;

		$docBlock = _("function");

		$commentEnd = $tokens[$commentStart]["comment_closer"];

		$foundTags = array();
		$tagTokens = array();
		foreach ($tokens[$commentStart]["comment_tags"] as $tag)
		    {
			$name    = $tokens[$tag]["content"];
			$comment = $phpcsFile->findNext(T_DOC_COMMENT_STRING, $tag, $commentEnd);
			if (isset($this->tags[$name]) === true)
			    {
				if ($this->tags[$name]["allow_multiple"] === false && isset($tagTokens[$name]) === true)
				    {
					$error = _("Only one") . " %s " . _("tag is allowed in a") . " %s " . _("comment");
					$data  = array(
						  $name,
						  $docBlock,
						 );
					$phpcsFile->addError($error, $tag, "Duplicate" . ucfirst(substr($name, 1)) . "Tag", $data);
				    }

				$foundTags[]        = $name;
				$tagTokens[$name][] = $tag;

				if (isset($this->tags[$name]["allow_empty"]) === false || $this->tags[$name]["allow_empty"] === false)
				    {
					if ($comment === false || $tokens[$comment]["line"] !== $tokens[$tag]["line"])
					    {
						$error = _("Content missing for") . " %s " . _("tag in") . " %s " . _("comment");
						$data  = array(
							  $name,
							  $docBlock,
							 );
						$phpcsFile->addError($error, $tag, "Empty" . ucfirst(substr($name, 1)) . "Tag", $data);
					    }
				    }
			    } //end if

			if ($name === "@return" && $comment !== false)
			    {
				$this->checkReturn($phpcsFile, $stackPtr, $tag);
			    }
		    } //end foreach

		// Check if the tags are in the correct position.
		$pos = 0;
		foreach ($this->tags as $tag => $tagData)
		    {
			if (isset($tagTokens[$tag]) === false)
			    {
				if ($tagData["required"] === true)
				    {
					$error = _("Missing") . " %s " . _("tag in") . " %s " . _("comment");
					$data  = array(
						  $tag,
						  $docBlock,
						 );
					$phpcsFile->addError($error, $commentEnd, "Missing" . ucfirst(substr($tag, 1)) . "Tag", $data);
				    }
			    }
			else
			    {
				if (isset($foundTags[$pos]) === false)
				    {
					break;
				    }

				if ($foundTags[$pos] !== $tag)
				    {
					$error = _("The tag in position") . " %s " . _("should be the") . " %s " . _("tag");
					$data  = array(
						  ($pos + 1),
						  $tag,
						 );
					$phpcsFile->addError($error, $tokens[$commentStart]["comment_tags"][$pos], ucfirst(substr($tag, 1)) . "TagOrder", $data);
				    }

				// Account for multiple tags.
				$pos++;
				while (isset($foundTags[$pos]) === true && $foundTags[$pos] === $tag)
				    {
					$pos++;
				    }
			    } //end if
		    } //end foreach
	    } //end processTags()


	/**
	 * Process any throw tags that this function comment has.
	 *
	 * @param File $phpcsFile    The file being scanned.
	 * @param int  $stackPtr     The position of the current token in the stack passed in $tokens.
	 * @param int  $commentStart The position in the stack where the comment started.
	 *
	 * @return void
	 *
	 * @untranslatable @throws
	 * @untranslatable InvalidNoThrows
	 */

	protected function processThrows(File &$phpcsFile, $stackPtr, $commentStart)
	    {
		parent::processThrows($phpcsFile, $stackPtr, $commentStart);

		$tokens = &$phpcsFile->tokens;

		$throws = false;
		foreach ($tokens[$commentStart]["comment_tags"] as $tag)
		    {
			if ($tokens[$tag]["content"] === "@throws")
			    {
				$throws = true;
				$pos    = $tag;
			    }
		    }

		if ($throws === true && isset($tokens[$stackPtr]["scope_closer"]) === true)
		    {
			$endToken   = $tokens[$stackPtr]["scope_closer"];
			$throwToken = $phpcsFile->findNext(array(T_THROW), $stackPtr, $endToken);
			if ($throwToken === false)
			    {
				$error = _("@throws declared but no throw statement found");
				$phpcsFile->addError($error, $pos, "InvalidNoThrows");
			    }
		    } //end if
	    } //end processThrows()


	/**
	 * Comments must start with a capital letter
	 *
	 * @param File   $phpcsFile The file being scanned.
	 * @param string $comment   Comment text
	 * @param int    $tag       Tag position
	 * @param string $what      Comment type
	 * @param string $short     Error short name
	 *
	 * @return void
	 *
	 * @untranslatable NotCapital
	 */

	protected function checkStartEnd(File &$phpcsFile, $comment, $tag, $what, $short)
	    {
		$firstChar = $comment{0};
		if (preg_match('|\p{Lu}|u', $firstChar) === 0)
		    {
			$error = $what . " " . _("must start with a capital letter");
			$phpcsFile->addError($error, $tag, $short . "NotCapital");
		    }
	    } //end checkStartEnd()


	/**
	 * Suggest a type
	 *
	 * @param string $varType Variable type
	 *
	 * @return string Suggested type
	 */

	protected function getSuggestedType($varType)
	    {
		return CodeSniffer::suggestType($varType, true);
	    } //end getSuggestedType()


    } //end class

?>
