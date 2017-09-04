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
 * ConstantSniff.
 *
 * Throws errors if constant is not declared
 *
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @author    Anastasia Bashkirtseva <anastasia@logics.net.au>
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2006-2012 Squiz Pty Ltd (ABN 77 084 670 600)
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 * @version   SVN: $Date: 2016-08-16 23:39:01 +0900 (Tue, 16 Aug 2016) $ $Revision: 26 $
 * @link      $HeadURL: https://open.logics.net.au/buildtools/codesniffer/tags/0.1.3/src/CodeSniffer/Standards/Beauty/Sniffs/Constants/UndeclaredConstantSniff.php $
 */

class UndeclaredConstantSniff implements Sniff
    {

	/**
	 * Returns an array of tokens this test wants to listen for.
	 *
	 * @return array
	 */

	public function register()
	    {
		return array(T_STRING);
	    } //end register()


	/**
	 * Processes this test, when one of its tokens is encountered.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $stackPtr  The position of the current token in the stack passed in $tokens.
	 *
	 * @return void
	 *
	 * @internalconst T_OPEN_PARENTHESIS T_OPEN_PARENTHESIS token
	 * @internalconst T_COMMA            T_COMMA token
	 *
	 * @untranslatable PHPUnit_MAIN_METHOD
	 */

	public function process(File $phpcsFile, $stackPtr)
	    {
		$tokens    = &$phpcsFile->tokens;
		$constName = $tokens[$stackPtr]["content"];

		if ($phpcsFile->hasCondition($stackPtr, T_START_HEREDOC) === false && $constName !== "PHPUnit_MAIN_METHOD")
		    {
			// If the next non-whitespace token after this token
			// is not an opening parenthesis then it is not a function call.
			$openBracket = $phpcsFile->findNext(T_WHITESPACE, ($stackPtr + 1), null, true);
			if ($tokens[$openBracket]["code"] !== T_OPEN_PARENTHESIS)
			    {
				$functionKeyword = $phpcsFile->findPrevious(
						    array(
						     T_WHITESPACE,
						     T_COMMA,
						     T_COMMENT,
						     T_STRING,
						     T_NS_SEPARATOR,
						    ),
						    ($stackPtr - 1), null, true
						   );

				$declarations = array(
						 T_FUNCTION,
						 T_CLASS,
						 T_INTERFACE,
						 T_TRAIT,
						 T_IMPLEMENTS,
						 T_EXTENDS,
						 T_INSTANCEOF,
						 T_NEW,
						 T_NAMESPACE,
						 T_USE,
						 T_AS,
						 T_GOTO,
						 T_INSTEADOF,
						);

				// This is not a declaration; we may have constants here.
				if (in_array($tokens[$functionKeyword]["code"], $declarations) === false)
				    {
					$this->_lookForConstants($phpcsFile, $tokens, $stackPtr, $functionKeyword, $constName);
				    } //end if
			    } //end if
		    } //end if
	    } //end process()


	/**
	 * Look for constants
	 *
	 * @param File   $phpcsFile       The file being scanned.
	 * @param array  $tokens          All tokens
	 * @param int    $stackPtr        The position of the current token in the stack passed in $tokens
	 * @param int    $functionKeyword The position of function keyword
	 * @param string $constName       Name of current constant
	 *
	 * @return void
	 *
	 * @internalconst T_CLOSE_CURLY_BRACKET T_CLOSE_CURLY_BRACKET token
	 * @internalconst T_OPEN_PARENTHESIS    T_OPEN_PARENTHESIS token
	 * @internalconst T_SEMICOLON           T_SEMICOLON token
	 * @internalconst T_OPEN_CURLY_BRACKET  T_OPEN_CURLY_BRACKET token
	 * @internalconst T_COLON               T_COLON token
	 *
	 * @untranslatable array_merge
	 * @untranslatable UndeclaredConstant
	 */

	private function _lookForConstants(File &$phpcsFile, array &$tokens, $stackPtr, $functionKeyword, $constName)
	    {
		if ($tokens[$functionKeyword]["code"] !== T_CONST)
		    {
			$prevPtr = $phpcsFile->findPrevious(T_WHITESPACE, ($stackPtr - 1), null, true);
			$nextPtr = $phpcsFile->findNext(T_WHITESPACE, ($stackPtr + 1), null, true);

			$nextCode = array(
				     T_DOUBLE_COLON,
				     T_NS_SEPARATOR,
				     T_INSTEADOF,
				     T_VARIABLE,
				    );
			$prevCode = array(
				     T_DOUBLE_COLON,
				     T_OBJECT_OPERATOR,
				     T_OPEN_CURLY_BRACKET,
				     T_CLOSE_CURLY_BRACKET,
				     T_NS_SEPARATOR,
				    );
			// Is this a class/namespace/insteadof name or type hint?
			// Is this a member var name?
			// Is this a variable name, in the form ${varname} ?
			// Is this a namespace name?
			if (in_array($tokens[$nextPtr]["code"], $nextCode) !== true &&
			    $phpcsFile->isReference($nextPtr) === false &&
			    in_array($tokens[$prevPtr]["code"], $prevCode) !== true)
			    {
				$functionToken = $phpcsFile->findPrevious(T_OPEN_TAG, ($stackPtr - 1));
				foreach ($tokens[$stackPtr]["conditions"] as $key => $condition)
				    {
					if ($condition === T_FUNCTION)
					    {
						$functionToken = $key;
						break;
					    }
				    }

				// Is this an instance of declare() or is this a goto label target?
				$prevPtrDeclare  = $phpcsFile->findPrevious(array(T_WHITESPACE, T_OPEN_PARENTHESIS), ($stackPtr - 1), null, true);
				$commentedConsts = $this->_getCommentedConsts($phpcsFile, $functionToken);

				$constants = get_defined_constants(true);
				unset($constants["user"]);
				$constants = call_user_func_array("array_merge", $constants);

				if ($tokens[$prevPtrDeclare]["code"] !== T_DECLARE && ($tokens[$nextPtr]["code"] !== T_COLON ||
				     in_array($tokens[$prevPtr]["code"], array(T_SEMICOLON, T_OPEN_CURLY_BRACKET, T_COLON), true) === false) &&
				     in_array($constName, $commentedConsts["internal"]) !== true && in_array($constName, $commentedConsts["required"]) !== true &&
				     in_array($constName, $commentedConsts["optional"]) !== true && in_array($constName, $commentedConsts["exception"]) !== true &&
				     isset($constants[$constName]) !== true)
				    {
					// This is a real constant.
					$error = _("Constant ") . $constName . _(" should be declared in function comment ") .
					    _("by @requiredconst, @optionalconst, @internalconst or @exceptioncode tags");
					$phpcsFile->addError($error, $stackPtr, "UndeclaredConstant");
				    }
			    } //end if
		    } //end if
	    } //end _lookForConstants()


	/**
	 * Get commented consts
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $stackPtr  The position of the current token in the stack passed in $tokens
	 *
	 * @return array Commented consts
	 *
	 * @internalconst T_DOC_COMMENT_OPEN_TAG   T_DOC_COMMENT_OPEN_TAG token
	 * @internalconst T_DOC_COMMENT_CLOSE_TAG  T_DOC_COMMENT_CLOSE_TAG token
	 * @internalconst T_DOC_COMMENT_WHITESPACE T_DOC_COMMENT_WHITESPACE token
	 * @internalconst T_DOC_COMMENT_STRING     T_DOC_COMMENT_STRING token
	 *
	 * @untranslatable internal
	 * @untranslatable required
	 * @untranslatable optional
	 * @untranslatable @internalconst
	 * @untranslatable @requiredconst
	 * @untranslatable @optionalconst
	 * @untranslatable @exceptioncode
	 */

	private function _getCommentedConsts(File $phpcsFile, $stackPtr)
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
			$commentStart = $phpcsFile->findNext(T_DOC_COMMENT_OPEN_TAG, ($stackPtr + 1));

			while ($commentStart !== false)
			    {
				$commentEnd   = $tokens[$commentStart]["comment_closer"];
				$tags         = array_merge($tags, $tokens[$commentStart]["comment_tags"]);
				$commentStart = $phpcsFile->findNext(T_DOC_COMMENT_OPEN_TAG, ($commentEnd + 1));
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

		return $tags;
	    } //end _getCommentedConsts()


	/**
	 * Add @userconfig error if you have it
	 *
	 * @param array $describedConstants Tokens number of tag and comment constants
	 * @param File  $phpcsFile          The file being scanned.
	 *
	 * @return array
	 *
	 * @untranslatable /^(?P<name>\S*)(?P<exampleWhitespace>\s+)(?P<example>\".*\")(?P<commentWhitespace>\s+)(?P<comment>.*)/i
	 */

	public function errorUserConfig(array $describedConstants, File $phpcsFile)
	    {
		$tagsName = array();
		$tokens   = &$phpcsFile->tokens;
		$pattern  = "/^(?P<name>\S*)(?P<exampleWhitespace>\s+)(?P<example>\".*\")(?P<commentWhitespace>\s+)(?P<comment>.*)/i";
		$key      = 0;
		foreach ($describedConstants as $describedConstant)
		    {
			$key++;
			if (isset($describedConstant["comment"]) === true &&
			    preg_match($pattern, $tokens[$describedConstant["comment"]]["content"], $parsedComment[$key]) > 0)
			    {
				$tagsName[] = $parsedComment[$key]["name"];
			    }
		    } //end foreach

		return $tagsName;
	    } //end errorUserConfig()


	/**
	 * Add @internalconst error if you have it
	 *
	 * @param array $describedConstants Tokens number of tag and comment constants
	 * @param File  $phpcsFile          The file being scanned.
	 *
	 * @return array
	 *
	 * @untranslatable /^(?P<name>\S*)(?P<commentWhitespace>\s+)(?P<comment>.*)/i
	 */

	public function errorInternalConst(array $describedConstants, File $phpcsFile)
	    {
		$tokens   = &$phpcsFile->tokens;
		$tagsName = array();
		$key      = 0;
		$pattern  = "/^(?P<name>\S*)(?P<commentWhitespace>\s+)(?P<comment>.*)/i";
		foreach ($describedConstants as $describedConstant)
		    {
			$key++;
			if (isset($describedConstant["comment"]) === true &&
			    preg_match($pattern, $tokens[$describedConstant["comment"]]["content"], $parsedComment[$key]) > 0)
			    {
				$tagsName[] = $parsedComment[$key]["name"];
			    }
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

				$key++;
			    }
		    } //end foreach

		return $tagsName;
	    } //end errorExceptionCode()


    } //end class

?>
