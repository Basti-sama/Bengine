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
	 * @param string	Image URL
	 * @param string	Additional title
	 * @param integer	Image width
	 * @param integer	Image height
	 * @param string	Additional CSS class designation
	 *
	 * @return string	Image tag
	 */
	public static function getImage($url, $title, $width = null, $height = null, $cssClass = "")
	{
		if(Core::getUser()->get("theme") != "" && !Link::isExternal($url))
		{
			$url = Core::getUser()->get("theme")."img/".$url;
		}
		if(!Link::isExternal($url))
		{
			$url = BASE_URL."img/".$url;
		}

		if(Str::length($cssClass) == 0) { $cssClass = self::IMAGE_CSS_CLASS; }
		$width = !is_null($width) ? " width=\"".$width."\"" : "";
		$height = !is_null($height) ? " height=\"".$height."\"" : "";
		$img = "<img src=\"".$url."\" title=\"".$title."\" alt=\"".$title."\"".$width.$height." class=\"".$cssClass."\" />";
		return $img;
	}
}
?>