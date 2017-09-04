<?php

/**
 * PHP version 5.6
 *
 * @package Logics\Tests\Foundation\CLI
 */

namespace Logics\Tests\Foundation\CLI;

use \Exception;
use \Logics\Foundation\CLI\Parser;
use \PHPUnit_Framework_TestCase;

/**
 * Test for CLI options class
 *
 * @author    Vladimir Bashkirtsev   <vladimir@bashkirtsev.com>
 * @author    Anastasia Bashkirtseva <anastasia@bashkirtseva.com>
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://opensource.org/licenses/MIT MIT License
 * @version   SVN: $Date: 2016-08-17 23:19:29 +0930 (Wed, 17 Aug 2016) $ $Revision: 63 $
 * @link      $HeadURL: http://svn.logics.net.au/foundation/CLI/trunk/tests/CLITest.php $
 *
 * @runTestsInSeparateProcesses
 *
 * @donottranslate
 */

class ParserTest extends PHPUnit_Framework_TestCase
    {

	/**
	 * Testing flag option
	 *
	 * @return void
	 */

	public function testShouldReturnCorrectFlagValue()
	    {
		$parser = new Parser();
		$parser->registerOption("f", "help");

		$GLOBALS["argv"] = explode(" ", "phpunit -f");
		$parser->parse();
		$this->assertTrue($parser->getOption("f"));
		$this->assertNull($parser->getOption("g"));
	    } //end testShouldReturnCorrectFlagValue()


	/**
	 * Testing cumulative option
	 *
	 * @return void
	 */

	public function testShouldReturnCorrectCumulativeValue()
	    {
		$parser = new Parser();
		$parser->registerOption("f*", "help");

		$GLOBALS["argv"] = explode(" ", "phpunit -f");
		$parser->parse();
		$this->assertEquals(1, $parser->getOption("f"));

		$GLOBALS["argv"] = explode(" ", "phpunit");
		$parser->parse();
		$this->assertEquals(0, $parser->getOption("f"));

		$GLOBALS["argv"] = explode(" ", "phpunit -f -f");
		$parser->parse();
		$this->assertEquals(2, $parser->getOption("f"));

		$GLOBALS["argv"] = explode(" ", "phpunit -ff");
		$parser->parse();
		$this->assertEquals(2, $parser->getOption("f"));
	    } //end testShouldReturnCorrectCumulativeValue()


	/**
	 * Testing required option
	 *
	 * @return void
	 *
	 * @exceptioncode EXCEPTION_MISSING_REQUIRED_OPTION
	 */

	public function testShouldReturnCorrectRequiredValue()
	    {
		defined("EXCEPTION_MISSING_REQUIRED_OPTION") || define("EXCEPTION_MISSING_REQUIRED_OPTION", 1);

		$parser = new Parser();
		$parser->registerOption("f:<name>=string", "help");

		$GLOBALS["argv"] = explode(" ", "phpunit --f=test");
		$parser->parse();
		$this->assertEquals("test", $parser->getOption("f"));

		$GLOBALS["argv"] = explode(" ", "phpunit -f test1");
		$parser->parse();
		$this->assertEquals("test1", $parser->getOption("f"));

		$GLOBALS["argv"] = explode(" ", "phpunit --f test2");
		$parser->parse();
		$this->assertEquals("test2", $parser->getOption("f"));

		$GLOBALS["argv"] = explode(" ", "phpunit");
		$this->expectException(Exception::CLASS);
		$this->expectExceptionCode(EXCEPTION_MISSING_REQUIRED_OPTION);
		$parser->parse();
	    } //end testShouldReturnCorrectRequiredValue()


	/**
	 * Testing multiple type option
	 *
	 * @return void
	 */

	public function testShouldReturnCorrectMultipleOptionValue()
	    {
		$parser = new Parser();
		$parser->registerOption("f+<name>=string", "help");

		$GLOBALS["argv"] = explode(" ", "phpunit --f=test");
		$parser->parse();
		$this->assertEquals(array("test"), $parser->getOption("f"));

		$GLOBALS["argv"] = explode(" ", "phpunit -f test1 -f test2");
		$parser->parse();
		$this->assertEquals(array(
				     "test1",
				     "test2",
				    ), $parser->getOption("f"));

		$GLOBALS["argv"] = explode(" ", "phpunit");
		$parser->parse();
		$this->assertEquals(array(), $parser->getOption("f"));
	    } //end testShouldReturnCorrectMultipleOptionValue()


	/**
	 * Testing some options
	 *
	 * @return void
	 */

	public function testShouldReturnCorrectOptionValues()
	    {
		$parser = new Parser();
		$parser->registerOption("f+<name>=string", "help");
		$parser->registerOption("e?<name>=string", "help");

		$GLOBALS["argv"] = explode(" ", "phpunit --f=test");
		$parser->parse();
		$this->assertEquals(array("test"), $parser->getOption("f"));
		$this->assertEquals(null, $parser->getOption("e"));

		$GLOBALS["argv"] = explode(" ", "phpunit -f test1 -f test2 -e 113");
		$parser->parse();
		$this->assertEquals(array(
				     "test1",
				     "test2",
				    ), $parser->getOption("f"));
		$this->assertEquals("113", $parser->getOption("e"));
	    } //end testShouldReturnCorrectOptionValues()


	/**
	 * Testing optional option
	 *
	 * @return void
	 *
	 * @exceptioncode EXCEPTION_MISSING_VALUE
	 */

	public function testShouldReturnCorrectOptionalOptionValue()
	    {
		defined("EXCEPTION_MISSING_VALUE") || define("EXCEPTION_MISSING_VALUE", 1);

		$parser = new Parser();
		$parser->registerOption("f?<name>=string", "help");

		$GLOBALS["argv"] = explode(" ", "phpunit --f=test");
		$parser->parse();
		$this->assertEquals("test", $parser->getOption("f"));

		$GLOBALS["argv"] = explode(" ", "phpunit -f test1");
		$parser->parse();
		$this->assertEquals("test1", $parser->getOption("f"));

		$GLOBALS["argv"] = explode(" ", "phpunit --f test2");
		$parser->parse();
		$this->assertEquals("test2", $parser->getOption("f"));

		$GLOBALS["argv"] = explode(" ", "phpunit");
		$parser->parse();
		$this->assertEquals(null, $parser->getOption("f"));

		$GLOBALS["argv"] = explode(" ", "phpunit -f");
		$this->expectException(Exception::CLASS);
		$this->expectExceptionCode(EXCEPTION_MISSING_VALUE);
		$parser->parse();
		$this->assertEquals(null, $parser->getOption("f"));
	    } //end testShouldReturnCorrectOptionalOptionValue()


	/**
	 * Testing option with default value
	 *
	 * @return void
	 */

	public function testShouldReturnCorrectOptionValueWithDefaultValue()
	    {
		unset($GLOBALS["argv"]);

		$parser = new Parser();
		$parser->registerOption("f?<name>=string/I'm default", "help");

		$_SERVER["argv"] = explode(" ", "phpunit --f=test");
		$parser->parse();
		$this->assertEquals("test", $parser->getOption("f"));

		$_SERVER["argv"] = explode(" ", "phpunit");
		$parser->parse();
		$this->assertEquals("I'm default", $parser->getOption("f"));
	    } //end testShouldReturnCorrectOptionValueWithDefaultValue()


	/**
	 * Testing failure when cannot read args
	 *
	 * @return void
	 *
	 * @exceptioncode EXCEPTION_CANNOT_READ_ARGS
	 */

	public function testFailsWhenCannotReadArgs()
	    {
		defined("EXCEPTION_CANNOT_READ_ARGS") || define("EXCEPTION_CANNOT_READ_ARGS", 1);

		unset($GLOBALS["argv"]);
		unset($_SERVER["argv"]);
		$parser = new Parser();
		$this->expectException(Exception::CLASS);
		$this->expectExceptionCode(EXCEPTION_CANNOT_READ_ARGS);
		$parser->parse();
	    } //end testFailsWhenCannotReadArgs()


	/**
	 * Testing failure on attempt to reregister option
	 *
	 * @return void
	 *
	 * @exceptioncode EXCEPTION_ATTEMPT_TO_REDEFINE_OPTION
	 */

	public function testFailsOnAttemptToReregisterOption()
	    {
		defined("EXCEPTION_ATTEMPT_TO_REDEFINE_OPTION") || define("EXCEPTION_ATTEMPT_TO_REDEFINE_OPTION", 1);

		$parser = new Parser();
		$parser->registerOption("f?<name>=string/I'm default", "help");
		$this->expectException(Exception::CLASS);
		$this->expectExceptionCode(EXCEPTION_ATTEMPT_TO_REDEFINE_OPTION);
		$parser->registerOption("f?<name>=string/I'm default", "help");
	    } //end testFailsOnAttemptToReregisterOption()


	/**
	 * Testing failure on attempt to register option on non-existing command
	 *
	 * @return void
	 *
	 * @exceptioncode EXCEPTION_COMMAND_NOT_REGISTERED
	 */

	public function testFailsOnAttemptToRegisterOptionOnNonExistingCommand()
	    {
		defined("EXCEPTION_COMMAND_NOT_REGISTERED") || define("EXCEPTION_COMMAND_NOT_REGISTERED", 1);

		$parser = new Parser();
		$this->expectException(Exception::CLASS);
		$this->expectExceptionCode(EXCEPTION_COMMAND_NOT_REGISTERED);
		$parser->registerOption("f?<name>=string/I'm default", "help", "bad_command");
	    } //end testFailsOnAttemptToRegisterOptionOnNonExistingCommand()


	/**
	 * Testing failure on attempt to set constraint on flag
	 *
	 * @return void
	 *
	 * @exceptioncode EXCEPTION_CONSTRAINT_IS_NOT_ALLOWED
	 */

	public function testFailsOnAttemptToSetConstraintOnFlag()
	    {
		defined("EXCEPTION_CONSTRAINT_IS_NOT_ALLOWED") || define("EXCEPTION_CONSTRAINT_IS_NOT_ALLOWED", 1);

		$parser = new Parser();
		$this->expectException(Exception::CLASS);
		$this->expectExceptionCode(EXCEPTION_CONSTRAINT_IS_NOT_ALLOWED);
		$parser->registerOption("f<name>=string/I'm default", "help");
	    } //end testFailsOnAttemptToSetConstraintOnFlag()


	/**
	 * Testing failure on attempt to set flag name
	 *
	 * @return void
	 *
	 * @exceptioncode EXCEPTION_NAME_IS_NOT_ALLOWED
	 */

	public function testFailsOnAttemptToSetFlagName()
	    {
		defined("EXCEPTION_NAME_IS_NOT_ALLOWED") || define("EXCEPTION_NAME_IS_NOT_ALLOWED", 1);

		$parser = new Parser();
		$this->expectException(Exception::CLASS);
		$this->expectExceptionCode(EXCEPTION_NAME_IS_NOT_ALLOWED);
		$parser->registerOption("f<name>", "help");
	    } //end testFailsOnAttemptToSetFlagName()


	/**
	 * Testing failure on attempt to set flag default
	 *
	 * @return void
	 *
	 * @exceptioncode EXCEPTION_DEFAULT_NOT_ALLOWED
	 */

	public function testFailsOnAttemptToSetFlagDefault()
	    {
		defined("EXCEPTION_DEFAULT_NOT_ALLOWED") || define("EXCEPTION_DEFAULT_NOT_ALLOWED", 1);

		$parser = new Parser();
		$this->expectException(Exception::CLASS);
		$this->expectExceptionCode(EXCEPTION_DEFAULT_NOT_ALLOWED);
		$parser->registerOption("f/name", "help");
	    } //end testFailsOnAttemptToSetFlagDefault()


	/**
	 * Testing failure on attempt to set bad option
	 *
	 * @return void
	 *
	 * @exceptioncode EXCEPTION_WRONG_OPTION_SPEC
	 */

	public function testFailsOnAttemptToSetBadOptionSpec()
	    {
		defined("EXCEPTION_WRONG_OPTION_SPEC") || define("EXCEPTION_WRONG_OPTION_SPEC", 1);

		$parser = new Parser();
		$this->expectException(Exception::CLASS);
		$this->expectExceptionCode(EXCEPTION_WRONG_OPTION_SPEC);
		$parser->registerOption("/bad/bad", "help");
	    } //end testFailsOnAttemptToSetBadOptionSpec()


	/**
	 * Testing failure on attempt to use unknown option
	 *
	 * @return void
	 *
	 * @exceptioncode EXCEPTION_UNKNOWN_OPTION
	 */

	public function testFailsOnAttemptToUseUnknownOption()
	    {
		defined("EXCEPTION_UNKNOWN_OPTION") || define("EXCEPTION_UNKNOWN_OPTION", 1);

		$parser = new Parser();

		$GLOBALS["argv"] = explode(" ", "phpunit --f=test");
		$this->expectException(Exception::CLASS);
		$this->expectExceptionCode(EXCEPTION_UNKNOWN_OPTION);
		$parser->parse();
	    } //end testFailsOnAttemptToUseUnknownOption()


	/**
	 * Testing failure on attempt to use unknown option
	 *
	 * @return void
	 *
	 * @exceptioncode EXCEPTION_CANNOT_BE_COMBINED
	 */

	public function testFailsOnAttemptToUseFlagWithAnotherOptions()
	    {
		defined("EXCEPTION_CANNOT_BE_COMBINED") || define("EXCEPTION_CANNOT_BE_COMBINED", 1);

		$parser = new Parser();
		$parser->registerOption("f", "help");
		$parser->registerOption("a:", "help");

		$GLOBALS["argv"] = explode(" ", "phpunit -fa param");
		$this->expectException(Exception::CLASS);
		$this->expectExceptionCode(EXCEPTION_CANNOT_BE_COMBINED);
		$parser->parse();
	    } //end testFailsOnAttemptToUseFlagWithAnotherOptions()


	/**
	 * Testing fails on ambiguous option
	 *
	 * @return void
	 *
	 * @exceptioncode EXCEPTION_AMBIGUOUS_OPTION
	 */

	public function testFailsOnAmbiguousOption()
	    {
		defined("EXCEPTION_AMBIGUOUS_OPTION") || define("EXCEPTION_AMBIGUOUS_OPTION", 1);

		$parser = new Parser();
		$parser->registerOption("f:", "help");

		$GLOBALS["argv"] = explode(" ", "phpunit -f param1 -f param2");
		$this->expectException(Exception::CLASS);
		$this->expectExceptionCode(EXCEPTION_AMBIGUOUS_OPTION);
		$parser->parse();
	    } //end testFailsOnAmbiguousOption()


	/**
	 * Testing fails on ambiguous flag option
	 *
	 * @return void
	 *
	 * @exceptioncode EXCEPTION_AMBIGUOUS_OPTION
	 */

	public function testFailsOnAmbiguousFlagOption()
	    {
		defined("EXCEPTION_AMBIGUOUS_OPTION") || define("EXCEPTION_AMBIGUOUS_OPTION", 1);

		$parser = new Parser();
		$parser->registerOption("f", "help");

		$GLOBALS["argv"] = explode(" ", "phpunit -f -f");
		$this->expectException(Exception::CLASS);
		$this->expectExceptionCode(EXCEPTION_AMBIGUOUS_OPTION);
		$parser->parse();
	    } //end testFailsOnAmbiguousFlagOption()


	/**
	 * Testing option validation
	 *
	 * @return void
	 *
	 * @exceptioncode EXCEPTION_NOT_VALID
	 */

	public function testShouldAllowToValidateOption()
	    {
		defined("EXCEPTION_NOT_VALID") || define("EXCEPTION_NOT_VALID", 1);

		$validate = function($value)
		    {
			return ($value === "correct");
		    };
		$parser = new Parser();
		$parser->registerOption("a:=string", "help", "", $validate);
		$GLOBALS["argv"] = explode(" ", "phpunit -a correct");
		$parser->parse();
		$this->assertEquals("correct", $parser->getOption("a"));

		$parser = new Parser();
		$parser->registerOption("b:=int", "help");
		$GLOBALS["argv"] = explode(" ", "phpunit -b 113");
		$parser->parse();
		$this->assertEquals(113, $parser->getOption("b"));

		$parser = new Parser();
		$parser->registerOption("b:=file", "help");
		$GLOBALS["argv"] = explode(" ", "phpunit -b " . __FILE__);
		$parser->parse();
		$this->assertEquals(__FILE__, $parser->getOption("b"));

		$parser = new Parser();
		$parser->registerOption("b:=dir", "help");
		$GLOBALS["argv"] = explode(" ", "phpunit -b " . __DIR__);
		$parser->parse();
		$this->assertEquals(__DIR__, $parser->getOption("b"));

		$parser = new Parser();
		$parser->registerOption("a:=bool", "help");
		$parser->registerOption("b:=bool", "help");
		$parser->registerOption("c:=bool", "help");

		$GLOBALS["argv"] = explode(" ", "phpunit -a 0 -b off -c false");
		$parser->parse();
		$this->assertFalse($parser->getOption("a"));
		$this->assertFalse($parser->getOption("b"));
		$this->assertFalse($parser->getOption("c"));

		$GLOBALS["argv"] = explode(" ", "phpunit -a 1 -b on -c true");
		$parser->parse();
		$this->assertTrue($parser->getOption("a"));
		$this->assertTrue($parser->getOption("b"));
		$this->assertTrue($parser->getOption("c"));

		$parser = new Parser();
		$parser->registerOption("a:=string", "help", "", $validate);
		$GLOBALS["argv"] = explode(" ", "phpunit -a test");
		$this->expectException(Exception::CLASS);
		$this->expectExceptionCode(EXCEPTION_NOT_VALID);
		$parser->parse();
	    } //end testShouldAllowToValidateOption()


	/**
	 * Testing fails on invalid integer option
	 *
	 * @return void
	 *
	 * @exceptioncode EXCEPTION_NOT_AN_INTEGER
	 */

	public function testFailsOnInvalidIntegerOption()
	    {
		defined("EXCEPTION_NOT_AN_INTEGER") || define("EXCEPTION_NOT_AN_INTEGER", 1);

		$parser = new Parser();
		$parser->registerOption("b:=int", "help");

		$GLOBALS["argv"] = explode(" ", "phpunit -b param");
		$this->expectException(Exception::CLASS);
		$this->expectExceptionCode(EXCEPTION_NOT_AN_INTEGER);
		$parser->parse();
	    } //end testFailsOnInvalidIntegerOption()


	/**
	 * Testing fails on invalid file option
	 *
	 * @return void
	 *
	 * @exceptioncode EXCEPTION_NOT_A_FILE
	 */

	public function testFailsOnInvalidFileOption()
	    {
		defined("EXCEPTION_NOT_A_FILE") || define("EXCEPTION_NOT_A_FILE", 1);

		$parser = new Parser();
		$parser->registerOption("b:=file", "help");

		$GLOBALS["argv"] = explode(" ", "phpunit -b param");
		$this->expectException(Exception::CLASS);
		$this->expectExceptionCode(EXCEPTION_NOT_A_FILE);
		$parser->parse();
	    } //end testFailsOnInvalidFileOption()


	/**
	 * Testing fails on invalid dir option
	 *
	 * @return void
	 *
	 * @exceptioncode EXCEPTION_NOT_A_DIRECTORY
	 */

	public function testFailsOnInvalidDirOption()
	    {
		defined("EXCEPTION_NOT_A_DIRECTORY") || define("EXCEPTION_NOT_A_DIRECTORY", 1);

		$parser = new Parser();
		$parser->registerOption("b:=dir", "help");

		$GLOBALS["argv"] = explode(" ", "phpunit -b param");
		$this->expectException(Exception::CLASS);
		$this->expectExceptionCode(EXCEPTION_NOT_A_DIRECTORY);
		$parser->parse();
	    } //end testFailsOnInvalidDirOption()


	/**
	 * Testing fails on invalid boolean option
	 *
	 * @return void
	 *
	 * @exceptioncode EXCEPTION_NOT_A_BOOLEAN
	 */

	public function testFailsOnInvalidBooleanOption()
	    {
		defined("EXCEPTION_NOT_A_BOOLEAN") || define("EXCEPTION_NOT_A_BOOLEAN", 1);

		$parser = new Parser();
		$parser->registerOption("b:=bool", "help");

		$GLOBALS["argv"] = explode(" ", "phpunit -b param");
		$this->expectException(Exception::CLASS);
		$this->expectExceptionCode(EXCEPTION_NOT_A_BOOLEAN);
		$parser->parse();
	    } //end testFailsOnInvalidBooleanOption()


	/**
	 * Testing immediate stop of parsing as soon as special option is encountered
	 *
	 * @return void
	 */

	public function testShouldStopProcessingAsSoonAsSpecialOptionIsMet()
	    {
		$parser = new Parser();
		$parser->registerOption("h!", "help");
		$parser->registerArgument("first:", "first");
		$parser->registerArgument("second:", "second");
		$parser->registerArgument("third:", "third");

		$GLOBALS["argv"] = explode(" ", "phpunit first -h second");
		$parser->parse();
		$this->assertEquals("first", $parser->getArgument("first"));
		$this->assertTrue($parser->getOption("h"));
		$this->assertEquals("second", $parser->getArgument("second"));
		$this->assertNull($parser->getArgument("third"));
	    } //end testShouldStopProcessingAsSoonAsSpecialOptionIsMet()


	/**
	 * Testing the use of subcommands
	 *
	 * @return void
	 *
	 * @exceptioncode EXCEPTION_COMMAND_ALREADY_REGISTERED
	 */

	public function testShouldAllowUseOfSubcommands()
	    {
		defined("EXCEPTION_COMMAND_ALREADY_REGISTERED") || define("EXCEPTION_COMMAND_ALREADY_REGISTERED", 1);

		$parser = new Parser();
		$parser->registerCommand("subcommand", "help");

		$GLOBALS["argv"] = explode(" ", "phpunit subcommand");
		$parser->parse();
		$this->assertEquals("subcommand", $parser->getCommand());

		$parser = new Parser();
		$parser->registerCommand("subcommand/subsubcommand", "help");

		$GLOBALS["argv"] = explode(" ", "phpunit subcommand subsubcommand");
		$parser->parse();
		$this->assertEquals("subcommand/subsubcommand", $parser->getCommand());

		$parser = new Parser();
		$parser->registerCommand("subcommand/subsubcommand", "help");
		$this->expectException(Exception::CLASS);
		$this->expectExceptionCode(EXCEPTION_COMMAND_ALREADY_REGISTERED);
		$parser->registerCommand("subcommand", "help");
	    } //end testShouldAllowUseOfSubcommands()


	/**
	 * Testing of ambiguity detection in subcommands
	 *
	 * @return void
	 *
	 * @exceptioncode EXCEPTION_AMBIGUOUS_OPTION
	 */

	public function testShouldProhibitUseOfAmbiguousOptionsInSubcommands()
	    {
		defined("EXCEPTION_AMBIGUOUS_OPTION") || define("EXCEPTION_AMBIGUOUS_OPTION", 1);

		$parser = new Parser();
		$parser->registerOption("b:", "help");
		$parser->registerCommand("subcommand/subsubcommand/thirdlevel", "help");
		$parser->registerOption("b:", "help", "subcommand");
		$parser->registerOption("b:", "help", "subcommand/subsubcommand");

		$GLOBALS["argv"] = explode(" ", "phpunit -b param subcommand -b param1 subsubcommand -b param2");
		$this->expectException(Exception::CLASS);
		$this->expectExceptionCode(EXCEPTION_AMBIGUOUS_OPTION);
		$parser->parse();
	    } //end testShouldProhibitUseOfAmbiguousOptionsInSubcommands()


	/**
	 * Testing of subcommand specific options
	 *
	 * @return void
	 */

	public function testAllowsToUseSubcommandSpecificOptions()
	    {
		$parser = new Parser();
		$parser->registerOption("a:", "help");
		$parser->registerCommand("subcommand/subsubcommand", "help");
		$parser->registerOption("b:", "help", "subcommand");
		$parser->registerOption("c:", "help", "subcommand/subsubcommand");
		$parser->registerOption("f?/default", "help", "subcommand/subsubcommand");

		$GLOBALS["argv"] = explode(" ", "phpunit -a param subcommand -b param1 subsubcommand -c param2");
		$parser->parse();
		$this->assertEquals("param", $parser->getOption("a"));
		$this->assertEquals("param1", $parser->getOption("b"));
		$this->assertEquals("param2", $parser->getOption("c"));
		$this->assertEquals("default", $parser->getOption("f"));
		$this->assertEquals("subcommand/subsubcommand", $parser->getCommand());
	    } //end testAllowsToUseSubcommandSpecificOptions()


	/**
	 * Testing that mandatory arguments are required
	 *
	 * @return void
	 *
	 * @exceptioncode EXCEPTION_MISSING_REQUIRED_ARGUMENT
	 */

	public function testShouldRequireMandatoryArguments()
	    {
		defined("EXCEPTION_MISSING_REQUIRED_ARGUMENT") || define("EXCEPTION_MISSING_REQUIRED_ARGUMENT", 1);

		$parser = new Parser();
		$parser->registerArgument("first:", "help");
		$parser->registerArgument("second:", "help");

		$GLOBALS["argv"] = explode(" ", "phpunit first second");
		$parser->parse();
		$this->assertEquals("first", $parser->getArgument("first"));
		$this->assertEquals("second", $parser->getArgument("second"));

		$parser = new Parser();
		$parser->registerArgument("first:", "help");
		$parser->registerArgument("second:", "help");

		$GLOBALS["argv"] = explode(" ", "phpunit first");
		$this->expectException(Exception::CLASS);
		$this->expectExceptionCode(EXCEPTION_MISSING_REQUIRED_ARGUMENT);
		$parser->parse();
	    } //end testShouldRequireMandatoryArguments()


	/**
	 * Testing the use of optional arguments
	 *
	 * @return void
	 *
	 * @exceptioncode EXCEPTION_SUPERFLUOUS_ARGUMENT
	 */

	public function testAllowsToUseOptionalArgumentsInTheEndOfArguments()
	    {
		defined("EXCEPTION_SUPERFLUOUS_ARGUMENT") || define("EXCEPTION_SUPERFLUOUS_ARGUMENT", 1);

		$parser = new Parser();
		$parser->registerArgument("first:", "help");
		$parser->registerArgument("second?/firstdefault", "help");
		$parser->registerArgument("third?/seconddefault", "help");

		$GLOBALS["argv"] = explode(" ", "phpunit first second");
		$parser->parse();
		$this->assertEquals("first", $parser->getArgument("first"));
		$this->assertEquals("second", $parser->getArgument("second"));
		$this->assertEquals("seconddefault", $parser->getArgument("third"));

		$parser = new Parser();
		$parser->registerArgument("first:", "help");
		$parser->registerArgument("second?/firstdefault", "help");
		$parser->registerArgument("third?/seconddefault", "help");

		$GLOBALS["argv"] = explode(" ", "phpunit first second third fourth");
		$this->expectException(Exception::CLASS);
		$this->expectExceptionCode(EXCEPTION_SUPERFLUOUS_ARGUMENT);
		$parser->parse();
	    } //end testAllowsToUseOptionalArgumentsInTheEndOfArguments()


	/**
	 * Testing use of multiple type argument in the end of arguments
	 *
	 * @return void
	 */

	public function testAllowsToUseMultipleTypeArgumentInTheEndOfArguments()
	    {
		$parser = new Parser();
		$parser->registerArgument("first:", "help");
		$parser->registerArgument("second+", "help");

		$GLOBALS["argv"] = explode(" ", "phpunit first");
		$parser->parse();
		$this->assertEquals("first", $parser->getArgument("first"));
		$this->assertEquals(array(), $parser->getArgument("second"));

		$parser = new Parser();
		$parser->registerArgument("first:", "help");
		$parser->registerArgument("second+", "help");

		$GLOBALS["argv"] = explode(" ", "phpunit first second third fourth");
		$parser->parse();
		$this->assertEquals("first", $parser->getArgument("first"));
		$this->assertEquals(array("second", "third", "fourth"), $parser->getArgument("second"));

		$parser = new Parser();
		$parser->registerArgument("first:", "help");
		$parser->registerArgument("second+", "help");
		$parser->registerCommand("subcommand", "help");
		$parser->registerArgument("third:", "help", "subcommand");
		$parser->registerArgument("fourth+", "help", "subcommand");

		$GLOBALS["argv"] = explode(" ", "phpunit first second third fourth subcommand a b c");
		$parser->parse();
		$this->assertEquals("first", $parser->getArgument("first"));
		$this->assertEquals(array("second", "third", "fourth"), $parser->getArgument("second"));
		$this->assertEquals("a", $parser->getArgument("third"));
		$this->assertEquals(array("b", "c"), $parser->getArgument("fourth"));

		$parser = new Parser();
		$parser->registerArgument("first:", "help");
		$parser->registerArgument("second+", "help");
		$parser->registerCommand("subcommand", "help");
		$parser->registerArgument("third:", "help", "subcommand");
		$parser->registerArgument("fourth+", "help", "subcommand");

		$GLOBALS["argv"] = explode(" ", "phpunit -- first second third fourth subcommand");
		$parser->parse();
		$this->assertEquals("first", $parser->getArgument("first"));
		$this->assertEquals(array("second", "third", "fourth", "subcommand"), $parser->getArgument("second"));
	    } //end testAllowsToUseMultipleTypeArgumentInTheEndOfArguments()


	/**
	 * Testing failure on attempt to set default on non-optional argument
	 *
	 * @return void
	 *
	 * @exceptioncode EXCEPTION_DEFAULT_NOT_ALLOWED
	 */

	public function testFailsOnAttemptToSetDefaultOnNonOptionalArgument()
	    {
		defined("EXCEPTION_DEFAULT_NOT_ALLOWED") || define("EXCEPTION_DEFAULT_NOT_ALLOWED", 1);

		$parser = new Parser();
		$this->expectException(Exception::CLASS);
		$this->expectExceptionCode(EXCEPTION_DEFAULT_NOT_ALLOWED);
		$parser->registerArgument("mandatory/name", "help");
	    } //end testFailsOnAttemptToSetDefaultOnNonOptionalArgument()


	/**
	 * Testing refusal to register arguments for missing command
	 *
	 * @return void
	 *
	 * @exceptioncode EXCEPTION_COMMAND_NOT_REGISTERED
	 */

	public function testRefusesToRegisterArgumentsForMissingCommand()
	    {
		defined("EXCEPTION_COMMAND_NOT_REGISTERED") || define("EXCEPTION_COMMAND_NOT_REGISTERED", 1);

		$parser = new Parser();
		$this->expectException(Exception::CLASS);
		$this->expectExceptionCode(EXCEPTION_COMMAND_NOT_REGISTERED);
		$parser->registerArgument("first:", "help", "nocommand");
	    } //end testRefusesToRegisterArgumentsForMissingCommand()


	/**
	 * Testing refusal to accept bad argument specification
	 *
	 * @return void
	 *
	 * @exceptioncode EXCEPTION_WRONG_ARGUMENT_SPEC
	 */

	public function testRefusesToAcceptBadArgumentSpec()
	    {
		defined("EXCEPTION_WRONG_ARGUMENT_SPEC") || define("EXCEPTION_WRONG_ARGUMENT_SPEC", 1);

		$parser = new Parser();
		$this->expectException(Exception::CLASS);
		$this->expectExceptionCode(EXCEPTION_WRONG_ARGUMENT_SPEC);
		$parser->registerArgument("badargument@", "help");
	    } //end testRefusesToAcceptBadArgumentSpec()


	/**
	 * Testing refusal to accept more than one multiple argument
	 *
	 * @return void
	 *
	 * @exceptioncode EXCEPTION_MUST_BE_LAST_ARGUMENT
	 */

	public function testRefusesToAcceptMoreThanOneMultipleArgument()
	    {
		defined("EXCEPTION_MUST_BE_LAST_ARGUMENT") || define("EXCEPTION_MUST_BE_LAST_ARGUMENT", 1);

		$parser = new Parser();
		$parser->registerArgument("first:", "help");
		$parser->registerArgument("second+", "help");
		$this->expectException(Exception::CLASS);
		$this->expectExceptionCode(EXCEPTION_MUST_BE_LAST_ARGUMENT);
		$parser->registerArgument("third+", "help");
	    } //end testRefusesToAcceptMoreThanOneMultipleArgument()


	/**
	 * Testing refusal to accept anything but optional argument after optional arguments
	 *
	 * @return void
	 *
	 * @exceptioncode EXCEPTION_OPTIONAL_ARGUMENT_EXCEPCTED
	 */

	public function testRefusesToAcceptAnythingButOptionalAfterOptionalArgument()
	    {
		defined("EXCEPTION_OPTIONAL_ARGUMENT_EXCEPCTED") || define("EXCEPTION_OPTIONAL_ARGUMENT_EXCEPCTED", 1);

		$parser = new Parser();
		$parser->registerArgument("first:", "help");
		$parser->registerArgument("second?", "help");
		$this->expectException(Exception::CLASS);
		$this->expectExceptionCode(EXCEPTION_OPTIONAL_ARGUMENT_EXCEPCTED);
		$parser->registerArgument("third:", "help");
	    } //end testRefusesToAcceptAnythingButOptionalAfterOptionalArgument()


	/**
	 * Testing help generation
	 *
	 * @return void
	 */

	public function testShouldGenerateAppropriateHelp()
	    {
		$GLOBALS["argv"] = explode(" ", "script first second subcommand third fourth");

		$parser = new Parser();

		$f = fopen(__DIR__ . "/testsets/help/setup", "r");
		while ($config = fgetcsv($f))
		    {
			$method = array_shift($config);
			call_user_func_array(array($parser, $method), $config);
		    }

		fclose($f);

		$parser->parse();

		$this->assertStringEqualsFile(__DIR__ . "/testsets/help/help", $parser->help());
	    } //end testShouldGenerateAppropriateHelp()


    } //end class

?>
