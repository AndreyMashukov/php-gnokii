<?php

/**
 * PHP version 5
 *
 * @package Logics\BuildTools\CodeSniffer\PEAR
 */

namespace Logics\BuildTools\CodeSniffer\PEAR;

use \Logics\BuildTools\CodeSniffer\File;
use \Logics\BuildTools\CodeSniffer\Sniff;
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
 * @link      $HeadURL: https://open.logics.net.au/buildtools/codesniffer/tags/0.1.3/src/CodeSniffer/Standards/PEAR/Sniffs/Commenting/FunctionCommentSniff.php $
 */

class FunctionCommentSniff implements Sniff
    {

	/**
	 * Returns an array of tokens this test wants to listen for.
	 *
	 * @return array
	 */

	public function register()
	    {
		return array(T_FUNCTION);
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
	 * @internalconst T_DOC_COMMENT_STRING    T_DOC_COMMENT_STRING token
	 *
	 * @untranslatable Missing
	 * @untranslatable WrongStyle
	 * @untranslatable SpacingAfter
	 * @untranslatable @see
	 * @untranslatable EmptySees
	 */

	public function process(File $phpcsFile, $stackPtr)
	    {
		$tokens = &$phpcsFile->tokens;
		$find   = Tokens::$methodPrefixes;
		$find[] = T_WHITESPACE;

		$commentEnd = $phpcsFile->findPrevious($find, ($stackPtr - 1), null, true);
		if ($tokens[$commentEnd]["code"] === T_COMMENT)
		    {
			// Inline comments might just be closing comments for  control structures or functions instead of function comments using the wrong comment type.
			// If there is other code on the line, assume they relate to that code.
			$prev = $phpcsFile->findPrevious($find, ($commentEnd - 1), null, true);
			if ($prev !== false && $tokens[$prev]["line"] === $tokens[$commentEnd]["line"])
			    {
				$commentEnd = $prev;
			    }
		    }

		if ($tokens[$commentEnd]["code"] !== T_DOC_COMMENT_CLOSE_TAG && $tokens[$commentEnd]["code"] !== T_COMMENT)
		    {
			$phpcsFile->addError(_("Missing function doc comment"), $stackPtr, "Missing");
			return;
		    }

		if ($tokens[$commentEnd]["code"] === T_COMMENT)
		    {
			$phpcsFile->addError(_("You must use ") . "\"/**\"" . _(" style comments for a function comment"), $stackPtr, "WrongStyle");
			return;
		    }

		if ($tokens[$commentEnd]["line"] !== ($tokens[$stackPtr]["line"] - 1))
		    {
			$error = _("There must be no blank lines after the function comment");
			$phpcsFile->addError($error, $commentEnd, "SpacingAfter");
		    }

		$commentStart = $tokens[$commentEnd]["comment_opener"];
		foreach ($tokens[$commentStart]["comment_tags"] as $tag)
		    {
			if ($tokens[$tag]["content"] === "@see")
			    {
				// Make sure the tag isn"t empty.
				$string = $phpcsFile->findNext(T_DOC_COMMENT_STRING, $tag, $commentEnd);
				if ($string === false || $tokens[$string]["line"] !== $tokens[$tag]["line"])
				    {
					$error = _("Content missing for @see tag in function comment");
					$phpcsFile->addError($error, $tag, "EmptySees");
				    }
			    }
		    } //end foreach

		$this->processReturn($phpcsFile, $stackPtr, $commentStart);
		$this->processThrows($phpcsFile, $stackPtr, $commentStart);
		$this->processParams($phpcsFile, $stackPtr, $commentStart);
	    } //end process()


	/**
	 * Process the return comment of this function comment.
	 *
	 * @param File $phpcsFile    The file being scanned.
	 * @param int  $stackPtr     The position of the current token in the stack passed in $tokens.
	 * @param int  $commentStart The position in the stack where the comment started.
	 *
	 * @return void
	 *
	 * @internalconst T_DOC_COMMENT_STRING T_DOC_COMMENT_STRING token
	 *
	 * @untranslatable __construct
	 * @untranslatable __destruct
	 * @untranslatable @return
	 * @untranslatable DuplicateReturn
	 * @untranslatable MissingReturnType
	 * @untranslatable MissingReturn
	 */

	protected function processReturn(File &$phpcsFile, $stackPtr, $commentStart)
	    {
		$tokens = &$phpcsFile->tokens;

		// Skip constructor and destructor.
		$methodName      = $phpcsFile->getDeclarationName($stackPtr);
		$isSpecialMethod = ($methodName === "__construct" || $methodName === "__destruct");

		$return = null;
		foreach ($tokens[$commentStart]["comment_tags"] as $tag)
		    {
			if ($tokens[$tag]["content"] === "@return")
			    {
				if ($return !== null)
				    {
					$error = _("Only 1 ") . "@return" . _(" tag is allowed in a function comment");
					$phpcsFile->addError($error, $tag, "DuplicateReturn");
					return;
				    }

				$return = $tag;
			    }
		    } //end foreach

		if ($isSpecialMethod === true)
		    {
			return;
		    }

		if ($return !== null)
		    {
			$content = $tokens[($return + 2)]["content"];
			if (empty($content) === true || $tokens[($return + 2)]["code"] !== T_DOC_COMMENT_STRING)
			    {
				$error = _("Return type missing for ") . "@return" . _(" tag in function comment");
				$phpcsFile->addError($error, $return, "MissingReturnType");
			    }
			else
			    {
				$this->checkReturn($phpcsFile, $stackPtr, $return);
			    }
		    }
		else
		    {
			$error = _("Missing ") . "@return" . _(" tag in function comment");
			$phpcsFile->addError($error, $tokens[$commentStart]["comment_closer"], "MissingReturn");
		    } //end if
	    } //end processReturn()


	/**
	 * Check the return comment
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $stackPtr  The position of the current token in the tokens stack.
	 * @param int  $return    The @return position
	 *
	 * @return void
	 */

	protected function checkReturn(File &$phpcsFile, $stackPtr, $return)
	    {
	    } //end checkReturn()


	/**
	 * Process any throw tags that this function comment has.
	 *
	 * @param File $phpcsFile    The file being scanned.
	 * @param int  $stackPtr     The position of the current token in the stack passed in $tokens.
	 * @param int  $commentStart The position in the stack where the comment started.
	 *
	 * @return void
	 *
	 * @internalconst T_DOC_COMMENT_STRING T_DOC_COMMENT_STRING token
	 *
	 * @untranslatable @throws
	 * @untranslatable InvalidThrows
	 */

	protected function processThrows(File &$phpcsFile, $stackPtr, $commentStart)
	    {
		unset($stackPtr);

		$tokens = &$phpcsFile->tokens;

		$throws = array();
		foreach ($tokens[$commentStart]["comment_tags"] as $tag)
		    {
			if ($tokens[$tag]["content"] !== "@throws")
			    {
				continue;
			    }

			$exception = null;
			$comment   = null;
			if ($tokens[($tag + 2)]["code"] === T_DOC_COMMENT_STRING)
			    {
				$matches = array();
				preg_match("/([^\s]+)(?:\s+(.*))?/", $tokens[($tag + 2)]["content"], $matches);
				$exception = $matches[1];
				if (isset($matches[2]) === true)
				    {
					$comment = $matches[2];
				    }
			    } //end if

			if ($exception === null)
			    {
				$error = _("Exception type missing for @throws tag in function comment");
				$phpcsFile->addError($error, $tag, "InvalidThrows");
			    }
		    } //end foreach
	    } //end processThrows()


	/**
	 * Process the function parameter comments.
	 *
	 * @param File $phpcsFile    The file being scanned.
	 * @param int  $stackPtr     The position of the current token in the stack passed in $tokens.
	 * @param int  $commentStart The position in the stack where the comment started.
	 *
	 * @return void
	 *
	 * @internalconst T_DOC_COMMENT_STRING T_DOC_COMMENT_STRING token
	 *
	 * @untranslatable @param
	 * @untranslatable MissingParamComment
	 * @untranslatable MissingParamName
	 * @untranslatable MissingParamType
	 * @untranslatable \"%s\"
	 * @untranslatable MissingParamTag
	 */

	protected function processParams(File &$phpcsFile, $stackPtr, $commentStart)
	    {
		$tokens = &$phpcsFile->tokens;

		$params  = array();
		$maxType = 0;
		$maxVar  = 0;
		foreach ($tokens[$commentStart]["comment_tags"] as $pos => $tag)
		    {
			if ($tokens[$tag]["content"] !== "@param")
			    {
				continue;
			    }

			$type      = "";
			$typeSpace = 0;
			$var       = "";
			$varSpace  = 0;
			$comment   = "";
			if ($tokens[($tag + 2)]["code"] === T_DOC_COMMENT_STRING)
			    {
				$matches = array();
				preg_match('/([^$&]+)(?:((?:\$|&)[^\s]+)(?:(\s+)(.*))?)?/', $tokens[($tag + 2)]["content"], $matches);

				$typeLen   = strlen($matches[1]);
				$type      = trim($matches[1]);
				$typeSpace = ($typeLen - strlen($type));
				$typeLen   = strlen($type);
				if ($typeLen > $maxType)
				    {
					$maxType = $typeLen;
				    }

				if (isset($matches[2]) === true)
				    {
					$var    = $matches[2];
					$varLen = strlen($var);
					if ($varLen > $maxVar)
					    {
						$maxVar = $varLen;
					    }

					if (isset($matches[4]) === true)
					    {
						$varSpace = strlen($matches[3]);
						$comment  = $matches[4];

						// Any strings until the next tag belong to this comment.
						if (isset($tokens[$commentStart]["comment_tags"][($pos + 1)]) === true)
						    {
							$end = $tokens[$commentStart]["comment_tags"][($pos + 1)];
						    }
						else
						    {
							$end = $tokens[$commentStart]["comment_closer"];
						    }

						for ($i = ($tag + 3); $i < $end; $i++)
						    {
							if ($tokens[$i]["code"] === T_DOC_COMMENT_STRING)
							    {
								$comment .= " " . $tokens[$i]["content"];
							    }
						    } //end for
					    }
					else
					    {
						$error = _("Missing parameter comment");
						$phpcsFile->addError($error, $tag, "MissingParamComment");
					    } //end if
				    }
				else
				    {
					$error = _("Missing parameter name");
					$phpcsFile->addError($error, $tag, "MissingParamName");
				    } //end if
			    }
			else
			    {
				$error = _("Missing parameter type");
				$phpcsFile->addError($error, $tag, "MissingParamType");
			    } //end if

			$params[] = array(
				     "tag"        => $tag,
				     "type"       => $type,
				     "var"        => $var,
				     "comment"    => $comment,
				     "type_space" => $typeSpace,
				     "var_space"  => $varSpace,
				    );
		    } //end foreach

		$realParams  = $phpcsFile->getMethodParameters($stackPtr);
		$foundParams = array();

		foreach ($params as $pos => $param)
		    {
			$this->checkParam($phpcsFile, $param, $maxType, $maxVar, $pos, $realParams, $foundParams);
		    } //end foreach

		$realNames = array();
		foreach ($realParams as $realParam)
		    {
			$realNames[] = $realParam["name"];
		    }

		// Report missing comments.
		$diff = array_diff($realNames, $foundParams);
		foreach ($diff as $neededParam)
		    {
			$error = _("Doc comment for parameter ") . "\"%s\"" . _(" missing");
			$data  = array($neededParam);
			$phpcsFile->addError($error, $commentStart, "MissingParamTag", $data);
		    }
	    } //end processParams()


	/**
	 * Process the function parameter comments.
	 *
	 * @param File  $phpcsFile   The file being scanned
	 * @param array $param       Parameter
	 * @param int   $maxType     Max type length
	 * @param int   $maxVar      Max var length
	 * @param int   $pos         Parameter position
	 * @param array $realParams  Actual parameters
	 * @param array $foundParams Found parameters
	 *
	 * @return bool
	 *
	 * @untranslatable SpacingAfterParamType
	 * @untranslatable ParamNameNoMatch
	 * @untranslatable ParamNameNoCaseMatch
	 * @untranslatable ExtraParamComment
	 * @untranslatable %s
	 * @untranslatable SpacingAfterParamName
	 */

	protected function checkParam(File &$phpcsFile, array $param, $maxType, $maxVar, $pos, array $realParams, array &$foundParams)
	    {
		$empty = false;

		if ($param["var"] === "")
		    {
			$empty = true;
		    }
		else
		    {
			$foundParams[] = $param["var"];

			// Check number of spaces after the type.
			$spaces = ($maxType - strlen($param["type"]) + 1);
			if ($param["type_space"] !== $spaces)
			    {
				$error = _("Expected ") . "%s" . _(" spaces after parameter type; ") . "%s" . _(" found");
				$data  = array(
					  $spaces,
					  $param["type_space"],
					 );

				$phpcsFile->addError($error, $param["tag"], "SpacingAfterParamType", $data);
			    }

			// Make sure the param name is correct.
			if (isset($realParams[$pos]) === true)
			    {
				$realName = $realParams[$pos]["name"];
				if ($realName !== $param["var"])
				    {
					$code = "ParamNameNoMatch";
					$data = array(
						 $param["var"],
						 $realName,
						);

					$error = _("Doc comment for parameter ") . "%s" . _(" does not match ");
					if (strtolower($param["var"]) === strtolower($realName))
					    {
						$error .= _("case of ");
						$code   = "ParamNameNoCaseMatch";
					    }

					$error .= _("actual variable name ") . "%s";

					$phpcsFile->addError($error, $param["tag"], $code, $data);
				    }
			    }
			else if (substr($param["var"], -4) !== ",...")
			    {
				// We must have an extra parameter comment.
				$error = _("Superfluous parameter comment");
				$phpcsFile->addError($error, $param["tag"], "ExtraParamComment");
			    } //end if

			if ($param["comment"] === "")
			    {
				$empty = true;
			    }
			else
			    {
				// Check number of spaces after the var name.
				$spaces = ($maxVar - strlen($param["var"]) + 1);
				if ($param["var_space"] !== $spaces)
				    {
					$error = _("Expected ") . "%s" . _(" spaces after parameter name; ") . "%s" . _(" found");
					$data  = array(
						  $spaces,
						  $param["var_space"],
						 );

					$phpcsFile->addError($error, $param["tag"], "SpacingAfterParamName", $data);
				    }
			    }
		    } //end if

		return $empty;
	    } //end checkParam()


    } //end class

?>
