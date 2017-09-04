<?php

/**
 * PHP version 5
 *
 * @package Logics\BuildTools\CodeSniffer\Beauty
 */

namespace Logics\BuildTools\CodeSniffer\Beauty;

use \Logics\BuildTools\CodeSniffer\File;
use \Logics\BuildTools\CodeSniffer\Sniff;

/**
 * Beauty_Sniffs_PHP_UseOrderSniff
 *
 * Use statements should be in alphabetical order
 *
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 * @version   SVN: $Date: 2016-08-16 23:39:01 +0900 (Tue, 16 Aug 2016) $ $Revision: 26 $
 * @link      $HeadURL: https://open.logics.net.au/buildtools/codesniffer/tags/0.1.3/src/CodeSniffer/Standards/Beauty/Sniffs/PHP/UseOrderSniff.php $
 */

class UseOrderSniff implements Sniff
    {

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
	 * Processes this test, when use statement is used
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $stackPtr  The position of the current token in the stack passed in $tokens.
	 *
	 * @return void
	 *
	 * @untranslatable UseOrder
	 */

	public function process(File $phpcsFile, $stackPtr)
	    {
		unset($stackPtr);
		$tokens = &$phpcsFile->tokens;
		$uses   = array();
		for ($i = 0; $i < $phpcsFile->numTokens; $i++)
		    {
			if ($tokens[$i]["code"] === T_USE && $tokens[$i]["level"] === 0)
			    {
				$nametoken        = $phpcsFile->findNext(array(T_WHITESPACE), ($i + 1), null, true);
				$uses[$nametoken] = $this->_getClassName($nametoken, $tokens);
			    }
		    }

		$sorted = $uses;
		asort($sorted);
		reset($sorted);

		foreach ($uses as $token => $use)
		    {
			$compare = each($sorted);
			if ($use !== $compare["value"])
			    {
				$phpcsFile->addError(_("Use statement not in alphabetical order"), $token, "UseOrder");
			    }
		    }
	    } //end process()


	/**
	 * Get class name from token stream
	 *
	 * @param int   $i       Token position from which class name should be reconstructed
	 * @param array $tokens  All tokens
	 * @param bool  $reverse True if class name reconstruction should be done in reverse
	 *
	 * @return string Class name
	 */

	private function _getClassName(&$i, array &$tokens, $reverse = false)
	    {
		$class = "";
		while ($tokens[$i]["code"] === T_STRING || $tokens[$i]["code"] === T_NS_SEPARATOR || $tokens[$i]["code"] === T_NAMESPACE)
		    {
			if ($reverse === true)
			    {
				$class = $tokens[$i]["content"] . $class;
				$i--;
			    }
			else
			    {
				$class .= $tokens[$i]["content"];
				$i++;
			    }
		    }

		return $class;
	    } //end _getClassName()


    } //end class

?>
