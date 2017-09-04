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
 * Class Declaration Test.
 *
 * Checks the declaration of the class is correct.
 *
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @author    Marc McIntyre <mmcintyre@squiz.net>
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2006-2012 Squiz Pty Ltd (ABN 77 084 670 600)
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   http://matrix.squiz.net/developer/tools/php_cs/licence BSD Licence
 * @version   SVN: $Date: 2016-08-16 23:39:01 +0900 (Tue, 16 Aug 2016) $ $Revision: 26 $
 * @link      $HeadURL: https://open.logics.net.au/buildtools/codesniffer/tags/0.1.3/src/CodeSniffer/Standards/Beauty/Sniffs/Classes/ClassDeclarationSniff.php $
 */

class ClassDeclarationSniff implements Sniff
    {

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
	 */

	public function process(File $phpcsFile, $stackPtr)
	    {
		$tokens = &$phpcsFile->tokens;

		if (isset($tokens[$stackPtr]["scope_opener"]) === false)
		    {
			$error  = _("Possible parse error:") . " ";
			$error .= $tokens[$stackPtr]["content"];
			$error .= " " . _("missing opening or closing brace");
			$phpcsFile->addWarning($error, $stackPtr);
			return;
		    }
		else
		    {
			$curlyBrace  = $tokens[$stackPtr]["scope_opener"];
			$lastContent = $phpcsFile->findPrevious(T_WHITESPACE, ($curlyBrace - 1), $stackPtr, true);
			$classLine   = $tokens[$lastContent]["line"];
			$braceLine   = $tokens[$curlyBrace]["line"];
			if ($braceLine === $classLine)
			    {
				$phpcsFile->addError(
				    _("Opening brace of a") . " " . $tokens[$stackPtr]["content"] . " " . _("must be on the line after the definition"), $curlyBrace
				);
			    }
			else if ($braceLine > ($classLine + 1))
			    {
				$difference  = ($braceLine - $classLine - 1);
				$difference .= ($difference === 1) ? " " . _("line") : " " . _("lines");
				$phpcsFile->addError(
				    _("Opening brace of a") . " " . $tokens[$stackPtr]["content"] . " " . _("must be on the line following the") . " " .
				    $tokens[$stackPtr]["content"] . " " . _("declaration") . "; " . _("found") . " " . $difference, $curlyBrace
				);
			    }
			else
			    {
				if ($tokens[($curlyBrace + 1)]["content"] !== $phpcsFile->eolChar)
				    {
					$phpcsFile->addError(
					    _("Opening") . " " . strtolower($tokens[$stackPtr]["content"]) . " " . _("brace must be on a line by itself"), $curlyBrace
					);
				    }

				if ($tokens[($curlyBrace - 1)]["code"] === T_WHITESPACE)
				    {
					$prevContent = $tokens[($curlyBrace - 1)]["content"];
					if ($prevContent !== $phpcsFile->eolChar)
					    {
						$blankSpace = substr($prevContent, strpos($prevContent, $phpcsFile->eolChar));
						$spaces     = strlen($blankSpace);
						if ($spaces !== 4)
						    {
							$phpcsFile->addError(_("Expected 4 spaces before opening brace;") . " " . $spaces . " " . _("found"), $curlyBrace);
						    }
					    }
				    }
			    } //end if
		    } //end if
	    } //end process()


    } //end class

?>
