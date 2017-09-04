<?php

/**
 * PHP version 5
 *
 * @package Logics\BuildTools\CodeSniffer\Squiz
 */

namespace Logics\BuildTools\CodeSniffer\Squiz;

use \Logics\BuildTools\CodeSniffer\Config;
use \Logics\BuildTools\CodeSniffer\File;
use \Logics\BuildTools\CodeSniffer\Sniff;

/**
 * Squiz_Sniffs_Debug_JSLintSniff.
 *
 * Runs jslint.js on the file.
 *
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2006-2012 Squiz Pty Ltd (ABN 77 084 670 600)
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 * @version   SVN: $Date: 2016-08-16 23:39:01 +0900 (Tue, 16 Aug 2016) $ $Revision: 26 $
 * @link      $HeadURL: https://open.logics.net.au/buildtools/codesniffer/tags/0.1.3/src/CodeSniffer/Standards/Squiz/Sniffs/Debug/JSLintSniff.php $
 *
 * @untranslatable JS
 */

class JSLintSniff implements Sniff
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
	 * @untranslatable jslint_path
	 * @untranslatable jslint says:
	 * @untranslatable ExternalTool
	 */

	public function process(File $phpcsFile, $stackPtr)
	    {
		unset($stackPtr);
		$fileName = $phpcsFile->getFilename();

		$rhinoPath  = Config::getConfigData("rhino_path");
		$jslintPath = Config::getConfigData("jslint_path");
		if ($rhinoPath !== null && $jslintPath !== null)
		    {
			$cmd = $rhinoPath . " \"" . $jslintPath . "\" \"" . $fileName . "\"";
			exec($cmd, $output);

			if (is_array($output) === true)
			    {
				$tokens = &$phpcsFile->tokens;

				foreach ($output as $finding)
				    {
					$matches    = array();
					$numMatches = preg_match("/Lint at line ([0-9]+).*:(.*)$/", $finding, $matches);
					if ($numMatches !== 0)
					    {
						$line    = (int) $matches[1];
						$message = "jslint says: " . trim($matches[2]);

						$lineToken = $this->_getLineToken($tokens, $line);

						if ($lineToken !== null)
						    {
							$phpcsFile->addWarning($message, $lineToken, "ExternalTool");
						    }
					    }
				    } //end foreach
			    } //end if
		    } //end if
	    } //end process()


	/**
	 * Find the token at the start of the line.
	 *
	 * @param array $tokens All tokens
	 * @param int   $line   Line number
	 *
	 * @return int Position of the token in the start of the line
	 */

	private function _getLineToken(array &$tokens, $line)
	    {
		$lineToken = null;
		foreach ($tokens as $ptr => $info)
		    {
			if ($info["line"] === $line)
			    {
				$lineToken = $ptr;
				break;
			    }
		    }

		return $lineToken;
	    } //end _getLineToken()


    } //end class

?>
