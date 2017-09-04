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
 * Squiz_Sniffs_CSS_OpacitySniff.
 *
 * Ensure that opacity values start with a 0 if it is not a whole number.
 *
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2006-2012 Squiz Pty Ltd (ABN 77 084 670 600)
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 * @version   SVN: $Date: 2016-08-16 23:39:01 +0900 (Tue, 16 Aug 2016) $ $Revision: 26 $
 * @link      $HeadURL: https://open.logics.net.au/buildtools/codesniffer/tags/0.1.3/src/CodeSniffer/Standards/Squiz/Sniffs/CSS/OpacitySniff.php $
 *
 * @untranslatable CSS
 */

class OpacitySniff implements Sniff
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
	 *
	 * @internalconst T_STYLE T_STYLE token
	 */

	public function register()
	    {
		return array(T_STYLE);
	    } //end register()


	/**
	 * Processes the tokens that this sniff is interested in.
	 *
	 * @param File $phpcsFile The file where the token was found.
	 * @param int  $stackPtr  The position in the stack where the token was found.
	 *
	 * @return void
	 *
	 * @internalconst T_COLON T_COLON token
	 *
	 * @untranslatable opacity
	 * @untranslatable SpacingAfterPoint
	 * @untranslatable PointNotRequired
	 * @untranslatable StartWithPoint
	 * @untranslatable Invalid
	 * @untranslatable 0%s
	 * @untranslatable %s
	 */

	public function process(File $phpcsFile, $stackPtr)
	    {
		$tokens = &$phpcsFile->tokens;

		if ($tokens[$stackPtr]["content"] === "opacity")
		    {
			$next    = $phpcsFile->findNext(array(T_COLON, T_WHITESPACE), ($stackPtr + 1), null, true);
			$numbers = array(
				    T_DNUMBER,
				    T_LNUMBER,
				   );

			if ($next !== false && in_array($tokens[$next]["code"], $numbers) === true)
			    {
				$value = $tokens[$next]["content"];
				if ($tokens[$next]["code"] === T_LNUMBER)
				    {
					if ($value !== "0" && $value !== "1")
					    {
						$error = _("Opacity values must be between 0 and 1");
						$phpcsFile->addError($error, $next, "Invalid");
					    }
				    }
				else
				    {
					if (strlen($value) > 3)
					    {
						$error = _("Opacity values must have a single value after the decimal point");
						$phpcsFile->addError($error, $next, "SpacingAfterPoint");
					    }
					else if ($value === "0.0" || $value === "1.0")
					    {
						$error = _("Opacity value does not require decimal point; use") . " %s " . _("instead");
						$data  = array($value{0});
						$phpcsFile->addError($error, $next, "PointNotRequired", $data);
					    }
					else if ($value{0} === ".")
					    {
						$error = _("Opacity values must not start with a decimal point; use") . " 0%s " . _("instead");
						$data  = array($value);
						$phpcsFile->addError($error, $next, "StartWithPoint", $data);
					    }
					else if ($value{0} !== "0")
					    {
						$error = _("Opacity values must be between 0 and 1");
						$phpcsFile->addError($error, $next, "Invalid");
					    } //end if
				    } //end if
			    } //end if
		    } //end if
	    } //end process()


    } //end class

?>
