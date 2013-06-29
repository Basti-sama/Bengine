<?php
/**
 * Mainly usage: Generate image codes for HTML.
 *
 * @package Recipe 1.2
 * @author Sebastian Noll
 * @copyright Copyright (c) 2009, Sebastian Noll
 * @license Proprietary
 * @version $Id: Image.util.php 8 2010-10-17 20:55:04Z secretchampion $
 */

class Image
{
	/**
	 * Default CSS class for images.
	 *
	 * @var string
	 */
	const IMAGE_CSS_CLASS = "image";

	/**
	 * Generate image tag in HTML.
	 *
	 * @param string $url		Image URL
	 * @param string $title		Additional title
	 * @param integer $width	Image width
	 * @param integer $height	Image height
	 * @param string $cssClass	Additional CSS class designation
	 *
	 * @return string			Image tag
	 */
	public static function getImage($url, $title, $width = null, $height = null, $cssClass = "")
	{
		$isExternal = Link::isExternal($url);
		if(Core::getUser()->get("theme") != "" && !$isExternal)
		{
			$url = Core::getUser()->get("theme")."img/".$url;
		}
		if(!$isExternal)
		{
			$url = BASE_URL."img/".$url;
		}
		else
		{
			$url = BASE_URL."img/remote.php?file=".$url;
		}

		if(Str::length($cssClass) == 0) { $cssClass = self::IMAGE_CSS_CLASS; }
		$width = !is_null($width) ? " width=\"".$width."\"" : "";
		$height = !is_null($height) ? " height=\"".$height."\"" : "";
		$img = "<img src=\"".$url."\" title=\"".$title."\" alt=\"".$title."\"".$width.$height." class=\"".$cssClass."\" />";
		return $img;
	}
}
?>