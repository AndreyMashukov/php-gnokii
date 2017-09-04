<?php

/**
 * PHP version 5.6
 *
 * @package Logics\Tests
 */

namespace Logics\Tests;

/*
    Init requires HostIdentifierWorker and DatabaseChecker classes
*/

require_once "HostIdentifierWorker.php";
require_once "DatabaseChecker.php";

if (HostIdentifierWorker::needSetUpEnvironment() === true)
    {
	include_once "TemporaryDirectoryChecker.php";
	include_once "ConfigurationMedium.php";

	HostIdentifierWorker::displayHeader();

	$temporaryDirectoryChecker = new TemporaryDirectoryChecker();
	$temporaryDirectoryChecker->setUp();
	$temporaryDirectoryChecker->displayWarnings();
	$temporaryDirectoryChecker->displayTemporaryDirectoryName();

	$databaseChecker = new DatabaseChecker(false);
	$databaseChecker->setUp();

	$injectionStatus = ConfigurationMedium::makeInjection(array($databaseChecker));
	if ($injectionStatus === false)
	    {
		$databaseChecker->injectionFailed();
	    }

	$databaseChecker->displayMessages();
	HostIdentifierWorker::displayFooter();

	unset($temporaryDirectoryChecker);
	unset($databaseChecker);
	unset($injectionStatus);
    }
else
    {
	$databaseChecker = new DatabaseChecker(true);
	$databaseChecker->setUp();
    } //end if

?>
