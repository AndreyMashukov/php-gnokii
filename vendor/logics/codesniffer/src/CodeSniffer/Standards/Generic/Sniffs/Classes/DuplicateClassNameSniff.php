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
 * Reports errors if the same class or interface name is used in multiple files.
 *
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2006-2012 Squiz Pty Ltd (ABN 77 084 670 600)
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 * @version   SVN: $Date: 2016-08-16 23:39:01 +0900 (Tue, 16 Aug 2016) $ $Revision: 26 $
 * @link      $HeadURL: https://open.logics.net.au/buildtools/codesniffer/tags/0.1.3/src/CodeSniffer/Standards/Generic/Sniffs/Classes/DuplicateClassNameSniff.php $
 */

class DuplicateClassNameSniff implements Sniff
    {

	/**
	 * List of classes that have been found during checking.
	 *
	 * @var array
	 */
	public $foundClasses = array();

	/**
	 * Registers the tokens that this sniff wants to listen for.
	 *
	 * @return array(int)
	 */

	public function register()
	    {
		return array(T_OPEN_TAG);
	    } //end register()


	/**
	 * Processes this test, when one of its tokens is encountered.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $stackPtr  The position of the current token in the stack passed in $tokens.
	 *
	 * @return void
	 *
	 * @untranslatable %s
	 * @untranslatable \"%s\"
	 * @untranslatable Found
	 */

	public function process(File $phpcsFile, $stackPtr)
	    {
		$tokens = &$phpcsFile->tokens;

		$namespace  = "";
		$findTokens = array(
			       T_CLASS,
			       T_INTERFACE,
			       T_NAMESPACE,
			       T_CLOSE_TAG,
			      );

		$stackPtr = $phpcsFile->findNext($findTokens, ($stackPtr + 1));
		while ($stackPtr !== false)
		    {
			if ($tokens[$stackPtr]["code"] === T_CLOSE_TAG)
			    {
				// We can stop here. The sniff will continue from the next open
				// tag when PHPCS reaches that token, if there is one.
				break;
			    }

			// Keep track of what namespace we are in.
			if ($tokens[$stackPtr]["code"] === T_NAMESPACE)
			    {
				$nsEnd = $phpcsFile->findNext(
				    array(
				     T_NS_SEPARATOR,
				     T_STRING,
				     T_WHITESPACE,
				    ), ($stackPtr + 1), null, true
				);

				$namespace = trim($phpcsFile->getTokensAsString(($stackPtr + 1), ($nsEnd - $stackPtr - 1)));
				$stackPtr  = $nsEnd;
			    }
			else
			    {
				$nameToken = $phpcsFile->findNext(T_STRING, $stackPtr);
				$name      = $tokens[$nameToken]["content"];
				if ($namespace !== "")
				    {
					$name = $namespace . "\\\\" . $name;
				    }

				if (isset($this->foundClasses[$name]) === true)
				    {
					$type  = strtolower($tokens[$stackPtr]["content"]);
					$file  = $this->foundClasses[$name]["file"];
					$line  = $this->foundClasses[$name]["line"];
					$error = _("Duplicate") . " %s " . _("name") . " \"%s\" " . _("found; first defined in") . " %s " . _("on line") . " %s";
					$data  = array(
						  $type,
						  $name,
						  $file,
						  $line,
						 );
					$phpcsFile->addWarning($error, $stackPtr, "Found", $data);
				    }
				else
				    {
					$this->foundClasses[$name] = array(
								      "file" => $phpcsFile->getFilename(),
								      "line" => $tokens[$stackPtr]["line"],
								     );
				    }
			    } //end if

			$stackPtr = $phpcsFile->findNext($findTokens, ($stackPtr + 1));
		    } //end while
	    } //end process()


    } //end class

?>