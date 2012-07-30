<?php
/**
 * Compressing CSS files.
 *
 * @package Bengine
 * @copyright Copyright protected by / Urheberrechtlich geschützt durch "Sebastian Noll" <snoll@4ym.org>
 * @version $Id: index.php 8 2010-10-17 20:55:04Z secretchampion $
 */

header("Content-Type: text/css");
if(!isset($_GET["f"]))
{
	exit;
}
$allowedExtensions = array("css");
$cacheExpires = 86400;
$disableCache = isset($_GET["c"]) ? $_GET["c"] : false;
$files = $_GET["f"];

$modified = false;
$cacheFile = dirname(dirname(__FILE__))."/var/cache/html-inc/".md5($files).".css";
$time = time();
if($disableCache || !file_exists($cacheFile) || filemtime($cacheFile) < $time - $cacheExpires)
{
	$files = explode(",", $files);
	$dir = realpath(dirname(__FILE__));
	$in = "";
	foreach($files as $file)
	{
		$file = realpath($dir."/".$file);
		$ext = pathinfo($file, PATHINFO_EXTENSION);
		if(file_exists($file) && strpos($file, $dir) !== false && in_array($ext, $allowedExtensions))
		{
			$in .= file_get_contents($file)."\n";
		}
	}
	if(!is_dir(dirname($cacheFile)))
	{
		mkdir(dirname($cacheFile), 0777, true);
	}

	// Optimize CSS...
	$search = array("; ", "; }", " { ", ": ", ", ");
	$replace = array(";", "}", "{", ":", ",");
	$in = str_replace($search, $replace, $in);

	file_put_contents($cacheFile, $in);
	$modified = true;
}
$lastModified = filemtime($cacheFile);

if(!$modified && isset($_SERVER["HTTP_IF_MODIFIED_SINCE"]) && strtotime($_SERVER["HTTP_IF_MODIFIED_SINCE"]) > $lastModified)
{
	header("HTTP/1.0 304 Not Modified");
	exit;
}

header("Last-Modified: ".gmdate("D, d M Y H:i:s \G\M\T", $lastModified));
header("Cache-Control: public, max-age=".$cacheExpires);
header("Date: ".gmdate("D, d M Y H:i:s \G\M\T", $time));
header("Expires: ".gmdate("D, d M Y H:i:s \G\M\T", $time+$cacheExpires));
$out = file_get_contents($cacheFile);
if(extension_loaded("zlib") && strtolower(ini_get("zlib.output_compression")) == "off" && !empty($out))
{
	ob_start("ob_gzhandler");
}

echo $out;
?>