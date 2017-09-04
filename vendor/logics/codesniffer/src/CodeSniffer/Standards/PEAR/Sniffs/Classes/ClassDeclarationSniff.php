<?php

/**
 * PHP version 5
 *
 * @package Logics\BuildTools\CodeSniffer\PEAR
 */

namespace Logics\BuildTools\CodeSniffer\PEAR;

use \Logics\BuildTools\CodeSniffer\File;
use \Logics\BuildTools\CodeSniffer\Sniff;

/**
 * Class Declaration Test.
 *
 * Checks the declaration of the class is correct.
 *
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @author    Marc McIntyre <mmcintyre@squiz.net>
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2006-2012 Squiz Pty Ltd (ABN 77 084 670 600)
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 * @version   SVN: $Date: 2016-08-16 23:39:01 +0900 (Tue, 16 Aug 2016) $ $Revision: 26 $
 * @link      $HeadURL: https://open.logics.net.au/buildtools/codesniffer/tags/0.1.3/src/CodeSniffer/Standards/PEAR/Sniffs/Classes/ClassDeclarationSniff.php $
 */

class ClassDeclarationSniff implements Sniff
    {

	/**
	 * The number of spaces code should be indented.
	 *
	 * @var int
	 */
	public $indent = 4;

	/**
	 * Returns an array of tokens this test wants to listen for.
	 *
	 * @return array
	 */

	public function register()
	    {
		return array(
			T_CLASS,
			T_INTERFACE,
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
	 * @untranslatable MissingBrace
	 * @untranslatable OpenBraceNewLine
	 * @untranslatable OpenBraceWrongLine
	 * @untranslatable OpenBraceNotAlone
	 * @untranslatable %s
	 * @untranslatable SpaceBeforeBrace
	 */

	public function process(File $phpcsFile, $stackPtr)
	    {
		$tokens    = &$phpcsFile->tokens;
		$errorData = array($tokens[$stackPtr]["content"]);

		if (isset($tokens[$stackPtr]["scope_opener"]) === false)
		    {
			$error = _("Possible parse error:") . " %s " . _("missing opening or closing brace");
			$phpcsFile->addWarning($error, $stackPtr, "MissingBrace", $errorData);
		    }
		else
		    {
			$curlyBrace  = $tokens[$stackPtr]["scope_opener"];
			$lastContent = $phpcsFile->findPrevious(T_WHITESPACE, ($curlyBrace - 1), $stackPtr, true);
			$classLine   = $tokens[$lastContent]["line"];
			$braceLine   = $tokens[$curlyBrace]["line"];
			if ($braceLine === $classLine)
			    {
				$error = _("Opening brace of a") . " %s " . _("must be on the line after the definition");
				$phpcsFile->addError($error, $curlyBrace, "OpenBraceNewLine", $errorData);
				return;
			    }
			else if ($braceLine > ($classLine + 1))
			    {
				$error = _("Opening brace of a") . " %s " . _("must be on the line following the") . " %s " . _("declaration; found") . " %s " . _("line(s)");
				$data  = array(
					  $tokens[$stackPtr]["content"],
					  $tokens[$stackPtr]["content"],
					  ($braceLine - $classLine - 1),
					 );
				$phpcsFile->addError($error, $curlyBrace, "OpenBraceWrongLine", $data);
				return;
			    }
			else
			    {
				if ($tokens[($curlyBrace + 1)]["content"] !== $phpcsFile->eolChar)
				    {
					$error = _("Opening") . " %s " . _("brace must be on a line by itself");
					$phpcsFile->addError($error, $curlyBrace, "OpenBraceNotAlone", $errorData);
				    }

				if ($tokens[($curlyBrace - 1)]["code"] === T_WHITESPACE)
				    {
					$prevContent = $tokens[($curlyBrace - 1)]["content"];
					if ($prevContent === $phpcsFile->eolChar)
					    {
						$spaces = 0;
					    }
					else
					    {
						$blankSpace = substr($prevContent, strpos($prevContent, $phpcsFile->eolChar));
						$spaces     = strlen($blankSpace);
					    }

					$expected = ($tokens[$stackPtr]["level"] * $this->indent);
					if ($spaces !== $expected)
					    {
						$error = _("Expected") . " %s " . _("spaces before opening brace;") . " %s " . _("found");
						$data  = array(
							  $expected,
							  $spaces,
							 );
						$phpcsFile->addError($error, $curlyBrace, "SpaceBeforeBrace", $data);
					    }
				    } //end if
			    } //end if
		    } //end if
	    } //end process()


    } //end class

?>