<?php

/**
 * PHP version 5
 *
 * @package Logics\BuildTools\CodeSniffer\PSR2
 */

namespace Logics\BuildTools\CodeSniffer\PSR2;

use \Logics\BuildTools\CodeSniffer\AbstractScopeSniff;
use \Logics\BuildTools\CodeSniffer\File;
use \Logics\BuildTools\CodeSniffer\Tokens;

/**
 * PSR2_Sniffs_Methods_MethodDeclarationSniff.
 *
 * Checks that the method declaration is correct.
 *
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2006-2012 Squiz Pty Ltd (ABN 77 084 670 600)
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 * @version   SVN: $Date: 2016-08-16 23:39:01 +0900 (Tue, 16 Aug 2016) $ $Revision: 26 $
 * @link      $HeadURL: https://open.logics.net.au/buildtools/codesniffer/tags/0.1.3/src/CodeSniffer/Standards/PSR2/Sniffs/Methods/MethodDeclarationSniff.php $
 */

class MethodDeclarationSniff extends AbstractScopeSniff
    {

	/**
	 * Constructs a Squiz_Sniffs_Scope_MethodScopeSniff.
	 *
	 * @return void
	 */

	public function __construct()
	    {
		parent::__construct(array(T_CLASS, T_INTERFACE), array(T_FUNCTION));
	    } //end __construct()


	/**
	 * Processes the function tokens within the class.
	 *
	 * @param File $phpcsFile The file where this token was found.
	 * @param int  $stackPtr  The position where the token was found.
	 * @param int  $currScope The current scope opener token.
	 *
	 * @return void
	 *
	 * @untranslatable \"%s\"
	 * @untranslatable Underscore
	 * @untranslatable StaticBeforeVisibility
	 * @untranslatable FinalAfterVisibility
	 * @untranslatable AbstractAfterVisibility
	 */

	protected function processTokenWithinScope(File &$phpcsFile, $stackPtr, $currScope)
	    {
		unset($currScope);
		$tokens = &$phpcsFile->tokens;

		$methodName = $phpcsFile->getDeclarationName($stackPtr);
		if ($methodName !== null)
		    {
			// Ignore closures.
			if ($methodName[0] === "_" && isset($methodName[1]) === true && $methodName[1] !== "_")
			    {
				$error = _("Method name") . " \"%s\" " . _("should not be prefixed with an underscore to indicate visibility");
				$data  = array($methodName);
				$phpcsFile->addWarning($error, $stackPtr, "Underscore", $data);
			    }

			$visibility = 0;
			$static     = 0;
			$abstract   = 0;
			$final      = 0;

			$find   = Tokens::$methodPrefixes;
			$find[] = T_WHITESPACE;
			$prev   = $phpcsFile->findPrevious($find, ($stackPtr - 1), null, true);

			$prefix = $phpcsFile->findPrevious(Tokens::$methodPrefixes, ($stackPtr - 1), $prev);
			while ($prefix !== false)
			    {
				if ($tokens[$prefix]["code"] === T_STATIC)
				    {
					$static = $prefix;
				    }
				else if ($tokens[$prefix]["code"] === T_ABSTRACT)
				    {
					$abstract = $prefix;
				    }
				else if ($tokens[$prefix]["code"] === T_FINAL)
				    {
					$final = $prefix;
				    }
				else
				    {
					$visibility = $prefix;
				    }

				$prefix = $phpcsFile->findPrevious(Tokens::$methodPrefixes, ($prefix - 1), $prev);
			    }

			if ($static !== 0 && $static < $visibility)
			    {
				$error = _("The static declaration must come after the visibility declaration");
				$phpcsFile->addError($error, $static, "StaticBeforeVisibility");
			    }

			if ($visibility !== 0 && $final > $visibility)
			    {
				$error = _("The final declaration must precede the visibility declaration");
				$phpcsFile->addError($error, $final, "FinalAfterVisibility");
			    }

			if ($visibility !== 0 && $abstract > $visibility)
			    {
				$error = _("The abstract declaration must precede the visibility declaration");
				$phpcsFile->addError($error, $abstract, "AbstractAfterVisibility");
			    }
		    } //end if
	    } //end processTokenWithinScope()


    } //end class

?>
