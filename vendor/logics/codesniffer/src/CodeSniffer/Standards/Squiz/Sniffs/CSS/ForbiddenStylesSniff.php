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
 * Squiz_Sniffs_CSS_ForbiddenStylesSniff.
 *
 * Bans the use of some styles, such as deprecated or browser-specific styles.
 *
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2006-2012 Squiz Pty Ltd (ABN 77 084 670 600)
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 * @version   SVN: $Date: 2016-08-16 23:39:01 +0900 (Tue, 16 Aug 2016) $ $Revision: 26 $
 * @link      $HeadURL: https://open.logics.net.au/buildtools/codesniffer/tags/0.1.3/src/CodeSniffer/Standards/Squiz/Sniffs/CSS/ForbiddenStylesSniff.php $
 *
 * @untranslatable CSS
 * @untranslatable border-radius
 * @untranslatable border-top-left-radius
 * @untranslatable border-top-right-radius
 * @untranslatable border-bottom-right-radius
 * @untranslatable border-bottom-left-radius
 * @untranslatable box-shadow
 */

class ForbiddenStylesSniff implements Sniff
    {

	/**
	 * A list of tokenizers this sniff supports.
	 *
	 * @var array
	 */
	public $supportedTokenizers = array("CSS");

	/**
	 * A list of forbidden styles with their alternatives.
	 *
	 * The value is NULL if no alternative exists. i.e., the
	 * function should just not be used.
	 *
	 * @var array(string => string|null)
	 */
	protected $forbiddenStyles = array(
				      "-moz-border-radius"             => "border-radius",
				      "-webkit-border-radius"          => "border-radius",
				      "-moz-border-radius-topleft"     => "border-top-left-radius",
				      "-moz-border-radius-topright"    => "border-top-right-radius",
				      "-moz-border-radius-bottomright" => "border-bottom-right-radius",
				      "-moz-border-radius-bottomleft"  => "border-bottom-left-radius",
				      "-moz-box-shadow"                => "box-shadow",
				      "-webkit-box-shadow"             => "box-shadow",
				     );

	/**
	 * A cache of forbidden style names, for faster lookups.
	 *
	 * @var array(string)
	 */
	protected $forbiddenStyleNames = array();

	/**
	 * If true, forbidden styles will be considered regular expressions.
	 *
	 * @var bool
	 */
	protected $patternMatch = false;

	/**
	 * If true, an error will be thrown; otherwise a warning.
	 *
	 * @var bool
	 */
	public $error = true;

	/**
	 * Returns an array of tokens this test wants to listen for.
	 *
	 * @return array
	 *
	 * @internalconst T_STYLE T_STYLE token
	 *
	 * @untranslatable /i
	 */

	public function register()
	    {
		$this->forbiddenStyleNames = array_keys($this->forbiddenStyles);

		if ($this->patternMatch === true)
		    {
			foreach ($this->forbiddenStyleNames as $i => $name)
			    {
				$this->forbiddenStyleNames[$i] = "/" . $name . "/i";
			    }
		    }

		return array(T_STYLE);
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
		$tokens  = &$phpcsFile->tokens;
		$style   = strtolower($tokens[$stackPtr]["content"]);
		$pattern = null;

		if ($this->patternMatch === true)
		    {
			$count   = 0;
			$pattern = preg_replace($this->forbiddenStyleNames, $this->forbiddenStyleNames, $style, 1, $count);

			if ($count === 0)
			    {
				return;
			    }

			// Remove the pattern delimiters and modifier.
			$pattern = substr($pattern, 1, -2);
		    }
		else
		    {
			if (in_array($style, $this->forbiddenStyleNames) === false)
			    {
				return;
			    }
		    } //end if

		$this->addError($phpcsFile, $stackPtr, $style, $pattern);
	    } //end process()


	/**
	 * Generates the error or warning for this sniff.
	 *
	 * @param File   $phpcsFile The file being scanned.
	 * @param int    $stackPtr  The position of the forbidden style in the token array.
	 * @param string $style     The name of the forbidden style.
	 * @param string $pattern   The pattern used for the match.
	 *
	 * @return void
	 *
	 * @untranslatable Found
	 * @untranslatable Discouraged
	 * @untranslatable %s
	 * @untranslatable WithAlternative
	 */

	protected function addError(File $phpcsFile, $stackPtr, $style, $pattern = null)
	    {
		$data  = array($style);
		$error = _("The use of style") . " %s " . _("is") . " ";
		if ($this->error === true)
		    {
			$type   = "Found";
			$error .= _("forbidden");
		    }
		else
		    {
			$type   = "Discouraged";
			$error .= _("discouraged");
		    }

		if ($pattern === null)
		    {
			$pattern = $style;
		    }

		if ($this->forbiddenStyles[$pattern] !== null)
		    {
			$data[] = $this->forbiddenStyles[$pattern];
			if ($this->error === true)
			    {
				$phpcsFile->addError($error . "; " . _("use") . " %s " . _("instead"), $stackPtr, $type . "WithAlternative", $data);
			    }
			else
			    {
				$phpcsFile->addWarning($error . "; " . _("use") . " %s " . _("instead"), $stackPtr, $type . "WithAlternative", $data);
			    }
		    }
		else
		    {
			if ($this->error === true)
			    {
				$phpcsFile->addError($error, $stackPtr, $type, $data);
			    }
			else
			    {
				$phpcsFile->addWarning($error, $stackPtr, $type, $data);
			    }
		    } //end if
	    } //end addError()


    } //end class

?>
