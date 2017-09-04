<?php

/**
 * PHP version 5
 *
 * @package Logics\BuildTools\CodeSniffer\Squiz
 */

namespace Logics\BuildTools\CodeSniffer\Squiz;

use \Logics\BuildTools\CodeSniffer\File;
use \Logics\BuildTools\CodeSniffer\Sniff;
use \Logics\BuildTools\CodeSniffer\Tokens;

/**
 * Ensures objects are assigned to a variable when instantiated.
 *
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2006-2012 Squiz Pty Ltd (ABN 77 084 670 600)
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 * @version   SVN: $Date: 2016-08-16 23:39:01 +0900 (Tue, 16 Aug 2016) $ $Revision: 26 $
 * @link      $HeadURL: https://open.logics.net.au/buildtools/codesniffer/tags/0.1.3/src/CodeSniffer/Standards/Squiz/Sniffs/Objects/ObjectMemberCommaSniff.php $
 *
 * @untranslatable JS
 */

class ObjectMemberCommaSniff implements Sniff
    {

	/**
	 * A list of tokenizers this sniff supports.
	 *
	 * @var array
	 */
	public $supportedTokenizers = array("JS");

	/**
	 * Registers the token types that this sniff wishes to listen to.
	 *
	 * @return array
	 *
	 * @internalconst T_CLOSE_OBJECT T_CLOSE_OBJECT token
	 */

	public function register()
	    {
		return array(T_CLOSE_OBJECT);
	    } //end register()


	/**
	 * Process the tokens that this sniff is listening for.
	 *
	 * @param File $phpcsFile The file where the token was found.
	 * @param int  $stackPtr  The position in the stack where the token was found.
	 *
	 * @return void
	 *
	 * @internalconst T_COMMA T_COMMA token
	 *
	 * @untranslatable Missing
	 */

	public function process(File $phpcsFile, $stackPtr)
	    {
		$tokens = &$phpcsFile->tokens;

		$prev = $phpcsFile->findPrevious(Tokens::$emptyTokens, ($stackPtr - 1), null, true);
		if ($tokens[$prev]["code"] === T_COMMA)
		    {
			$error = _("Last member of object must not be followed by a comma");
			$phpcsFile->addError($error, $prev, "Missing");
		    }
	    } //end process()


    } //end class

?>
