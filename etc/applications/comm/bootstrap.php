<?php
/**
 * Bootstrapping community application.
 *
 * @package Bengine\Comm
 * @license <http://www.bengine.de/pyl.txt> Public Bengine License
 */

define("REQUEST_LEVEL_NAMES", "lang,controller,action,1,2,3,4");
define("LOGIN_REQUIRED", false);

require_once "Functions.inc.php";
require_once "Bengine/Comm.php";
return new Comm($this);
