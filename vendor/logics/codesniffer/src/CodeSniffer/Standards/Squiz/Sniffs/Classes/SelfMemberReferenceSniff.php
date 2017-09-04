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
 * Tests self member references.
 *
 * Verifies that :
 * <ul>
 *  <li>self:: is used instead of Self::</li>
 *  <li>self:: is used for local static member reference</li>
 *  <li>self:: is used instead of self ::</li>
 * </ul>
 *
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @author    Marc McIntyre <mmcintyre@squiz.net>
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2006-2012 Squiz Pty Ltd (ABN 77 084 670 600)
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 * @version   SVN: $Date: 2016-08-16 23:39:01 +0900 (Tue, 16 Aug 2016) $ $Revision: 26 $
 * @link      $HeadURL: https://open.logics.net.au/buildtools/codesniffer/tags/0.1.3/src/CodeSniffer/Standards/Squiz/Sniffs/Classes/SelfMemberReferenceSniff.php $
 */

class SelfMemberReferenceSniff extends AbstractScopeSniff
    {

	/**
	 * Constructs a Squiz_Sniffs_Classes_SelfMemberReferenceSniff.
	 *
	 * @return void
	 */

	public function __construct()
	    {
		parent::__construct(array(T_CLASS), array(T_DOUBLE_COLON));
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
	 * @internalconst T_SELF    T_SELF token
	 * @internalconst T_CLOSURE T_CLOSURE token
	 *
	 * @untranslatable IncorrectCase
	 * @untranslatable NotUsed
	 * @untranslatable SpaceBefore
	 * @untranslatable SpaceAfter
	 * @untranslatable %s
	 * @untranslatable \"%s::\"
	 */

	protected function processTokenWithinScope(File &$phpcsFile, $stackPtr, $currScope)
	    {
		$tokens = &$phpcsFile->tokens;
		$fault  = false;

		$className = ($stackPtr - 1);
		if ($tokens[$className]["code"] === T_SELF)
		    {
			if (strtolower($tokens[$className]["content"]) !== $tokens[$className]["content"])
			    {
				$error = _("Must use \"self::\" for local static member reference; found") . " \"%s::\"";
				$data  = array($tokens[$className]["content"]);
				$phpcsFile->addError($error, $className, "IncorrectCase", $data);
				$fault = true;
			    }
		    }
		else if ($tokens[$className]["code"] === T_STRING)
		    {
			// Make sure this is another class reference.
			$declarationName        = $phpcsFile->getDeclarationName($currScope);
			$fullQualifiedClassName = $tokens[$className]["content"];

			// If the class is called with a namespace prefix, build fully qualified
			// namespace calls for both current scope class and requested class.
			if ($tokens[($className - 1)]["code"] === T_NS_SEPARATOR)
			    {
				$declarationName         = $this->getDeclarationNameWithNamespace($tokens, $className);
				$declarationName         = substr($declarationName, 1);
				$fullQualifiedClassName  = $this->getNamespaceOfScope($phpcsFile, $currScope);
				$fullQualifiedClassName .= "\\" . $tokens[$className]["content"];
			    }

			if ($declarationName === $fullQualifiedClassName)
			    {
				// Class name is the same as the current class, which is not allowed
				// except if being used inside a closure.
				if ($phpcsFile->hasCondition($stackPtr, T_CLOSURE) === false)
				    {
					$error = _("Must use \"self::\" for local static member reference");
					$phpcsFile->addError($error, $className, "NotUsed");
					$fault = true;
				    }
			    }
		    } //end if

		if ($fault === false)
		    {
			if ($tokens[($stackPtr - 1)]["code"] === T_WHITESPACE)
			    {
				$found = strlen($tokens[($stackPtr - 1)]["content"]);
				$error = _("Expected 0 spaces before double colon;") . " %s " . _("found");
				$data  = array($found);
				$phpcsFile->addError($error, $className, "SpaceBefore", $data);
			    }

			if ($tokens[($stackPtr + 1)]["code"] === T_WHITESPACE)
			    {
				$found = strlen($tokens[($stackPtr + 1)]["content"]);
				$error = _("Expected 0 spaces after double colon;") . " %s " . _("found");
				$data  = array($found);
				$phpcsFile->addError($error, $className, "SpaceAfter", $data);
			    }
		    }
	    } //end processTokenWithinScope()


	/**
	 * Returns the declaration names for classes/interfaces/functions with a namespace.
	 *
	 * @param array $tokens   Token stack for this file
	 * @param int   $stackPtr The position where the namespace building will start.
	 *
	 * @return string
	 */

	protected function getDeclarationNameWithNamespace(array $tokens, $stackPtr)
	    {
		$nameParts      = array();
		$currentPointer = $stackPtr;
		while ($tokens[$currentPointer]["code"] === T_NS_SEPARATOR || $tokens[$currentPointer]["code"] === T_STRING)
		    {
			$nameParts[] = $tokens[$currentPointer]["content"];
			$currentPointer--;
		    }

		$nameParts = array_reverse($nameParts);
		return implode("", $nameParts);
	    } //end getDeclarationNameWithNamespace()


	/**
	 * Returns the namespace declaration of a file.
	 *
	 * @param File $phpcsFile The file where this token was found.
	 * @param int  $stackPtr  The position where the search for the namespace declaration will start.
	 *
	 * @return string
	 *
	 * @internalconst T_SEMICOLON T_SEMICOLON token
	 */

	protected function getNamespaceOfScope(File &$phpcsFile, $stackPtr)
	    {
		$namespace            = "\\";
		$namespaceDeclaration = $phpcsFile->findPrevious(T_NAMESPACE, $stackPtr);

		if ($namespaceDeclaration !== false)
		    {
			$endOfNamespaceDeclaration = $phpcsFile->findNext(T_SEMICOLON, $namespaceDeclaration);
			$tokens                    = &$phpcsFile->tokens;
			$namespace                 = $this->getDeclarationNameWithNamespace($tokens, ($endOfNamespaceDeclaration - 1));
		    }

		return $namespace;
	    } //end getNamespaceOfScope()


    } //end class

?>