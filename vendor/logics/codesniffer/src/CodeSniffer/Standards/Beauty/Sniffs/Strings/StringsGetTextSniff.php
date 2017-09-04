<?php

/**
 * PHP version 5.6
 *
 * @package Logics\BuildTools\CodeSniffer\Beauty
 */

namespace Logics\BuildTools\CodeSniffer\Beauty;

use \Logics\BuildTools\CodeSniffer\File;
use \Logics\BuildTools\CodeSniffer\Sniff;

/**
 * StringGetTextSniff
 *
 * @author    Ekaterina Bizimova <kate@logics.net.au>
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   http://matrix.squiz.net/developer/tools/php_cs/licence BSD Licence
 * @version   SVN: $Date: 2016-08-16 23:39:01 +0900 (Tue, 16 Aug 2016) $ $Revision: 26 $
 * @link      $HeadURL: https://open.logics.net.au/buildtools/codesniffer/tags/0.1.3/src/CodeSniffer/Standards/Beauty/Sniffs/Strings/StringsGetTextSniff.php $
 *
 * @untranslatable preg_match
 * @untranslatable preg_match_all
 * @untranslatable preg_replace
 * @untranslatable preg_replace_callback
 * @untranslatable preg_split
 * @untranslatable preg_quote
 * @untranslatable preg_grep
 * @untranslatable define
 * @untranslatable createElement
 */

