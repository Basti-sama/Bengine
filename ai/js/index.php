<?php
/**
 * Compressing JavaScript files.
 *
 * @package Bengine
 * @copyright Copyright protected by / Urheberrechtlich geschützt durch "Sebastian Noll" <snoll@4ym.org>
 * @version $Id: index.php 8 2010-10-17 20:55:04Z secretchampion $
 */

header("Content-Type: text/javascript");
$allowedExtensions = array("js");
$files = $_GET["f"];
$files = explode(",", $files);
$dir = realpath(dirname(__FILE__));
$out = "";
foreach($files as $file)
{
	$file = realpath($dir."/".$file);
	$ext = pathinfo($file, PATHINFO_EXTENSION);
	if(file_exists($file) && strpos($file, $dir) !== false && in_array($ext, $allowedExtensions))
	{
		$out .= file_get_contents($file)."\n";
	}
}

if(extension_loaded("zlib") && strtolower(ini_get("zlib.output_compression")) == "off" && !empty($out))
{
	ob_start("ob_gzhandler");
}

echo $out;
?>