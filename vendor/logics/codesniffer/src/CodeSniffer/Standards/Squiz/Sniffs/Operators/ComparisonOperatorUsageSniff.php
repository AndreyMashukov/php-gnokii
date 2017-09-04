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
 * A Sniff to enforce the use of IDENTICAL type operators rather than EQUAL operators.
 *
 * The use of === true is enforced over implicit true statements,
 * for example:
 *
 * <code>
 * if ($a)
 * {
 *     ...
 * }
 * </code>
 *
 * should be:
 *
 * <code>
 * if ($a === true)
 * {
 *     ...
 * }
 * </code>
 *
 * It also enforces the use of === false over ! operators.
 *
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @author    Marc McIntyre <mmcintyre@squiz.net>
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2006-2012 Squiz Pty Ltd (ABN 77 084 670 600)
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 * @version   SVN: $Date: 2016-08-16 23:39:01 +0900 (Tue, 16 Aug 2016) $ $Revision: 26 $
 * @link      $HeadURL: https://open.logics.net.au/buildtools/codesniffer/tags/0.1.3/src/CodeSniffer/Standards/Squiz/Sniffs/Operators/ComparisonOperatorUsageSniff.php $
 *
 * @internalconst T_LESS_THAN    T_LESS_THAN token
 * @internalconst T_GREATER_THAN T_GREATER_THAN token
 * @internalconst T_BOOLEAN_NOT  T_BOOLEAN_NOT token
 *
 * @untranslatable === FALSE
 * @untranslatable PHP
 * @untranslatable JS
 */

