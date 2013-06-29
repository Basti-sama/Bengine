<?php
/**
 * Assures anonymous loading of images to hide session id.
 *
 * @package Bengine
 * @copyright Copyright protected by / Urheberrechtlich geschützt durch "Sebastian Noll" <snoll@4ym.org>
 */

if(!empty($_GET["file"]))
{
	$link = curl_init();
	curl_setopt($link, CURLOPT_URL, $_GET["file"]);
	curl_setopt($link, CURLOPT_REFERER, "http://".$_SERVER["SERVER_NAME"]);
	curl_setopt($link, CURLOPT_HEADER, true);
	curl_setopt($link, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($link, CURLOPT_BINARYTRANSFER, 1);
	$response = curl_exec($link);
	curl_close($link);

	list($header, $image) = explode("\r\n\r\n", $response, 2);

	$header = explode(PHP_EOL, $header);
	foreach($header as $index => $headerLine)
	{
		header($headerLine, !$index ? true : false);
	}

	echo $image;
}