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
 * Squiz_Sniffs_Objects_ObjectInstantiationSniff.
 *
 * Ensures objects are assigned to a variable when instantiated.
 *
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @author    Marc McIntyre <mmcintyre@squiz.net>
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2006-2012 Squiz Pty Ltd (ABN 77 084 670 600)
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 * @version   SVN: $Date: 2016-08-16 23:39:01 +0900 (Tue, 16 Aug 2016) $ $Revision: 26 $
 * @link      $HeadURL: https://open.logics.net.au/buildtools/codesniffer/tags/0.1.3/src/CodeSniffer/Standards/Squiz/Sniffs/Objects/ObjectInstantiationSniff.php $
 */

class ObjectInstantiationSniff implements Sniff
    {

	/**
	 * Registers the token types that this sniff wishes to listen to.
	 *
	 * @return array
	 */

	public function register()
	    {
		return array(T_NEW);
	    } //end register()


	/**
	 * Process the tokens that this sniff is listening for.
	 *
	 * @param File $phpcsFile The file where the token was found.
	 * @param int  $stackPtr  The position in the stack where the token was found.
	 *
	 * @return void
	 *
	 * @internalconst T_BITWISE_AND T_BITWISE_AND token
	 * @internalconst T_EQUAL       T_EQUAL token
	 * @internalconst T_INLINE_THEN T_INLINE_THEN token
	 * @internalconst T_INLINE_ELSE T_INLINE_ELSE token
	 *
	 * @untranslatable NotAssigned
	 */

	public function process(File $phpcsFile, $stackPtr)
	    {
		$tokens = &$phpcsFile->tokens;

		$allowedTokens   = Tokens::$emptyTokens;
		$allowedTokens[] = T_BITWISE_AND;

		$prev = $phpcsFile->findPrevious($allowedTokens, ($stackPtr - 1), null, true);

		$allowedTokens = array(
				  T_EQUAL,
				  T_DOUBLE_ARROW,
				  T_THROW,
				  T_RETURN,
				  T_INLINE_THEN,
				  T_INLINE_ELSE,
				 );

		if (in_array($tokens[$prev]["code"], $allowedTokens) === false)
		    {
			$error = _("New objects must be assigned to a variable");
			$phpcsFile->addError($error, $stackPtr, "NotAssigned");
		    }
	    } //end process()


    } //end class

?>
