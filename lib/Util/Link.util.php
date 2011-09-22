<?php
/**
 * Link-related functions. Mainly used to generate HTML Links.
 *
 * @package Recipe 1.2
 * @author Sebastian Noll
 * @copyright Copyright (c) 2009, Sebastian Noll
 * @license Proprietary
 * @version $Id: Link.util.php 8 2010-10-17 20:55:04Z secretchampion $
 */

class Link
{
	/**
	 * CSS class for external links.
	 *
	 * @var string
	 */
	const CSS_EXTERNAL_URL = "external";

	/**
	 * CSS class for standard links.
	 *
	 * @var string
	 */
	const CSS_NORMAL_URL = "link";

	/**
	 * Enable this to append host and path automatically
	 * to a link url.
	 *
	 * @var boolean
	 */
	const APPEND_HOST_PATH = true;

	/**
	 * Appends the language code to each URL.
	 *
	 * @var boolean
	 */
	const APPEND_LANG_TO_URL = false;

	/**
	 * Set required data for link.
	 *
	 * @param string	URL to link
	 * @param string	Link name
	 * @param string	Additional title
	 * @param string	Particular css class
	 * @param string	Additional attachment for link
	 * @param boolean	Append session id to url
	 * @param boolean	Activate URL rewrite
	 * @param boolean	Refer external URLs to "refdir"
	 *
	 * @return string	HTML link
	 */
	public static function get($url, $name, $title = "", $cssClass = "", $attachment = "", $appendSession = false, $rewrite = true, $refdir = true)
	{
		if(Str::length($cssClass) <= 0)
		{
			if(self::isExternal($url))
			{
				$cssClass = self::CSS_EXTERNAL_URL;
			}
			else
			{
				$cssClass = self::CSS_NORMAL_URL;
			}
		}
		if(Str::length($attachment) > 0) { $attachment = " ".$attachment; }

		if(self::isExternal($url) && $refdir)
		{
			$link = "<a href=\"".BASE_URL."refdir.php?url=".$url."\" title=\"".$title."\" class=\"".$cssClass."\"".$attachment.">".$name."</a>";
		}
		else
		{
			$url = self::url($url, $appendSession, $rewrite);
			$link = "<a href=\"".$url."\" title=\"".$title."\" class=\"".$cssClass."\"".$attachment.">".$name."</a>";
		}
		return $link;
	}

	/**
	 * Generates a full readable url.
	 *
	 * @param string	The url parameters
	 * @param boolean	Append session id to url
	 * @param boolean	Refer external URLs to "refdir"
	 *
	 * @return string
	 */
	public static function url($url, $appendSession = false, $rewrite = true)
	{
		$session = Core::getRequest()->getGET("sid");
		if(!COOKIE_SESSION && $appendSession && $session)
		{
			if(Str::inString("?", $url))
			{
				$url .= "?sid=".$session;
			}
			else
			{
				$url .= "&amp;sid=".$session;
			}
		}
		$lang = (Core::getRequest()->getPOST("lang")) ? Core::getRequest()->getPOST("lang") : Core::getRequest()->getGET("lang");
		if(self::APPEND_LANG_TO_URL && $lang)
		{
			if(Str::inString("?", $url))
			{
				$url = $url."?lang=".$lang;
			}
			else
			{
				$url = $url."&amp;lang=".$lang;
			}
		}
		else if(!Link::isExternal($url) && !preg_match("~^".BASE_URL."~si", $url) && self::APPEND_HOST_PATH)
		{
			$url = BASE_URL.$url;
		}
		return $url;
	}

	/**
	 * Check whether url is beyond current site.
	 *
	 * @param string	URL to check
	 *
	 * @return boolean
	 */
	public static function isExternal($url)
	{
		if(preg_match("#^((http(s)?|ftp)://)+[a-z\d\.@_-]*[a-z\d@_-]+\.([a-z]{2}|biz|com|gov|info|int|museum|name|net|org)#i", $url)
		|| preg_match("#^((http(s)?|ftp)://)(\d{1,3})\.(\d{1,3})\.(\d{1,3})\.(\d{1,3})#", $url)
		|| preg_match("#^file:///#i", $url))
		{
			return true;
		}
		return false;
	}

	/**
	 * Encode URL according to RFC1738 (spaces will be replaced with _).
	 *
	 * @param string	URL to validate
	 *
	 * @return string	Validated URL
	 */
	public static function validateURL($url)
	{
		$url = Str::replace(" ", "_", $url);
		$url = rawurlencode($url);
		return $url;
	}

	/**
	 * Normalize the URL into readable string for the Rewrite-Engine.
	 *
	 * @param string	URL to normalize
	 *
	 * @return string	Normalized URL
	 */
	public static function normalizeURL($url)
	{
		if(strpos($url, "?") > 0)
		{
			$url = preg_replace("/\?(.*?)=/i", "/$1:", $url); // Replace ?arg= with /arg:
			$url = preg_replace("/\&amp;(.*?)=/i", "/$1:", $url); // Replace &amp;arg= with /arg:
			$url = preg_replace("/\&(.*?)=/i", "/$1:", $url); // Replace &arg= with /arg:

			// Now remove useless arg names.
			$parsedURL = parse_url($url);

			$path = Str::substring(Str::replace($_SERVER["SCRIPT_NAME"], "", $parsedURL["path"]), 1);
			$splitted = explode("/", $path);
			$size = count($splitted);
			for($i = 0; $i < $size; $i++)
			{
				if(strpos($splitted[$i], ":"))
				{
					$splitted[$i] = explode(":", $splitted[$i]);
					$levelNames = explode(",", REQUEST_LEVEL_NAMES);
					if(Str::compare($splitted[$i][0], $levelNames[$i], true)) { $url = Str::replace($splitted[$i][0].":", "", $url); }
				}
			}
		}
		return BASE_URL.$url;
	}
}
?>