<?php

/**
 * Represents a CodeSniffer sniff for sniffing coding standards.
 *
 * PHP version 5
 *
 * @package Logics\BuildTools\CodeSniffer
 */

namespace Logics\BuildTools\CodeSniffer;

/**
 * Represents a CodeSniffer sniff for sniffing coding standards.
 *
 * A sniff registers what token types it wishes to listen for, then, when
 * CodeSniffer encounters that token, the sniff is invoked and passed
 * information about where the token was found in the stack, and the
 * CodeSniffer file in which the token was found.
 *
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @author    Marc McIntyre <mmcintyre@squiz.net>
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2006-2012 Squiz Pty Ltd (ABN 77 084 670 600)
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 * @version   SVN: $Date: 2016-08-16 23:39:01 +0900 (Tue, 16 Aug 2016) $ $Revision: 26 $
 * @link      $HeadURL: https://open.logics.net.au/buildtools/codesniffer/tags/0.1.3/src/CodeSniffer/Sniff.php $
 */

interface Sniff
    {

	/**
	 * Registers the tokens that this sniff wants to listen for.
	 *
	 * An example return value for a sniff that wants to listen for whitespace
	 * and any comments would be:
	 *
	 * <code>
	 *    return array(
	 *            T_WHITESPACE,
	 *            T_DOC_COMMENT,
	 *            T_COMMENT,
	 *           );
	 * </code>
	 *
	 * @return array(int)
	 * @see    Tokens.php
	 */

	public function register();


	/**
	 * Called when one of the token types that this sniff is listening for
	 * is found.
	 *
	 * The stackPtr variable indicates where in the stack the token was found.
	 * A sniff can acquire information this token, along with all the other
	 * tokens within the stack by first acquiring the token stack:
	 *
	 * <code>
	 *    $tokens = $phpcsFile->getTokens();
	 *    echo 'Encountered a '.$tokens[$stackPtr]['type'].' token';
	 *    echo 'token information: ';
	 *    print_r($tokens[$stackPtr]);
	 * </code>
	 *
	 * If the sniff discovers an anomaly in the code, they can raise an error
	 * by calling addError() on the File object, specifying an error
	 * message and the position of the offending token:
	 *
         * <code>
	 *    $phpcsFile->addError('Encountered an error', $stackPtr);
	 * </code>
	 *
	 * @param File $phpcsFile The CodeSniffer file where the
	 *                        token was found.
	 * @param int  $stackPtr  The position in the CodeSniffer
	 *                        file's token stack where the token
	 *                        was found.
	 *
	 * @return void
	 */

	public function process(File $phpcsFile, $stackPtr);


    } //end interface

?>
