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
 * Checks the cyclomatic complexity (McCabe) for functions.
 *
 * The cyclomatic complexity (also called McCabe code metrics)
 * indicates the complexity within a function by counting
 * the different paths the function includes.
 *
 * @author    Johann-Peter Hartmann <hartmann@mayflower.de>
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2007 Mayflower GmbH
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 * @version   SVN: $Date: 2016-08-16 23:39:01 +0900 (Tue, 16 Aug 2016) $ $Revision: 26 $
 * @link      $HeadURL: https://open.logics.net.au/buildtools/codesniffer/tags/0.1.3/src/CodeSniffer/Standards/Generic/Sniffs/Metrics/CyclomaticComplexitySniff.php $
 */

class CyclomaticComplexitySniff implements Sniff
    {

	/**
	 * A complexity higher than this value will throw a warning.
	 *
	 * @var int
	 */
	public $complexity = 10;

	/**
	 * A complexity higer than this value will throw an error.
	 *
	 * @var int
	 */
	public $absoluteComplexity = 20;

	/**
	 * Returns an array of tokens this test wants to listen for.
	 *
	 * @return array
	 */

	public function register()
	    {
		return array(T_FUNCTION);
	    } //end register()


	/**
	 * Processes this test, when one of its tokens is encountered.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $stackPtr  The position of the current token in the stack passed in $tokens.
	 *
	 * @return void
	 *
	 * @untranslatable T_CASE
	 * @untranslatable T_DEFAULT
	 * @untranslatable T_CATCH
	 * @untranslatable T_IF
	 * @untranslatable T_FOR
	 * @untranslatable T_FOREACH
	 * @untranslatable T_WHILE
	 * @untranslatable T_DO
	 * @untranslatable T_ELSEIF
	 * @untranslatable (%s)
	 * @untranslatable %s
	 * @untranslatable MaxExceeded
	 * @untranslatable %s;
	 * @untranslatable TooHigh
	 */

	public function process(File $phpcsFile, $stackPtr)
	    {
		$this->currentFile = $phpcsFile;

		$tokens = &$phpcsFile->tokens;

		// Ignore abstract methods.
		if (isset($tokens[$stackPtr]["scope_opener"]) === true)
		    {
			// Detect start and end of this function definition.
			$start = $tokens[$stackPtr]["scope_opener"];
			$end   = $tokens[$stackPtr]["scope_closer"];

			// Predicate nodes for PHP.
			$find = array(
				 "T_CASE",
				 "T_DEFAULT",
				 "T_CATCH",
				 "T_IF",
				 "T_FOR",
				 "T_FOREACH",
				 "T_WHILE",
				 "T_DO",
				 "T_ELSEIF",
				);

			$complexity = 1;

			// Iterate from start to end and count predicate nodes.
			for ($i = ($start + 1); $i < $end; $i++)
			    {
				if (in_array($tokens[$i]["type"], $find) === true)
				    {
					$complexity++;
				    }
			    }

			if ($complexity > $this->absoluteComplexity)
			    {
				$error = _("Function's cyclomatic complexity") . " (%s) " . _("exceeds allowed maximum of") . " %s";
				$data  = array(
					  $complexity,
					  $this->absoluteComplexity,
					 );
				$phpcsFile->addError($error, $stackPtr, "MaxExceeded", $data);
			    }
			else if ($complexity > $this->complexity)
			    {
				$warning = _("Function's cyclomatic complexity") . " (%s) " . _("exceeds") . " %s; " . _("consider refactoring the function");
				$data    = array(
					    $complexity,
					    $this->complexity,
					   );
				$phpcsFile->addWarning($warning, $stackPtr, "TooHigh", $data);
			    }
		    } //end if
	    } //end process()


    } //end class

?>
