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
 * Squiz_Sniffs_CSS_DuplicateClassDefinitionSniff.
 *
 * Check for duplicate class definitions that can be merged into one.
 *
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2006-2012 Squiz Pty Ltd (ABN 77 084 670 600)
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 * @version   SVN: $Date: 2016-08-16 23:39:01 +0900 (Tue, 16 Aug 2016) $ $Revision: 26 $
 * @link      $HeadURL: https://open.logics.net.au/buildtools/codesniffer/tags/0.1.3/src/CodeSniffer/Standards/Squiz/Sniffs/CSS/DuplicateClassDefinitionSniff.php $
 *
 * @untranslatable CSS
 */

class DuplicateClassDefinitionSniff implements Sniff
    {

	/**
	 * A list of tokenizers this sniff supports.
	 *
	 * @var array
	 */
	public $supportedTokenizers = array("CSS");

	/**
	 * Returns the token types that this sniff is interested in.
	 *
	 * @return array(int)
	 */

	public function register()
	    {
		return array(T_OPEN_TAG);
	    } //end register()


	/**
	 * Processes the tokens that this sniff is interested in.
	 *
	 * @param File $phpcsFile The file where the token was found.
	 * @param int  $stackPtr  The position in the stack where the token was found.
	 *
	 * @return void
	 *
	 * @internalconst T_CLOSE_CURLY_BRACKET T_CLOSE_CURLY_BRACKET token
	 * @internalconst T_OPEN_CURLY_BRACKET  T_OPEN_CURLY_BRACKET token
	 *
	 * @untranslatable Found
	 * @untranslatable %s
	 */

	public function process(File $phpcsFile, $stackPtr)
	    {
		$tokens = &$phpcsFile->tokens;

		// Find the content of each class definition name.
		$classNames = array();
		$next       = $phpcsFile->findNext(T_OPEN_CURLY_BRACKET, ($stackPtr + 1));
		if ($next !== false)
		    {
			$find = array(
				 T_CLOSE_CURLY_BRACKET,
				 T_COMMENT,
				 T_OPEN_TAG,
				);

			while ($next !== false)
			    {
				$prev = $phpcsFile->findPrevious($find, ($next - 1));

				// Create a sorted name for the class so we can compare classes
				// even when the individual names are all over the place.
				$name = "";
				for ($i = ($prev + 1); $i < $next; $i++)
				    {
					$name .= $tokens[$i]["content"];
				    }

				$name = trim($name);
				$name = str_replace("\n", " ", $name);
				$name = preg_replace("/[\s]+/", " ", $name);
				$name = str_replace(", ", ",", $name);

				$names = explode(",", $name);
				sort($names);
				$name = implode(",", $names);

				if (isset($classNames[$name]) === true)
				    {
					$first = $classNames[$name];
					$error = _("Duplicate class definition found; first defined on line") . " %s";
					$data  = array($tokens[$first]["line"]);
					$phpcsFile->addError($error, $next, "Found", $data);
				    }
				else
				    {
					$classNames[$name] = $next;
				    }

				$next = $phpcsFile->findNext(T_OPEN_CURLY_BRACKET, ($next + 1));
			    } //end while
		    } //end if
	    } //end process()


    } //end class

?>
