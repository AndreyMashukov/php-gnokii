<?php

/**
 * PHP version 5
 *
 * @package Logics\BuildTools\CodeSniffer\Generic
 */

namespace Logics\BuildTools\CodeSniffer\Generic;

use \Logics\BuildTools\CodeSniffer\File;
use \Logics\BuildTools\CodeSniffer\Sniff;

/**
 * LowerCaseKeywordSniff.
 *
 * Checks that all PHP keywords are lowercase.
 *
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2006-2012 Squiz Pty Ltd (ABN 77 084 670 600)
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 * @version   SVN: $Date: 2016-08-16 23:39:01 +0900 (Tue, 16 Aug 2016) $ $Revision: 26 $
 * @link      $HeadURL: https://open.logics.net.au/buildtools/codesniffer/tags/0.1.3/src/CodeSniffer/Standards/Generic/Sniffs/PHP/LowerCaseKeywordSniff.php $
 */

class LowerCaseKeywordSniff implements Sniff
    {

	/**
	 * Returns an array of tokens this test wants to listen for.
	 *
	 * @return array
	 */

	public function register()
	    {
		return array(
			T_HALT_COMPILER,
			T_ABSTRACT,
			T_ARRAY,
			T_AS,
			T_BREAK,
			T_CALLABLE,
			T_CASE,
			T_CATCH,
			T_CLASS,
			T_CLONE,
			T_CONST,
			T_CONTINUE,
			T_DECLARE,
			T_DEFAULT,
			T_DO,
			T_ECHO,
			T_ELSE,
			T_ELSEIF,
			T_EMPTY,
			T_ENDDECLARE,
			T_ENDFOR,
			T_ENDFOREACH,
			T_ENDIF,
			T_ENDSWITCH,
			T_ENDWHILE,
			T_EVAL,
			T_EXIT,
			T_EXTENDS,
			T_FINAL,
			T_FINALLY,
			T_FOR,
			T_FOREACH,
			T_FUNCTION,
			T_GLOBAL,
			T_GOTO,
			T_IF,
			T_IMPLEMENTS,
			T_INCLUDE,
			T_INCLUDE_ONCE,
			T_INSTANCEOF,
			T_INSTEADOF,
			T_INTERFACE,
			T_ISSET,
			T_LIST,
			T_LOGICAL_AND,
			T_LOGICAL_OR,
			T_LOGICAL_XOR,
			T_NAMESPACE,
			T_NEW,
			T_PRINT,
			T_PRIVATE,
			T_PROTECTED,
			T_PUBLIC,
			T_REQUIRE,
			T_REQUIRE_ONCE,
			T_RETURN,
			T_STATIC,
			T_SWITCH,
			T_THROW,
			T_TRAIT,
			T_TRY,
			T_UNSET,
			T_USE,
			T_VAR,
			T_WHILE,
		       );
	    } //end register()


	/**
	 * Processes this sniff, when one of its tokens is encountered.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $stackPtr  The position of the current token in the stack passed in $tokens.
	 *
	 * @return void
	 *
	 * @untranslatable \"%s\"
	 * @untranslatable Found
	 */

	public function process(File $phpcsFile, $stackPtr)
	    {
		$tokens  = &$phpcsFile->tokens;
		$keyword = $tokens[$stackPtr]["content"];
		if (strtolower($keyword) !== $keyword)
		    {
			$error = _("PHP keywords must be lowercase; expected") . " \"%s\" " . _("but found") . " \"%s\"";
			$data  = array(
				  strtolower($keyword),
				  $keyword,
				 );
			$phpcsFile->addError($error, $stackPtr, "Found", $data);
		    }
	    } //end process()


    } //end class

?>
