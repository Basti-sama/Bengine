<?php
/**
 * Bootstrapping game application.
 *
 * @package Bengine\Comm
 * @license <http://www.bengine.de/pyl.txt> Public Bengine License
 */

define("REQUEST_LEVEL_NAMES", "package,sid,controller,action,1,2,3,4,5");
define("LOGIN_REQUIRED", true);
define("VERSION_CHECK_PAGE", "http://bengine.de/changelog.json");
define("COOKIE_SESSION", false);
define("URL_SESSION", true);

require_once "Functions.inc.php";
require_once "Bengine/Game.php";
return new Game($this);
