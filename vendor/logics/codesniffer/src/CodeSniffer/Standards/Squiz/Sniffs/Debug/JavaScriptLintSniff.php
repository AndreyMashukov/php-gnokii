<?php

/**
 * PHP version 5
 *
 * @package Logics\BuildTools\CodeSniffer\Squiz
 */

namespace Logics\BuildTools\CodeSniffer\Squiz;

use \Exception;
use \Logics\BuildTools\CodeSniffer\Config;
use \Logics\BuildTools\CodeSniffer\File;
use \Logics\BuildTools\CodeSniffer\Sniff;

/**
 * Squiz_Sniffs_Debug_JavaScriptLintSniff.
 *
 * Runs JavaScript Lint on the file.
 *
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2006-2012 Squiz Pty Ltd (ABN 77 084 670 600)
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 * @version   SVN: $Date: 2016-08-16 23:39:01 +0900 (Tue, 16 Aug 2016) $ $Revision: 26 $
 * @link      $HeadURL: https://open.logics.net.au/buildtools/codesniffer/tags/0.1.3/src/CodeSniffer/Standards/Squiz/Sniffs/Debug/JavaScriptLintSniff.php $
 *
 * @untranslatable JS
 */

class JavaScriptLintSniff implements Sniff
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
	 * @throws Exception If unable to execute JavaScript Lint
	 *
	 * @exceptioncode EXCEPTION_JAVASCRIPTLINT_INVOCATION_FAILURE
	 *
	 * @untranslatable jsl_path
	 * @untranslatable \" -nologo -nofilelisting -nocontext -nosummary -output-format __LINE__:__ERROR__ -process \"
	 * @untranslatable ExternalTool
	 */

	public function process(File $phpcsFile, $stackPtr)
	    {
		unset($stackPtr);
		$fileName = $phpcsFile->getFilename();

		$jslPath = Config::getConfigData("jsl_path");
		if ($jslPath !== null)
		    {
			$cmd = "\"" . $jslPath . "\" -nologo -nofilelisting -nocontext -nosummary -output-format __LINE__:__ERROR__ -process \"" . $fileName . "\"";
			$msg = exec($cmd, $output, $retval);

			// Variable $exitCode is the last line of $output if no error occurs, on error it
			// is numeric. Try to handle various error conditions and provide useful
			// error reporting.
			if ($retval === 2 || $retval === 4)
			    {
				if (is_array($output) === true)
				    {
					$msg = join("\n", $output);
				    }

				throw new Exception(
				    _("Failed invoking JavaScript Lint, retval was [") . $retval . _("], output was [") . $msg . "]",
				    EXCEPTION_JAVASCRIPTLINT_INVOCATION_FAILURE
				);
			    }

			if (is_array($output) === true)
			    {
				$tokens = &$phpcsFile->tokens;

				foreach ($output as $finding)
				    {
					$split   = strpos($finding, ":");
					$line    = substr($finding, 0, $split);
					$message = substr($finding, ($split + 1));

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
						$phpcsFile->addWarning(trim($message), $ptr, "ExternalTool");
					    }
				    } //end foreach
			    } //end if
		    } //end if
	    } //end process()


    } //end class

?>
