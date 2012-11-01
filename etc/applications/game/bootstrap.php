<?php
/**
 * Bootstrapping game application.
 *
 * @package Bengine\Comm
 * @license <http://www.bengine.de/pyl.txt> Public Bengine License
 */

define("REQUEST_LEVEL_NAMES", "packages,sid,controller,action,1,2,3,4");
define("LOGIN_REQUIRED", true);
define("VERSION_CHECK_PAGE", "http://bengine.de/version.php");

require_once "Functions.inc.php";
require_once "Bengine/Game.php";
return new Game($this);
