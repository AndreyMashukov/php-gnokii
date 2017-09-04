<?php

/**
 * PHP version 5
 *
 * @package Logics\BuildTools\CodeSniffer\Squiz
 */

namespace Logics\BuildTools\CodeSniffer\Squiz;

use \Logics\BuildTools\CodeSniffer\AbstractScopeSniff;
use \Logics\BuildTools\CodeSniffer\File;

/**
 * Squiz_Sniffs_Scope_StaticThisUsageSniff.
 *
 * Checks for usage of "$this" in static methods, which will cause
 * runtime errors.
 *
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @author    Marc McIntyre <mmcintyre@squiz.net>
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2006-2012 Squiz Pty Ltd (ABN 77 084 670 600)
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 * @version   SVN: $Date: 2016-08-16 23:39:01 +0900 (Tue, 16 Aug 2016) $ $Revision: 26 $
 * @link      $HeadURL: https://open.logics.net.au/buildtools/codesniffer/tags/0.1.3/src/CodeSniffer/Standards/Squiz/Sniffs/Scope/StaticThisUsageSniff.php $
 */

class StaticThisUsageSniff extends AbstractScopeSniff
    {

	/**
	 * Constructs the test with the tokens it wishes to listen for.
	 *
	 * @return void
	 */

	public function __construct()
	    {
		parent::__construct(array(T_CLASS), array(T_FUNCTION));
	    } //end __construct()


	/**
	 * Processes this test, when one of its tokens is encountered.
	 *
	 * @param File $phpcsFile The current file being scanned.
	 * @param int  $stackPtr  The position of the current token in the stack passed in $tokens.
	 * @param int  $currScope A pointer to the start of the scope.
	 *
	 * @return void
	 *
	 * @untranslatable \$this
	 * @untranslatable Found
	 */

	public function processTokenWithinScope(File &$phpcsFile, $stackPtr, $currScope)
	    {
		unset($currScope);

		$tokens   = &$phpcsFile->tokens;
		$function = $tokens[($stackPtr + 2)];

		if ($function["code"] === T_STRING)
		    {
			$methodProps = $phpcsFile->getMethodProperties($stackPtr);

			if ($methodProps["is_static"] === true)
			    {
				// There is no scope opener or closer, so the function
				// must be abstract.
				if (isset($tokens[$stackPtr]["scope_closer"]) === true)
				    {
					$thisUsage = $phpcsFile->findNext(array(T_VARIABLE), ($stackPtr + 1), $tokens[$stackPtr]["scope_closer"], false, "\$this");
					while ($thisUsage !== false)
					    {
						$error = _("Usage of \"\$this\" in static methods will cause runtime errors");
						$phpcsFile->addError($error, $thisUsage, "Found");

						$thisUsage = $phpcsFile->findNext(array(T_VARIABLE), ($thisUsage + 1), $tokens[$stackPtr]["scope_closer"], false, "\$this");
					    }
				    }
			    } //end if
		    } //end if
	    } //end processTokenWithinScope()


    } //end class

?>
