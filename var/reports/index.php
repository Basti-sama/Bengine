<?php
/**
 * Displays error reports.
 *
 * @package Bengine
 * @copyright Copyright protected by / Urheberrechtlich geschützt durch "Sebastian Noll" <snoll@4ym.org>
 * @version $Id: index.php 8 2010-10-17 20:55:04Z secretchampion $
 */

if(!defined("__DIR__")) define("__DIR__", dirname(__FILE__));

if(empty($_GET["error"]) || preg_match("#[^A-Za-z0-9]+#i", $_GET["error"]) || !file_exists(__DIR__."/exception_".$_GET["error"].".html"))
{
	header("HTTP/1.1 404 Not Found");
	die("<h1>Page Not Found!</h1>");
}

if(extension_loaded("zlib") && strtolower(ini_get("zlib.output_compression")) == "off")
{
	ob_start("ob_gzhandler");
}

echo file_get_contents(__DIR__."/exception_".$_GET["error"].".html");
?>