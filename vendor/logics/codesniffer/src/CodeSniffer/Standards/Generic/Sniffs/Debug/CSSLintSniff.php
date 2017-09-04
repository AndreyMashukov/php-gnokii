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
 * CSSLintSniff.
 *
 * Runs csslint on the file.
 *
 * @author    Roman Levishchenko <index.0h@gmail.com>
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2013 Roman Levishchenko
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 * @version   SVN: $Date: 2016-08-16 23:39:01 +0900 (Tue, 16 Aug 2016) $ $Revision: 26 $
 * @link      $HeadURL: https://open.logics.net.au/buildtools/codesniffer/tags/0.1.3/src/CodeSniffer/Standards/Generic/Sniffs/Debug/CSSLintSniff.php $
 *
 * @untranslatable CSS
 */

class CSSLintSniff implements Sniff
    {

	/**
	 * A list of tokenizers this sniff supports.
	 *
	 * @var array
	 */
	public $supportedTokenizers = array("CSS");

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
	 * @untranslatable csslint_path
	 * @untranslatable csslint
	 * @untranslatable ExternalTool
	 */

	public function process(File $phpcsFile, $stackPtr)
	    {
		unset($stackPtr);

		$fileName = $phpcsFile->getFilename();

		$csslintPath = Config::getConfigData("csslint_path");
		if ($csslintPath !== null)
		    {
			$cmd = $csslintPath . " " . escapeshellarg($fileName);
			exec($cmd, $output, $retval);

			$tokens = &$phpcsFile->tokens;
			$count  = count($output);

			for ($i = 0; $i < $count; $i++)
			    {
				if (preg_match("/(error|warning) at line (\d+)/", $output[$i], $matches) > 0)
				    {
					$line    = (int) $matches[2];
					$message = "csslint " . _("says") . ": " . $output[($i + 1)];
					// First line is message with error line and error code.
					// Second error message.
					// Third wrong line in file.
					// Fourth empty line.
					$i += 4;

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
				    } //end if
			    } //end for
		    } //end if
	    } //end process()


    } //end class

?>
