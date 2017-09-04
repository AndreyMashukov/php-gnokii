<?php

/**
 * PHP version 5.6
 *
 * @package Logics\Tests
 */

namespace Logics\Tests;

/*
    SilentInit requires HostIdentifierWorker class
*/

require_once "HostIdentifierWorker.php";

if (HostIdentifierWorker::needSetUpEnvironment() === true)
    {
	include_once "TemporaryDirectoryChecker.php";
	include_once "DatabaseChecker.php";
	include_once "ConfigurationMedium.php";

	$temporaryDirectoryChecker = new TemporaryDirectoryChecker();
	$temporaryDirectoryChecker->setUp();

	$databaseChecker = new DatabaseChecker(false, false);
	$databaseChecker->setUp();

	$injectionStatus = ConfigurationMedium::redefineVariables(array($databaseChecker));

	unset($temporaryDirectoryChecker);
	unset($databaseChecker);
	unset($injectionStatus);
    } //end if

?>
