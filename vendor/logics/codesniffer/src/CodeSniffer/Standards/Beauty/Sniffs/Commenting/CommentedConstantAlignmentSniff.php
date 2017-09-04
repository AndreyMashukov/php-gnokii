<?php

/**
 * PHP version 5
 *
 * @package Logics\BuildTools\CodeSniffer\Beauty
 */

namespace Logics\BuildTools\CodeSniffer\Beauty;

use \Logics\BuildTools\CodeSniffer\File;
use \Logics\BuildTools\CodeSniffer\Sniff;
use \Logics\BuildTools\CodeSniffer\Tokens;

/**
 * CommentedConstantAlignmentSniff.
 *
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @author    Marc McIntyre <mmcintyre@squiz.net>
 * @author    Anastasia Bashkirtseva <anastasia@logics.net.au>
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2006-2012 Squiz Pty Ltd (ABN 77 084 670 600)
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 * @version   SVN: $Date: 2016-08-16 23:39:01 +0900 (Tue, 16 Aug 2016) $ $Revision: 26 $
 * @link      $HeadURL: https://open.logics.net.au/buildtools/codesniffer/tags/0.1.3/src/CodeSniffer/Standards/Beauty/Sniffs/Commenting/CommentedConstantAlignmentSniff.php $
 */

