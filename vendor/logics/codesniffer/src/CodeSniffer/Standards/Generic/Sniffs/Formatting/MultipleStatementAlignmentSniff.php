<?php

/**
 * PHP version 5
 *
 * @package Logics\BuildTools\CodeSniffer\Generic
 */

namespace Logics\BuildTools\CodeSniffer\Generic;

use \Logics\BuildTools\CodeSniffer\File;
use \Logics\BuildTools\CodeSniffer\Sniff;
use \Logics\BuildTools\CodeSniffer\Tokens;

/**
 * MultipleStatementAlignmentSniff.
 *
 * Checks alignment of assignments. If there are multiple adjacent assignments,
 * it will check that the equals signs of each assignment are aligned. It will
 * display a warning to advise that the signs should be aligned.
 *
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @author    Marc McIntyre <mmcintyre@squiz.net>
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2006-2012 Squiz Pty Ltd (ABN 77 084 670 600)
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 * @version   SVN: $Date: 2016-08-16 23:39:01 +0900 (Tue, 16 Aug 2016) $ $Revision: 26 $
 * @link      $HeadURL: https://open.logics.net.au/buildtools/codesniffer/tags/0.1.3/src/CodeSniffer/Standards/Generic/Sniffs/Formatting/MultipleStatementAlignmentSniff.php $
 *
 * @untranslatable PHP
 * @untranslatable JS
 */

