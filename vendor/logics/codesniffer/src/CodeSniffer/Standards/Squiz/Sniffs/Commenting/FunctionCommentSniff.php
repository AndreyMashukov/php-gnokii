<?php

/**
 * PHP version 5
 *
 * @package Logics\BuildTools\CodeSniffer\Squiz
 */

namespace Logics\BuildTools\CodeSniffer\Squiz;

use \Logics\BuildTools\CodeSniffer\PEAR\FunctionCommentSniff as PEAR_FunctionCommentSniff;
use \Logics\BuildTools\CodeSniffer\CodeSniffer;
use \Logics\BuildTools\CodeSniffer\File;

/**
 * Parses and verifies the doc comments for functions.
 *
 * Verifies that :
 * <ul>
 *  <li>A comment exists</li>
 *  <li>There is a blank newline after the short description</li>
 *  <li>There is a blank newline between the long and short description</li>
 *  <li>There is a blank newline between the long description and tags</li>
 *  <li>Parameter names represent those in the method</li>
 *  <li>Parameter comments are in the correct order</li>
 *  <li>Parameter comments are complete</li>
 *  <li>A type hint is provided for array and custom class</li>
 *  <li>Type hint matches the actual variable/class type</li>
 *  <li>A blank line is present before the first and after the last parameter</li>
 *  <li>A return type exists</li>
 *  <li>Any throw tag must have a comment</li>
 *  <li>The tag order and indentation are correct</li>
 * </ul>
 *
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @author    Marc McIntyre <mmcintyre@squiz.net>
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2006-2012 Squiz Pty Ltd (ABN 77 084 670 600)
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 * @version   SVN: $Date: 2016-08-16 23:39:01 +0900 (Tue, 16 Aug 2016) $ $Revision: 26 $
 * @link      $HeadURL: https://open.logics.net.au/buildtools/codesniffer/tags/0.1.3/src/CodeSniffer/Standards/Squiz/Sniffs/Commenting/FunctionCommentSniff.php $
 */

