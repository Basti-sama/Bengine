<?php
/**
 * Header related functions.
 *
 * @package Recipe 1.2
 * @author Sebastian Noll
 * @copyright Copyright (c) 2009, Sebastian Noll
 * @license Proprietary
 * @version $Id: Header.php 8 2010-10-17 20:55:04Z secretchampion $
 */

class Recipe_Header
{
	/**
	 * Header options.
	 *
	 * @var array
	 */
	protected static $header = array();

	/**
	 * HTTP protocol version (1.1 [default] or 1.0).
	 *
	 * @var string
	 */
	protected static $protocol = "1.1";

	/**
	 * HTTP Response status code.
	 *
	 * @var integer
	 */
	protected static $statusCode = 200;

	/**
	 * Adds a new header option.
	 *
	 * @param string|array $type	Type name or array of elements
	 * @param mixed $value			Value [optional]
	 *
	 * @return array		Header elements
	 */
	public static function add($type, $value = null)
	{
		if(is_array($type))
		{
			foreach($type as $header)
			{
				self::add($header["type"], $header["value"]);
			}
			return self::$header;
		}
		if(!is_null($value))
		{
			self::$header[] = array("type" => $type, "value" => $value);
		}
		else
		{
			self::$header[] = $type;
		}
		return self::$header;
	}

	/**
	 * Sets the header.
	 *
	 * @param string|array $type	Type name or array of elements
	 * @param mixed $value			Value [optional]
	 *
	 * @return array		Header elements
	 */
	public static function set($type, $value = null)
	{
		self::clear();
		return self::add($type, $value);
	}

	/**
	 * Checks if headers have been sent.
	 *
	 * @return boolean
	 */
	public static function isSend()
	{
		return headers_sent();
	}

	/**
	 * Clears all header elements.
	 *
	 * @return void
	 */
	public static function clear()
	{
		self::$header = array();
		return;
	}

	/**
	 * Sends all header elements.
	 *
	 * @return array	Header elements
	 */
	public static function send()
	{
		if(!self::isSend())
		{
			header("HTTP/".self::protocol()." ".self::statusCode());
			foreach(self::$header as $header)
			{
				if(is_array($header))
				{
					header($header["type"].": ".$header["value"]);
				}
				else
				{
					header($header);
				}
			}
		}
		return self::$header;
	}

	/**
	 * Sets Location header and response code. Forces replacement of any prior
     * redirects.
	 *
	 * @param string $url				URL to redirect
	 * @param boolean $appendSession	Append the session string
	 *
	 * @return void
	 */
	public static function redirect($url, $appendSession = true)
	{
		if(Link::isExternal($url) || Str::inString("http://", $url))
		{
			$path = $url;
		}
		else
		{
			if($appendSession && !Str::inString("sid=", $url) && URL_SESSION)
			{
				(!Str::inString("?", $url)) ? $url .= "?sid=".SID : $url .= "&sid=".SID;
			}
			else { $path = HTTP_HOST.REQUEST_DIR.$url; }
		}
		self::set("Location", $path);
		self::statusCode(302);
		self::send();
		return exit();
	}

	/**
	 * Sets and/or returns the HTTP status code.
	 *
	 * @param integer $statusCode	Status code [optional]
	 *
	 * @return integer	Status code
	 */
	public static function statusCode($statusCode = null)
	{
		if(!is_null($statusCode))
		{
			self::$statusCode = (int) $statusCode;
		}
		return self::$statusCode;
	}

	/**
	 * Sets and/or returns the HTTP protocol version.
	 *
	 * @param string $protocol	Protocol version [optional]
	 *
	 * @return string	Protocol version
	 */
	public static function protocol($protocol = null)
	{
		if(!is_null($protocol))
		{
			self::$protocol = $protocol;
		}
		return self::$protocol;
	}
}
?>