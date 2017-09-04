<?php

/**
 * PHP version 5
 *
 * @package Logics\BuildTools\CodeSniffer\Generic
 */

namespace Logics\BuildTools\CodeSniffer\Generic;

use \Logics\BuildTools\CodeSniffer\Config;
use \Logics\BuildTools\CodeSniffer\File;
use \Logics\BuildTools\CodeSniffer\Sniff;

/**
 * JSHintSniff.
 *
 * Runs jshint.js on the file.
 *
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @author    Alexander Weiss <aweisswa@gmx.de>
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2006-2012 Squiz Pty Ltd (ABN 77 084 670 600)
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 * @version   SVN: $Date: 2016-08-16 23:39:01 +0900 (Tue, 16 Aug 2016) $ $Revision: 26 $
 * @link      $HeadURL: https://open.logics.net.au/buildtools/codesniffer/tags/0.1.3/src/CodeSniffer/Standards/Generic/Sniffs/Debug/JSHintSniff.php $
 *
 * @untranslatable JS
 */

class JSHintSniff implements Sniff
    {

	/**
	 * A list of tokenizers this sniff supports.
	 *
	 * @var array
	 */
	public $supportedTokenizers = array("JS");

	/**
	 * Returns the token types that this sniff is interested in.
	 *
	 * @return array(int)
	 */

	public function register()
	    {
		return array(T_OPEN_TAG);
	    } //end register()


	/**
	 * Processes the tokens that this sniff is interested in.
	 *
	 * @param File $phpcsFile The file where the token was found.
	 * @param int  $stackPtr  The position in the stack where the token was found.
	 *
	 * @return void
	 *
	 * @untranslatable rhino_path
	 * @untranslatable jshint_path
	 * @untranslatable jshint
	 * @untranslatable ExternalTool
	 */

	public function process(File $phpcsFile, $stackPtr)
	    {
		unset($stackPtr);

		$fileName = $phpcsFile->getFilename();

		$rhinoPath  = Config::getConfigData("rhino_path");
		$jshintPath = Config::getConfigData("jshint_path");
		if ($rhinoPath !== null && $jshintPath !== null)
		    {
			$cmd = $rhinoPath . " \"" . $jshintPath . "\" \"" . $fileName . "\"";
			$msg = exec($cmd, $output, $retval);

			$tokens = &$phpcsFile->tokens;

			foreach ($output as $finding)
			    {
				if (preg_match("/^(.+)\(.+:([0-9]+).*:[0-9]+\)$/", $finding, $matches) > 0)
				    {
					$line    = (int) $matches[2];
					$message = "jshint " . _("says") . ": " . trim($matches[1]);

					// Find the token at the start of the line.
					$lineToken = null;
					foreach ($tokens as $ptr => $info)
					    {
						if ($info["line"] === $line)
						    {
							$lineToken = $ptr;
							break;
						    }
					    }

					if ($lineToken !== null)
					    {
						$phpcsFile->addWarning($message, $lineToken, "ExternalTool");
					    }
				    }
			    } //end foreach
		    } //end if
	    } //end process()


    } //end class

?>
