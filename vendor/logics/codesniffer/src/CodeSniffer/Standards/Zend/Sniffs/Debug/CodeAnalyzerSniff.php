<?php

/**
 * PHP version 5
 *
 * @package Logics\BuildTools\CodeSniffer\Zend
 */

namespace Logics\BuildTools\CodeSniffer\Zend;

use \Exception;
use \Logics\BuildTools\CodeSniffer\Config;
use \Logics\BuildTools\CodeSniffer\File;
use \Logics\BuildTools\CodeSniffer\Sniff;

/**
 * CodeAnalyzerSniff.
 *
 * Runs the Zend Code Analyzer (from Zend Studio) on the file.
 *
 * @author    Holger Kral <holger.kral@zend.com>
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2006-2012 Squiz Pty Ltd (ABN 77 084 670 600)
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 * @version   SVN: $Date: 2016-08-16 23:39:01 +0900 (Tue, 16 Aug 2016) $ $Revision: 26 $
 * @link      $HeadURL: https://open.logics.net.au/buildtools/codesniffer/tags/0.1.3/src/CodeSniffer/Standards/Zend/Sniffs/Debug/CodeAnalyzerSniff.php $
 */

class CodeAnalyzerSniff implements Sniff
    {

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
	 * @throws Exception Zend analyzer failure
	 *
	 * @exceptioncode EXCEPTION_ZENDCODEANALYZER_INVOCATION_FAILURE
	 *
	 * @untranslatable zend_ca_path
	 * @untranslatable ExternalTool
	 */

	public function process(File $phpcsFile, $stackPtr)
	    {
		$analyzerPath = Config::getConfigData("zend_ca_path");

		// Because we are analyzing the whole file in one step, execute this method only on first occurrence of a T_OPEN_TAG.
		$prevOpenTag = $phpcsFile->findPrevious(T_OPEN_TAG, ($stackPtr - 1));

		if ($prevOpenTag === false && $analyzerPath !== null)
		    {
			$fileName = $phpcsFile->getFilename();

			// In the command, 2>&1 is important because the code analyzer sends its findings to stderr.
			// $output normally contains only stdout, so using 2>&1 will pipe even stderr to stdout.
			$cmd = $analyzerPath . " " . $fileName . " 2>&1";

			// There is the possibility to pass "--ide" as an option to the analyzer.
			// This would result in an output format which would be easier to parse.
			// The problem here is that no cleartext error messages are returnwd.
			// Only error-code-labels. So for a start we go for cleartext output.
			$exitCode = exec($cmd, $output, $retval);

			// Code $exitCode is the last line of $output if no error occures, on error it is numeric.
			// Try to handle various error conditions and provide useful error reporting.
			if (is_numeric($exitCode) === true && $exitCode > 0)
			    {
				$msg = join('\n', $output);

				throw new Exception(
				    _("Failed invoking ZendCodeAnalyzer, exitcode was") . " [" . $exitCode . "], " .
				    _("retval was") . " [" . $retval . "], " . _("output was") . " [" . $msg . "]",
				    EXCEPTION_ZENDCODEANALYZER_INVOCATION_FAILURE
				);
			    }

			$tokens = &$phpcsFile->tokens;
			foreach ($output as $finding)
			    {
				// The first two lines of analyzer output contain something like this.
				// Zend Code Analyzer 1.2.2  Analyzing <filename>... So skip these...
				$res = preg_match("/^.+\(line ([0-9]+)\):(.+)$/", $finding, $regs);
				if (empty($regs) === false && $res > 0)
				    {
					// Find the token at the start of the line.
					$lineToken = null;
					foreach ($tokens as $ptr => $info)
					    {
						if ($info["line"] === $regs[1])
						    {
							$lineToken = $ptr;
							break;
						    }
					    }

					if ($lineToken !== null)
					    {
						$phpcsFile->addWarning(trim($regs[2]), $ptr, "ExternalTool");
					    }
				    }
			    } //end foreach
		    } //end if
	    } //end process()


    } //end class

?>