class CommentedConstantAlignmentSniff implements Sniff
    {

	/**
	 * Returns an array of tokens this test wants to listen for.
	 *
	 * @return array
	 */

	public function register()
	    {
		return array(
			T_FUNCTION,
			T_OPEN_TAG,
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
	 * @internalconst T_DOC_COMMENT_OPEN_TAG   T_DOC_COMMENT_OPEN_TAG token
	 * @internalconst T_DOC_COMMENT_CLOSE_TAG  T_DOC_COMMENT_CLOSE_TAG token
	 * @internalconst T_DOC_COMMENT_WHITESPACE T_DOC_COMMENT_WHITESPACE token
	 * @internalconst T_DOC_COMMENT_STRING     T_DOC_COMMENT_STRING token
	 *
	 * @untranslatable @optionalconst
	 * @untranslatable @requiredconst
	 * @untranslatable @internalconst
	 * @untranslatable @exceptioncode
	 */

	public function process(File $phpcsFile, $stackPtr)
	    {
		$checkedTags = array(
				"@requiredconst",
				"@optionalconst",
				"@internalconst",
				"@exceptioncode",
			       );
		$tokens      = &$phpcsFile->tokens;

		$excludedTokens = array_merge(Tokens::$methodPrefixes, array(
									T_WHITESPACE,
									T_STRING,
								       ));

		$tags = array();
		if ($tokens[$stackPtr]["code"] === T_OPEN_TAG)
		    {
			$commentStart = $phpcsFile->findNext($excludedTokens, ($stackPtr + 1), null, true);
			if ($tokens[$commentStart]["code"] === T_DOC_COMMENT_OPEN_TAG)
			    {
				$tags = $tokens[$commentStart]["comment_tags"];
			    }
		    }
		else
		    {
			$commentEnd = $phpcsFile->findPrevious($excludedTokens, ($stackPtr - 1), null, true);
			if ($commentEnd !== false && $tokens[$commentEnd]["code"] === T_DOC_COMMENT_CLOSE_TAG)
			    {
				$tags = $tokens[$tokens[$commentEnd]["comment_opener"]]["comment_tags"];
			    }
		    }

		$describedConstants = array(
				       "@requiredconst" => array(),
				       "@optionalconst" => array(),
				       "@internalconst" => array(),
				       "@exceptioncode" => array(),
				      );

		foreach ($tags as $tag)
		    {
			if (in_array($tokens[$tag]["content"], $checkedTags) === true)
			    {
				$comment = $phpcsFile->findNext(T_DOC_COMMENT_WHITESPACE, ($tag + 1), null, true);
				if ($tokens[$comment]["code"] === T_DOC_COMMENT_STRING)
				    {
					$describedConstants[$tokens[$tag]["content"]][] = array(
											   "tag"     => $tag,
											   "comment" => $comment,
											  );
				    }
				else
				    {
					$describedConstants[$tokens[$tag]["content"]][] = array("tag" => $tag);
				    }
			    } //end if
		    } //end foreach

		$tags = array(
			 "required"  => $this->errorUserConfig($describedConstants["@requiredconst"], $phpcsFile, "@requiredconst"),
			 "optional"  => $this->errorUserConfig($describedConstants["@optionalconst"], $phpcsFile, "@optionalconst"),
			 "internal"  => $this->errorInternalConst($describedConstants["@internalconst"], $phpcsFile),
			 "exception" => $this->errorExceptionCode($describedConstants["@exceptioncode"], $phpcsFile),
			);

		$this->errorUnusedConstants($tags, $phpcsFile, $stackPtr);
	    } //end process()


	/**
	 * Add @userconfig error if you have it
	 *
	 * @param array  $describedConstants Tokens number of tag and comment constants
	 * @param File   $phpcsFile          The file being scanned.
	 * @param string $tag                Tag name
	 *
	 * @return array
	 *
	 * @untranslatable CommentedConstantAlignment
	 * @untranslatable /^(?P<name>\S*)(?P<exampleWhitespace>\s+)(?P<example>\".*\")(?P<commentWhitespace>\s+)(?P<comment>.*)/i
	 */

	public function errorUserConfig(array $describedConstants, File $phpcsFile, $tag)
	    {
		$tokens   = &$phpcsFile->tokens;
		$tagsName = array();
		$pattern  = "/^(?P<name>\S*)(?P<exampleWhitespace>\s+)(?P<example>\".*\")(?P<commentWhitespace>\s+)(?P<comment>.*)/i";
		$key      = 0;
		foreach ($describedConstants as $describedConstant)
		    {
			if (isset($describedConstant["comment"]) === true &&
			    preg_match($pattern, $tokens[$describedConstant["comment"]]["content"], $parsedComment[$key]) > 0)
			    {
				$tagsName[$describedConstant["comment"]] = $parsedComment[$key]["name"];

				if ($key !== 0)
				    {
					$currentName  = (strlen($parsedComment[$key]["name"]) + strlen($parsedComment[$key]["exampleWhitespace"]));
					$previousName = (strlen($parsedComment[($key - 1)]["name"]) + strlen($parsedComment[($key - 1)]["exampleWhitespace"]));
					if ($currentName !== $previousName)
					    {
						$phpcsFile->addError($tag . _(" examples don't align"), $describedConstant["comment"], "CommentedConstantAlignment");
					    }

					$currentExample  = (strlen($parsedComment[$key]["example"]) + strlen($parsedComment[$key]["commentWhitespace"]));
					$previousExample = (strlen($parsedComment[($key - 1)]["example"]) + strlen($parsedComment[($key - 1)]["commentWhitespace"]));
					if ($currentExample !== $previousExample)
					    {
						$phpcsFile->addError($tag . _(" comments don't align"), $describedConstant["comment"], "CommentedConstantAlignment");
					    }
				    } //end if

				$key++;
			    }
			else
			    {
				$phpcsFile->addError($tag . _(" description must contain NAME \"Example\" Comment"), $describedConstant["tag"], "CommentedConstantAlignment");
			    } //end if
		    } //end foreach

		return $tagsName;
	    } //end errorUserConfig()


	/**
	 * Add @internalconst error if you have it
	 *
	 * @param array $describedConstants Constant tags
	 * @param File  $phpcsFile          The file being scanned.
	 *
	 * @return array
	 *
	 * @untranslatable CommentedConstantAlignment
	 * @untranslatable /^(?P<name>\S*)(?P<commentWhitespace>\s+)(?P<comment>.*)/i
	 */

	public function errorInternalConst(array $describedConstants, File $phpcsFile)
	    {
		$tokens   = &$phpcsFile->tokens;
		$tagsName = array();
		$pattern  = "/^(?P<name>\S*)(?P<commentWhitespace>\s+)(?P<comment>.*)/i";
		$key      = 0;
		foreach ($describedConstants as $describedConstant)
		    {
			if (isset($describedConstant["comment"]) === true &&
			    preg_match($pattern, $tokens[$describedConstant["comment"]]["content"], $parsedComment[$key]) > 0)
			    {
				$tagsName[$describedConstant["comment"]] = $parsedComment[$key]["name"];
				if ($key !== 0)
				    {
					$currentName  = (strlen($parsedComment[$key]["name"]) + strlen($parsedComment[$key]["commentWhitespace"]));
					$previousName = (strlen($parsedComment[($key - 1)]["name"]) + strlen($parsedComment[($key - 1)]["commentWhitespace"]));
					if ($currentName !== $previousName)
					    {
						$phpcsFile->addError(_("@internalconst comments don't align"), $describedConstant["comment"], "CommentedConstantAlignment");
					    }
				    } //end if

				$key++;
			    }
			else
			    {
				$phpcsFile->addError(_("@internalconst description must contain NAME Comment"), $describedConstant["tag"], "CommentedConstantAlignment");
			    } //end if
		    } //end foreach

		return $tagsName;
	    } //end errorInternalConst()


	/**
	 * Add exception code tag errors if you have it
	 *
	 * @param array $describedConstants Constant tags
	 * @param File  $phpcsFile          The file being scanned.
	 *
	 * @return array
	 *
	 * @untranslatable CommentedConstantAlignment
	 * @untranslatable /^(?P<name>\S*)((?P<commentWhitespace>\s+)(?P<comment>.*))?/i
	 */

	public function errorExceptionCode(array $describedConstants, File $phpcsFile)
	    {
		$tokens   = &$phpcsFile->tokens;
		$tagsName = array();
		$pattern  = "/^(?P<name>\S*)((?P<commentWhitespace>\s+)(?P<comment>.*))?/i";
		$key      = 0;
		foreach ($describedConstants as $describedConstant)
		    {
			if (isset($describedConstant["comment"]) === true &&
			    preg_match($pattern, $tokens[$describedConstant["comment"]]["content"], $parsedComment[$key]) > 0)
			    {
				$tagsName[$describedConstant["comment"]] = $parsedComment[$key]["name"];
				if ($key !== 0 && isset($parsedComment[$key]["comment"]) === true && isset($parsedComment[($key - 1)]["comment"]) === true)
				    {
					$currentName  = (strlen($parsedComment[$key]["name"]) + strlen($parsedComment[$key]["commentWhitespace"]));
					$previousName = (strlen($parsedComment[($key - 1)]["name"]) + strlen($parsedComment[($key - 1)]["commentWhitespace"]));
					if ($currentName !== $previousName)
					    {
						$phpcsFile->addError(_("@exceptioncode comments don't align"), $describedConstant["comment"], "CommentedConstantAlignment");
					    }
				    } //end if

				$key++;
			    }
			else
			    {
				$phpcsFile->addError(_("@exceptioncode description must contain NAME Comment or NAME"), $describedConstant["tag"], "CommentedConstantAlignment");
			    } //end if
		    } //end foreach

		return $tagsName;
	    } //end errorExceptionCode()


	/**
	 * Add unused errors
	 *
	 * @param array $tagConstants Tokens number of tag and comment constants
	 * @param File  $phpcsFile    The file being scanned.
	 * @param int   $stackPtr     The position of the current token in the stack passed in $tokens.
	 *
	 * @return void
	 *
	 * @untranslatable CommentedConstantAlignment
	 */

	public function errorUnusedConstants(array $tagConstants, File $phpcsFile, $stackPtr)
	    {
		$tokens = &$phpcsFile->tokens;
		$end    = ($tokens[$stackPtr]["code"] === T_FUNCTION) ? $tokens[$stackPtr]["code"]["scope_closer"] : null;
		foreach ($tagConstants as $tags)
		    {
			foreach ($tags as $key => $tag)
			    {
				if ($phpcsFile->findNext(T_STRING, ($stackPtr + 1), $end, false, $tag) === false)
				    {
					$phpcsFile->addError(_("Unused constant description: ") . $tag, $key, "CommentedConstantAlignment");
				    }
			    }
		    } //end foreach
	    } //end errorUnusedConstants()


    } //end class

?>
