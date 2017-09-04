<?php

/**
 * Ensures that getRequestData() is used to access super globals.
 *
 * PHP version 5
 *
 * @package Logics\BuildTools\CodeSniffer\MySource
 */

namespace Logics\BuildTools\CodeSniffer\MySource;

use \Logics\BuildTools\CodeSniffer\File;
use \Logics\BuildTools\CodeSniffer\Sniff;

/**
 * Ensures that getRequestData() is used to access super globals.
 *
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2006-2012 Squiz Pty Ltd (ABN 77 084 670 600)
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 * @version   SVN: $Date: 2016-08-16 23:39:01 +0900 (Tue, 16 Aug 2016) $ $Revision: 26 $
 * @link      $HeadURL: https://open.logics.net.au/buildtools/codesniffer/tags/0.1.3/src/CodeSniffer/Standards/MySource/Sniffs/PHP/GetRequestDataSniff.php $
 */

class GetRequestDataSniff implements Sniff
    {

	/**
	 * Returns an array of tokens this test wants to listen for.
	 *
	 * @return array
	 */

	public function register()
	    {
		return array(T_VARIABLE);
	    } //end register()


	/**
	 * Processes this sniff, when one of its tokens is encountered.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $stackPtr  The position of the current token in the stack passed in $tokens.
	 *
	 * @return void
	 *
	 * @internalconst T_OPEN_SQUARE_BRACKET T_OPEN_SQUARE_BRACKET token
	 *
	 * @untranslatable \$_REQUEST
	 * @untranslatable \$_GET
	 * @untranslatable \$_POST
	 * @untranslatable \$_FILES
	 * @untranslatable security
	 * @untranslatable getrequestdata
	 * @untranslatable SuperglobalAccessed
	 * @untranslatable %s
	 * @untranslatable WithVar
	 * @untranslatable %s, '%s'
	 */

	public function process(File $phpcsFile, $stackPtr)
	    {
		$tokens = &$phpcsFile->tokens;

		$varName = $tokens[$stackPtr]["content"];
		if ($varName === "\$_REQUEST" || $varName === "\$_GET" || $varName === "\$_POST" || $varName === "\$_FILES")
		    {
			// The only place these super globals can be accessed directly is
			// in the getRequestData() method of the Security class.
			$inClass = false;
			$valid   = false;
			foreach ($tokens[$stackPtr]["conditions"] as $i => $type)
			    {
				if ($tokens[$i]["code"] === T_CLASS)
				    {
					$className = $phpcsFile->findNext(T_STRING, $i);
					$className = $tokens[$className]["content"];
					if (strtolower($className) === "security")
					    {
						$inClass = true;
					    }
					else
					    {
						// We don't have nested classes.
						break;
					    }
				    }
				else if ($inClass === true && $tokens[$i]["code"] === T_FUNCTION)
				    {
					$funcName = $phpcsFile->findNext(T_STRING, $i);
					$funcName = $tokens[$funcName]["content"];
					$valid    = (strtolower($funcName) === "getrequestdata");
					break;
				    } //end if
			    } //end foreach

			if ($valid === false)
			    {
				// If we get to here, the super global was used incorrectly.
				// First find out how it is being used.
				$globalName = strtolower(substr($varName, 2));
				$usedVar    = "";

				$openBracket = $phpcsFile->findNext(T_WHITESPACE, ($stackPtr + 1), null, true);
				if ($tokens[$openBracket]["code"] === T_OPEN_SQUARE_BRACKET)
				    {
					$closeBracket = $tokens[$openBracket]["bracket_closer"];
					$usedVar      = $phpcsFile->getTokensAsString(($openBracket + 1), ($closeBracket - $openBracket - 1));
				    }

				$type  = "SuperglobalAccessed";
				$error = _("The") . " %s " . _("super global must not be accessed directly; use Security::getRequestData(");
				$data  = array($varName);
				if ($usedVar !== "")
				    {
					$type  .= "WithVar";
					$error .= "%s, '%s'";
					$data[] = $usedVar;
					$data[] = $globalName;
				    }

				$error .= ") " . _("instead");
				$phpcsFile->addError($error, $stackPtr, $type, $data);
			    } //end if
		    } //end if
	    } //end process()


    } //end class

?>
