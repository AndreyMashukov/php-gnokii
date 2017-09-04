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
 * ClosureLinterSniff.
 *
 * Runs gjslint on the file.
 *
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2006-2012 Squiz Pty Ltd (ABN 77 084 670 600)
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 * @version   SVN: $Date: 2016-08-16 23:39:01 +0900 (Tue, 16 Aug 2016) $ $Revision: 26 $
 * @link      $HeadURL: https://open.logics.net.au/buildtools/codesniffer/tags/0.1.3/src/CodeSniffer/Standards/Generic/Sniffs/Debug/ClosureLinterSniff.php $
 *
 * @untranslatable JS
 */

class ClosureLinterSniff implements Sniff
    {

	/**
	 * A list of error codes that should show errors.
	 *
	 * All other error codes will show warnings.
	 *
	 * @var int
	 */
	public $errorCodes = array();

	/**
	 * A list of error codes to ignore.
	 *
	 * @var int
	 */
	public $ignoreCodes = array();

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
	 * @untranslatable gjslint_path
	 * @untranslatable --nosummary --notime --unix_mode \"
	 * @untranslatable gjslint
	 * @untranslatable : (%s) %s
	 * @untranslatable ExternalToolError
	 * @untranslatable ExternalTool
	 */

	public function process(File $phpcsFile, $stackPtr)
	    {
		unset($stackPtr);

		$fileName = $phpcsFile->getFilename();

		$lintPath = Config::getConfigData("gjslint_path");
		if ($lintPath !== null)
		    {
			$cmd = $lintPath . " --nosummary --notime --unix_mode \"" . $fileName . "\"";
			$msg = exec($cmd, $output, $retval);

			$tokens = &$phpcsFile->tokens;

			foreach ($output as $finding)
			    {
				if (preg_match("/^(.*):([0-9]+):\(.*?([0-9]+)\)(.*)$/", $finding, $matches) > 0 && in_array($matches[3], $this->ignoreCodes) === false)
				    {
					$line  = (int) $matches[2];
					$code  = $matches[3];
					$error = trim($matches[4]);

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
						$message = "gjslint " . _("says") . ": (%s) %s";
						$data    = array(
							    $code,
							    $error,
							   );
						if (in_array($code, $this->errorCodes) === true)
						    {
							$phpcsFile->addError($message, $lineToken, "ExternalToolError", $data);
						    }
						else
						    {
							$phpcsFile->addWarning($message, $lineToken, "ExternalTool", $data);
						    }
					    }
				    } //end if
			    } //end foreach
		    } //end if
	    } //end process()


    } //end class

?>
