<?php
/**
 * Initilizing file. Launches program and define important constants.
 *
 * @package Recipe 1.2
 * @author Sebastian Noll
 * @copyright Copyright (c) 2009, Sebastian Noll
 * @license Proprietary
 * @version $Id: init.php 14 2011-05-13 08:59:35Z secretchampion $
 */

// Set error reporting level.
error_reporting(ERROR_REPORTING_TYPE);

// If an error occured, throw an exception.
function triggerError($errno, $errstr, $errfile, $errline)
{
	$stackTrace = debug_backtrace();
	array_shift($stackTrace);
	$exception = new Recipe_Exception_Generic($errstr, $errno);
	$exception->setFile($errfile)
		->setLine($errline)
		->setTrace($stackTrace);
	throw $exception;
	return;
}
set_error_handler("triggerError", ERROR_REPORTING_TYPE);

// Handles uncaught exceptions
function exceptionHandler(Exception $exception)
{
	if($exception instanceof Recipe_Exception_Global)
	{
		$exception->printError();
	}
	echo "<pre>";
	print_r($exception);
	echo "</pre>";
	exit;
}
set_exception_handler("exceptionHandler");

// Load program.
include_once(RECIPE_ROOT_DIR."AutoLoader.php");
$autoloader = new AutoLoader();
?>