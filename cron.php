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

require_once("./global.inc.php");
require_once("Functions.inc.php");

new Core();
Application::loadMeta();
Core::getCron()->exeCron();
?>