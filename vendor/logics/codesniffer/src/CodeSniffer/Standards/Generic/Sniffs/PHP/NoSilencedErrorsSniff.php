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
 * NoSilencedErrorsSniff.
 *
 * Throws an error or warning when any code prefixed with an asperand is encountered.
 *
 * <code>
 *  if (@in_array($array, $needle))
 *  {
 *      doSomething();
 *  }
 * </code>
 *
 * @author    Andy Brockhurst <abrock@yahoo-inc.com>
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 * @version   SVN: $Date: 2016-08-16 23:39:01 +0900 (Tue, 16 Aug 2016) $ $Revision: 26 $
 * @link      $HeadURL: https://open.logics.net.au/buildtools/codesniffer/tags/0.1.3/src/CodeSniffer/Standards/Generic/Sniffs/PHP/NoSilencedErrorsSniff.php $
 */

class NoSilencedErrorsSniff implements Sniff
    {

	/**
	 * If true, an error will be thrown; otherwise a warning.
	 *
	 * @var bool
	 */
	public $error = false;

	/**
	 * Returns an array of tokens this test wants to listen for.
	 *
	 * @return array
	 *
	 * @internalconst T_ASPERAND T_ASPERAND token
	 */

	public function register()
	    {
		return array(T_ASPERAND);
	    } //end register()


	/**
	 * Processes this test, when one of its tokens is encountered.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $stackPtr  The position of the current token in the stack passed in $tokens.
	 *
	 * @return void
	 *
	 * @untranslatable Forbidden
	 * @untranslatable Discouraged
	 */

	public function process(File $phpcsFile, $stackPtr)
	    {
		if ($this->error === true)
		    {
			$phpcsFile->addError(_("Silencing errors is forbidden"), $stackPtr, "Forbidden");
		    }
		else
		    {
			$phpcsFile->addWarning(_("Silencing errors is discouraged"), $stackPtr, "Discouraged");
		    }
	    } //end process()


    } //end class

?>
