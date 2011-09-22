<?php
/**
 * Executes cronjobs.
 *
 * @package Bengine
 * @author Sebastian Noll <snoll@4ym.org>
 * @copyright Copyright protected by / Urheberrechtlich geschützt durch "Sebastian Noll"
 * @license Proprietary
 * @version $Id: cron.php 8 2010-10-17 20:55:04Z secretchampion $
 */

define("INGAME", false);
define("EXEC_CRON", true);
define("LOGIN_REQUIRED", false);
define("MOD_REWRITE", false);
define("REQUEST_LEVEL_NAMES", "");

require_once("./global.inc.php");
require_once("Functions.inc.php");

new Core();
?>