class MultipleStatementAlignmentSniff implements Sniff
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
	 * If true, an error will be thrown; otherwise a warning.
	 *
	 * @var bool
	 */
	public $error = false;

	/**
	 * The maximum amount of padding before the alignment is ignored.
	 *
	 * If the amount of padding required to align this assignment with the
	 * surrounding assignments exceeds this number, the assignment will be
	 * ignored and no errors or warnings will be thrown.
	 *
	 * @var int
	 */
	public $maxPadding = 1000;

	/**
	 * If true, multi-line assignments are not checked.
	 *
	 * @var int
	 */
	public $ignoreMultiLine = false;

	/**
	 * Returns an array of tokens this test wants to listen for.
	 *
	 * @return array
	 */

	public function register()
	    {
		$tokens = Tokens::$assignmentTokens;
		unset($tokens[T_DOUBLE_ARROW]);
		return $tokens;
	    } //end register()


	/**
	 * Processes this test, when one of its tokens is encountered.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $stackPtr  The position of the current token in the stack passed in $tokens.
	 *
	 * @return void
	 */

	public function process(File $phpcsFile, $stackPtr)
	    {
		$tokens = &$phpcsFile->tokens;

		// Ignore assignments used in a condition, like an IF or FOR.
		if (isset($tokens[$stackPtr]["nested_parenthesis"]) === true)
		    {
			foreach ($tokens[$stackPtr]["nested_parenthesis"] as $start => $end)
			    {
				if (isset($tokens[$start]["parenthesis_owner"]) === true)
				    {
					return;
				    }
			    }
		    }

		$lastAssign = $this->checkAlignment($phpcsFile, $stackPtr);
		return ($lastAssign + 1);
	    } //end process()


	/**
	 * Processes this test, when one of its tokens is encountered.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $stackPtr  The position of the current token in the stack passed in $tokens.
	 *
	 * @return int
	 *
	 * @internalconst T_SEMICOLON T_SEMICOLON token
	 *
	 * @untranslatable Incorrect
	 * @untranslatable NotSame
	 * @untranslatable %s
	 * @untranslatable Warning
	 */

	public function checkAlignment(File &$phpcsFile, $stackPtr)
	    {
		$tokens = &$phpcsFile->tokens;

		$assignments = array();
		$prevAssign  = null;
		$lastLine    = $tokens[$stackPtr]["line"];
		$maxPadding  = null;
		$stopped     = null;
		$lastCode    = $stackPtr;
		$lastSemi    = null;

		$find = Tokens::$assignmentTokens;
		unset($find[T_DOUBLE_ARROW]);

		for ($assign = $stackPtr; $assign < $phpcsFile->numTokens; $assign++)
		    {
			if (isset($find[$tokens[$assign]["code"]]) === false)
			    {
				// A blank line indicates that the assignment block has ended.
				if (isset(Tokens::$emptyTokens[$tokens[$assign]["code"]]) === false)
				    {
					if (($tokens[$assign]["line"] - $tokens[$lastCode]["line"]) > 1)
					    {
						break;
					    }

					$lastCode = $assign;

					if ($tokens[$assign]["code"] === T_SEMICOLON)
					    {
						if ($tokens[$assign]["conditions"] === $tokens[$stackPtr]["conditions"])
						    {
							if ($lastSemi !== null && $prevAssign !== null && $lastSemi > $prevAssign)
							    {
								// This statement did not have an assignment operator in it.
								break;
							    }
							else
							    {
								$lastSemi = $assign;
							    }
						    }
						else
						    {
							// Statement is in a different context, so the block is over.
							break;
						    }
					    }
				    } //end if
			    }
			else if ($assign === $stackPtr || $tokens[$assign]["line"] !== $lastLine)
			    {
				if ($assign !== $stackPtr)
				    {
					// Has to be nested inside the same conditions as the first assignment.
					if ($tokens[$assign]["conditions"] !== $tokens[$stackPtr]["conditions"])
					    {
						break;
					    }

					// Make sure it is not assigned inside a condition (eg. IF, FOR).
					if (isset($tokens[$assign]["nested_parenthesis"]) === true)
					    {
						foreach ($tokens[$assign]["nested_parenthesis"] as $start => $end)
						    {
							if (isset($tokens[$start]["parenthesis_owner"]) === true)
							    {
								break(2);
							    }
						    }
					    }
				    } //end if

				$var = $phpcsFile->findPrevious(Tokens::$emptyTokens, ($assign - 1), null, true);

				// Make sure we wouldn't break our max padding length if we
				// aligned with this statement, or they wouldn't break the max
				// padding length if they aligned with us.
				$varEnd    = $tokens[($var + 1)]["column"];
				$assignLen = $tokens[$assign]["length"];
				if ($assign !== $stackPtr)
				    {
					if (($varEnd + 1) > $assignments[$prevAssign]["assign_col"])
					    {
						$padding      = 1;
						$assignColumn = ($varEnd + 1);
					    }
					else
					    {
						$padding = ($assignments[$prevAssign]["assign_col"] - $varEnd + $assignments[$prevAssign]["assign_len"] - $assignLen);
						if ($padding === 0)
						    {
							$padding = 1;
						    }

						if ($padding > $this->maxPadding)
						    {
							$stopped = $assign;
							break;
						    }

						$assignColumn = ($varEnd + $padding);
					    } //end if

					if (($assignColumn + $assignLen) > ($assignments[$maxPadding]["assign_col"] + $assignments[$maxPadding]["assign_len"]))
					    {
						$newPadding = ($varEnd - $assignments[$maxPadding]["var_end"] + $assignLen - $assignments[$maxPadding]["assign_len"] + 1);
						if ($newPadding > $this->maxPadding)
						    {
							$stopped = $assign;
							break;
						    }
						else
						    {
							// New alignment settings for previous assignments.
							foreach ($assignments as $i => $data)
							    {
								if ($i === $assign)
								    {
									break;
								    }

								$newPadding                    = ($varEnd - $data["var_end"] + $assignLen - $data["assign_len"] + 1);
								$assignments[$i]["expected"]   = $newPadding;
								$assignments[$i]["assign_col"] = ($data["var_end"] + $newPadding);
							    }

							$padding      = 1;
							$assignColumn = ($varEnd + 1);
						    } //end if
					    }
					else if ($padding > $assignments[$maxPadding]["expected"])
					    {
						$maxPadding = $assign;
					    } //end if
				    }
				else
				    {
					$padding      = 1;
					$assignColumn = ($varEnd + 1);
					$maxPadding   = $assign;
				    } //end if

				$found = 0;
				if ($tokens[($var + 1)]["code"] === T_WHITESPACE)
				    {
					$found = $tokens[($var + 1)]["length"];
					if ($found === 0)
					    {
						// This means a newline was found.
						$found = 1;
					    }
				    }

				$assignments[$assign] = array(
							 "var_end"    => $varEnd,
							 "assign_len" => $assignLen,
							 "assign_col" => $assignColumn,
							 "expected"   => $padding,
							 "found"      => $found,
							);

				$lastLine   = $tokens[$assign]["line"];
				$prevAssign = $assign;
			    } //end if
		    } //end for

		if (empty($assignments) === true)
		    {
			return $stackPtr;
		    }
		else
		    {
			foreach ($assignments as $assignment => $data)
			    {
				if ($data["found"] !== $data["expected"])
				    {
					$expectedText = $data["expected"] . " " . (($data["expected"] !== 1) ? _("spaces") : _("space"));

					if ($data["found"] === null)
					    {
						$foundText = _("a new line");
					    }
					else
					    {
						$foundText = $data["found"] . " " . (($data["found"] !== 1) ? _("spaces") : _("space"));
					    }

					if (count($assignments) === 1)
					    {
						$type  = "Incorrect";
						$error = _("Equals sign not aligned correctly; expected") . " %s " . _("but found") . " %s";
					    }
					else
					    {
						$type  = "NotSame";
						$error = _("Equals sign not aligned with surrounding assignments; expected") . " %s " . _("but found") . " %s";
					    }

					$errorData = array(
						      $expectedText,
						      $foundText,
						     );

					if ($this->error === true)
					    {
						$phpcsFile->addError($error, $assignment, $type, $errorData);
					    }
					else
					    {
						$phpcsFile->addWarning($error, $assignment, $type . "Warning", $errorData);
					    }
				    } //end if
			    } //end foreach

			if ($stopped !== null)
			    {
				return $this->checkAlignment($phpcsFile, $stopped);
			    }
			else
			    {
				return $assignment;
			    }
		    } //end if
	    } //end checkAlignment()


	/**
	 * Ignore assignments used in a condition, like an IF or FOR.
	 *
	 * @param array $tokens   All tokens
	 * @param int   $stackPtr Current token position
	 *
	 * @return boolean True if current token is assignment
	 */

	private function _checkAssignment(array &$tokens, $stackPtr)
	    {
		$isAssign = true;

		if (isset($tokens[$stackPtr]["nested_parenthesis"]) === true)
		    {
			foreach ($tokens[$stackPtr]["nested_parenthesis"] as $start => $end)
			    {
				if (isset($tokens[$start]["parenthesis_owner"]) === true)
				    {
					$isAssign = false;
					break;
				    }
			    }
		    }

		return $isAssign;
	    } //end _checkAssignment()


	/**
	 * Collect assignments block
	 *
	 * @param File  $phpcsFile   The file being scanned.
	 * @param array $tokens      All tokens
	 * @param int   $stackPtr    Current token position
	 * @param array $assignments Assignments in this block
	 *
	 * @return void
	 *
	 * @internalconst T_SEMICOLON T_SEMICOLON token
	 */

	private function _collectAssignments(File &$phpcsFile, array &$tokens, $stackPtr, array &$assignments)
	    {
		// Getting here means that this is the last in a block of statements.
		$assignments    = array();
		$assignments[]  = $stackPtr;
		$prevAssignment = $phpcsFile->findPrevious(Tokens::$assignmentTokens, ($stackPtr - 1));
		$lastLine       = $tokens[$stackPtr]["line"];

		while ($prevAssignment !== false)
		    {
			// We are not interested in double arrows as they assign values inside
			// arrays and loops and do not use the same indentation rules.
			if ($tokens[$prevAssignment]["code"] !== T_DOUBLE_ARROW)
			    {
				// The assignment's end token must be on the line directly
				// above the current one to be in the same assignment block.
				$lineEnd = $phpcsFile->findNext(T_SEMICOLON, ($prevAssignment + 1));

				// And the end token must actually belong to this assignment.
				$nextOpener = $phpcsFile->findNext(Tokens::$scopeOpeners, ($prevAssignment + 1));

				if ($nextOpener !== false && $nextOpener < $lineEnd)
				    {
					break;
				    }

				if ($tokens[$lineEnd]["line"] !== ($lastLine - 1))
				    {
					break;
				    }

				// Make sure it is not assigned inside a condition (eg. IF, FOR).
				if (isset($tokens[$prevAssignment]["nested_parenthesis"]) === true)
				    {
					foreach ($tokens[$prevAssignment]["nested_parenthesis"] as $start => $end)
					    {
						if (isset($tokens[$start]["parenthesis_owner"]) === true)
						    {
							break(2);
						    }
					    }
				    }

				$assignments[] = $prevAssignment;
				$lastLine      = $tokens[$prevAssignment]["line"];
			    } //end if

			$prevAssignment = $phpcsFile->findPrevious(Tokens::$assignmentTokens, ($prevAssignment - 1));
		    } //end while
	    } //end _collectAssignments()


	/**
	 * Collect assignments block
	 *
	 * @param File  $phpcsFile           The file being scanned.
	 * @param array $tokens              All tokens
	 * @param array $assignments         Assignments in this block
	 * @param int   $column              Column
	 * @param int   $maxAssignmentLength Maximum assignment length
	 *
	 * @return void
	 */

	private function _calculateAlignment(File &$phpcsFile, array &$tokens, array $assignments, &$column, &$maxAssignmentLength)
	    {
		$assignmentData      = array();
		$maxAssignmentLength = 0;
		$maxVariableLength   = 0;

		foreach ($assignments as $assignment)
		    {
			$prev = $phpcsFile->findPrevious(Tokens::$emptyTokens, ($assignment - 1), null, true);

			$endColumn = $tokens[($prev + 1)]["column"];

			if ($maxVariableLength < $endColumn)
			    {
				$maxVariableLength = $endColumn;
			    }

			if ($maxAssignmentLength < strlen($tokens[$assignment]["content"]))
			    {
				$maxAssignmentLength = strlen($tokens[$assignment]["content"]);
			    }

			$assignmentData[$assignment] = array(
							"variable_length"   => $endColumn,
							"assignment_length" => strlen($tokens[$assignment]["content"]),
						       );
		    } //end foreach

		foreach ($assignmentData as $assignment => $data)
		    {
			if ($data["assignment_length"] === $maxAssignmentLength)
			    {
				if ($data["variable_length"] === $maxVariableLength)
				    {
					// The assignment is the longest possible, so the column that
					// everything has to align to is based on it.
					$column = ($maxVariableLength + 1);
					break;
				    }
				else
				    {
					// The assignment token is the longest out of all of the
					// assignments, but the variable name is not, so the column
					// the start at can go back more to cover the space
					// between the variable name and the assignment operator.
					$column = ($maxVariableLength - ($maxAssignmentLength - 1) + 1);
				    }
			    }
		    }
	    } //end _calculateAlignment()


	/**
	 * Collect assignments block
	 *
	 * @param File  $phpcsFile           The file being scanned.
	 * @param array $tokens              All tokens
	 * @param array $assignments         Assignments in this block
	 * @param int   $column              Column
	 * @param int   $maxAssignmentLength Maximum assignment length
	 *
	 * @return void
	 *
	 * @untranslatable Incorrect
	 * @untranslatable %s
	 * @untranslatable NotSame
	 * @untranslatable Warning
	 */

	private function _determineActualPositions(File &$phpcsFile, array &$tokens, array $assignments, $column, $maxAssignmentLength)
	    {
		// Determine the actual position that each equals sign should be in.
		foreach ($assignments as $assignment)
		    {
			// Actual column takes into account the length of the assignment operator.
			$actualColumn = ($column + $maxAssignmentLength - strlen($tokens[$assignment]["content"]));
			if ($tokens[$assignment]["column"] !== $actualColumn)
			    {
				$prev = $phpcsFile->findPrevious(Tokens::$emptyTokens, ($assignment - 1), null, true);

				$expected = ($actualColumn - $tokens[($prev + 1)]["column"]);

				if ($tokens[$assignment]["line"] !== $tokens[$prev]["line"])
				    {
					// Instead of working out how many spaces there are
					// across new lines, the error message becomes more
					// generic below.
					$found = null;
				    }
				else
				    {
					$found = ($tokens[$assignment]["column"] - $tokens[($prev + 1)]["column"]);
				    }

				// If the expected number of spaces for alignment exceeds the
				// maxPadding rule, we just check for a single space as no
				// alignment is required.
				if ($expected <= $this->maxPadding || $found !== 1)
				    {
					if ($expected > $this->maxPadding && $found !== 1)
					    {
						$expected = 1;
					    }

					// Skip multi-line assignments if required.
					if ($found !== null || $this->ignoreMultiLine === false)
					    {
						$expected .= ($expected === 1) ? " " . _("space") : " " . _("spaces");
						if ($found === null)
						    {
							$found = _("a new line");
						    }
						else
						    {
							$found .= ($found === 1) ? " " . _("space") : " " . _("spaces");
						    }

						if (count($assignments) === 1)
						    {
							$type  = "Incorrect";
							$error = _("Equals sign not aligned correctly; expected") . " %s " . _("but found") . " %s";
						    }
						else
						    {
							$type  = "NotSame";
							$error = _("Equals sign not aligned with surrounding assignments; expected") . " %s " . _("but found") . " %s";
						    }

						$errorData = array(
							      $expected,
							      $found,
							     );

						if ($this->error === true)
						    {
							$phpcsFile->addError($error, $assignment, $type, $errorData);
						    }
						else
						    {
							$phpcsFile->addWarning($error, $assignment, $type . "Warning", $errorData);
						    }
					    } //end if
				    } //end if
			    } //end if
		    } //end foreach
	    } //end _determineActualPositions()


    } //end class

?>