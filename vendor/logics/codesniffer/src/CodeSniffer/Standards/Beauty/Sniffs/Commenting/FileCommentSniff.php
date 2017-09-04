<?php

/**
 * PHP version 5.6
 *
 * @package Logics\BuildTools\CodeSniffer\Beauty
 */

namespace Logics\BuildTools\CodeSniffer\Beauty;

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
 * @version   SVN: $Date: 2016-09-19 20:10:11 +0900 (Mon, 19 Sep 2016) $ $Revision: 44 $
 * @link      $HeadURL: https://open.logics.net.au/buildtools/codesniffer/tags/0.1.3/src/CodeSniffer/Standards/Beauty/Sniffs/Commenting/FileCommentSniff.php $
 */

class FileCommentSniff implements Sniff
    {

	/**
	 * Tags in correct order and related info.
	 *
	 * @var array
	 */
	protected $tags = array(
			   "@package"        => array(
						 "required"       => true,
						 "allow_multiple" => false,
						),
			   "@author"         => array(
						 "required"       => false,
						 "allow_multiple" => true,
						),
			   "@copyright"      => array(
						 "required"       => false,
						 "allow_multiple" => true,
						),
			   "@license"        => array(
						 "required"       => false,
						 "allow_multiple" => false,
						),
			   "@version"        => array(
						 "required"       => false,
						 "allow_multiple" => false,
						),
			   "@link"           => array(
						 "required"       => false,
						 "allow_multiple" => true,
						),
			   "@see"            => array(
						 "required"       => false,
						 "allow_multiple" => true,
						),
			   "@since"          => array(
						 "required"       => false,
						 "allow_multiple" => false,
						),
			   "@deprecated"     => array(
						 "required"       => false,
						 "allow_multiple" => false,
						),
			   "@exceptioncode"  => array(
						 "required"       => false,
						 "allow_multiple" => true,
						),
			   "@optionalconst"  => array(
						 "required"       => false,
						 "allow_multiple" => true,
						),
			   "@donottranslate" => array(
						 "required"       => false,
						 "allow_multiple" => false,
						 "allow_empty"    => true,
						),
			   "@untranslatable" => array(
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
	 * @untranslatable file
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
		    }

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
			$error = _("You must use \"/**\" style comments for a file comment");
			$phpcsFile->addError($error, $errorToken, "WrongStyle");
			return ($phpcsFile->numTokens + 1);
		    }
		else if ($commentStart === false || $tokens[$commentStart]["code"] !== T_DOC_COMMENT_OPEN_TAG)
		    {
			$phpcsFile->addError(_("Missing file doc comment"), $errorToken, "Missing");
			return ($phpcsFile->numTokens + 1);
		    }
		else
		    {
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
			    }

			if ($found === false)
			    {
				$error = _("PHP version not specified");
				$phpcsFile->addWarning($error, $commentEnd, "MissingVersion");
			    }

			// Check each tag.
			$this->processTags($phpcsFile, $commentStart, "file");

			// Ignore the rest of the file.
			return ($phpcsFile->numTokens + 1);
		    } //end if
	    } //end process()


	/**
	 * Processes each required or optional tag.
	 *
	 * @param File   $phpcsFile    The file being scanned.
	 * @param int    $commentStart Position in the stack where the comment started.
	 * @param string $docBlock     Comment type currently parsed
	 *
	 * @return void
	 *
	 * @internalconst T_DOC_COMMENT_STRING T_DOC_COMMENT_STRING token
	 *
	 * @untranslatable Duplicate
	 * @untranslatable Empty
	 * @untranslatable Missing
	 * @untranslatable Tag
	 * @untranslatable TagNotAllowed
	 * @untranslatable process
	 * @untranslatable %s
	 * @untranslatable TagOrder
	 */

	protected function processTags(File &$phpcsFile, $commentStart, $docBlock)
	    {
		$tokens = &$phpcsFile->tokens;

		$commentEnd = $tokens[$commentStart]["comment_closer"];

		$foundTags = array();
		$tagTokens = array();
		foreach ($tokens[$commentStart]["comment_tags"] as $tag)
		    {
			$name = $tokens[$tag]["content"];
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
					$string = $phpcsFile->findNext(T_DOC_COMMENT_STRING, $tag, $commentEnd);

					if ($string === false || $tokens[$string]["line"] !== $tokens[$tag]["line"])
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
			else
			    {
				// Unknown tags are not parsed, do not process further.
				$error = "%s " . _("tag is not allowed in") . " " . $docBlock . " " . _("comment");
				$data  = array($name);
				$phpcsFile->addWarning($error, $tag, "TagNotAllowed", $data);
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
				$method = "process" . substr($tag, 1);
				if (method_exists($this, $method) === true)
				    {
					// Process each tag if a method is defined.
					call_user_func(array($this, $method), $phpcsFile, $tagTokens[$tag]);
				    }

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
			if ($tokens[($tag + 2)]["code"] === T_DOC_COMMENT_STRING)
			    {
				$content = $tokens[($tag + 2)]["content"];
				if (CodeSniffer::isUnderscoreName($content) === false)
				    {
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
					    }

					$error     = _("Package name") . " \"%s\" " . _("is not valid; consider") . " \"%s\" " . _("instead");
					$validName = trim($newName, "_");
					$data      = array(
						      $content,
						      $validName,
						     );
					$phpcsFile->addError($error, $tag, "InvalidPackage", $data);
				    } //end if
			    } //end if
		    } //end foreach
	    } //end processPackage()


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
	 * @untranslatable InvalidAuthors
	 */

	protected function processAuthor(File &$phpcsFile, array $tags)
	    {
		$tokens = &$phpcsFile->tokens;
		foreach ($tags as $tag)
		    {
			if ($tokens[($tag + 2)]["code"] === T_DOC_COMMENT_STRING)
			    {
				$content = $tokens[($tag + 2)]["content"];
				$local   = "\da-zA-Z-_+";
				// Dot character cannot be the first or last character in the local-part.
				$localMiddle = $local . ".\w";
				if (preg_match("/^([^<]*)\s+<([" . $local . "]([" . $localMiddle . "]*[" . $local .
					       "])*@[\da-zA-Z][-.\w]*[\da-zA-Z]\.[a-zA-Z]{2,7})>$/", $content) === 0)
				    {
					$error = _("Content of the @author tag must be in the form \"Display Name <username@example.com>\"");
					$phpcsFile->addError($error, $tag, "InvalidAuthors");
				    }
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
	 * @untranslatable IncompleteCopyright
	 */

	protected function processCopyright(File &$phpcsFile, array $tags)
	    {
		$tokens = &$phpcsFile->tokens;
		foreach ($tags as $tag)
		    {
			if ($tokens[($tag + 2)]["code"] === T_DOC_COMMENT_STRING)
			    {
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
							$error = _("Invalid year span") . " \"" . $matches[1] . $matches[3] . $matches[4] . "\" " .
								 _("found; consider") . " \"" . $matches[4] . "-" . $matches[1] . "\" " . _("instead");
							$phpcsFile->addWarning($error, $tag, "InvalidCopyright");
						    }
					    }
				    }
				else
				    {
					$error = _("@copyright tag must contain a year and the name of the copyright holder");
					$phpcsFile->addError($error, $tag, "IncompleteCopyright");
				    } //end if
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
	 * @untranslatable IncompleteLicense
	 */

	protected function processLicense(File &$phpcsFile, array $tags)
	    {
		$tokens = &$phpcsFile->tokens;
		foreach ($tags as $tag)
		    {
			if ($tokens[($tag + 2)]["code"] === T_DOC_COMMENT_STRING)
			    {
				$content = $tokens[($tag + 2)]["content"];
				$matches = array();
				preg_match("/^([^\s]+)\s+(.*)/", $content, $matches);
				if (count($matches) !== 3)
				    {
					$error = _("@license tag must contain a URL and a license name");
					$phpcsFile->addError($error, $tag, "IncompleteLicense");
				    }
			    }
		    }
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
	 * @untranslatable \"%s\"
	 * @untranslatable \"CVS: <cvs_id>\"
	 * @untranslatable \"SVN: <svn_id>\"
	 * @untranslatable \"GIT: <git_id>\"
	 * @untranslatable \"HG: <hg_id>\"
	 * @untranslatable SVN
	 * @untranslatable PHP_CODESNIFFER_SVNLOOK
	 * @untranslatable svn:keywords \"Date Revision\"
	 * @untranslatable InvalidVersion
	 */

	protected function processVersion(File &$phpcsFile, array $tags)
	    {
		$tokens = &$phpcsFile->tokens;
		foreach ($tags as $tag)
		    {
			if ($tokens[($tag + 2)]["code"] === T_DOC_COMMENT_STRING)
			    {
				$content = $tokens[($tag + 2)]["content"];
				if (preg_match("/^(?P<system>CVS|SVN|GIT|HG):\s(?P<id>.*)/", $content, $m) === 0)
				    {
					$error = _("Invalid version") . " \"%s\" " . _("in file comment; consider") . " \"CVS: <cvs_id>\" " . _("or") .
						 " \"SVN: <svn_id>\" " . _("or") . " \"GIT: <git_id>\" " . _("or") . " \"HG: <hg_id>\" " . _("instead");
					$data  = array($content);
					$phpcsFile->addWarning($error, $tag, "InvalidVersion", $data);
				    }
				else
				    {
					if ($m["system"] === "SVN")
					    {
						if (preg_match('/\$' . 'Date: \d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2} \+\d{4} \(.*\) \$ \$' . 'Revision: \d+ \$/', $m["id"]) === 0 &&
						    defined("PHP_CODESNIFFER_SVNLOOK") === false)
						    {
							$error = _("SVN property") . " svn:keywords \"Date Revision\" " . _("is not set");
							$data  = array($content);
							$phpcsFile->addWarning($error, $tag, "InvalidVersion", $data);
						    }
					    }
				    }
			    } //end if
		    } //end foreach
	    } //end processVersion()


    } //end class

?>