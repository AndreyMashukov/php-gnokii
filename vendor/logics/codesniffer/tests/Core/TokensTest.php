<?php

/**
 * PHP version 5
 *
 * @package Logics\Tests\BuildTools\CodeSniffer
 */

namespace Logics\Tests\BuildTools\CodeSniffer;

use \Logics\BuildTools\CodeSniffer\Tokens;
use \PHPUnit_Framework_TestCase;

/**
 * Tests for extended tokens
 *
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2006-2012 Squiz Pty Ltd (ABN 77 084 670 600)
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 * @version   SVN: $Date: 2016-08-16 23:39:01 +0900 (Tue, 16 Aug 2016) $ $Revision: 26 $
 * @link      $HeadURL: https://open.logics.net.au/buildtools/codesniffer/tags/0.1.3/tests/Core/TokensTest.php $
 *
 * @runTestsInSeparateProcesses
 *
 * @donottranslate
 */

class TokensTest extends PHPUnit_Framework_TestCase
    {

	/**
	 * Test for highest weighted token
	 *
	 * @return void
	 */

	public function testCanIdentifyMostInluentialTokenInArray()
	    {
		$this->assertEquals(false, Tokens::getHighestWeightedToken(array()));
		$this->assertEquals(T_WHILE, Tokens::getHighestWeightedToken(array(T_WHILE)));
		$this->assertEquals(T_CLASS, Tokens::getHighestWeightedToken(array(T_WHILE, T_CLASS)));
	    } //end testCanIdentifyMostInluentialTokenInArray()


	/**
	 * Test resolution of simple tokens
	 *
	 * @return void
	 */

	public function testResolvesSimpleTokensIntoComplexToken()
	    {
		$this->assertEquals(
		    array(
		     "type"    => "T_OPEN_CURLY_BRACKET",
		     "code"    => "PHPCS_T_OPEN_CURLY_BRACKET",
		     "content" => "{",
		    ),
		    Tokens::resolveSimpleToken("{")
		);
	    } //end testResolvesSimpleTokensIntoComplexToken()


	/**
	 * Test conversion from PHP tokens to CodeSniffer tokens
	 *
	 * @return void
	 */

	public function testConvertsPhpTokensToCodesnifferTokens()
	    {
		$this->assertEquals(
		    array(
		     "type"    => "T_OPEN_CURLY_BRACKET",
		     "code"    => "PHPCS_T_OPEN_CURLY_BRACKET",
		     "content" => "{",
		    ),
		    Tokens::standardiseToken("{")
		);

		$this->assertEquals(
		    array(
		     "type"    => "T_FALSE",
		     "code"    => "PHPCS_T_FALSE",
		     "content" => "false",
		    ),
		    Tokens::standardiseToken(array(T_STRING, "false", 1))
		);

		$this->assertEquals(
		    array(
		     "type"    => "T_STRING",
		     "code"    => T_STRING,
		     "content" => "text",
		    ),
		    Tokens::standardiseToken(array(T_STRING, "text", 1))
		);

		$this->assertEquals(
		    array(
		     "type"    => "T_OPEN_CURLY_BRACKET",
		     "code"    => "PHPCS_T_OPEN_CURLY_BRACKET",
		     "content" => "{",
		    ),
		    Tokens::standardiseToken(array(T_CURLY_OPEN, "{", 1))
		);

		$this->assertEquals(
		    array(
		     "type"    => "T_IF",
		     "code"    => T_IF,
		     "content" => "if",
		    ),
		    Tokens::standardiseToken(array(T_IF, "if", 1))
		);
	    } //end testConvertsPhpTokensToCodesnifferTokens()


    } //end class

?>