class ComparisonOperatorUsageSniff implements Sniff
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
	 * A list of valid comparison operators.
	 *
	 * @var array
	 */
	private static $_validOps = array(
				     T_IS_IDENTICAL,
				     T_IS_NOT_IDENTICAL,
				     T_LESS_THAN,
				     T_GREATER_THAN,
				     T_IS_GREATER_OR_EQUAL,
				     T_IS_SMALLER_OR_EQUAL,
				     T_INSTANCEOF,
				    );

	/**
	 * A list of invalid operators with their alternatives.
	 *
	 * @var array(int => string)
	 */
	private static $_invalidOps = array(
				       "PHP" => array(
						 T_IS_EQUAL     => "===",
						 T_IS_NOT_EQUAL => "!==",
						 T_BOOLEAN_NOT  => "=== FALSE",
						),
				       "JS"  => array(
						 T_IS_EQUAL     => "===",
						 T_IS_NOT_EQUAL => "!==",
						),
				      );

	/**
	 * Registers the token types that this sniff wishes to listen to.
	 *
	 * @return array
	 *
	 * @internalconst T_INLINE_THEN T_INLINE_THEN token
	 */

	public function register()
	    {
		return array(
			T_IF,
			T_INLINE_THEN,
		       );
	    } //end register()


	/**
	 * Process the tokens that this sniff is listening for.
	 *
	 * @param File $phpcsFile The file where the token was found.
	 * @param int  $stackPtr  The position in the stack where the token was found.
	 *
	 * @return void
	 *
	 * @internalconst T_INLINE_THEN         T_INLINE_THEN token
	 * @internalconst T_CLOSE_PARENTHESIS   T_CLOSE_PARENTHESIS token
	 * @internalconst T_SEMICOLON           T_SEMICOLON token
	 * @internalconst T_CLOSE_CURLY_BRACKET T_CLOSE_CURLY_BRACKET token
	 * @internalconst T_OPEN_CURLY_BRACKET  T_OPEN_CURLY_BRACKET token
	 */

	public function process(File $phpcsFile, $stackPtr)
	    {
		$tokens = &$phpcsFile->tokens;

		if ($tokens[$stackPtr]["code"] === T_INLINE_THEN)
		    {
			$end = $phpcsFile->findPrevious(Tokens::$emptyTokens, ($stackPtr - 1), null, true);
			if ($tokens[$end]["code"] !== T_CLOSE_PARENTHESIS)
			    {
				// This inline IF statement does not have its condition
				// bracketed, so we need to guess where it starts.
				for ($i = ($end - 1); $i >= 0; $i--)
				    {
					if ($tokens[$i]["code"] === T_SEMICOLON)
					    {
						// Stop here as we assume it is the end
						// of the previous statement.
						break;
					    }
					else if ($tokens[$i]["code"] === T_OPEN_TAG)
					    {
						// Stop here as this is the start of the file.
						break;
					    }
					else if ($tokens[$i]["code"] === T_CLOSE_CURLY_BRACKET)
					    {
						// Stop if this is the closing brace of
						// a code block.
						if (isset($tokens[$i]["scope_opener"]) === true)
						    {
							break;
						    }
					    }
					else if ($tokens[$i]["code"] === T_OPEN_CURLY_BRACKET)
					    {
						// Stop if this is the opening brace of
						// a code block.
						if (isset($tokens[$i]["scope_closer"]) === true)
						    {
							break;
						    }
					    } //end if
				    } //end for

				$start = $phpcsFile->findNext(Tokens::$emptyTokens, ($i + 1), null, true);
			    }
			else
			    {
				$start = $tokens[$end]["parenthesis_opener"];
			    } //end if
		    }
		else
		    {
			$start = $tokens[$stackPtr]["parenthesis_opener"];
			$end   = $tokens[$stackPtr]["parenthesis_closer"];
		    } //end if

		$this->_checkOps($phpcsFile, $stackPtr, $tokens, $start, $end);
	    } //end process()


	/**
	 * Check ops
	 *
	 * @param File  $phpcsFile The file where the token was found.
	 * @param int   $stackPtr  The position in the stack where the token was found.
	 * @param array $tokens    All tokens
	 * @param int   $start     Start
	 * @param int   $end       End
	 *
	 * @return void
	 *
	 * @untranslatable NotAllowed
	 * @untranslatable JS
	 * @untranslatable ImplicitTrue
	 * @untranslatable %s
	 */

	private function _checkOps(File &$phpcsFile, $stackPtr, array &$tokens, $start, $end)
	    {
		$tokenizer = $phpcsFile->tokenizerType;

		$requiredOps = 0;
		$foundOps    = 0;

		for ($i = $start; $i <= $end; $i++)
		    {
			$type = $tokens[$i]["code"];
			if (in_array($type, array_keys(self::$_invalidOps[$tokenizer])) === true)
			    {
				$error = _("Operator") . " %s " . _("prohibited; use") . " %s " . _("instead");
				$data  = array(
					  $tokens[$i]["content"],
					  self::$_invalidOps[$tokenizer][$type],
					 );
				$phpcsFile->addError($error, $i, "NotAllowed", $data);
				$foundOps++;
			    }
			else if (in_array($type, self::$_validOps) === true)
			    {
				$foundOps++;
			    }

			if ($phpcsFile->tokenizerType !== "JS")
			    {
				if ($tokens[$i]["code"] === T_BOOLEAN_AND || $tokens[$i]["code"] === T_BOOLEAN_OR)
				    {
					$requiredOps++;

					// If we get to here and we have not found the right number of
					// comparison operators, then we must have had an implicit
					// true operation ie. if ($a) instead of the required
					// if ($a === true), so let's add an error.
					if ($requiredOps !== $foundOps)
					    {
						$error = _("Implicit true comparisons prohibited; use === TRUE instead");
						$phpcsFile->addError($error, $stackPtr, "ImplicitTrue");
						$foundOps++;
					    }
				    }
			    } //end if
		    } //end for

		$requiredOps++;

		if ($phpcsFile->tokenizerType !== "JS")
		    {
			if ($foundOps < $requiredOps)
			    {
				$error = _("Implicit true comparisons prohibited; use === TRUE instead");
				$phpcsFile->addError($error, $stackPtr, "ImplicitTrue");
			    }
		    }
	    } //end _checkOps()


    } //end class

?>
