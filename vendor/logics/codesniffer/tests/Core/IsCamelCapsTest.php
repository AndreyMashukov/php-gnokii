<?php

/**
 * PHP version 5
 *
 * @package Logics\Tests\BuildTools\CodeSniffer
 */

namespace Logics\Tests\BuildTools\CodeSniffer;

use \Logics\BuildTools\CodeSniffer\CodeSniffer;
use \PHPUnit_Framework_TestCase;

/**
 * Tests for the CodeSniffer:isCamelCaps method.
 *
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @author    Marc McIntyre <mmcintyre@squiz.net>
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2006-2012 Squiz Pty Ltd (ABN 77 084 670 600)
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 * @version   SVN: $Date: 2016-08-16 23:39:01 +0900 (Tue, 16 Aug 2016) $ $Revision: 26 $
 * @link      $HeadURL: https://open.logics.net.au/buildtools/codesniffer/tags/0.1.3/tests/Core/IsCamelCapsTest.php $
 *
 * @donottranslate
 */

class IsCamelCapsTest extends PHPUnit_Framework_TestCase
    {

	/**
	 * Test valid public function/method names.
	 *
	 * @return void
	 */

	public function testValidNotClassFormatPublic()
	    {
		$this->assertTrue(CodeSniffer::isCamelCaps("thisIsCamelCaps", false, true, true));
		$this->assertTrue(CodeSniffer::isCamelCaps("thisISCamelCaps", false, true, false));
	    } //end testValidNotClassFormatPublic()


	/**
	 * Test invalid public function/method names.
	 *
	 * @return void
	 */

	public function testInvalidNotClassFormatPublic()
	    {
		$this->assertFalse(CodeSniffer::isCamelCaps("_thisIsCamelCaps", false, true, true));
		$this->assertFalse(CodeSniffer::isCamelCaps("thisISCamelCaps", false, true, true));
		$this->assertFalse(CodeSniffer::isCamelCaps("ThisIsCamelCaps", false, true, true));

		$this->assertFalse(CodeSniffer::isCamelCaps("3thisIsCamelCaps", false, true, true));
		$this->assertFalse(CodeSniffer::isCamelCaps("*thisIsCamelCaps", false, true, true));
		$this->assertFalse(CodeSniffer::isCamelCaps("-thisIsCamelCaps", false, true, true));

		$this->assertFalse(CodeSniffer::isCamelCaps("this*IsCamelCaps", false, true, true));
		$this->assertFalse(CodeSniffer::isCamelCaps("this-IsCamelCaps", false, true, true));
		$this->assertFalse(CodeSniffer::isCamelCaps("this_IsCamelCaps", false, true, true));
		$this->assertFalse(CodeSniffer::isCamelCaps("this_is_camel_caps", false, true, true));
	    } //end testInvalidNotClassFormatPublic()


	/**
	 * Test valid private method names.
	 *
	 * @return void
	 */

	public function testValidNotClassFormatPrivate()
	    {
		$this->assertTrue(CodeSniffer::isCamelCaps("_thisIsCamelCaps", false, false, true));
		$this->assertTrue(CodeSniffer::isCamelCaps("_thisISCamelCaps", false, false, false));
		$this->assertTrue(CodeSniffer::isCamelCaps("_i18N", false, false, true));
		$this->assertTrue(CodeSniffer::isCamelCaps("_i18n", false, false, true));
	    } //end testValidNotClassFormatPrivate()


	/**
	 * Test invalid private method names.
	 *
	 * @return void
	 */

	public function testInvalidNotClassFormatPrivate()
	    {
		$this->assertFalse(CodeSniffer::isCamelCaps("thisIsCamelCaps", false, false, true));
		$this->assertFalse(CodeSniffer::isCamelCaps("_thisISCamelCaps", false, false, true));
		$this->assertFalse(CodeSniffer::isCamelCaps("_ThisIsCamelCaps", false, false, true));
		$this->assertFalse(CodeSniffer::isCamelCaps("__thisIsCamelCaps", false, false, true));
		$this->assertFalse(CodeSniffer::isCamelCaps("__thisISCamelCaps", false, false, false));

		$this->assertFalse(CodeSniffer::isCamelCaps("3thisIsCamelCaps", false, false, true));
		$this->assertFalse(CodeSniffer::isCamelCaps("*thisIsCamelCaps", false, false, true));
		$this->assertFalse(CodeSniffer::isCamelCaps("-thisIsCamelCaps", false, false, true));
		$this->assertFalse(CodeSniffer::isCamelCaps("_this_is_camel_caps", false, false, true));
	    } //end testInvalidNotClassFormatPrivate()


	/**
	 * Test valid class names.
	 *
	 * @return void
	 */

	public function testValidClassFormatPublic()
	    {
		$this->assertTrue(CodeSniffer::isCamelCaps("ThisIsCamelCaps", true, true, true));
		$this->assertTrue(CodeSniffer::isCamelCaps("ThisISCamelCaps", true, true, false));
		$this->assertTrue(CodeSniffer::isCamelCaps("This3IsCamelCaps", true, true, false));
	    } //end testValidClassFormatPublic()


	/**
	 * Test invalid class names.
	 *
	 * @return void
	 */

	public function testInvalidClassFormat()
	    {
		$this->assertFalse(CodeSniffer::isCamelCaps("thisIsCamelCaps", true));
		$this->assertFalse(CodeSniffer::isCamelCaps("This-IsCamelCaps", true));
		$this->assertFalse(CodeSniffer::isCamelCaps("This_Is_Camel_Caps", true));
	    } //end testInvalidClassFormat()


	/**
	 * Test invalid class names with the private flag set.
	 *
	 * Note that the private flag is ignored if the class format
	 * flag is set, so these names are all invalid.
	 *
	 * @return void
	 */

	public function testInvalidClassFormatPrivate()
	    {
		$this->assertFalse(CodeSniffer::isCamelCaps("_ThisIsCamelCaps", true, true));
		$this->assertFalse(CodeSniffer::isCamelCaps("_ThisIsCamelCaps", true, false));
	    } //end testInvalidClassFormatPrivate()


    } //end class

?>