class StringsGetTextSniff implements Sniff
    {

	/**
	 * Functions which do not require translations
	 *
	 * @var array
	 */
	private $_functions = array(
			       "preg_match",
			       "preg_match_all",
			       "preg_replace",
			       "preg_replace_callback",
			       "preg_split",
			       "preg_quote",
			       "preg_grep",
			       "createElement",
			       "define",
			      );

	/**
	 * Returns an array of tokens this test wants to listen for.
	 *
	 * @return array
	 *
	 * @internalconst T_DOUBLE_QUOTED_STRING T_DOUBLE_QUOTED_STRING token
	 */

	public function register()
	    {
		return array(
			T_CONSTANT_ENCAPSED_STRING,
			T_DOUBLE_QUOTED_STRING,
			T_CLASS,
			T_FUNCTION,
			T_TRAIT,
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
	 * @internalconst T_DOUBLE_QUOTED_STRING T_DOUBLE_QUOTED_STRING token
	 */

	public function process(File $phpcsFile, $stackPtr)
	    {
		$tokens = &$phpcsFile->tokens;
		$code   = $tokens[$stackPtr]["code"];
		if ($code === T_CONSTANT_ENCAPSED_STRING || $code === T_DOUBLE_QUOTED_STRING)
		    {
			$this->_stringProcess($phpcsFile, $stackPtr);
		    }
		else if ($code === T_CLASS)
		    {
			$this->_checkUnusedUntranslatableForClass($phpcsFile, $stackPtr);
		    }
		else if ($code === T_TRAIT)
		    {
			$this->_checkUnusedUntranslatableForTrait($phpcsFile, $stackPtr);
		    }
		else if ($code === T_FUNCTION)
		    {
			$this->_checkUnusedUntranslatableForFunction($phpcsFile, $stackPtr);
		    } //end if
	    } //end process()


	/**
	 * Processes string tokens.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $stackPtr  The position of the current token in the stack passed in $tokens.
	 *
	 * @return void
	 */

	private function _stringProcess(File $phpcsFile, $stackPtr)
	    {
		$tokens = &$phpcsFile->tokens;
		$s      = $tokens[$stackPtr]["content"];
		$s      = $this->_ifTranslatable($s, $phpcsFile, $stackPtr);

		if ($s !== false)
		    {
			if ($this->_ifGetText($phpcsFile, $stackPtr) === false)
			    {
				if ($this->_checkDoNotTranslateInClass($phpcsFile, $stackPtr) === false)
				    {
					if ($this->_checkUntranslatableInClass($s, $phpcsFile, $stackPtr) === false)
					    {
						$this->_checkTraitAndFunctions($s, $phpcsFile, $stackPtr);
					    } //end if
				    } //end if
			    } //end if
		    } //end if
	    } //end _stringProcess()


	/**
	 * Processes this test, when one of its tokens is encountered.
	 *
	 * @param string $s         String
	 * @param File   $phpcsFile The file being scanned.
	 * @param int    $stackPtr  The position of the current token in the stack passed in $tokens.
	 *
	 * @return mixed
	 *
	 * @donottranslate
	 */

	private function _ifTranslatable($s, File $phpcsFile, $stackPtr)
	    {
		$tokens = &$phpcsFile->tokens;
		if (preg_match("/^['\"](?P<string>.*)['\"]$/", $s, $m) > 0)
		    {
			$s = $m["string"];
			if (preg_match("/(\w+\/)*\w*\.(php|xsd|tpl|xml|wsdl|txt|png|jpg|gif|bmp)$/", $s) === 0 &&
			    preg_match(
			    "/^(SELECT\s+|UPDATE\s+|INSERT\s+|DELETE\s+|CREATE\s+|DROP\s+|SHOW TABLES\s+|VALUES\s*\(|FROM\s+|WHERE\s+|START TRANSACTION$|COMMIT$)/", $s) === 0 &&
			    preg_match("/[[:alpha:]]+/", $s) > 0 &&
			    preg_match("/ENGINE=/", $s) === 0 &&
			    preg_match("/^(\\\\n|\\\\r|\\\\t)$/", $s) === 0 &&
			    preg_match("/`\w+`/", $s) === 0 &&
			    preg_match("/^\w+:\/\//", $s) === 0 &&
			    preg_match("/^urn:\w+/", $s) === 0 &&
			    preg_match("/^cac:\w+/", $s) === 0 &&
			    preg_match("/^cbc:\w+/", $s) === 0 &&
			    preg_match("/^xmlns/", $s) === 0 &&
			    $this->_ifFunction($phpcsFile, $stackPtr) === false &&
			    $this->_ifArrayIndex($phpcsFile, $stackPtr) === false &&
			    $tokens[($stackPtr - 2)]["content"] !== "case")
			    {
				return $s;
			    }
			else
			    {
				return false;
			    }
		    } //end if
	    } //end _ifTranslatable()


	/**
	 * Processes this test, when one of its tokens is encountered.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $stackPtr  The position of the current token in the stack passed in $tokens.
	 *
	 * @return bool
	 *
	 * @internalconst T_COMMA            T_COMMA token
	 * @internalconst T_OPEN_PARENTHESIS T_OPEN_PARENTHESIS token
	 */

	private function _ifFunction(File $phpcsFile, $stackPtr)
	    {
		$tokens      = &$phpcsFile->tokens;
		$comma       = $phpcsFile->findPrevious(array(T_COMMA), ($stackPtr - 1));
		$openBracket = $phpcsFile->findPrevious(array(T_OPEN_PARENTHESIS), ($stackPtr - 1));
		$function    = false;
		if ($openBracket !== false)
		    {
			if ($comma === false || $comma < $openBracket)
			    {
				if (in_array($tokens[($openBracket - 1)]["content"], $this->_functions) === true)
				    {
					$function = true;
				    }
			    } //end if
		    } //end if

		return $function;
	    } //end _ifFunction()


	/**
	 * Processes this test, when one of its tokens is encountered.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $stackPtr  The position of the current token in the stack passed in $tokens.
	 *
	 * @return bool
	 *
	 * @internalconst T_STRING_CONCAT        T_STRING_CONCAT token
	 * @internalconst T_OPEN_SQUARE_BRACKET  T_OPEN_SQUARE_BRACKET token
	 * @internalconst T_CLOSE_SQUARE_BRACKET T_CLOSE_SQUARE_BRACKET token
	 */

	private function _ifArrayIndex(File $phpcsFile, $stackPtr)
	    {
		$tokens       = &$phpcsFile->tokens;
		$arrayIndex   = false;
		$concat       = $phpcsFile->findPrevious(array(T_STRING_CONCAT), ($stackPtr - 1));
		$openBracket  = $phpcsFile->findPrevious(array(T_OPEN_SQUARE_BRACKET), ($stackPtr - 1));
		$closeBracket = $phpcsFile->findPrevious(array(T_CLOSE_SQUARE_BRACKET), ($stackPtr - 1));
		if ($tokens[($stackPtr - 1)]["content"] === "[" ||
		$tokens[($stackPtr + 2)]["content"] === "=>")
		    {
			$arrayIndex = true;
		    }
		else if ($concat !== 0 && $openBracket !== 0)
		    {
			if ($concat > $openBracket && $openBracket > $closeBracket)
			    {
				$arrayIndex = true;
			    }
		    }

		return $arrayIndex;
	    } //end _ifArrayIndex()


	/**
	 * Processes this test, when one of its tokens is encountered.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $stackPtr  The position of the current token in the stack passed in $tokens.
	 *
	 * @return bool
	 *
	 * @internalconst T_STRING_CONCAT        T_STRING_CONCAT token
	 * @internalconst T_DOUBLE_QUOTED_STRING T_DOUBLE_QUOTED_STRING token
	 *
	 * @untranslatable gettext
	 */

	private function _ifGetText(File $phpcsFile, $stackPtr)
	    {
		$tokens = &$phpcsFile->tokens;

		do
		    {
			$stackPtr--;
		    }

		while ($stackPtr > 0 && in_array($tokens[$stackPtr]["code"], array(T_STRING_CONCAT, T_WHITESPACE, T_CONSTANT_ENCAPSED_STRING, T_DOUBLE_QUOTED_STRING)));

		return (($tokens[($stackPtr - 1)]["content"] === "gettext" || $tokens[($stackPtr - 1)]["content"] === "_") &&
			$tokens[($stackPtr)]["content"] === "(");
	    } //end _ifGetText()


	/**
	 * Check functions and traits.
	 *
	 * @param string $s         String token
	 * @param File   $phpcsFile The file being scanned.
	 * @param int    $stackPtr  The position of the current token in the stack passed in $tokens.
	 *
	 * @return void
	 *
	 * @donottranslate
	 */

	private function _checkTraitAndFunctions($s, File $phpcsFile, $stackPtr)
	    {
		if ($this->_checkDoNotTranslateInTrait($phpcsFile, $stackPtr) === false)
		    {
			if ($this->_checkUntranslatableInTrait($s, $phpcsFile, $stackPtr) === false)
			    {
				if ($this->_checkDoNotTranslateInFunction($phpcsFile, $stackPtr) === false)
				    {
					if ($this->_checkUntranslatableInFunction($s, $phpcsFile, $stackPtr) === false)
					    {
						$phpcsFile->addError(_("Use @untranslatable in function comment if this phrase is untranslatable. ") .
								     _("Please specify if translatable: ") .
								     trim($s), $stackPtr, "GetText");
					    }
				    } //end if
			    } //end if
		    } //end if
	    } //end _checkTraitAndFunctions()


	/**
	 * Check doNotTranslate strings in function.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $stackPtr  The position of the current token in the stack passed in $tokens.
	 *
	 * @return bool
	 */

	private function _checkDoNotTranslateInFunction(File $phpcsFile, $stackPtr)
	    {
		$tags = $this->_getFunctionCommentParser($phpcsFile, $stackPtr);
		return (count($tags["@donottranslate"]) > 0);
	    } //end _checkDoNotTranslateInFunction()


	/**
	 * Check untranslaitable strings in function.
	 *
	 * @param string $s         Current string
	 * @param File   $phpcsFile The file being scanned.
	 * @param int    $stackPtr  The position of the current token in the stack passed in $tokens.
	 *
	 * @return bool
	 */

	private function _checkUntranslatableInFunction($s, File $phpcsFile, $stackPtr)
	    {
		$tags               = $this->_getFunctionCommentParser($phpcsFile, $stackPtr);
		$untranslatable     = $tags["@untranslatable"];
		$untranslatableFlag = false;
		foreach ($untranslatable as $element)
		    {
			$content = $element["comment"];
			if (trim($content) === trim($s))
			    {
				$untranslatableFlag = true;
				break;
			    }
		    }

		return $untranslatableFlag;
	    } //end _checkUntranslatableInFunction()


	/**
	 * Get function comment parser.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $stackPtr  The position of the current token in the stack passed in $tokens.
	 *
	 * @return array
	 *
	 * @internalconst T_DOC_COMMENT_CLOSE_TAG  T_DOC_COMMENT_CLOSE_TAG token
	 * @internalconst T_DOC_COMMENT_WHITESPACE T_DOC_COMMENT_WHITESPACE token
	 * @internalconst T_DOC_COMMENT_STRING     T_DOC_COMMENT_STRING token
	 *
	 * @untranslatable @untranslatable
	 * @untranslatable @donottranslate
	 */

	private function _getFunctionCommentParser(File $phpcsFile, $stackPtr)
	    {
		$tags               = array();
		$tokens             = &$phpcsFile->tokens;
		$commentEnd         = $phpcsFile->findPrevious(T_DOC_COMMENT_CLOSE_TAG, ($stackPtr - 2));
		$untranslatableTags = array(
				       "@untranslatable" => array(),
				       "@donottranslate" => array(),
				      );
		if ($tokens[$commentEnd]["code"] === T_DOC_COMMENT_CLOSE_TAG)
		    {
			$commentStart = $tokens[$commentEnd]["comment_opener"];
			$tags         = $tokens[$commentStart]["comment_tags"];
		    }

		foreach ($tags as $tag)
		    {
			if ($tokens[$tag]["content"] === "@untranslatable" || $tokens[$tag]["content"] === "@donottranslate")
			    {
				$comment = $phpcsFile->findNext(T_DOC_COMMENT_WHITESPACE, ($tag + 1), null, true);
				$content = "";
				if ($tokens[$comment]["code"] === T_DOC_COMMENT_STRING)
				    {
					$content = $tokens[$comment]["content"];
				    }

				$untranslatableTags[$tokens[$tag]["content"]][] = array(
										   "tag"     => $tag,
										   "comment" => $content,
										  );
			    } //end if
		    } //end foreach

		return $untranslatableTags;
	    }  //end _getFunctionCommentParser()


	/**
	 * Check doNotTranslate strings in class.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $stackPtr  The position of the current token in the stack passed in $tokens.
	 *
	 * @return bool
	 */

	private function _checkDoNotTranslateInClass(File $phpcsFile, $stackPtr)
	    {
		$tags = $this->_getClassCommentParser($phpcsFile, $stackPtr);
		return (count($tags["@donottranslate"]) > 0);
	    } //end _checkDoNotTranslateInClass()


	/**
	 * Check untranslatable elements in class.
	 *
	 * @param string $s         Current string
	 * @param File   $phpcsFile The file being scanned.
	 * @param int    $stackPtr  The position of the current token in the stack passed in $tokens.
	 *
	 * @return bool
	 */

	private function _checkUntranslatableInClass($s, File $phpcsFile, $stackPtr)
	    {
		$tags               = $this->_getClassCommentParser($phpcsFile, $stackPtr);
		$untranslatableFlag = false;
		foreach ($tags["@untranslatable"] as $element)
		    {
			if (trim($element["comment"]) === trim($s))
			    {
				$untranslatableFlag = true;
				break;
			    }
		    }

		return $untranslatableFlag;
	    } //end _checkUntranslatableInClass()


	/**
	 * Get class comment parser.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $stackPtr  The position of the current token in the stack passed in $tokens.
	 *
	 * @return array
	 *
	 * @internalconst T_DOC_COMMENT_CLOSE_TAG  T_DOC_COMMENT_CLOSE_TAG token
	 * @internalconst T_DOC_COMMENT_WHITESPACE T_DOC_COMMENT_WHITESPACE token
	 * @internalconst T_DOC_COMMENT_STRING     T_DOC_COMMENT_STRING token
	 *
	 * @untranslatable @untranslatable
	 * @untranslatable @donottranslate
	 */

	private function _getClassCommentParser(File $phpcsFile, $stackPtr)
	    {
		$current = $phpcsFile->findPrevious(T_CLASS, ($stackPtr - 1));

		$find = array(
			 T_ABSTRACT,
			 T_WHITESPACE,
			 T_FINAL,
			);

		$tags               = array();
		$tokens             = &$phpcsFile->tokens;
		$commentEnd         = $phpcsFile->findPrevious($find, ($current - 1), null, true);
		$untranslatableTags = array(
				       "@untranslatable" => array(),
				       "@donottranslate" => array(),
				      );
		if ($tokens[$commentEnd]["code"] === T_DOC_COMMENT_CLOSE_TAG)
		    {
			$commentStart = $tokens[$commentEnd]["comment_opener"];
			$tags         = $tokens[$commentStart]["comment_tags"];
		    }

		foreach ($tags as $tag)
		    {
			if ($tokens[$tag]["content"] === "@untranslatable" || $tokens[$tag]["content"] === "@donottranslate")
			    {
				$comment = $phpcsFile->findNext(T_DOC_COMMENT_WHITESPACE, ($tag + 1), null, true);
				$content = "";
				if ($tokens[$comment]["code"] === T_DOC_COMMENT_STRING)
				    {
					$content = $tokens[$comment]["content"];
				    }

				$untranslatableTags[$tokens[$tag]["content"]][] = array(
										   "tag"     => $tag,
										   "comment" => $content,
										  );
			    } //end if
		    } //end foreach

		return $untranslatableTags;
	    } //end _getClassCommentParser()


	/**
	 * Check doNotTranslate strings in trait.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $stackPtr  The position of the current token in the stack passed in $tokens.
	 *
	 * @return bool
	 */

	private function _checkDoNotTranslateInTrait(File $phpcsFile, $stackPtr)
	    {
		$tags = $this->_getTraitCommentParser($phpcsFile, $stackPtr);
		return (count($tags["@donottranslate"]) > 0);
	    } //end _checkDoNotTranslateInTrait()


	/**
	 * Check untranslatable elements in trait.
	 *
	 * @param string $s         Current string
	 * @param File   $phpcsFile The file being scanned.
	 * @param int    $stackPtr  The position of the current token in the stack passed in $tokens.
	 *
	 * @return bool
	 */

	private function _checkUntranslatableInTrait($s, File $phpcsFile, $stackPtr)
	    {
		$tags               = $this->_getTraitCommentParser($phpcsFile, $stackPtr);
		$untranslatable     = $tags["@untranslatable"];
		$untranslatableFlag = false;
		foreach ($untranslatable as $element)
		    {
			if (trim($element["comment"]) === trim($s))
			    {
				$untranslatableFlag = true;
				break;
			    }
		    }

		return $untranslatableFlag;
	    } //end _checkUntranslatableInTrait()


	/**
	 * Get trait comment parser.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $stackPtr  The position of the current token in the stack passed in $tokens.
	 *
	 * @return array
	 *
	 * @internalconst T_DOC_COMMENT_CLOSE_TAG  T_DOC_COMMENT_CLOSE_TAG token
	 * @internalconst T_DOC_COMMENT_WHITESPACE T_DOC_COMMENT_WHITESPACE token
	 * @internalconst T_DOC_COMMENT_STRING     T_DOC_COMMENT_STRING token
	 *
	 * @untranslatable @untranslatable
	 * @untranslatable @donottranslate
	 */

	private function _getTraitCommentParser(File $phpcsFile, $stackPtr)
	    {
		$current            = $phpcsFile->findPrevious(T_TRAIT, ($stackPtr - 1));
		$tags               = array();
		$tokens             = &$phpcsFile->tokens;
		$untranslatableTags = array(
				       "@untranslatable" => array(),
				       "@donottranslate" => array(),
				      );
		if ($current !== false)
		    {
			$commentEnd = $phpcsFile->findPrevious(T_DOC_COMMENT_CLOSE_TAG, ($current - 1));
			if ($tokens[$commentEnd]["code"] !== false)
			    {
				$commentStart = $tokens[$commentEnd]["comment_opener"];
				if (isset($tokens[$commentStart]["comment_tags"]) === true)
				    {
					$tags = $tokens[$commentStart]["comment_tags"];
				    }
			    }

			foreach ($tags as $tag)
			    {
				if ($tokens[$tag]["content"] === "@untranslatable" || $tokens[$tag]["content"] === "@donottranslate")
				    {
					$comment = $phpcsFile->findNext(T_DOC_COMMENT_WHITESPACE, ($tag + 1), null, true);
					$content = "";
					if ($tokens[$comment]["code"] === T_DOC_COMMENT_STRING)
					    {
						$content = $tokens[$comment]["content"];
					    }

					$untranslatableTags[$tokens[$tag]["content"]][] = array(
											   "tag"     => $tag,
											   "comment" => $content,
											  );
				    } //end if
			    } //end foreach
		    } //end if

		return $untranslatableTags;
	    } //end _getTraitCommentParser()


	/**
	 * Check unused untranslaitable elements for class.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $stackPtr  The position of the current token in the stack passed in $tokens.
	 *
	 * @return void
	 *
	 * @donottranslate
	 */

	private function _checkUnusedUntranslatableForClass(File $phpcsFile, $stackPtr)
	    {
		$tokens = &$phpcsFile->tokens;
		$tags   = $this->_getClassCommentParser($phpcsFile, ($stackPtr + 1));

		$untranslatable = $tags["@untranslatable"];
		$doNotTranslate = $tags["@donottranslate"];

		$equalUntranslatablePos = 0;
		foreach ($untranslatable as $element)
		    {
			$content                = $element["comment"];
			$equalUntranslatablePos = $this->_checkEqualUntranslatable($phpcsFile, $content, $untranslatable, $equalUntranslatablePos);

			if (count($doNotTranslate) > 0)
			    {
				$phpcsFile->addError(_("Do not use @untranslatable and @donottranslate at the same time: "), $element["tag"], "GetText");
			    }

			$this->_processEmptyUntranslatable($element, $phpcsFile);

			$unusedUntranslatable = $this->_getUnusedUntranslatableInClass($content, $phpcsFile, $stackPtr);
			if ($unusedUntranslatable === true)
			    {
				foreach ($untranslatable as $commentPointer)
				    {
					$contentComment = $commentPointer["comment"];
					if ($contentComment === $content)
					    {
						$phpcsFile->addError(_("Unused untranslatable for class: ") . trim($content), $commentPointer["tag"], "GetText");
						break;
					    }
				    }
			    }
		    } //end foreach
	    } //end _checkUnusedUntranslatableForClass()


	/**
	 * Check unused untranslaitable elements for trait.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $stackPtr  The position of the current token in the stack passed in $tokens.
	 *
	 * @return void
	 *
	 * @donottranslate
	 */

	private function _checkUnusedUntranslatableForTrait(File $phpcsFile, $stackPtr)
	    {
		$tokens         = &$phpcsFile->tokens;
		$tags           = $this->_getTraitCommentParser($phpcsFile, ($stackPtr + 1));
		$untranslatable = $tags["@untranslatable"];
		$doNotTranslate = $tags["@donottranslate"];

		$equalUntranslatablePos = 0;
		foreach ($untranslatable as $element)
		    {
			$content                = $element["comment"];
			$equalUntranslatablePos = $this->_checkEqualUntranslatable($phpcsFile, $content, $untranslatable, $equalUntranslatablePos);

			if (count($doNotTranslate) > 0)
			    {
				$phpcsFile->addError(_("Do not use @untranslatable and @donottranslate at the same time: "), $element["tag"], "GetText");
			    }

			$this->_processEmptyUntranslatable($element, $phpcsFile);

			$unusedUntranslatable = $this->_getUnusedUntranslatableInClass($content, $phpcsFile, $stackPtr);

			if ($unusedUntranslatable === true)
			    {
				foreach ($untranslatable as $commentPointer)
				    {
					if (isset($commentPointer["comment"]) === true && $commentPointer["comment"] === $content)
					    {
						$phpcsFile->addError(_("Unused untranslatable for trait: ") . trim($content), $commentPointer["tag"], "GetText");
						break;
					    }
				    }
			    }
		    } //end foreach
	    } //end _checkUnusedUntranslatableForTrait()


	/**
	 * Check unused untranslatable strings for function.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $stackPtr  The position of the current token in the stack passed in $tokens.
	 *
	 * @return void
	 *
	 * @donottranslate
	 */

	private function _checkUnusedUntranslatableForFunction(File $phpcsFile, $stackPtr)
	    {
		$tokens = &$phpcsFile->tokens;

		$tags           = $this->_getFunctionCommentParser($phpcsFile, $stackPtr);
		$untranslatable = $tags["@untranslatable"];
		$doNotTranslate = $tags["@donottranslate"];

		$tags                  = $this->_getClassCommentParser($phpcsFile, $stackPtr);
		$doNotTranslateInClass = $tags["@donottranslate"];
		$tags                  = $this->_getTraitCommentParser($phpcsFile, $stackPtr);
		$doNotTranslateInTrait = $tags["@donottranslate"];

		if (count($doNotTranslate) > 0 && (count($doNotTranslateInClass) > 0 || count($doNotTranslateInTrait) > 0))
		    {
			$this->_ifDoNotTranslateInClassAndInFunction($doNotTranslate, $phpcsFile);
		    }

		$functionStackPtr = $stackPtr;

		$equalUntranslatablePos = 0;
		foreach ($untranslatable as $element)
		    {
			$content                = $element["comment"];
			$equalUntranslatablePos = $this->_checkEqualUntranslatable($phpcsFile, $content, $untranslatable, $equalUntranslatablePos);

			if (count($doNotTranslate) > 0)
			    {
				$phpcsFile->addError(_("Do not use @untranslatable and @donottranslate at the same time: "), $element["tag"], "GetText");
			    }

			$this->_processEmptyUntranslatable($element, $phpcsFile);

			$unusedUntranslatable = $this->_getUnusedUntranslatableInFunction($content, $phpcsFile, $stackPtr);

			if ($unusedUntranslatable === true)
			    {
				foreach ($untranslatable as $commentPointer)
				    {
					if ($commentPointer["comment"] !== "" && $commentPointer["comment"] === $content)
					    {
						$phpcsFile->addError(_("Unused untranslatable for function: ") . trim($content), $commentPointer["tag"], "GetText");
						break;
					    }
				    }
			    }
		    } //end foreach
	    } //end _checkUnusedUntranslatableForFunction()


	/**
	 * Check equal untranslatable in function.
	 *
	 * @param File   $phpcsFile              The file being scanned.
	 * @param string $content                Content of untranslatable element.
	 * @param array  $untranslatable         Array of untranslatable elements.
	 * @param int    $equalUntranslatablePos Previous equal untranslatable position
	 *
	 * @return int
	 *
	 * @untranslatable GetText
	 */

	private function _checkEqualUntranslatable(File $phpcsFile, $content, array $untranslatable, $equalUntranslatablePos)
	    {
		$equalUntranslatable = 0;
		foreach ($untranslatable as $element)
		    {
			if ($content === $element["comment"])
			    {
				if ($equalUntranslatablePos !== $element["tag"])
				    {
					$errorPos = $element["tag"];
				    }

				$equalUntranslatable++;
			    }
		    }

		if ($equalUntranslatable > 1)
		    {
			if ($equalUntranslatablePos !== $errorPos)
			    {
				$phpcsFile->addError(_("Duplicate untranslatable"), $errorPos, "GetText");
				$equalUntranslatablePos = $errorPos;
			    }
		    }

		return $equalUntranslatablePos;
	    } //end _checkEqualUntranslatable()


	/**
	 * Get unused untranslaitable elements in class.
	 *
	 * @param string $element   Untranslatable element.
	 * @param File   $phpcsFile The file being scanned.
	 * @param int    $stackPtr  The position of the current token in the stack passed in $tokens.
	 *
	 * @return bool
	 */

	private function _getUnusedUntranslatableInClass($element, File $phpcsFile, $stackPtr)
	    {
		$tokens               = &$phpcsFile->tokens;
		$unusedUntranslatable = true;
		while ($stackPtr < $phpcsFile->numTokens)
		    {
			$s = $tokens[$stackPtr]["content"];
			$s = $this->_ifTranslatable($s, $phpcsFile, $stackPtr);
			if ($s !== false)
			    {
				if ($this->_ifGetText($phpcsFile, $stackPtr) === false)
				    {
					if (trim($element) === trim($s))
					    {
						$unusedUntranslatable = false;
						break;
					    }
				    }
			    }

			$stackPtr++;
		    }

		return $unusedUntranslatable;
	    } //end _getUnusedUntranslatableInClass()


	/**
	 * Get error string.
	 *
	 * @param array $element   Array of untranslatable tokens
	 * @param File  $phpcsFile The file being scanned.
	 *
	 * @return void
	 *
	 * @untranslatable GetText
	 */

	private function _processEmptyUntranslatable(array $element, File $phpcsFile)
	    {
		if ($element["comment"] === "")
		    {
			$phpcsFile->addError(_("Empty untranslatable"), $element["tag"], "GetText");
		    }
	    } //end _processEmptyUntranslatable()


	/**
	 * Check untranslaitable strings in function.
	 *
	 * @param array $donottranslate Array of donottranslate tokens
	 * @param File  $phpcsFile      The file being scanned.
	 *
	 * @return void
	 *
	 * @untranslatable GetText
	 */

	private function _ifDoNotTranslateInClassAndInFunction(array $donottranslate, File $phpcsFile)
	    {
		foreach ($donottranslate as $element)
		    {
			$phpcsFile->addError(_("Do not use @donottranslate in function and in class at the same time: "), $element["tag"], "GetText");
			break;
		    }
	    } //end _ifDoNotTranslateInClassAndInFunction()


	/**
	 * Get unused untranslatable strings in function.
	 *
	 * @param string $element   Untranslatable element.
	 * @param File   $phpcsFile The file being scanned.
	 * @param int    $stackPtr  The position of the current token in the stack passed in $tokens.
	 *
	 * @return bool
	 *
	 * @internalconst T_DOUBLE_QUOTED_STRING T_DOUBLE_QUOTED_STRING token
	 */

	private function _getUnusedUntranslatableInFunction($element, File $phpcsFile, $stackPtr)
	    {
		$tokens               = &$phpcsFile->tokens;
		$unusedUntranslatable = true;

		$end = $tokens[$stackPtr]["scope_closer"];

		for ($i = $stackPtr; $i < $end; $i++)
		    {
			if ($tokens[$i]["code"] === T_CONSTANT_ENCAPSED_STRING || $tokens[$i]["code"] === T_DOUBLE_QUOTED_STRING)
			    {
				$s = $this->_ifTranslatable($tokens[$i]["content"], $phpcsFile, $stackPtr);
				if ($s !== false)
				    {
					if ($this->_ifGetText($phpcsFile, $i) === false)
					    {
						if (trim($element) === trim($s))
						    {
							$unusedUntranslatable = false;
							break;
						    }
					    }
				    }
			    }
		    }

		return $unusedUntranslatable;
	    } //end _getUnusedUntranslatableInFunction()


    } //end class

?>