<?php
/**
 * Compressing CSS files.
 *
 * @package Bengine
 * @copyright Copyright protected by / Urheberrechtlich geschützt durch "Sebastian Noll" <snoll@4ym.org>
 * @version $Id: index.php 9 2010-10-28 20:18:35Z secretchampion $
 */

header("Content-Type: text/css");
$allowedExtensions = array("css");
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

if(@extension_loaded("zlib") && !empty($out))
{
	ob_start("ob_gzhandler");
}

echo $out;
?>