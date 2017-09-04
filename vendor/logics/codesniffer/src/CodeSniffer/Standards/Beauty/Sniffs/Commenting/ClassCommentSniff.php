<?php

/**
 * PHP version 5.6
 *
 * @package Logics\BuildTools\CodeSniffer\Beauty
 */

namespace Logics\BuildTools\CodeSniffer\Beauty;

use \Logics\BuildTools\CodeSniffer\File;
use \Logics\BuildTools\CodeSniffer\Tokens;

/**
 * Parses and verifies the class doc comment.
 *
 * Verifies that :
 * <ul>
 *  <li>A class doc comment exists.</li>
 *  <li>There is exactly one blank line before the class comment.</li>
 *  <li>Short description ends with a full stop.</li>
 *  <li>There is a blank line after the short description.</li>
 *  <li>Each paragraph of the long description ends with a full stop.</li>
 *  <li>There is a blank line between the description and the tags.</li>
 *  <li>Check the format of the since tag (x.x.x).</li>
 * </ul>
 *
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @author    Marc McIntyre <mmcintyre@squiz.net>
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2006-2012 Squiz Pty Ltd (ABN 77 084 670 600)
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 * @version   SVN: $Date: 2016-10-31 16:39:17 +0800 (Mon, 31 Oct 2016) $ $Revision: 49 $
 * @link      $HeadURL: https://open.logics.net.au/buildtools/codesniffer/tags/0.1.3/src/CodeSniffer/Standards/Beauty/Sniffs/Commenting/ClassCommentSniff.php $
 */

class ClassCommentSniff extends FileCommentSniff
    {

	/**
	 * Tags in correct order and related info.
	 *
	 * @var array
	 */
	protected $tags = array(
			   "@author"                      => array(
							      "required"       => true,
							      "allow_multiple" => true,
							     ),
			   "@copyright"                   => array(
							      "required"       => true,
							      "allow_multiple" => true,
							     ),
			   "@license"                     => array(
							      "required"       => true,
							      "allow_multiple" => false,
							     ),
			   "@version"                     => array(
							      "required"       => true,
							      "allow_multiple" => false,
							     ),
			   "@link"                        => array(
							      "required"       => true,
							      "allow_multiple" => true,
							     ),
			   "@webserverconfig"             => array(
							      "required"       => false,
							      "allow_multiple" => true,
							     ),
			   "@requiredcommand"             => array(
							      "required"       => false,
							      "allow_multiple" => true,
							     ),
			   "@optionalconst"               => array(
							      "required"       => false,
							      "allow_multiple" => true,
							     ),
			   "@codeCoverageIgnore"          => array(
							      "required"       => false,
							      "allow_multiple" => false,
							      "allow_empty"    => true,
							     ),
			   "@runTestsInSeparateProcesses" => array(
							      "required"       => false,
							      "allow_multiple" => false,
							      "allow_empty"    => true,
							     ),
			   "@soap-wsdl"                   => array(
							      "required"       => false,
							      "allow_multiple" => true,
							     ),
			   "@soap-indicator"              => array(
							      "required"       => false,
							      "allow_multiple" => false,
							     ),
			   "@untranslatable"              => array(
							      "required"       => false,
							      "allow_multiple" => true,
							     ),
			   "@donottranslate"              => array(
							      "required"       => false,
							      "allow_multiple" => false,
							      "allow_empty"    => true,
							     ),
			   "@defaultdaoimplementation"    => array(
							      "required"       => false,
							      "allow_multiple" => false,
							      "allow_empty"    => true,
							     ),
			   "@see"                         => array(
							      "required"       => false,
							      "allow_multiple" => true,
							     ),
			  );

	/**
	 * Returns an array of tokens this test wants to listen for.
	 *
	 * @return array
	 */

	public function register()
	    {
		return array(
			T_CLASS,
			T_TRAIT,
			T_INTERFACE,
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
	 * @internalconst T_DOC_COMMENT_CLOSE_TAG T_DOC_COMMENT_CLOSE_TAG token
	 *
	 * @untranslatable class
	 * @untranslatable trait
	 * @untranslatable interface
	 * @untranslatable Missing
	 * @untranslatable WrongStyle
	 * @untranslatable SpacingAfter
	 * @untranslatable SpacingBefore
	 */

	public function process(File $phpcsFile, $stackPtr)
	    {
		$tokens = &$phpcsFile->tokens;
		$find   = Tokens::$methodPrefixes;
		$find[] = T_WHITESPACE;

		$type = ($tokens[$stackPtr]["code"] === T_CLASS) ? "class" : (($tokens[$stackPtr]["code"] === T_TRAIT) ? "trait" : "interface");

		$commentEnd = $phpcsFile->findPrevious($find, ($stackPtr - 1), null, true);
		if ($tokens[$commentEnd]["code"] !== T_DOC_COMMENT_CLOSE_TAG && $tokens[$commentEnd]["code"] !== T_COMMENT)
		    {
			$phpcsFile->addError(_("Missing") . " " . $type . " " . _("doc comment"), $stackPtr, "Missing");
		    }
		else
		    {
			// Try and determine if this is a file comment instead of a class comment.
			// We assume that if this is the first comment after the open PHP tag, then
			// it is most likely a file comment instead of a class comment.
			if ($tokens[$commentEnd]["code"] === T_DOC_COMMENT_CLOSE_TAG)
			    {
				$start = ($tokens[$commentEnd]["comment_opener"] - 1);
			    }
			else
			    {
				$start = $phpcsFile->findPrevious(T_COMMENT, ($commentEnd - 1), null, true);
			    }

			$prev = $phpcsFile->findPrevious(T_WHITESPACE, $start, null, true);
			if ($tokens[$prev]["code"] === T_OPEN_TAG && $phpcsFile->findPrevious(T_OPEN_TAG, ($prev - 1)) === false)
			    {
				// This is a comment directly after the first open tag,
				// so probably a file comment.
				$phpcsFile->addError(_("Missing") . " " . $type . " " . _("doc comment"), $stackPtr, "Missing");
			    }
			else if ($tokens[$commentEnd]["code"] === T_COMMENT)
			    {
				$phpcsFile->addError(_("You must use") . " \"/**\" " . _("style comments for a") . " " . $type . " " . _("comment"), $stackPtr, "WrongStyle");
			    }
			else
			    {
				if ($tokens[$commentEnd]["line"] !== ($tokens[$stackPtr]["line"] - 2))
				    {
					$error = _("There must be one blank lines after the") . " " . $type . " " . _("comment");
					$phpcsFile->addError($error, $commentEnd, "SpacingAfter");
				    }

				$commentStart = $tokens[$commentEnd]["comment_opener"];
				if ($tokens[$prev]["line"] !== ($tokens[$commentStart]["line"] - 2))
				    {
					$error = _("There must be exactly one blank line before the") . " " . $type . " " . _("comment");
					$phpcsFile->addError($error, $commentStart, "SpacingBefore");
				    }

				$this->processTags($phpcsFile, $commentStart, $type);
			    } //end if
		    } //end if
	    } //end process()


    } //end class

?>