<?php

/**
 * PHP version 5
 *
 * @package Logics\BuildTools\CodeSniffer\Squiz
 */

namespace Logics\BuildTools\CodeSniffer\Squiz;

use \Logics\BuildTools\CodeSniffer\File;
use \Logics\BuildTools\CodeSniffer\Sniff;

/**
 * Squiz_Sniffs_Classes_DuplicatePropertySniff.
 *
 * Ensures JS classes don't contain duplicate property names.
 *
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2006-2012 Squiz Pty Ltd (ABN 77 084 670 600)
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 * @version   SVN: $Date: 2016-08-16 23:39:01 +0900 (Tue, 16 Aug 2016) $ $Revision: 26 $
 * @link      $HeadURL: https://open.logics.net.au/buildtools/codesniffer/tags/0.1.3/src/CodeSniffer/Standards/Squiz/Sniffs/Classes/DuplicatePropertySniff.php $
 *
 * @untranslatable JS
 */

class DuplicatePropertySniff implements Sniff
    {

	/**
	 * A list of tokenizers this sniff supports.
	 *
	 * @var array
	 */
	public $supportedTokenizers = array("JS");

	/**
	 * Returns an array of tokens this test wants to listen for.
	 *
	 * @return array
	 *
	 * @internalconst T_OBJECT T_OBJECT token
	 */

	public function register()
	    {
		return array(T_OBJECT);
	    } //end register()


	/**
	 * Processes this test, when one of its tokens is encountered.
	 *
	 * @param File $phpcsFile The current file being processed.
	 * @param int  $stackPtr  The position of the current token in the stack passed in $tokens.
	 *
	 * @return void
	 *
	 * @internalconst T_PROPERTY T_PROPERTY token
	 * @internalconst T_OBJECT   T_OBJECT token
	 *
	 * @untranslatable Found
	 * @untranslatable %s
	 */

	public function process(File $phpcsFile, $stackPtr)
	    {
		$tokens = &$phpcsFile->tokens;

		$properties   = array();
		$wantedTokens = array(
				 T_PROPERTY,
				 T_OBJECT,
				);

		$next = $phpcsFile->findNext($wantedTokens, ($stackPtr + 1), $tokens[$stackPtr]["bracket_closer"]);
		while ($next !== false && $next < $tokens[$stackPtr]["bracket_closer"])
		    {
			if ($tokens[$next]["code"] === T_OBJECT)
			    {
				// Skip nested objects.
				$next = $tokens[$next]["bracket_closer"];
			    }
			else
			    {
				$propName = $tokens[$next]["content"];
				if (isset($properties[$propName]) === true)
				    {
					$error = _("Duplicate property definition found for ") . "%s" . _("; previously defined on line ") . "%s";
					$data  = array(
						  $propName,
						  $tokens[$properties[$propName]]["line"],
						 );
					$phpcsFile->addError($error, $next, "Found", $data);
				    }

				$properties[$propName] = $next;
			    } //end if

			$next = $phpcsFile->findNext($wantedTokens, ($next + 1), $tokens[$stackPtr]["bracket_closer"]);
		    } //end while
	    } //end process()


    } //end class

?>
