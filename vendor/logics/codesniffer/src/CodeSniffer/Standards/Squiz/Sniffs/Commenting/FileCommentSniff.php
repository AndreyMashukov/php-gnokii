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
 * Parses and verifies the file doc comment.
 *
 * Verifies that :
 * <ul>
 *  <li>A file doc comment exists.</li>
 *  <li>There is no blank line between the open tag and the file comment.</li>
 *  <li>Short description ends with a full stop.</li>
 *  <li>There is a blank line after the short description.</li>
 *  <li>Each paragraph of the long description ends with a full stop.</li>
 *  <li>There is a blank line between the description and the tags.</li>
 *  <li>Check the order, indentation and content of each tag.</li>
 *  <li>There is exactly one blank line after the file comment.</li>
 * </ul>
 *
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @author    Marc McIntyre <mmcintyre@squiz.net>
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2006-2012 Squiz Pty Ltd (ABN 77 084 670 600)
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 * @version   SVN: $Date: 2016-08-16 23:39:01 +0900 (Tue, 16 Aug 2016) $ $Revision: 26 $
 * @link      $HeadURL: https://open.logics.net.au/buildtools/codesniffer/tags/0.1.3/src/CodeSniffer/Standards/Squiz/Sniffs/Commenting/FileCommentSniff.php $
 *
 * @untranslatable PHP
 * @untranslatable JS
 */

class FileCommentSniff implements Sniff
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
	 * @return int
	 *
	 * @internalconst T_DOC_COMMENT_OPEN_TAG T_DOC_COMMENT_OPEN_TAG token
	 * @internalconst T_DOC_COMMENT_STRING   T_DOC_COMMENT_STRING token
	 *
	 * @untranslatable WrongStyle
	 * @untranslatable Missing
	 * @untranslatable SpacingAfterOpen
	 * @untranslatable SpacingAfterComment
	 * @untranslatable %s
	 * @untranslatable Duplicate
	 * @untranslatable Tag
	 * @untranslatable Empty
	 * @untranslatable @author
	 * @untranslatable IncorrectAuthor
	 * @untranslatable IncorrectCopyright
	 * @untranslatable @copyright
	 * @untranslatable TagOrder
	 * @untranslatable \"xxxx-xxxx Squiz Pty Ltd (ABN 77 084 670 600)\"
	 * @untranslatable \"Squiz Pty Ltd <products@squiz.net>\"
	 * @untranslatable Squiz Pty Ltd <products@squiz.net>
	 */

	public function process(File $phpcsFile, $stackPtr)
	    {
		$this->currentFile = $phpcsFile;

		$tokens       = &$phpcsFile->tokens;
		$commentStart = $phpcsFile->findNext(T_WHITESPACE, ($stackPtr + 1), null, true);

		if ($tokens[$commentStart]["code"] === T_COMMENT)
		    {
			$phpcsFile->addError(_("You must use \"/**\" style comments for a file comment"), $commentStart, "WrongStyle");
			return ($phpcsFile->numTokens + 1);
		    }
		else if ($commentStart === false || $tokens[$commentStart]["code"] !== T_DOC_COMMENT_OPEN_TAG)
		    {
			$phpcsFile->addError(_("Missing file doc comment"), $stackPtr, "Missing");
			return ($phpcsFile->numTokens + 1);
		    }

		$commentEnd = $tokens[$commentStart]["comment_closer"];

		// No blank line between the open tag and the file comment.
		if ($tokens[$commentStart]["line"] > ($tokens[$stackPtr]["line"] + 1))
		    {
			$error = _("There must be no blank lines before the file comment");
			$phpcsFile->addError($error, $stackPtr, "SpacingAfterOpen");
		    }

		// Exactly one blank line after the file comment.
		$next = $phpcsFile->findNext(T_WHITESPACE, ($commentEnd + 1), null, true);
		if ($tokens[$next]["line"] !== ($tokens[$commentEnd]["line"] + 2))
		    {
			$error = _("There must be exactly one blank line after the file comment");
			$phpcsFile->addError($error, $commentEnd, "SpacingAfterComment");
		    }

		// Required tags in correct order.
		$required = array(
			     "@package"    => true,
			     "@subpackage" => true,
			     "@author"     => true,
			     "@copyright"  => true,
			    );

		$foundTags = array();
		foreach ($tokens[$commentStart]["comment_tags"] as $tag)
		    {
			$name       = $tokens[$tag]["content"];
			$isRequired = isset($required[$name]);

			if ($isRequired === true && in_array($name, $foundTags) === true)
			    {
				$error = _("Only one ") . "%s" . _(" tag is allowed in a file comment");
				$data  = array($name);
				$phpcsFile->addError($error, $tag, "Duplicate" . ucfirst(substr($name, 1)) . "Tag", $data);
			    }

			$foundTags[] = $name;

			if ($isRequired === false)
			    {
				continue;
			    }

			$string = $phpcsFile->findNext(T_DOC_COMMENT_STRING, $tag, $commentEnd);
			if ($string === false || $tokens[$string]["line"] !== $tokens[$tag]["line"])
			    {
				$error = _("Content missing for ") . "%s" . _(" tag in file comment");
				$data  = array($name);
				$phpcsFile->addError($error, $tag, "Empty" . ucfirst(substr($name, 1)) . "Tag", $data);
				continue;
			    }

			if ($name === "@author")
			    {
				if ($tokens[$string]["content"] !== "Squiz Pty Ltd <products@squiz.net>")
				    {
					$error = _("Expected ") . "\"Squiz Pty Ltd <products@squiz.net>\"" . _(" for author tag");
					$phpcsFile->addError($error, $tag, "IncorrectAuthor");
				    }
			    }
			else if ($name === "@copyright")
			    {
				if (preg_match("/^([0-9]{4})(-[0-9]{4})? (Squiz Pty Ltd \(ABN 77 084 670 600\))$/", $tokens[$string]["content"]) === 0)
				    {
					$error = _("Expected ") . "\"xxxx-xxxx Squiz Pty Ltd (ABN 77 084 670 600)\"" . _(" for copyright declaration");
					$phpcsFile->addError($error, $tag, "IncorrectCopyright");
				    }
			    } //end if
		    } //end foreach

		// Check if the tags are in the correct position.
		$pos = 0;
		foreach ($required as $tag => $true)
		    {
			if (in_array($tag, $foundTags) === false)
			    {
				$error = _("Missing ") . "%s" . _(" tag in file comment");
				$data  = array($tag);
				$phpcsFile->addError($error, $commentEnd, "Missing" . ucfirst(substr($tag, 1)) . "Tag", $data);
			    }

			if (isset($foundTags[$pos]) === false)
			    {
				break;
			    }

			if ($foundTags[$pos] !== $tag)
			    {
				$error = _("The tag in position ") . "%s" . _(" should be the ") . "%s" . _(" tag");
				$data  = array(
					  ($pos + 1),
					  $tag,
					 );
				$phpcsFile->addError($error, $tokens[$commentStart]["comment_tags"][$pos], ucfirst(substr($tag, 1)) . "TagOrder", $data);
			    }

			$pos++;
		    } //end foreach

		// Ignore the rest of the file.
		return ($phpcsFile->numTokens + 1);
	    } //end process()


    } //end class

?>