class FunctionCommentSniff extends PEAR_FunctionCommentSniff
    {

	/**
	 * Check the return comment
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $stackPtr  The position of the current token in the tokens stack.
	 * @param int  $return    Tag @return position
	 *
	 * @return void
	 *
	 * @internalconst T_CLOSURE   T_CLOSURE token
	 * @internalconst T_SEMICOLON T_SEMICOLON token
	 *
	 * @untranslatable \"%s\"
	 * @untranslatable InvalidReturn
	 * @untranslatable void
	 * @untranslatable InvalidReturnVoid
	 * @untranslatable mixed
	 * @untranslatable InvalidNoReturn
	 * @untranslatable InvalidReturnNotVoid
	 */

	protected function checkReturn(File &$phpcsFile, $stackPtr, $return)
	    {
		$tokens = &$phpcsFile->tokens;

		$content = $tokens[($return + 2)]["content"];

		// Check return type (can be multiple, separated by "|").
		$typeNames      = explode("|", $content);
		$suggestedNames = array();
		foreach ($typeNames as $i => $typeName)
		    {
			$suggestedName = $this->getSuggestedType($typeName);
			if (in_array($suggestedName, $suggestedNames) === false)
			    {
				$suggestedNames[] = $suggestedName;
			    }
		    } //end foreach

		$suggestedType = implode("|", $suggestedNames);
		if ($content !== $suggestedType)
		    {
			$error = _("Expected ") . "\"%s\"" . _(" but found ") . "\"%s\"" . _(" for function return type");
			$data  = array(
				  $suggestedType,
				  $content,
				 );
			$phpcsFile->addError($error, $return, "InvalidReturn", $data);
		    }

		// If the return type is void, make sure there is no return statement in the function.
		if ($content === "void")
		    {
			if (isset($tokens[$stackPtr]["scope_closer"]) === true)
			    {
				$endToken = $tokens[$stackPtr]["scope_closer"];
				for ($returnToken = $stackPtr; $returnToken < $endToken; $returnToken++)
				    {
					if ($tokens[$returnToken]["code"] === T_CLOSURE)
					    {
						$returnToken = $tokens[$returnToken]["scope_closer"];
						continue;
					    }

					if ($tokens[$returnToken]["code"] === T_RETURN || $tokens[$returnToken]["code"] === T_YIELD)
					    {
						break;
					    }
				    } //end for

				if ($returnToken !== $endToken)
				    {
					// If the function is not returning anything, just exiting, then there is no problem.
					$semicolon = $phpcsFile->findNext(T_WHITESPACE, ($returnToken + 1), null, true);
					if ($tokens[$semicolon]["code"] !== T_SEMICOLON)
					    {
						$error = _("Function return type is void, but function contains return statement");
						$phpcsFile->addError($error, $return, "InvalidReturnVoid");
					    }
				    }
			    } //end if
		    }
		else if ($content !== "mixed")
		    {
			// If return type is not void, there needs to be a return statement somewhere in the function that returns something.
			if (isset($tokens[$stackPtr]["scope_closer"]) === true)
			    {
				$endToken    = $tokens[$stackPtr]["scope_closer"];
				$returnToken = $phpcsFile->findNext(array(T_RETURN, T_YIELD), $stackPtr, $endToken);
				if ($returnToken === false)
				    {
					$error = _("Function return type is not void, but function has no return statement");
					$phpcsFile->addError($error, $return, "InvalidNoReturn");
				    }
				else
				    {
					$semicolon = $phpcsFile->findNext(T_WHITESPACE, ($returnToken + 1), null, true);
					if ($tokens[$semicolon]["code"] === T_SEMICOLON)
					    {
						$error = _("Function return type is not void, but function is returning void here");
						$phpcsFile->addError($error, $returnToken, "InvalidReturnNotVoid");
					    }
				    }
			    } //end if
		    } //end if
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
	 * @untranslatable EmptyThrows
	 * @untranslatable Throws
	 */

	protected function processThrows(File &$phpcsFile, $stackPtr, $commentStart)
	    {
		unset($stackPtr);

		$tokens = &$phpcsFile->tokens;

		$throws = array();
		foreach ($tokens[$commentStart]["comment_tags"] as $pos => $tag)
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
				if (isset($matches[2]) === true && trim($matches[2]) !== "")
				    {
					$comment = $matches[2];
				    }
			    }

			if ($exception === null)
			    {
				$error = _("Exception type and comment missing for ") . "@throws" . _(" tag in function comment");
				$phpcsFile->addError($error, $tag, "InvalidThrows");
			    }
			else if ($comment === null)
			    {
				$error = _("Comment missing for ") . "@throws" . _(" tag in function comment");
				$phpcsFile->addError($error, $tag, "EmptyThrows");
			    }
			else
			    {
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
				    }

				$this->checkStartEnd($phpcsFile, $comment, ($tag + 2), "@throws " . _("tag comment"), "Throws");
			    } //end if
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
	 * @internalconst T_DOC_COMMENT_STRING     T_DOC_COMMENT_STRING token
	 * @internalconst T_DOC_COMMENT_WHITESPACE T_DOC_COMMENT_WHITESPACE token
	 *
	 * @untranslatable @param
	 * @untranslatable MissingParamComment
	 * @untranslatable MissingParamName
	 * @untranslatable MissingParamType
	 * @untranslatable \"%s\"
	 * @untranslatable IncorrectParamVarName
	 * @untranslatable array
	 * @untranslatable callable
	 * @untranslatable %s
	 * @untranslatable TypeHintMissing
	 * @untranslatable IncorrectTypeHint
	 * @untranslatable InvalidTypeHint
	 * @untranslatable ParamComment
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

			$type         = "";
			$typeSpace    = 0;
			$var          = "";
			$varSpace     = 0;
			$comment      = "";
			$commentLines = array();
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
						$varSpace       = strlen($matches[3]);
						$comment        = $matches[4];
						$commentLines[] = array(
								   "comment" => $comment,
								   "token"   => ($tag + 2),
								   "indent"  => $varSpace,
								  );

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
								$indent = 0;
								if ($tokens[($i - 1)]["code"] === T_DOC_COMMENT_WHITESPACE)
								    {
									$indent = strlen($tokens[($i - 1)]["content"]);
								    }

								$comment       .= " " . $tokens[$i]["content"];
								$commentLines[] = array(
										   "comment" => $tokens[$i]["content"],
										   "token"   => $i,
										   "indent"  => $indent,
										  );
							    }
						    } //end for
					    }
					else
					    {
						$error = _("Missing parameter comment");
						$phpcsFile->addError($error, $tag, "MissingParamComment");
						$commentLines[] = array("comment" => "");
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
				     "tag"          => $tag,
				     "type"         => $type,
				     "var"          => $var,
				     "comment"      => $comment,
				     "commentLines" => $commentLines,
				     "type_space"   => $typeSpace,
				     "var_space"    => $varSpace,
				    );
		    } //end foreach

		$realParams  = $phpcsFile->getMethodParameters($stackPtr);
		$foundParams = array();

		foreach ($params as $pos => $param)
		    {
			// If the type is empty, the whole line is empty.
			if ($param["type"] === "")
			    {
				continue;
			    }

			// Check the param type value.
			$typeNames = explode("|", $param["type"]);
			foreach ($typeNames as $typeName)
			    {
				$suggestedName = $this->getSuggestedType($typeName);
				if ($typeName !== $suggestedName)
				    {
					$error = _("Expected ") . "\"%s\"" . _(" but found ") . "\"%s\"" . _(" for parameter type");
					$data  = array(
						  $suggestedName,
						  $typeName,
						 );

					$phpcsFile->addError($error, $param["tag"], "IncorrectParamVarName", $data);
				    }
				else if (count($typeNames) === 1)
				    {
					// Check type hint for array and custom type.
					$suggestedTypeHint = "";
					if (strpos($suggestedName, "array") !== false)
					    {
						$suggestedTypeHint = "array";
					    }
					else if (strpos($suggestedName, "callable") !== false)
					    {
						$suggestedTypeHint = "callable";
					    }
					else if (in_array($typeName, CodeSniffer::$allowedTypes) === false)
					    {
						$suggestedTypeHint = $suggestedName;
					    }

					if ($suggestedTypeHint !== "" && isset($realParams[$pos]) === true)
					    {
						$typeHint = $realParams[$pos]["type_hint"];
						if ($typeHint === "")
						    {
							$error = _("Type hint ") . "\"%s\"" . _(" missing for ") . "%s";
							$data  = array(
								  $suggestedTypeHint,
								  $param["var"],
								 );
							$phpcsFile->addError($error, $stackPtr, "TypeHintMissing", $data);
						    }
						else if ($typeHint !== substr($suggestedTypeHint, (strlen($typeHint) * -1)))
						    {
							$error = _("Expected type hint ") . "\"%s\"" . _("; found ") . "\"%s\"" . _(" for ") . "%s";
							$data  = array(
								  $suggestedTypeHint,
								  $typeHint,
								  $param["var"],
								 );
							$phpcsFile->addError($error, $stackPtr, "IncorrectTypeHint", $data);
						    }
					    }
					else if ($suggestedTypeHint === "" && isset($realParams[$pos]) === true)
					    {
						$typeHint = $realParams[$pos]["type_hint"];
						if ($typeHint !== "")
						    {
							$error = _("Unknown type hint ") . "\"%s\"" . _(" found for ") . "%s";
							$data  = array(
								  $typeHint,
								  $param["var"],
								 );
							$phpcsFile->addError($error, $stackPtr, "InvalidTypeHint", $data);
						    }
					    } //end if
				    } //end if
			    } //end foreach

			if ($this->checkParam($phpcsFile, $param, $maxType, $maxVar, $pos, $realParams, $foundParams) === false)
			    {
				$this->checkStartEnd($phpcsFile, $param["comment"], $param["tag"], _("Parameter comment"), "ParamComment");
			    }
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
	 * Comments must start with a capital letter and end with the full stop.
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
	 * @untranslatable NoFullStop
	 */

	protected function checkStartEnd(File &$phpcsFile, $comment, $tag, $what, $short)
	    {
		$firstChar = $comment{0};
		if (preg_match('|\p{Lu}|u', $firstChar) === 0)
		    {
			$error = $what . " " . _("must start with a capital letter");
			$phpcsFile->addError($error, $tag, $short . "NotCapital");
		    }

		$lastChar = substr($comment, -1);
		if ($lastChar !== ".")
		    {
			$error = $what . " " . _("must end with a full stop");
			$phpcsFile->addError($error, $tag, $short . "NoFullStop");
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
		return CodeSniffer::suggestType($varType);
	    } //end getSuggestedType()


    } //end class

?>
