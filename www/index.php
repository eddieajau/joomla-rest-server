<?php
/**
 * Analyses the pull request and issues in a Github repository.
 *
 * @copyright  Copyright (C) 2013 New Life in IT Pty Ltd. All rights reserved.
 * @license    MIT
 */

// Max out error reporting.
error_reporting(-1);
ini_set('display_errors', 1);

// Bootstrap the Joomla Framework.
require realpath(__DIR__ . '/../vendor/autoload.php');

try
{
	define('APPLICATION_CONFIG', realpath(__DIR__ . '/../etc/config.json'));
	define('APPLICATION_ROUTES', realpath(__DIR__ . '/../etc/routes.json'));

	$app = new Server\Application;
	$app->execute();
}
catch (Exception $e)
{
	// Set the server response code.
	header('Status: 500', true, 500);

	// An exception has been caught, echo the message and exit.
	echo json_encode(array('message' => $e->getMessage(), 'code' => $e->getCode(), 'type' => get_class($e)));
	exit;
}
