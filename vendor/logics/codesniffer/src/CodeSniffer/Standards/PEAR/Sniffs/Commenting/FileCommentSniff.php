<?php

/**
 * PHP version 5
 *
 * @package Logics\BuildTools\CodeSniffer\PEAR
 */

namespace Logics\BuildTools\CodeSniffer\PEAR;

use \Logics\BuildTools\CodeSniffer\CodeSniffer;
use \Logics\BuildTools\CodeSniffer\File;
use \Logics\BuildTools\CodeSniffer\Sniff;

/**
 * Parses and verifies the doc comments for files.
 *
 * Verifies that :
 * <ul>
 *  <li>A doc comment exists.</li>
 *  <li>There is a blank newline after the short description.</li>
 *  <li>There is a blank newline between the long and short description.</li>
 *  <li>There is a blank newline between the long description and tags.</li>
 *  <li>A PHP version is specified.</li>
 *  <li>Check the order of the tags.</li>
 *  <li>Check the indentation of each tag.</li>
 *  <li>Check required and optional tags and the format of their content.</li>
 * </ul>
 *
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @author    Marc McIntyre <mmcintyre@squiz.net>
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2006-2012 Squiz Pty Ltd (ABN 77 084 670 600)
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 * @version   SVN: $Date: 2016-08-16 23:39:01 +0900 (Tue, 16 Aug 2016) $ $Revision: 26 $
 * @link      $HeadURL: https://open.logics.net.au/buildtools/codesniffer/tags/0.1.3/src/CodeSniffer/Standards/PEAR/Sniffs/Commenting/FileCommentSniff.php $
 */

