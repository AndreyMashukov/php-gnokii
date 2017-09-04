<?php

/**
 * Ensures that systems, asset types and libs are included before they are used.
 *
 * PHP version 5
 *
 * @package Logics\BuildTools\CodeSniffer\MySource
 */

namespace Logics\BuildTools\CodeSniffer\MySource;

use \Logics\BuildTools\CodeSniffer\AbstractScopeSniff;
use \Logics\BuildTools\CodeSniffer\File;
use \Logics\BuildTools\CodeSniffer\Tokens;

/**
 * Ensures that systems, asset types and libs are included before they are used.
 *
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2006-2012 Squiz Pty Ltd (ABN 77 084 670 600)
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 * @version   SVN: $Date: 2016-08-16 23:39:01 +0900 (Tue, 16 Aug 2016) $ $Revision: 26 $
 * @link      $HeadURL: https://open.logics.net.au/buildtools/codesniffer/tags/0.1.3/src/CodeSniffer/Standards/MySource/Sniffs/Channels/IncludeSystemSniff.php $
 *
 * @untranslatable self
 * @untranslatable parent
 * @untranslatable channels
 * @untranslatable basesystem
 * @untranslatable dal
 * @untranslatable init
 * @untranslatable pdo
 * @untranslatable util
 * @untranslatable ziparchive
 * @untranslatable phpunit_framework_assert
 * @untranslatable abstractmysourceunittest
 * @untranslatable abstractdatacleanunittest
 * @untranslatable exception
 * @untranslatable abstractwidgetwidgettype
 * @untranslatable domdocument
 */

