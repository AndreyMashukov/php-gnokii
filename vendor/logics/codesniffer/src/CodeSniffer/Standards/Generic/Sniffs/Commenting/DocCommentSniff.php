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
 * Ensures doc blocks follow basic formatting.
 *
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2006-2012 Squiz Pty Ltd (ABN 77 084 670 600)
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 * @version   SVN: $Date: 2016-08-16 23:39:01 +0900 (Tue, 16 Aug 2016) $ $Revision: 26 $
 * @link      $HeadURL: https://open.logics.net.au/buildtools/codesniffer/tags/0.1.3/src/CodeSniffer/Standards/Generic/Sniffs/Commenting/DocCommentSniff.php $
 *
 * @untranslatable PHP
 * @untranslatable JS
 */

class DocCommentSniff implements Sniff
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
		return array(T_DOC_COMMENT_OPEN_TAG);
	    } //end register()


	/**
	 * Processes this test, when one of its tokens is encountered.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $stackPtr  The position of the current token in the stack passed in $tokens.
	 *
	 * @return void
	 *
	 * @internalconst T_DOC_COMMENT_WHITESPACE T_DOC_COMMENT_WHITESPACE token
	 * @internalconst T_DOC_COMMENT_STAR       T_DOC_COMMENT_STAR token
	 * @internalconst T_DOC_COMMENT_TAG        T_DOC_COMMENT_TAG token
	 * @internalconst T_DOC_COMMENT_STRING     T_DOC_COMMENT_STRING token
	 *
	 * @untranslatable Empty
	 * @untranslatable ContentAfterOpen
	 * @untranslatable ContentBeforeClose
	 * @untranslatable SpacingAfter
	 * @untranslatable MissingShort
	 * @untranslatable SpacingBeforeShort
	 * @untranslatable ShortNotCapital
	 * @untranslatable SpacingBetween
	 * @untranslatable LongNotCapital
	 * @untranslatable SpacingBeforeTags
	 * @untranslatable @param
	 * @untranslatable ParamGroup
	 * @untranslatable NonParamGroup
	 * @untranslatable SpacingAfterTagGroup
	 * @untranslatable %s
	 * @untranslatable TagValueIndent
	 * @untranslatable ParamNotFirst
	 * @untranslatable TagsNotGrouped
	 */

	public function process(File $phpcsFile, $stackPtr)
	    {
		$tokens       = &$phpcsFile->tokens;
		$commentStart = $stackPtr;
		$commentEnd   = $tokens[$stackPtr]["comment_closer"];

		$empty = array(
			  T_DOC_COMMENT_WHITESPACE,
			  T_DOC_COMMENT_STAR,
			 );

		$short = $phpcsFile->findNext($empty, ($stackPtr + 1), $commentEnd, true);
		if ($short === false)
		    {
			// No content at all.
			$error = _("Doc comment is empty");
			$phpcsFile->addError($error, $stackPtr, "Empty");
			return;
		    }

		// The first line of the comment should just be the /** code.
		if ($tokens[$short]["line"] === $tokens[$stackPtr]["line"])
		    {
			$error = _("The open comment tag must be the only content on the line");
			$phpcsFile->addError($error, $stackPtr, "ContentAfterOpen");
		    }

		// The last line of the comment should just be the */ code.
		$prev = $phpcsFile->findPrevious($empty, ($commentEnd - 1), $stackPtr, true);
		if ($tokens[$prev]["line"] === $tokens[$commentEnd]["line"])
		    {
			$error = _("The close comment tag must be the only content on the line");
			$phpcsFile->addError($error, $commentEnd, "ContentBeforeClose");
		    }

		// Check for additional blank lines at the end of the comment.
		if ($tokens[$prev]["line"] < ($tokens[$commentEnd]["line"] - 1))
		    {
			$error = _("Additional blank lines found at end of doc comment");
			$phpcsFile->addError($error, $commentEnd, "SpacingAfter");
		    }

		// Check for a comment description.
		if ($tokens[$short]["code"] !== T_DOC_COMMENT_STRING)
		    {
			$error = _("Missing short description in doc comment");
			$phpcsFile->addError($error, $stackPtr, "MissingShort");
			return;
		    }

		// No extra newline before short description.
		if ($tokens[$short]["line"] !== ($tokens[$stackPtr]["line"] + 1))
		    {
			$error = _("Doc comment short description must be on the first line");
			$phpcsFile->addError($error, $short, "SpacingBeforeShort");
		    }

		// Account for the fact that a short description might cover
		// multiple lines.
		$shortContent = $tokens[$short]["content"];
		$shortEnd     = $short;
		for ($i = ($short + 1); $i < $commentEnd; $i++)
		    {
			if ($tokens[$i]["code"] === T_DOC_COMMENT_STRING)
			    {
				if ($tokens[$i]["line"] === ($tokens[$shortEnd]["line"] + 1))
				    {
					$shortContent .= $tokens[$i]["content"];
					$shortEnd      = $i;
				    }
				else
				    {
					break;
				    }
			    }
		    }

		if (preg_match("/\p{Lu}|\P{L}/u", $shortContent[0]) === 0)
		    {
			$error = _("Doc comment short description must start with a capital letter");
			$phpcsFile->addError($error, $short, "ShortNotCapital");
		    }

		$long = $phpcsFile->findNext($empty, ($shortEnd + 1), ($commentEnd - 1), true);
		if ($long === false)
		    {
			return;
		    }

		if ($tokens[$long]["code"] === T_DOC_COMMENT_STRING)
		    {
			if ($tokens[$long]["line"] !== ($tokens[$shortEnd]["line"] + 2))
			    {
				$error = _("There must be exactly one blank line between descriptions in a doc comment");
				$phpcsFile->addError($error, $long, "SpacingBetween");
			    }

			if (preg_match("/\p{Lu}|\P{L}/u", $tokens[$long]["content"][0]) === 0)
			    {
				$error = _("Doc comment long description must start with a capital letter");
				$phpcsFile->addError($error, $long, "LongNotCapital");
			    }
		    } //end if

		if (empty($tokens[$commentStart]["comment_tags"]) === true)
		    {
			// No tags in the comment.
			return;
		    }

		$firstTag = $tokens[$commentStart]["comment_tags"][0];
		$prev     = $phpcsFile->findPrevious($empty, ($firstTag - 1), $stackPtr, true);
		if ($tokens[$firstTag]["line"] !== ($tokens[$prev]["line"] + 2))
		    {
			$error = _("There must be exactly one blank line before the tags in a doc comment");
			$phpcsFile->addError($error, $firstTag, "SpacingBeforeTags");
		    }

		// Break out the tags into groups and check alignment within each.
		// A tag group is one where there are no blank lines between tags.
		// The param tag group is special as it requires all @param tags to be inside.
		$tagGroups    = array();
		$groupid      = 0;
		$paramGroupid = null;
		foreach ($tokens[$commentStart]["comment_tags"] as $pos => $tag)
		    {
			if ($pos > 0)
			    {
				$prev = $phpcsFile->findPrevious(T_DOC_COMMENT_STRING, ($tag - 1), $tokens[$commentStart]["comment_tags"][($pos - 1)]);

				if ($prev === false)
				    {
					$prev = $tokens[$commentStart]["comment_tags"][($pos - 1)];
				    }

				if ($tokens[$prev]["line"] !== ($tokens[$tag]["line"] - 1))
				    {
					$groupid++;
				    }
			    }

			if ($tokens[$tag]["content"] === "@param")
			    {
				if (($paramGroupid === null && empty($tagGroups[$groupid]) === false) || ($paramGroupid !== null && $paramGroupid !== $groupid))
				    {
					$error = _("Parameter tags must be grouped together in a doc commment");
					$phpcsFile->addError($error, $tag, "ParamGroup");
				    }

				if ($paramGroupid === null)
				    {
					$paramGroupid = $groupid;
				    }
			    }
			else if ($groupid === $paramGroupid)
			    {
				$error = _("Tag cannot be grouped with parameter tags in a doc comment");
				$phpcsFile->addError($error, $tag, "NonParamGroup");
			    } //end if

			$tagGroups[$groupid][] = $tag;
		    } //end foreach

		foreach ($tagGroups as $group)
		    {
			$maxLength = 0;
			$paddings  = array();
			foreach ($group as $pos => $tag)
			    {
				$tagLength = strlen($tokens[$tag]["content"]);
				if ($tagLength > $maxLength)
				    {
					$maxLength = $tagLength;
				    }

				// Check for a value. No value means no padding needed.
				$string = $phpcsFile->findNext(T_DOC_COMMENT_STRING, $tag, $commentEnd);
				if ($string !== false && $tokens[$string]["line"] === $tokens[$tag]["line"])
				    {
					$paddings[$tag] = strlen($tokens[($tag + 1)]["content"]);
				    }
			    }

			// Check that there was single blank line after the tag block
			// but account for a multi-line tag comments.
			$lastTag = $group[$pos];
			$next    = $phpcsFile->findNext(T_DOC_COMMENT_TAG, ($lastTag + 3), $commentEnd);
			if ($next !== false)
			    {
				$prev = $phpcsFile->findPrevious(array(T_DOC_COMMENT_TAG, T_DOC_COMMENT_STRING), ($next - 1), $commentStart);
				if ($tokens[$next]["line"] !== ($tokens[$prev]["line"] + 2))
				    {
					$error = _("There must be a single blank line after a tag group");
					$phpcsFile->addError($error, $lastTag, "SpacingAfterTagGroup");
				    }
			    } //end if

			// Now check paddings.
			foreach ($paddings as $tag => $padding)
			    {
				$required = ($maxLength - strlen($tokens[$tag]["content"]) + 1);

				if ($padding !== $required)
				    {
					$error = _("Tag value indented incorrectly; expected") . " %s " . _("spaces but found") . " %s";
					$data  = array(
						  $required,
						  $padding,
						 );

					$phpcsFile->addError($error, ($tag + 1), "TagValueIndent", $data);
				    }
			    }
		    } //end foreach

		// If there is a param group, it needs to be first.
		if ($paramGroupid !== null && $paramGroupid !== 0)
		    {
			$error = _("Parameter tags must be defined first in a doc commment");
			$phpcsFile->addError($error, $tagGroups[$paramGroupid][0], "ParamNotFirst");
		    }

		$foundTags = array();
		foreach ($tokens[$stackPtr]["comment_tags"] as $pos => $tag)
		    {
			$tagName = $tokens[$tag]["content"];
			if (isset($foundTags[$tagName]) === true)
			    {
				$lastTag = $tokens[$stackPtr]["comment_tags"][($pos - 1)];
				if ($tokens[$lastTag]["content"] !== $tagName)
				    {
					$error = _("Tags must be grouped together in a doc comment");
					$phpcsFile->addError($error, $tag, "TagsNotGrouped");
				    }

				continue;
			    }

			$foundTags[$tagName] = true;
		    }
	    } //end process()


    } //end class

?>
