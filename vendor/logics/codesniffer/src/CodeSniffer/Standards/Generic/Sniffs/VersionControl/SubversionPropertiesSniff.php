<?php

/**
 * PHP version 5
 *
 * @package Logics\BuildTools\CodeSniffer\Generic
 */

namespace Logics\BuildTools\CodeSniffer\Generic;

use \Exception;
use \Logics\BuildTools\CodeSniffer\File;
use \Logics\BuildTools\CodeSniffer\Sniff;

/**
 * SubversionPropertiesSniff.
 *
 * Tests that the correct Subversion properties are set.
 *
 * @author    Jack Bates <ms419@freezone.co.uk>
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2006-2012 Squiz Pty Ltd (ABN 77 084 670 600)
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 * @version   SVN: $Date: 2016-08-16 23:39:01 +0900 (Tue, 16 Aug 2016) $ $Revision: 26 $
 * @link      $HeadURL: https://open.logics.net.au/buildtools/codesniffer/tags/0.1.3/src/CodeSniffer/Standards/Generic/Sniffs/VersionControl/SubversionPropertiesSniff.php $
 *
 * @untranslatable Author Id Revision
 * @untranslatable native
 */

class SubversionPropertiesSniff implements Sniff
    {

	/**
	 * The Subversion properties that should be set.
	 *
	 * Key of array is the SVN property and the value is the
	 * exact value the property should have or NULL if the
	 * property should just be set but the value is not fixed.
	 *
	 * @var array
	 */
	protected $properties = array(
				 "svn:keywords"  => "Author Id Revision",
				 "svn:eol-style" => "native",
				);

	/**
	 * Returns an array of tokens this test wants to listen for.
	 *
	 * @return array
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
	 * @untranslatable \"%s\" = \"%s\"
	 * @untranslatable Unexpected
	 * @untranslatable Missing
	 * @untranslatable \"%s\"
	 * @untranslatable NoMatch
	 */

	public function process(File $phpcsFile, $stackPtr)
	    {
		$tokens = &$phpcsFile->tokens;

		// Make sure this is the first PHP open tag so we don't process the
		// same file twice.
		$prevOpenTag = $phpcsFile->findPrevious(T_OPEN_TAG, ($stackPtr - 1));
		if ($prevOpenTag === false)
		    {
			$path       = $phpcsFile->getFileName();
			$properties = $this->getProperties($path);
			if ($properties !== null)
			    {
				// Under version control.
				$allProperties = ($properties + $this->properties);
				foreach ($allProperties as $key => $value)
				    {
					if (isset($properties[$key]) === true && isset($this->properties[$key]) === false)
					    {
						$error = _("Unexpected Subversion property") . " \"%s\" = \"%s\"";
						$data  = array(
							  $key,
							  $properties[$key],
							 );
						$phpcsFile->addError($error, $stackPtr, "Unexpected", $data);
					    }
					else if (isset($properties[$key]) === false && isset($this->properties[$key]) === true)
					    {
						$error = _("Missing Subversion property") . " \"%s\" = \"%s\"";
						$data  = array(
							  $key,
							  $this->properties[$key],
							 );
						$phpcsFile->addError($error, $stackPtr, "Missing", $data);
					    }
					else if ($properties[$key] !== null && $properties[$key] !== $this->properties[$key])
					    {
						$error = _("Subversion property") . " \"%s\" = \"%s\" " . _("does not match") . " \"%s\"";
						$data  = array(
							  $key,
							  $properties[$key],
							  $this->properties[$key],
							 );
						$phpcsFile->addError($error, $stackPtr, "NoMatch", $data);
					    } //end if
				    } //end foreach
			    } //end if
		    } //end if
	    } //end process()


	/**
	 * Returns the Subversion properties which are actually set on a path.
	 *
	 * Returns NULL if the file is not under version control.
	 *
	 * @param string $path The path to return Subversion properties on.
	 *
	 * @return array
	 *
	 * @throws Exception If Subversion properties file could not be opened.
	 *
	 * @exceptioncode EXCEPTION_CANNOT_OPEN_FILE
	 *
	 * @untranslatable /.svn/props/
	 * @untranslatable .svn-work
	 * @untranslatable /.svn/prop-base/
	 * @untranslatable .svn-base
	 * @untranslatable r
	 * @untranslatable END
	 */

	protected function getProperties($path)
	    {
		$properties = array();

		$paths   = array();
		$paths[] = dirname($path) . "/.svn/props/" . basename($path) . ".svn-work";
		$paths[] = dirname($path) . "/.svn/prop-base/" . basename($path) . ".svn-base";

		$foundPath = false;
		foreach ($paths as $path)
		    {
			if (file_exists($path) === true)
			    {
				$foundPath = true;

				$handle = fopen($path, "r");
				if ($handle === false)
				    {
					throw new Exception(
					    _("Error opening file; could not get Subversion properties"),
					    EXCEPTION_CANNOT_OPEN_FILE
					);
				    }

				while (feof($handle) === false)
				    {
					// Read a key length line. Might be END, though.
					$buffer = trim(fgets($handle));

					// Check for the end of the hash.
					if ($buffer === "END")
					    {
						break;
					    }

					// Now read that much into a buffer.
					$key = fread($handle, substr($buffer, 2));

					// Suck up extra newline after key data.
					fgetc($handle);

					// Read a value length line.
					$buffer = trim(fgets($handle));

					// Now read that much into a buffer.
					$length = substr($buffer, 2);
					if ($length === "0")
					    {
						// Length of value is ZERO characters, so
						// value is actually empty.
						$value = "";
					    }
					else
					    {
						$value = fread($handle, $length);
					    }

					// Suck up extra newline after value data.
					fgetc($handle);

					$properties[$key] = $value;
				    } //end while

				fclose($handle);
			    } //end if
		    } //end foreach

		if ($foundPath === false)
		    {
			return null;
		    }
		else
		    {
			return $properties;
		    }
	    } //end getProperties()


    } //end class

?>