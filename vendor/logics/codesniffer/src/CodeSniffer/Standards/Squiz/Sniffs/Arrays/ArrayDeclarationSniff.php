<?php

/**
 * PHP version 5
 *
 * @package Logics\BuildTools\CodeSniffer\Squiz
 */

namespace Logics\BuildTools\CodeSniffer\Squiz;

use \Logics\BuildTools\CodeSniffer\File;
use \Logics\BuildTools\CodeSniffer\Sniff;
use \Logics\BuildTools\CodeSniffer\Tokens;

/**
 * A test to ensure that arrays conform to the array coding standard.
 *
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @author    Marc McIntyre <mmcintyre@squiz.net>
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2006-2012 Squiz Pty Ltd (ABN 77 084 670 600)
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 * @version   SVN: $Date: 2016-08-16 23:39:01 +0900 (Tue, 16 Aug 2016) $ $Revision: 26 $
 * @link      $HeadURL: https://open.logics.net.au/buildtools/codesniffer/tags/0.1.3/src/CodeSniffer/Standards/Squiz/Sniffs/Arrays/ArrayDeclarationSniff.php $
 */

class ArrayDeclarationSniff implements Sniff
    {

	/**
	 * Returns an array of tokens this test wants to listen for.
	 *
	 * @return array
	 */

	public function register()
	    {
		return array(T_ARRAY);
	    } //end register()


	/**
	 * Processes this sniff, when one of its tokens is encountered.
	 *
	 * @param File $phpcsFile The current file being checked.
	 * @param int  $stackPtr  The position of the current token in the stack passed in $tokens.
	 *
	 * @return void
	 *
	 * @untranslatable NotLowerCase
	 * @untranslatable SpaceAfterKeyword
	 * @untranslatable SpaceInEmptyArray
	 * @untranslatable CloseBraceNewLine
	 * @untranslatable CloseBraceNotAligned
	 * @untranslatable \"%s\"
	 * @untranslatable %s
	 */

	public function process(File $phpcsFile, $stackPtr)
	    {
		$tokens = &$phpcsFile->tokens;

		// Array keyword should be lower case.
		if (strtolower($tokens[$stackPtr]["content"]) !== $tokens[$stackPtr]["content"])
		    {
			$error = _("Array keyword should be lower case; expected \"array\" but found") . " \"%s\"";
			$data  = array($tokens[$stackPtr]["content"]);
			$phpcsFile->addError($error, $stackPtr, "NotLowerCase", $data);
		    }

		$arrayStart   = $tokens[$stackPtr]["parenthesis_opener"];
		$arrayEnd     = $tokens[$arrayStart]["parenthesis_closer"];
		$keywordStart = $tokens[$stackPtr]["column"];

		if ($arrayStart !== ($stackPtr + 1))
		    {
			$error = _("There must be no space between the Array keyword and the opening parenthesis");
			$phpcsFile->addError($error, $stackPtr, "SpaceAfterKeyword");
		    }

		// Check for empty arrays.
		$content = $phpcsFile->findNext(array(T_WHITESPACE), ($arrayStart + 1), ($arrayEnd + 1), true);
		if ($content === $arrayEnd && ($arrayEnd - $arrayStart) !== 1)
		    {
			// Empty array, but if the brackets aren't together, there's a problem.
			$error = _("Empty array declaration must have no space between the parentheses");
			$phpcsFile->addError($error, $stackPtr, "SpaceInEmptyArray");
		    }
		else if ($tokens[$arrayStart]["line"] === $tokens[$arrayEnd]["line"])
		    {
			// Single line array.
			$commas     = array();
			$valueCount = $this->_findValues($tokens, $arrayStart, $arrayEnd, $commas);
			$this->_checkDoubleArrows($phpcsFile, $tokens, $arrayStart, $arrayEnd);
			$this->_checkValues($phpcsFile, $tokens, $stackPtr, $valueCount, $commas);
		    }
		else
		    {
			// Check the closing bracket is on a new line.
			$lastContent = $phpcsFile->findPrevious(T_WHITESPACE, ($arrayEnd - 1), $arrayStart, true);
			if ($tokens[$lastContent]["line"] !== ($tokens[$arrayEnd]["line"] - 1))
			    {
				$error = _("Closing parenthesis of array declaration must be on a new line");
				$phpcsFile->addError($error, $arrayEnd, "CloseBraceNewLine");
			    }
			else if ($tokens[$arrayEnd]["column"] !== $keywordStart)
			    {
				// Check the closing bracket is lined up under the a in array.
				$expected = $keywordStart;
				$found    = $tokens[$arrayEnd]["column"];
				$error    = _("Closing parenthesis not aligned correctly; expected") . " %s " . _("space(s) but found") . " %s";
				$data     = array(
					     $expected,
					     $found,
					    );
				$phpcsFile->addError($error, $arrayEnd, "CloseBraceNotAligned", $data);
			    }

			$indices = array();
			$found   = $this->_findAllDoubleArrows($phpcsFile, $tokens, $stackPtr, $arrayStart, $arrayEnd, $lastToken, $keyUsed, $indices, $maxLength);

			if ($found === true)
			    {
				$this->_checkSingleValueArray($phpcsFile, $tokens, $stackPtr, $arrayEnd, $lastToken, $keyUsed, $indices, $keywordStart, $maxLength);
			    }
		    } //end if
	    } //end process()


	/**
	 * Find all array values
	 *
	 * @param array $tokens     All tokens
	 * @param int   $arrayStart Array start position
	 * @param int   $arrayEnd   Array end position
	 * @param array $commas     Array which will be filled with commas positions
	 *
	 * @return int Values count
	 *
	 * @internalconst T_OPEN_PARENTHESIS T_OPEN_PARENTHESIS token
	 * @internalconst T_COMMA            T_COMMA token
	 */

	private function _findValues(array &$tokens, $arrayStart, $arrayEnd, array &$commas)
	    {
		// Check if there are multiple values. If so, then it has to be multiple lines
		// unless it is contained inside a function call or condition.
		$valueCount = 0;
		$commas     = array();
		for ($i = ($arrayStart + 1); $i < $arrayEnd; $i++)
		    {
			// Skip bracketed statements, like function calls.
			if ($tokens[$i]["code"] === T_OPEN_PARENTHESIS)
			    {
				$i = $tokens[$i]["parenthesis_closer"];
			    }
			else if ($tokens[$i]["code"] === T_COMMA)
			    {
				$valueCount++;
				$commas[] = $i;
			    }
		    }

		return $valueCount;
	    } //end _findValues()


	/**
	 * Now check each of the double arrows (if any).
	 *
	 * @param File  $phpcsFile  The current file being checked.
	 * @param array $tokens     All tokens
	 * @param int   $arrayStart Array start position
	 * @param int   $arrayEnd   Array end position
	 *
	 * @return void
	 *
	 * @untranslatable NoSpaceBeforeDoubleArrow
	 * @untranslatable SpaceBeforeDoubleArrow
	 * @untranslatable NoSpaceAfterDoubleArrow
	 * @untranslatable SpaceAfterDoubleArrow
	 * @untranslatable \"%s\"; %s
	 * @untranslatable \"%s\"
	 * @untranslatable %s
	 */

	private function _checkDoubleArrows(File &$phpcsFile, array &$tokens, $arrayStart, $arrayEnd)
	    {
		$nextArrow = $phpcsFile->findNext(T_DOUBLE_ARROW, ($arrayStart + 1), $arrayEnd);
		while ($nextArrow !== false)
		    {
			if ($tokens[($nextArrow - 1)]["code"] !== T_WHITESPACE)
			    {
				$content = $tokens[($nextArrow - 1)]["content"];
				$error   = _("Expected 1 space between") . " \"%s\" " . _("and double arrow; 0 found");
				$data    = array($content);
				$phpcsFile->addError($error, $nextArrow, "NoSpaceBeforeDoubleArrow", $data);
			    }
			else
			    {
				$spaceLength = strlen($tokens[($nextArrow - 1)]["content"]);
				if ($spaceLength !== 1)
				    {
					$content = $tokens[($nextArrow - 2)]["content"];
					$error   = _("Expected 1 space between") . " \"%s\" " . _("and double arrow;") . " %s " . _("found");
					$data    = array(
						    $content,
						    $spaceLength,
						   );
					$phpcsFile->addError($error, $nextArrow, "SpaceBeforeDoubleArrow", $data);
				    }
			    }

			if ($tokens[($nextArrow + 1)]["code"] !== T_WHITESPACE)
			    {
				$content = $tokens[($nextArrow + 1)]["content"];
				$error   = _("Expected 1 space between double arrow and") . " \"%s\"" . _("; 0 found");
				$data    = array($content);
				$phpcsFile->addError($error, $nextArrow, "NoSpaceAfterDoubleArrow", $data);
			    }
			else
			    {
				$spaceLength = strlen($tokens[($nextArrow + 1)]["content"]);
				if ($spaceLength !== 1)
				    {
					$content = $tokens[($nextArrow + 2)]["content"];
					$error   = _("Expected 1 space between double arrow and") . " \"%s\"; %s " . _("found");
					$data    = array(
						    $content,
						    $spaceLength,
						   );
					$phpcsFile->addError($error, $nextArrow, "SpaceAfterDoubleArrow", $data);
				    }
			    }

			$nextArrow = $phpcsFile->findNext(T_DOUBLE_ARROW, ($nextArrow + 1), $arrayEnd);
		    } //end while
	    } //end _checkDoubleArrows()


	/**
	 * Now check array values
	 *
	 * @param File  $phpcsFile  The current file being checked.
	 * @param array $tokens     All tokens
	 * @param int   $stackPtr   Current token position
	 * @param int   $valueCount Number of values
	 * @param array $commas     Commas positions
	 *
	 * @return void
	 *
	 * @internalconst T_OPEN_PARENTHESIS T_OPEN_PARENTHESIS token
	 * @internalconst T_SEMICOLON        T_SEMICOLON token
	 *
	 * @untranslatable SingleLineNotAllowed
	 * @untranslatable NoSpaceAfterComma
	 * @untranslatable SpaceAfterComma
	 * @untranslatable SpaceBeforeComma
	 * @untranslatable \"%s\"; %s
	 * @untranslatable \"%s\"
	 * @untranslatable %s
	 */

	private function _checkValues(File $phpcsFile, array &$tokens, $stackPtr, $valueCount, array $commas)
	    {
		if ($valueCount > 0)
		    {
			$conditionCheck = $phpcsFile->findPrevious(array(T_OPEN_PARENTHESIS, T_SEMICOLON), ($stackPtr - 1), null, false);
			if (($conditionCheck === false) || ($tokens[$conditionCheck]["line"] !== $tokens[$stackPtr]["line"]))
			    {
				$error = _("Array with multiple values cannot be declared on a single line");
				$phpcsFile->addError($error, $stackPtr, "SingleLineNotAllowed");
			    }
			else
			    {
				// We have a multiple value array that is inside a condition or
				// function. Check its spacing is correct.
				foreach ($commas as $comma)
				    {
					if ($tokens[($comma + 1)]["code"] !== T_WHITESPACE)
					    {
						$content = $tokens[($comma + 1)]["content"];
						$error   = _("Expected 1 space between comma and") . " \"%s\"" . _("; 0 found");
						$data    = array($content);
						$phpcsFile->addError($error, $comma, "NoSpaceAfterComma", $data);
					    }
					else
					    {
						$spaceLength = strlen($tokens[($comma + 1)]["content"]);
						if ($spaceLength !== 1)
						    {
							$content = $tokens[($comma + 2)]["content"];
							$error   = _("Expected 1 space between comma and") . " \"%s\"; %s " . _("found");
							$data    = array(
								    $content,
								    $spaceLength,
								   );
							$phpcsFile->addError($error, $comma, "SpaceAfterComma", $data);
						    }
					    }

					if ($tokens[($comma - 1)]["code"] === T_WHITESPACE)
					    {
						$content     = $tokens[($comma - 2)]["content"];
						$spaceLength = strlen($tokens[($comma - 1)]["content"]);
						$error       = _("Expected 0 spaces between") . " \"%s\" " . _("and comma;") . " %s " . _("found");
						$data        = array(
								$content,
								$spaceLength,
							       );
						$phpcsFile->addError($error, $comma, "SpaceBeforeComma", $data);
					    }
				    } //end foreach
			    } //end if
		    } //end if
	    } //end _checkValues()


	/**
	 * Find all the double arrows that reside in this scope.
	 *
	 * @param File  $phpcsFile  The current file being checked.
	 * @param array $tokens     All tokens
	 * @param int   $stackPtr   Current token position
	 * @param int   $arrayStart Array start position
	 * @param int   $arrayEnd   Array end position
	 * @param int   $lastToken  Last token
	 * @param bool  $keyUsed    True if keys are used
	 * @param array $indices    Indices
	 * @param int   $maxLength  Maximum index length
	 *
	 * @return bool
	 *
	 * @internalconst T_COMMA T_COMMA token
	 *
	 * @untranslatable NoKeySpecified
	 * @untranslatable SpaceBeforeComma
	 * @untranslatable KeySpecified
	 * @untranslatable \"%s\"
	 * @untranslatable %s
	 */

	private function _findAllDoubleArrows(File &$phpcsFile, array &$tokens, $stackPtr, $arrayStart,
					      $arrayEnd, &$lastToken, &$keyUsed, array &$indices, &$maxLength)
	    {
		$nextToken  = $phpcsFile->findNext(array(T_DOUBLE_ARROW, T_COMMA, T_ARRAY), ($stackPtr + 1), $arrayEnd);
		$lastComma  = $stackPtr;
		$keyUsed    = false;
		$singleUsed = false;
		$lastToken  = "";
		$indices    = array();
		$maxLength  = 0;
		$found      = true;

		while ($nextToken !== false)
		    {
			$currentEntry = array();

			if ($tokens[$nextToken]["code"] === T_ARRAY)
			    {
				// Let subsequent calls of this test handle nested arrays.
				$indices[] = array("value" => $nextToken);
				$nextToken = $tokens[$tokens[$nextToken]["parenthesis_opener"]]["parenthesis_closer"];
			    }
			else if ($tokens[$nextToken]["code"] === T_COMMA)
			    {
				$stackPtrCount = ((isset($tokens[$stackPtr]["nested_parenthesis"]) === true) ? count($tokens[$stackPtr]["nested_parenthesis"]) : 0);

				// This comma is inside more parenthesis than the ARRAY keyword,
				// then there it is actually a comma used to separate arguments
				// in a function call.
				if (count($tokens[$nextToken]["nested_parenthesis"]) <= ($stackPtrCount + 1))
				    {
					if ($keyUsed === true && $lastToken === T_COMMA)
					    {
						$error = _("No key specified for array entry; first entry specifies key");
						$phpcsFile->addError($error, $nextToken, "NoKeySpecified");
						$found = false;
						break;
					    }

					if ($keyUsed === false)
					    {
						if ($tokens[($nextToken - 1)]["code"] === T_WHITESPACE)
						    {
							$content     = $tokens[($nextToken - 2)]["content"];
							$spaceLength = strlen($tokens[($nextToken - 1)]["content"]);
							$error       = _("Expected 0 spaces between") . " \"%s\" " . _("and comma;") . " %s " . _("found");
							$data        = array(
									$content,
									$spaceLength,
								       );
							$phpcsFile->addError($error, $nextToken, "SpaceBeforeComma", $data);
						    }

						$indices[]  = array("value" => $this->_findValueContent($phpcsFile, $tokens, $nextToken, $arrayStart));
						$singleUsed = true;
					    } //end if

					$lastToken = T_COMMA;
				    } //end if
			    }
			else if ($tokens[$nextToken]["code"] === T_DOUBLE_ARROW)
			    {
				if ($singleUsed === true)
				    {
					$error = _("Key specified for array entry; first entry has no key");
					$phpcsFile->addError($error, $nextToken, "KeySpecified");
					$found = false;
					break;
				    }

				$currentEntry["arrow"] = $nextToken;
				$keyUsed               = true;

				// Find the start of index that uses this double arrow.
				$indexEnd   = $phpcsFile->findPrevious(T_WHITESPACE, ($nextToken - 1), $arrayStart, true);
				$indexStart = $phpcsFile->findPrevious(T_WHITESPACE, $indexEnd, $arrayStart);

				$index = (($indexStart === false) ? $indexEnd : ($indexStart + 1));

				$currentEntry["index"]         = $index;
				$currentEntry["index_content"] = $phpcsFile->getTokensAsString($index, ($indexEnd - $index + 1));

				$indexLength = strlen($currentEntry["index_content"]);
				$maxLength   = (($maxLength < $indexLength) ? $indexLength : $maxLength);

				// Find the value of this index.
				$nextContent           = $phpcsFile->findNext(array(T_WHITESPACE), ($nextToken + 1), $arrayEnd, true);
				$currentEntry["value"] = $nextContent;
				$indices[]             = $currentEntry;
				$lastToken             = T_DOUBLE_ARROW;
			    } //end if

			$nextToken = $phpcsFile->findNext(array(T_DOUBLE_ARROW, T_COMMA, T_ARRAY), ($nextToken + 1), $arrayEnd);
		    } //end while

		return $found;
	    } //end _findAllDoubleArrows()


	/**
	 * Find the value, which will be the first token on the line,
	 * excluding the leading whitespace.
	 *
	 * @param File  $phpcsFile  The current file being checked.
	 * @param array $tokens     All tokens
	 * @param int   $nextToken  Next token position
	 * @param int   $arrayStart Array start position
	 *
	 * @return int Value content position
	 */

	private function _findValueContent(File &$phpcsFile, array &$tokens, $nextToken, $arrayStart)
	    {
		$valueContent = $phpcsFile->findPrevious(Tokens::$emptyTokens, ($nextToken - 1), null, true);
		while ($tokens[$valueContent]["line"] === $tokens[$nextToken]["line"] && $valueContent !== $arrayStart)
		    {
			$valueContent--;
		    }

		return $phpcsFile->findNext(T_WHITESPACE, ($valueContent + 1), $nextToken, true);
	    } //end _findValueContent()


	/**
	 * Check for mutli-line arrays that should be single-line.
	 *
	 * @param File  $phpcsFile    The current file being checked.
	 * @param array $tokens       All tokens
	 * @param int   $stackPtr     Current token position
	 * @param int   $arrayEnd     Array end position
	 * @param int   $lastToken    Last token
	 * @param bool  $keyUsed      True if keys are used
	 * @param array $indices      Indices
	 * @param int   $keywordStart Keyword start
	 * @param int   $maxLength    Maximum index length
	 *
	 * @return void
	 *
	 * @internalconst T_COMMA T_COMMA token
	 *
	 * @untranslatable MultiLineNotAllowed
	 */

	private function _checkSingleValueArray(File &$phpcsFile, array &$tokens, $stackPtr, $arrayEnd,
						$lastToken, $keyUsed, array $indices, $keywordStart, $maxLength)
	    {
		$singleValue = false;

		if (empty($indices) === true)
		    {
			$singleValue = true;
		    }
		else if (count($indices) === 1)
		    {
			if ($lastToken === T_COMMA)
			    {
				// There may be another array value without a comma.
				$exclude     = Tokens::$emptyTokens;
				$exclude[]   = T_COMMA;
				$nextContent = $phpcsFile->findNext($exclude, ($indices[0]["value"] + 1), $arrayEnd, true);
				if ($nextContent === false)
				    {
					$singleValue = true;
				    }
			    }

			if ($singleValue === false && isset($indices[0]["arrow"]) === false)
			    {
				// A single nested array as a value is fine.
				if ($tokens[$indices[0]["value"]]["code"] !== T_ARRAY)
				    {
					$singleValue === true;
				    }
			    }
		    } //end if

		if ($singleValue === true)
		    {
			// Array cannot be empty, so this is a multi-line array with
			// a single value. It should be defined on single line.
			$error = _("Multi-line array contains a single value; use single-line array instead");
			$phpcsFile->addError($error, $stackPtr, "MultiLineNotAllowed");
		    }
		else
		    {
			$this->_checkKeylessArray($phpcsFile, $tokens, $arrayEnd, $keyUsed, $indices, $keywordStart);
			$this->_checkArrayIndentation($phpcsFile, $tokens, $stackPtr, $arrayEnd, $indices, $keywordStart, $maxLength);
		    }
	    } //end _checkSingleValueArray()


	/**
	 * This section checks for arrays that don't specify keys.
	 *
	 * Arrays such as:
	 *  array(
	 *   "aaa",
	 *   "bbb",
	 *   "d",
	 *  );
	 *
	 * @param File  $phpcsFile    The current file being checked.
	 * @param array $tokens       All tokens
	 * @param int   $arrayEnd     Array end position
	 * @param bool  $keyUsed      True if keys are used
	 * @param array $indices      Indices
	 * @param int   $keywordStart Keyword start
	 *
	 * @return void
	 *
	 * @internalconst T_COMMA T_COMMA token
	 *
	 * @untranslatable NoCommaAfterLast
	 * @untranslatable ValueNotAligned
	 * @untranslatable %s
	 */

	private function _checkKeylessArray(File &$phpcsFile, array &$tokens, $arrayEnd, $keyUsed, array $indices, $keywordStart)
	    {
		if ($keyUsed === false && empty($indices) === false)
		    {
			$count     = count($indices);
			$lastIndex = $indices[($count - 1)]["value"];

			$trailingContent = $phpcsFile->findPrevious(T_WHITESPACE, ($arrayEnd - 1), $lastIndex, true);
			if ($tokens[$trailingContent]["code"] !== T_COMMA)
			    {
				$error = _("Comma required after last value in array declaration");
				$phpcsFile->addError($error, $trailingContent, "NoCommaAfterLast");
			    }

			foreach ($indices as $value)
			    {
				if (empty($value["value"]) === false && $tokens[($value["value"] - 1)]["code"] === T_WHITESPACE)
				    {
					// Array was malformed and we couldn't figure out
					// the array value correctly, so we have to ignore it.
					// Other parts of this sniff will correct the error.
					// A whitespace token before this value means that the value
					// was indented and not flush with the opening parenthesis.
					if ($tokens[$value["value"]]["column"] !== ($keywordStart + 1))
					    {
						$error = _("Array value not aligned correctly; expected") . " %s " . _("spaces but found") . " %s";
						$data  = array(
							  ($keywordStart + 1),
							  $tokens[$value["value"]]["column"],
							 );
						$phpcsFile->addError($error, $value["value"], "ValueNotAligned", $data);
					    }
				    }
			    } //end foreach
		    } //end if
	    } //end _checkKeylessArray()


	/**
	 * Below the actual indentation of the array is checked.
	 * Errors will be thrown when a key is not aligned, when
	 * a double arrow is not aligned, and when a value is not
	 * aligned correctly.
	 * If an error is found in one of the above areas, then errors
	 * are not reported for the rest of the line to avoid reporting
	 * spaces and columns incorrectly. Often fixing the first
	 * problem will fix the other 2 anyway.
	 *
	 * For example:
	 *
	 * $a = array(
	 *       "index"  => "2",
	 *      );
	 *
	 * In this array, the double arrow is indented too far, but this
	 * will also cause an error in the value's alignment. If the arrow were
	 * to be moved back one space however, then both errors would be fixed.
	 *
	 * @param File  $phpcsFile    The current file being checked.
	 * @param array $tokens       All tokens
	 * @param int   $stackPtr     Current token position
	 * @param int   $arrayEnd     Array end position
	 * @param array $indices      Indices
	 * @param int   $keywordStart Keyword start
	 * @param int   $maxLength    Maximum index length
	 *
	 * @return void
	 *
	 * @untranslatable FirstValueNoNewline
	 * @untranslatable FirstIndexNoNewline
	 * @untranslatable KeyNotAligned
	 * @untranslatable DoubleArrowNotAligned
	 * @untranslatable ValueNotAligned
	 * @untranslatable %s
	 */

	private function _checkArrayIndentation(File &$phpcsFile, array &$tokens, $stackPtr, $arrayEnd, array $indices, $keywordStart, $maxLength)
	    {
		$numValues = count($indices);

		$indicesStart = ($keywordStart + 1);
		$arrowStart   = ($indicesStart + $maxLength + 1);
		$valueStart   = ($arrowStart + 3);
		foreach ($indices as $index)
		    {
			if (isset($index["index"]) === false)
			    {
				// Array value only.
				if (($tokens[$index["value"]]["line"] === $tokens[$stackPtr]["line"]) && ($numValues > 1))
				    {
					$error = _("The first value in a multi-value array must be on a new line");
					$phpcsFile->addError($error, $stackPtr, "FirstValueNoNewline");
				    }
			    }
			else if (($tokens[$index["index"]]["line"] === $tokens[$stackPtr]["line"]))
			    {
				$error = _("The first index in a multi-value array must be on a new line");
				$phpcsFile->addError($error, $stackPtr, "FirstIndexNoNewline");
			    }
			else if ($tokens[$index["index"]]["column"] !== $indicesStart)
			    {
				$error = _("Array key not aligned correctly; expected") . " %s " . _("spaces but found") . " %s";
				$data  = array(
					  ($indicesStart - 1),
					  ($tokens[$index["index"]]["column"] - 1),
					 );
				$phpcsFile->addError($error, $index["index"], "KeyNotAligned", $data);
			    }
			else if ($tokens[$index["arrow"]]["column"] !== $arrowStart)
			    {
				$expected = ($arrowStart - (strlen($index["index_content"]) + $tokens[$index["index"]]["column"]));
				$found    = ($tokens[$index["arrow"]]["column"] - (strlen($index["index_content"]) + $tokens[$index["index"]]["column"]));

				$error = _("Array double arrow not aligned correctly; expected") . " %s " . _("space(s) but found") . " %s";
				$data  = array(
					  $expected,
					  $found,
					 );
				$phpcsFile->addError($error, $index["arrow"], "DoubleArrowNotAligned", $data);
			    }
			else
			    {
				if ($tokens[$index["value"]]["column"] !== $valueStart)
				    {
					$expected = ($valueStart - (strlen($tokens[$index["arrow"]]["content"]) + $tokens[$index["arrow"]]["column"]));
					$found    = ($tokens[$index["value"]]["column"] - (strlen($tokens[$index["arrow"]]["content"]) + $tokens[$index["arrow"]]["column"]));

					$error = _("Array value not aligned correctly; expected") . " %s " . _("space(s) but found") . " %s";
					$data  = array(
						  $expected,
						  $found,
						 );
					$phpcsFile->addError($error, $index["arrow"], "ValueNotAligned", $data);
				    }

				$this->_checkEndComma($phpcsFile, $tokens, $index, $arrayEnd);
			    } //end if
		    } //end foreach
	    } //end _checkArrayIndentation()


	/**
	 * Check each line ends in a comma.
	 *
	 * @param File  $phpcsFile The current file being checked.
	 * @param array $tokens    All tokens
	 * @param array $index     Current index
	 * @param int   $arrayEnd  Array end position
	 *
	 * @return void
	 *
	 * @internalconst T_OPEN_PARENTHESIS T_OPEN_PARENTHESIS token
	 * @internalconst T_COMMA            T_COMMA token
	 *
	 * @untranslatable NoComma
	 * @untranslatable \"%s\"
	 * @untranslatable %s
	 * @untranslatable SpaceBeforeComma
	 */

	private function _checkEndComma(File &$phpcsFile, array &$tokens, array $index, $arrayEnd)
	    {
		if ($tokens[$index["value"]]["code"] !== T_ARRAY)
		    {
			$nextComma = false;
			for ($i = ($index["value"] + 1); $i < $arrayEnd; $i++)
			    {
				// Skip bracketed statements, like function calls.
				if ($tokens[$i]["code"] === T_OPEN_PARENTHESIS)
				    {
					$i = $tokens[$i]["parenthesis_closer"];
				    }
				else if ($tokens[$i]["code"] === T_COMMA)
				    {
					$nextComma = $i;
					break;
				    }
			    }

			if (($nextComma === false) || ($tokens[$nextComma]["line"] !== $tokens[$index["value"]]["line"]))
			    {
				$error = _("Each line in an array declaration must end in a comma");
				$phpcsFile->addError($error, $index["value"], "NoComma");
			    }

			// Check that there is no space before the comma.
			if ($nextComma !== false && $tokens[($nextComma - 1)]["code"] === T_WHITESPACE)
			    {
				$content     = $tokens[($nextComma - 2)]["content"];
				$spaceLength = strlen($tokens[($nextComma - 1)]["content"]);
				$error       = _("Expected 0 spaces between") . " \"%s\" " . _("and comma;") . " %s " . _("found");
				$data        = array(
						$content,
						$spaceLength,
					       );
				$phpcsFile->addError($error, $nextComma, "SpaceBeforeComma", $data);
			    }
		    } //end if
	    } //end _checkEndComma()


    } //end class

?>