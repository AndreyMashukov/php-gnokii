<?php

/**
 * An AbstractScopeTest allows for tests that extend from this class to
 * listen for tokens within a particular scope.
 *
 * PHP version 5.6
 *
 * @package Logics\BuildTools\CodeSniffer
 */

namespace Logics\BuildTools\CodeSniffer;

use \Exception;

/**
 * An AbstractScopeTest allows for tests that extend from this class to
 * listen for tokens within a particular scope.
 *
 * Below is a test that listens to methods that exist only within classes:
 * <code>
 * class ClassScopeTest extends AbstractScopeSniff
 * {
 *     public function __construct()
 *     {
 *         parent::__construct(array(T_CLASS), array(T_FUNCTION));
 *     }
 *
 *     protected function processTokenWithinScope(File &$phpcsFile, $)
 *     {
 *         $className = $phpcsFile->getDeclarationName($currScope);
 *         echo 'encountered a method within class '.$className;
 *     }
 * }
 * </code>
 *
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @author    Marc McIntyre <mmcintyre@squiz.net>
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2006-2012 Squiz Pty Ltd (ABN 77 084 670 600)
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 * @version   SVN: $Date: 2016-08-16 23:39:01 +0900 (Tue, 16 Aug 2016) $ $Revision: 26 $
 * @link      $HeadURL: https://open.logics.net.au/buildtools/codesniffer/tags/0.1.3/src/CodeSniffer/Standards/AbstractScopeSniff.php $
 */

abstract class AbstractScopeSniff implements Sniff
    {

	/**
	 * The token types that this test wishes to listen to within the scope.
	 *
	 * @var array
	 */
	private $_tokens = array();

	/**
	 * The type of scope opener tokens that this test wishes to listen to.
	 *
	 * @var string
	 */
	private $_scopeTokens = array();

	/**
	 * True if this test should fire on tokens outside of the scope.
	 *
	 * @var bool
	 */
	private $_listenOutside = false;

	/**
	 * Constructs a new AbstractScopeTest.
	 *
	 * @param array $scopeTokens   The type of scope the test wishes to listen to.
	 * @param array $tokens        The tokens that the test wishes to listen to within the scope.
	 * @param bool  $listenOutside If true this test will also alert the extending class when a token is found outside
	 *                             the scope, by calling the processTokenOutsideScope method.
	 *
	 * @return void
	 *
	 * @throws Exception If the specified tokens array is empty.
	 *
	 * @exceptioncode EXCEPTION_SCOPE_TOKENS_LIST_IS_EMPTY
	 * @exceptioncode EXCEPTION_TOKENS_LIST_IS_EMPTY
	 * @exceptioncode EXCEPTION_INVALID_SCOPE_TOKENS
	 *
	 * @see CodeSniffer.getValidScopeTokeners()
	 */

	public function __construct(array $scopeTokens, array $tokens, $listenOutside = false)
	    {
		if (empty($scopeTokens) === true)
		    {
			throw new Exception(_("The scope tokens list cannot be empty"), EXCEPTION_SCOPE_TOKENS_LIST_IS_EMPTY);
		    }

		if (empty($tokens) === true)
		    {
			throw new Exception(_("The tokens list cannot be empty"), EXCEPTION_TOKENS_LIST_IS_EMPTY);
		    }

		$invalidScopeTokens = array_intersect($scopeTokens, $tokens);
		if (empty($invalidScopeTokens) === false)
		    {
			throw new Exception(
			    _("Scope tokens") . " [" . implode(", ", $invalidScopeTokens) . "] " . _("cannot be in the tokens array"),
			    EXCEPTION_INVALID_SCOPE_TOKENS
			);
		    }

		$this->_listenOutside = $listenOutside;
		$this->_scopeTokens   = $scopeTokens;
		$this->_tokens        = $tokens;
	    } //end __construct()


	/**
	 * The method that is called to register the tokens this test wishes to
	 * listen to.
	 *
	 * DO NOT OVERRIDE THIS METHOD. Use the constructor of this class to register
	 * for the desired tokens and scope.
	 *
	 * @return array(int)
	 *
	 * @see __constructor()
	 */

	public final function register()
	    {
		return $this->_tokens;
	    } //end register()


	/**
	 * Processes the tokens that this test is listening for.
	 *
	 * @param File $phpcsFile The file where this token was found.
	 * @param int  $stackPtr  The position in the stack where this token was found.
	 *
	 * @return void
	 *
	 * @see processTokenWithinScope()
	 */

	public final function process(File $phpcsFile, $stackPtr)
	    {
		$tokens = &$phpcsFile->tokens;

		$foundScope = false;
		foreach ($tokens[$stackPtr]["conditions"] as $scope => $code)
		    {
			if (in_array($code, $this->_scopeTokens) === true)
			    {
				$this->processTokenWithinScope($phpcsFile, $stackPtr, $scope);
				$foundScope = true;
			    }
		    }

		if ($this->_listenOutside === true && $foundScope === false)
		    {
			$this->processTokenOutsideScope($phpcsFile, $stackPtr);
		    }
	    } //end process()


	/**
	 * Processes a token that is found within the scope that this test is
	 * listening to.
	 *
	 * @param File $phpcsFile The file where this token was found.
	 * @param int  $stackPtr  The position in the stack where this token was found.
	 * @param int  $currScope The position in the tokens array that opened the scope
	 *                        that this test is listening for.
	 *
	 * @return void
	 */

	protected abstract function processTokenWithinScope(File &$phpcsFile, $stackPtr, $currScope);


	/**
	 * Processes a token that is found within the scope that this test is
	 * listening to.
	 *
	 * @param File $phpcsFile The file where this token was found.
	 * @param int  $stackPtr  The position in the stack where this token was found.
	 *
	 * @return void
	 */

	protected function processTokenOutsideScope(File &$phpcsFile, $stackPtr)
	    {
		unset($phpcsFile);
		unset($stackPtr);
	    } //end processTokenOutsideScope()


    } //end class

?>