class IncludeSystemSniff extends AbstractScopeSniff
    {

	/**
	 * A list of classes that don't need to be included.
	 *
	 * @var array(string)
	 */
	private $_ignore = array(
			    "self",
			    "parent",
			    "channels",
			    "basesystem",
			    "dal",
			    "init",
			    "pdo",
			    "util",
			    "ziparchive",
			    "phpunit_framework_assert",
			    "abstractmysourceunittest",
			    "abstractdatacleanunittest",
			    "exception",
			    "abstractwidgetwidgettype",
			    "domdocument",
			   );

	/**
	 * Constructs a Squiz_Sniffs_Scope_MethodScopeSniff.
	 *
	 * @return void
	 */

	public function __construct()
	    {
		parent::__construct(array(T_FUNCTION), array(T_DOUBLE_COLON, T_EXTENDS), true);
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
	 * @untranslatable actions
	 * @untranslatable includewidget
	 * @untranslatable widgettype
	 * @untranslatable \"%s\";
	 * @untranslatable NotIncludedCall
	 */

	protected function processTokenWithinScope(File &$phpcsFile, $stackPtr, $currScope)
	    {
		$tokens = &$phpcsFile->tokens;

		// Determine the name of the class that the static function
		// is being called on.
		$classNameToken = $phpcsFile->findPrevious(T_WHITESPACE, ($stackPtr - 1), null, true);

		$className = $tokens[$classNameToken]["content"];
		if (in_array(strtolower($className), $this->_ignore) === false)
		    {
			$includedClasses = array();

			$fileName = strtolower($phpcsFile->getFilename());
			$matches  = array();
			if (preg_match("|/systems/(.*)/([^/]+)?actions.inc$|", $fileName, $matches) !== 0)
			    {
				// This is an actions file, which means we don't
				// have to include the system in which it exists.
				$includedClasses[] = $matches[2];

				// Or a system it implements.
				$class      = $phpcsFile->getCondition($stackPtr, T_CLASS);
				$implements = $phpcsFile->findNext(T_IMPLEMENTS, $class, ($class + 10));
				if ($implements !== false)
				    {
					$implementsClass     = $phpcsFile->findNext(T_STRING, $implements);
					$implementsClassName = strtolower($tokens[$implementsClass]["content"]);
					if (substr($implementsClassName, -7) === "actions")
					    {
						$includedClasses[] = substr($implementsClassName, 0, -7);
					    }
				    }
			    }

			// Go searching for includeSystem and includeAsset calls within this
			// function, or the inclusion of .inc files, which
			// would be library files.
			for ($i = ($currScope + 1); $i < $stackPtr; $i++)
			    {
				$name = $this->getIncludedClassFromToken($phpcsFile, $tokens, $i);
				if ($name !== false)
				    {
					$includedClasses[] = $name;
					// Special case for Widgets cause they are, well, special.
				    }
				else if (strtolower($tokens[$i]["content"]) === "includewidget")
				    {
					$typeName          = $phpcsFile->findNext(T_CONSTANT_ENCAPSED_STRING, ($i + 1));
					$typeName          = trim($tokens[$typeName]["content"], " '");
					$includedClasses[] = strtolower($typeName) . "widgettype";
				    }
			    }

			$includedClasses = array_merge($includedClasses, $this->_lookOutsideTheScope($phpcsFile, $tokens, $stackPtr, $currScope));
			$includedClasses = array_merge($includedClasses, $this->_findClassesWithSetupFunction($phpcsFile, $tokens, $stackPtr));

			if (in_array(strtolower($className), $includedClasses) === false)
			    {
				$error = _("Static method called on non-included class or system") . " \"%s\"; " .
					 _("include system with Channels::includeSystem() or include class with require_once");
				$data  = array($className);
				$phpcsFile->addError($error, $stackPtr, "NotIncludedCall", $data);
			    }
		    } //end if
	    } //end processTokenWithinScope()


	/**
	 * Look outside of the scope
	 *
	 * @param File  $phpcsFile The file where this token was found.
	 * @param array $tokens    All tokens
	 * @param int   $stackPtr  The position where the token was found.
	 * @param int   $currScope Current scope
	 *
	 * @return array Included clases
	 */

	private function _lookOutsideTheScope(File &$phpcsFile, array &$tokens, $stackPtr, $currScope)
	    {
		$includedClasses = array();

		// Now go searching for includeSystem, includeAsset or require/include
		// calls outside our scope. If we are in a class, look outside the
		// class. If we are not, look outside the function.
		$condPtr = $currScope;
		if ($phpcsFile->hasCondition($stackPtr, T_CLASS) === true)
		    {
			foreach ($tokens[$stackPtr]["conditions"] as $condPtr => $condType)
			    {
				if ($condType === T_CLASS)
				    {
					break;
				    }
			    }
		    }

		for ($i = 0; $i < $condPtr; $i++)
		    {
			// Skip other scopes.
			if (isset($tokens[$i]["scope_closer"]) === true)
			    {
				$i = $tokens[$i]["scope_closer"];
			    }
			else
			    {
				$name = $this->getIncludedClassFromToken($phpcsFile, $tokens, $i);
				if ($name !== false)
				    {
					$includedClasses[] = $name;
				    }
			    }
		    } //end for

		return $includedClasses;
	    } //end _lookOutsideTheScope()


	/**
	 * Find all classes with setup function
	 *
	 * @param File  $phpcsFile The file where this token was found.
	 * @param array $tokens    All tokens
	 * @param int   $stackPtr  The position where the token was found.
	 *
	 * @return array Included clases
	 *
	 * @untranslatable UnitTest
	 * @untranslatable setUp
	 */

	private function _findClassesWithSetupFunction(File &$phpcsFile, array &$tokens, $stackPtr)
	    {
		$includedClasses = array();

		// If we are in a testing class, we might have also included
		// some systems and classes in our setUp() method.
		$setupFunction = null;
		if ($phpcsFile->hasCondition($stackPtr, T_CLASS) === true)
		    {
			foreach ($tokens[$stackPtr]["conditions"] as $condPtr => $condType)
			    {
				if ($condType === T_CLASS)
				    {
					// Is this is a testing class?
					$name = $phpcsFile->findNext(T_STRING, $condPtr);
					$name = $tokens[$name]["content"];
					if (substr($name, -8) === "UnitTest")
					    {
						// Look for a method called setUp().
						$end      = $tokens[$condPtr]["scope_closer"];
						$function = $phpcsFile->findNext(T_FUNCTION, ($condPtr + 1), $end);
						while ($function !== false && $setupFunction === null)
						    {
							$name          = $phpcsFile->findNext(T_STRING, $function);
							$setupFunction = ($tokens[$name]["content"] === "setUp") ? $function : null;
							$function      = $phpcsFile->findNext(T_FUNCTION, ($function + 1), $end);
						    }
					    }
				    } //end if
			    } //end foreach
		    } //end if

		if ($setupFunction !== null)
		    {
			$start = ($tokens[$setupFunction]["scope_opener"] + 1);
			$end   = $tokens[$setupFunction]["scope_closer"];
			for ($i = $start; $i < $end; $i++)
			    {
				$name = $this->getIncludedClassFromToken($phpcsFile, $tokens, $i);
				if ($name !== false)
				    {
					$includedClasses[] = $name;
				    }
			    }
		    } //end if

		return $includedClasses;
	    } //end _findClassesWithSetupFunction()


	/**
	 * Processes a token within the scope that this test is listening to.
	 *
	 * @param File $phpcsFile The file where the token was found.
	 * @param int  $stackPtr  The position in the stack where this token was found.
	 *
	 * @return void
	 *
	 * @untranslatable includewidget
	 * @untranslatable widgettype
	 * @untranslatable \"%s\";
	 * @untranslatable NotIncludedExtends
	 * @untranslatable NotIncludedCall
	 */

	protected function processTokenOutsideScope(File &$phpcsFile, $stackPtr)
	    {
		$tokens = &$phpcsFile->tokens;

		if ($tokens[$stackPtr]["code"] === T_EXTENDS)
		    {
			// Find the class name.
			$classNameToken = $phpcsFile->findNext(T_STRING, ($stackPtr + 1));
			$className      = $tokens[$classNameToken]["content"];
		    }
		else
		    {
			// Determine the name of the class that the static function
			// is being called on.
			$classNameToken = $phpcsFile->findPrevious(T_WHITESPACE, ($stackPtr - 1), null, true);
			$className      = $tokens[$classNameToken]["content"];
		    }

		// Some systems are always available.
		if (in_array(strtolower($className), $this->_ignore) === false)
		    {
			$includedClasses = array();

			$fileName = strtolower($phpcsFile->getFilename());
			$matches  = array();
			if (preg_match("|/systems/([^/]+)/([^/]+)?actions.inc$|", $fileName, $matches) !== 0)
			    {
				// This is an actions file, which means we don't
				// have to include the system in which it exists
				// We know the system from the path.
				$includedClasses[] = $matches[1];
			    }

			// Go searching for includeSystem, includeAsset or require/include calls outside our scope.
			for ($i = 0; $i < $stackPtr; $i++)
			    {
				// Skip classes and functions as will we never get
				// into their scopes when including this file, although
				// we have a chance of getting into IF's, WHILE's etc.
				$ignoreTokens = array(
						 T_CLASS,
						 T_INTERFACE,
						 T_FUNCTION,
						);

				if (in_array($tokens[$i]["code"], $ignoreTokens) === true && isset($tokens[$i]["scope_closer"]) === true)
				    {
					$i = $tokens[$i]["scope_closer"];
				    }
				else
				    {
					$name = $this->getIncludedClassFromToken($phpcsFile, $tokens, $i);
					if ($name !== false)
					    {
						$includedClasses[] = $name;
						// Special case for Widgets cause they are, well, special.
					    }
					else if (strtolower($tokens[$i]["content"]) === "includewidget")
					    {
						$typeName          = $phpcsFile->findNext(T_CONSTANT_ENCAPSED_STRING, ($i + 1));
						$typeName          = trim($tokens[$typeName]["content"], " '");
						$includedClasses[] = strtolower($typeName) . "widgettype";
					    }
				    }
			    } //end for

			if (in_array(strtolower($className), $includedClasses) === false)
			    {
				if ($tokens[$stackPtr]["code"] === T_EXTENDS)
				    {
					$error = _("Class extends non-included class or system") . " \"%s\"; " .
						 _("include system with Channels::includeSystem() or include class with require_once");
					$data  = array($className);
					$phpcsFile->addError($error, $stackPtr, "NotIncludedExtends", $data);
				    }
				else
				    {
					$error = _("Static method called on non-included class or system") . " \"%s\"; " .
						 _("include system with Channels::includeSystem() or include class with require_once");
					$data  = array($className);
					$phpcsFile->addError($error, $stackPtr, "NotIncludedCall", $data);
				    }
			    }
		    } //end if
	    } //end processTokenOutsideScope()


	/**
	 * Determines the included class name from given token.
	 *
	 * @param File  $phpcsFile The file where this token was found.
	 * @param array $tokens    The array of file tokens.
	 * @param int   $stackPtr  The position in the tokens array of the potentially included class.
	 *
	 * @return string
	 *
	 * @untranslatable includesystem
	 * @untranslatable includeasset
	 * @untranslatable assettype
	 * @untranslatable .inc
	 */

	protected function getIncludedClassFromToken(File &$phpcsFile, array $tokens, $stackPtr)
	    {
		if (strtolower($tokens[$stackPtr]["content"]) === "includesystem")
		    {
			$systemName = $phpcsFile->findNext(T_CONSTANT_ENCAPSED_STRING, ($stackPtr + 1));
			$systemName = trim($tokens[$systemName]["content"], " '");
			return strtolower($systemName);
		    }
		else if (strtolower($tokens[$stackPtr]["content"]) === "includeasset")
		    {
			$typeName = $phpcsFile->findNext(T_CONSTANT_ENCAPSED_STRING, ($stackPtr + 1));
			$typeName = trim($tokens[$typeName]["content"], " '");
			return strtolower($typeName) . "assettype";
		    }
		else if (in_array($tokens[$stackPtr]["code"], Tokens::$includeTokens) === true)
		    {
			$filePath = $phpcsFile->findNext(T_CONSTANT_ENCAPSED_STRING, ($stackPtr + 1));
			$filePath = $tokens[$filePath]["content"];
			$filePath = trim($filePath, " '");
			$filePath = basename($filePath, ".inc");
			return strtolower($filePath);
		    }
		else
		    {
			return false;
		    } //end if
	    } //end getIncludedClassFromToken()


    } //end class

?>