class FileCommentSniff implements Sniff
    {

	/**
	 * Tags in correct order and related info.
	 *
	 * @var array
	 */
	protected $tags = array(
			   "@category"   => array(
					     "required"       => true,
					     "allow_multiple" => false,
					    ),
			   "@package"    => array(
					     "required"       => true,
					     "allow_multiple" => false,
					    ),
			   "@subpackage" => array(
					     "required"       => false,
					     "allow_multiple" => false,
					    ),
			   "@author"     => array(
					     "required"       => true,
					     "allow_multiple" => true,
					    ),
			   "@copyright"  => array(
					     "required"       => false,
					     "allow_multiple" => true,
					    ),
			   "@license"    => array(
					     "required"       => true,
					     "allow_multiple" => false,
					    ),
			   "@version"    => array(
					     "required"       => false,
					     "allow_multiple" => false,
					    ),
			   "@link"       => array(
					     "required"       => true,
					     "allow_multiple" => true,
					    ),
			   "@see"        => array(
					     "required"       => false,
					     "allow_multiple" => true,
					    ),
			   "@since"      => array(
					     "required"       => false,
					     "allow_multiple" => false,
					    ),
			   "@deprecated" => array(
					     "required"       => false,
					     "allow_multiple" => false,
					    ),
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
	 * @internalconst T_SEMICOLON            T_SEMICOLON token
	 * @internalconst T_DOC_COMMENT_OPEN_TAG T_DOC_COMMENT_OPEN_TAG token
	 * @internalconst T_DOC_COMMENT_TAG      T_DOC_COMMENT_TAG token
	 * @internalconst T_DOC_COMMENT_STRING   T_DOC_COMMENT_STRING token
	 *
	 * @untranslatable vim:
	 * @untranslatable WrongStyle
	 * @untranslatable Missing
	 * @untranslatable php version
	 * @untranslatable MissingVersion
	 */

	public function process(File $phpcsFile, $stackPtr)
	    {
		$tokens = &$phpcsFile->tokens;

		// Find the next non whitespace token.
		$commentStart = $phpcsFile->findNext(T_WHITESPACE, ($stackPtr + 1), null, true);

		// Allow declare() statements at the top of the file.
		if ($tokens[$commentStart]["code"] === T_DECLARE)
		    {
			$semicolon    = $phpcsFile->findNext(T_SEMICOLON, ($commentStart + 1));
			$commentStart = $phpcsFile->findNext(T_WHITESPACE, ($semicolon + 1), null, true);
		    }

		// Ignore vim header.
		if ($tokens[$commentStart]["code"] === T_COMMENT)
		    {
			if (strstr($tokens[$commentStart]["content"], "vim:") !== false)
			    {
				$commentStart = $phpcsFile->findNext(T_WHITESPACE, ($commentStart + 1), null, true);
			    }
		    } //end if

		$errorToken = ($stackPtr + 1);
		if (isset($tokens[$errorToken]) === false)
		    {
			$errorToken--;
		    }

		if ($tokens[$commentStart]["code"] === T_CLOSE_TAG)
		    {
			// We are only interested if this is the first open tag.
			return ($phpcsFile->numTokens + 1);
		    }
		else if ($tokens[$commentStart]["code"] === T_COMMENT)
		    {
			$error = _("You must use ") . "\"/**\"" . _(" style comments for a file comment");
			$phpcsFile->addError($error, $errorToken, "WrongStyle");
			return ($phpcsFile->numTokens + 1);
		    }
		else if ($commentStart === false || $tokens[$commentStart]["code"] !== T_DOC_COMMENT_OPEN_TAG)
		    {
			$phpcsFile->addError(_("Missing file doc comment"), $errorToken, "Missing");
			return ($phpcsFile->numTokens + 1);
		    }

		// Check the PHP Version, which should be in some text before the first tag.
		$commentEnd = $tokens[$commentStart]["comment_closer"];
		$found      = false;
		for ($i = ($commentStart + 1); $i < $commentEnd; $i++)
		    {
			if ($tokens[$i]["code"] === T_DOC_COMMENT_TAG)
			    {
				break;
			    }
			else if ($tokens[$i]["code"] === T_DOC_COMMENT_STRING && strstr(strtolower($tokens[$i]["content"]), "php version") !== false)
			    {
				$found = true;
				break;
			    }
		    } //end for

		if ($found === false)
		    {
			$error = _("PHP version not specified");
			$phpcsFile->addWarning($error, $commentEnd, "MissingVersion");
		    }

		// Check each tag.
		$this->processTags($phpcsFile, $stackPtr, $commentStart);

		// Ignore the rest of the file.
		return ($phpcsFile->numTokens + 1);
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
	 * @untranslatable \\Logics\\BuildTools\\CodeSniffer\\PEAR\\FileCommentSniff
	 * @untranslatable Empty
	 * @untranslatable Tag
	 * @untranslatable %s
	 * @untranslatable Missing
	 * @untranslatable TagOrder
	 * @untranslatable process
	 * @untranslatable file
	 * @untranslatable class
	 * @untranslatable Duplicate
	 */

	protected function processTags(File &$phpcsFile, $stackPtr, $commentStart)
	    {
		unset($stackPtr);

		$tokens = &$phpcsFile->tokens;

		if (get_class($this) === "\\Logics\\BuildTools\\CodeSniffer\\PEAR\\FileCommentSniff")
		    {
			$docBlock = "file";
		    }
		else
		    {
			$docBlock = "class";
		    }

		$commentEnd = $tokens[$commentStart]["comment_closer"];

		$foundTags = array();
		$tagTokens = array();
		foreach ($tokens[$commentStart]["comment_tags"] as $tag)
		    {
			$name = $tokens[$tag]["content"];
			if (isset($this->tags[$name]) === false)
			    {
				continue;
			    }

			if ($this->tags[$name]["allow_multiple"] === false && isset($tagTokens[$name]) === true)
			    {
				$error = _("Only one ") . "%s" . _(" tag is allowed in a ") . "%s" . _(" comment");
				$data  = array(
					  $name,
					  $docBlock,
					 );
				$phpcsFile->addError($error, $tag, "Duplicate" . ucfirst(substr($name, 1)) . "Tag", $data);
			    }

			$foundTags[]        = $name;
			$tagTokens[$name][] = $tag;

			$string = $phpcsFile->findNext(T_DOC_COMMENT_STRING, $tag, $commentEnd);
			if ($string === false || $tokens[$string]["line"] !== $tokens[$tag]["line"])
			    {
				$error = _("Content missing for ") . "%s" . _(" tag in ") . "%s" . _(" comment");
				$data  = array(
					  $name,
					  $docBlock,
					 );
				$phpcsFile->addError($error, $tag, "Empty" . ucfirst(substr($name, 1)) . "Tag", $data);
				continue;
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
					$error = _("Missing ") . "%s" . _(" tag in ") . "%s" . _(" comment");
					$data  = array(
						  $tag,
						  $docBlock,
						 );
					$phpcsFile->addError($error, $commentEnd, "Missing" . ucfirst(substr($tag, 1)) . "Tag", $data);
				    }

				continue;
			    }
			else
			    {
				$method = "process" . substr($tag, 1);
				if (method_exists($this, $method) === true)
				    {
					// Process each tag if a method is defined.
					call_user_func(array($this, $method), $phpcsFile, $tagTokens[$tag]);
				    }
			    } //end if

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

			// Account for multiple tags.
			$pos++;
			while (isset($foundTags[$pos]) === true && $foundTags[$pos] === $tag)
			    {
				$pos++;
			    }
		    } //end foreach
	    } //end processTags()


	/**
	 * Process the category tag.
	 *
	 * @param File  $phpcsFile The file being scanned.
	 * @param array $tags      The tokens for these tags.
	 *
	 * @return void
	 *
	 * @internalconst T_DOC_COMMENT_STRING T_DOC_COMMENT_STRING token
	 *
	 * @untranslatable \"%s\"
	 * @untranslatable InvalidCategory
	 */

	protected function processCategory(File &$phpcsFile, array $tags)
	    {
		$tokens = &$phpcsFile->tokens;
		foreach ($tags as $tag)
		    {
			if ($tokens[($tag + 2)]["code"] !== T_DOC_COMMENT_STRING)
			    {
				// No content.
				continue;
			    }

			$content = $tokens[($tag + 2)]["content"];
			if (CodeSniffer::isUnderscoreName($content) !== true)
			    {
				$newContent = str_replace(" ", "_", $content);
				$nameBits   = explode("_", $newContent);
				$firstBit   = array_shift($nameBits);
				$newName    = ucfirst($firstBit) . "_";
				foreach ($nameBits as $bit)
				    {
					if ($bit !== "")
					    {
						$newName .= ucfirst($bit) . "_";
					    }
				    }

				$error     = _("Category name ") . "\"%s\"" . _(" is not valid; consider ") . "\"%s\"" . _(" instead");
				$validName = trim($newName, "_");
				$data      = array(
					      $content,
					      $validName,
					     );
				$phpcsFile->addError($error, $tag, "InvalidCategory", $data);
			    } //end if
		    } //end foreach
	    } //end processCategory()


	/**
	 * Process the package tag.
	 *
	 * @param File  $phpcsFile The file being scanned.
	 * @param array $tags      The tokens for these tags.
	 *
	 * @return void
	 *
	 * @internalconst T_DOC_COMMENT_STRING T_DOC_COMMENT_STRING token
	 *
	 * @untranslatable \"%s\"
	 * @untranslatable InvalidPackage
	 */

	protected function processPackage(File &$phpcsFile, array $tags)
	    {
		$tokens = &$phpcsFile->tokens;
		foreach ($tags as $tag)
		    {
			if ($tokens[($tag + 2)]["code"] !== T_DOC_COMMENT_STRING)
			    {
				// No content.
				continue;
			    }

			$content = $tokens[($tag + 2)]["content"];
			if (CodeSniffer::isUnderscoreName($content) === true)
			    {
				continue;
			    }

			$newContent = str_replace(" ", "_", $content);
			$newContent = trim($newContent, "_");
			$newContent = preg_replace("/[^A-Za-z_]/", "", $newContent);
			$nameBits   = explode("_", $newContent);
			$firstBit   = array_shift($nameBits);
			$newName    = strtoupper($firstBit{0}) . substr($firstBit, 1) . "_";
			foreach ($nameBits as $bit)
			    {
				if ($bit !== "")
				    {
					$newName .= strtoupper($bit{0}) . substr($bit, 1) . "_";
				    }
			    } //end foreach

			$error     = _("Package name ") . "\"%s\"" . _(" is not valid; consider ") . "\"%s\"" . _(" instead");
			$validName = trim($newName, "_");
			$data      = array(
				      $content,
				      $validName,
				     );
			$phpcsFile->addError($error, $tag, "InvalidPackage", $data);
		    } //end foreach
	    } //end processPackage()


	/**
	 * Process the subpackage tag.
	 *
	 * @param File  $phpcsFile The file being scanned.
	 * @param array $tags      The tokens for these tags.
	 *
	 * @return void
	 *
	 * @internalconst T_DOC_COMMENT_STRING T_DOC_COMMENT_STRING token
	 *
	 * @untranslatable \"%s\"
	 * @untranslatable InvalidSubpackage
	 */

	protected function processSubpackage(File &$phpcsFile, array $tags)
	    {
		$tokens = &$phpcsFile->tokens;
		foreach ($tags as $tag)
		    {
			if ($tokens[($tag + 2)]["code"] !== T_DOC_COMMENT_STRING)
			    {
				// No content.
				continue;
			    }

			$content = $tokens[($tag + 2)]["content"];
			if (CodeSniffer::isUnderscoreName($content) === true)
			    {
				continue;
			    }

			$newContent = str_replace(" ", "_", $content);
			$nameBits   = explode("_", $newContent);
			$firstBit   = array_shift($nameBits);
			$newName    = strtoupper($firstBit{0}) . substr($firstBit, 1) . "_";
			foreach ($nameBits as $bit)
			    {
				if ($bit !== "")
				    {
					$newName .= strtoupper($bit{0}) . substr($bit, 1) . "_";
				    }
			    } //end foreach

			$error     = _("Subpackage name ") . "\"%s\"" . _(" is not valid; consider ") . "\"%s\"" . _(" instead");
			$validName = trim($newName, "_");
			$data      = array(
				      $content,
				      $validName,
				     );
			$phpcsFile->addError($error, $tag, "InvalidSubpackage", $data);
		    } //end foreach
	    } //end processSubpackage()


	/**
	 * Process the author tag(s) that this header comment has.
	 *
	 * @param File  $phpcsFile The file being scanned.
	 * @param array $tags      The tokens for these tags.
	 *
	 * @return void
	 *
	 * @internalconst T_DOC_COMMENT_STRING T_DOC_COMMENT_STRING token
	 *
	 * @untranslatable \da-zA-Z-_+
	 * @untranslatable .\w
	 * @untranslatable @author
	 * @untranslatable <username@example.com>
	 * @untranslatable InvalidAuthors
	 */

	protected function processAuthor(File &$phpcsFile, array $tags)
	    {
		$tokens = &$phpcsFile->tokens;
		foreach ($tags as $tag)
		    {
			if ($tokens[($tag + 2)]["code"] !== T_DOC_COMMENT_STRING)
			    {
				// No content.
				continue;
			    }

			$content = $tokens[($tag + 2)]["content"];
			$local   = "\da-zA-Z-_+";
			// Dot character cannot be the first or last character in the local-part.
			$localMiddle = $local . ".\w";
			if (preg_match(
			    "/^([^<]*)\s+<([" . $local . "]([" . $localMiddle . "]*[" . $local . "])*@[\da-zA-Z][-.\w]*[\da-zA-Z]\.[a-zA-Z]{2,7})>$/", $content) === 0
			)
			    {
				$error = _("Content of the ") . "@author" . _(" tag must be in the form \"Display Name ") . "<username@example.com>" . "\"";
				$phpcsFile->addError($error, $tag, "InvalidAuthors");
			    }
		    }
	    } //end processAuthor()


	/**
	 * Process the copyright tags.
	 *
	 * @param File  $phpcsFile The file being scanned.
	 * @param array $tags      The tokens for these tags.
	 *
	 * @return void
	 *
	 * @internalconst T_DOC_COMMENT_STRING T_DOC_COMMENT_STRING token
	 *
	 * @untranslatable CopyrightHyphen
	 * @untranslatable InvalidCopyright
	 * @untranslatable @copyright
	 * @untranslatable IncompleteCopyright
	 */

	protected function processCopyright(File &$phpcsFile, array $tags)
	    {
		$tokens = &$phpcsFile->tokens;
		foreach ($tags as $tag)
		    {
			if ($tokens[($tag + 2)]["code"] !== T_DOC_COMMENT_STRING)
			    {
				// No content.
				continue;
			    }

			$content = $tokens[($tag + 2)]["content"];
			$matches = array();
			if (preg_match("/^([0-9]{4})((.{1})([0-9]{4}))? (.+)$/", $content, $matches) !== 0)
			    {
				// Check earliest-latest year order.
				if ($matches[3] !== "")
				    {
					if ($matches[3] !== "-")
					    {
						$error = _("A hyphen must be used between the earliest and latest year");
						$phpcsFile->addError($error, $tag, "CopyrightHyphen");
					    }

					if ($matches[4] !== "" && $matches[4] < $matches[1])
					    {
						$error = _("Invalid year span ") . "\"" . $matches[1] . $matches[3] . $matches[4] . "\"" .
						    _(" found; consider ") . "\"" . $matches[4] . "-" . $matches[1] . "\"" . _(" instead");
						$phpcsFile->addWarning($error, $tag, "InvalidCopyright");
					    }
				    } //end if
			    }
			else
			    {
				$error = "@copyright" . _(" tag must contain a year and the name of the copyright holder");
				$phpcsFile->addError($error, $tag, "IncompleteCopyright");
			    } //end if
		    } //end foreach
	    } //end processCopyright()


	/**
	 * Process the license tag.
	 *
	 * @param File  $phpcsFile The file being scanned.
	 * @param array $tags      The tokens for these tags.
	 *
	 * @return void
	 *
	 * @internalconst T_DOC_COMMENT_STRING T_DOC_COMMENT_STRING token
	 *
	 * @untranslatable @license
	 * @untranslatable IncompleteLicense
	 */

	protected function processLicense(File &$phpcsFile, array $tags)
	    {
		$tokens = &$phpcsFile->tokens;
		foreach ($tags as $tag)
		    {
			if ($tokens[($tag + 2)]["code"] !== T_DOC_COMMENT_STRING)
			    {
				// No content.
				continue;
			    }

			$content = $tokens[($tag + 2)]["content"];
			$matches = array();
			preg_match("/^([^\s]+)\s+(.*)/", $content, $matches);
			if (count($matches) !== 3)
			    {
				$error = "@license" . _(" tag must contain a URL and a license name");
				$phpcsFile->addError($error, $tag, "IncompleteLicense");
			    }
		    } //end foreach
	    } //end processLicense()


	/**
	 * Process the version tag.
	 *
	 * @param File  $phpcsFile The file being scanned.
	 * @param array $tags      The tokens for these tags.
	 *
	 * @return void
	 *
	 * @internalconst T_DOC_COMMENT_STRING T_DOC_COMMENT_STRING token
	 *
	 * @untranslatable CVS:
	 * @untranslatable SVN:
	 * @untranslatable GIT:
	 * @untranslatable HG:
	 * @untranslatable \"%s\"
	 * @untranslatable \"CVS: <cvs_id>\"
	 * @untranslatable \"SVN: <svn_id>\"
	 * @untranslatable \"GIT: <git_id>\"
	 * @untranslatable \"HG: <hg_id>\"
	 * @untranslatable InvalidVersion
	 */

	protected function processVersion(File &$phpcsFile, array $tags)
	    {
		$tokens = &$phpcsFile->tokens;
		foreach ($tags as $tag)
		    {
			if ($tokens[($tag + 2)]["code"] !== T_DOC_COMMENT_STRING)
			    {
				// No content.
				continue;
			    }

			$content = $tokens[($tag + 2)]["content"];
			if (strstr($content, "CVS:") === false && strstr($content, "SVN:") === false
			&& strstr($content, "GIT:") === false && strstr($content, "HG:") === false)
			    {
				$error = _("Invalid version ") . "\"%s\"" . _(" in file comment; consider ") . "\"CVS: <cvs_id>\"" . _(" or ") .
				    "\"SVN: <svn_id>\"" . _(" or ") . "\"GIT: <git_id>\"" . _(" or ") . "\"HG: <hg_id>\"" . _(" instead");
				$data  = array($content);
				$phpcsFile->addWarning($error, $tag, "InvalidVersion", $data);
			    }
		    } //end foreach
	    } //end processVersion()


    } //end class

?>
