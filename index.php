<?php
/**
 * Community bootstrap file.
 *
 * @package Bengine
 * @license <http://www.bengine.de/pyl.txt> Public Bengine License
 * @version $Id: index.php 8 2010-10-17 20:55:04Z secretchampion $
 */

define("INGAME", false);
define("EXEC_CRON", false);
define("LOGIN_REQUIRED", false);
define("MOD_REWRITE", true);
define("REQUEST_LEVEL_NAMES", "lang,controller,action,1,2,3,4");

require_once("./global.inc.php");
require_once("Functions.inc.php");
Comm::run();

?>