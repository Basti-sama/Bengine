<?php
/**
 * Game bootstrap file.
 *
 * @package Bengine
 * @copyright Copyright protected by / Urheberrechtlich geschützt durch "Sebastian Noll" <snoll@4ym.org>
 * @version $Id: game.php 8 2010-10-17 20:55:04Z secretchampion $
 */

define("INGAME", true);
define("EXEC_CRON", false);
define("LOGIN_REQUIRED", true);
define("MOD_REWRITE", false);
define("REQUEST_LEVEL_NAMES", "sid,controller,action,1,2,3,4,5");

require_once("./global.inc.php");
require_once("Functions.inc.php");

Bengine::run();

?>