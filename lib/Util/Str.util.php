<?php
/**
 * Advanced string functions.
 *
 * @package Recipe 1.2
 * @author Sebastian Noll
 * @copyright Copyright (c) 2009, Sebastian Noll
 * @license Proprietary
 * @version $Id: Str.util.php 17 2011-05-19 15:25:04Z secretchampion $
 */

class Str
{
	/**
	 * Holds all valid encryptions used by encode().
	 *
	 * @var array
	 */
	protected static $validEncryptions = array("md5", "md5_salt", "sha1", "crypt", "crc32", "base64_encode");

	/**
	 * Generate XHTML valid code.
	 *
	 * @param string	Incoming text
	 *
	 * @return string	Validated text
	 */
	public static function validateXHTML($text)
	{
		if(strlen($text) > 0)
		{
			$XHTMLConvertEntities = get_html_translation_table(HTML_SPECIALCHARS);
			$text = trim($text);
			$text = strtr($text, $XHTMLConvertEntities);
		}
		return $text;
	}

	/**
	 * Encode string to specified encryption method.
	 *
	 * @param string	String to encode
	 * @param string	Encryption method
	 *
	 * @return string	Encoded string
	 */
	public static function encode($text, $encryption = "md5")
	{
		if(in_array($encryption, self::$validEncryptions))
		{
			if($encryption == "md5_salt")
			{
				$salt = md5(strlen($text));
				return md5("*_/()%&?!".$text.$salt);
			}
			return $encryption($text);
		}
		return $text;
	}

	/**
	 * Identical to substr().
	 *
	 * @param string	String
	 * @param integer	Start position
	 * @param integer	Substring length
	 *
	 * @return string	The extracted part of string
	 */
	public static function substring($string, $start, $length = null)
	{
		if($length === null)
		{
			$length = self::length($string);
		}
		return substr($string, $start, $length);
	}

	/**
	 * Identical to strlen().
	 *
	 * @param string
	 *
	 * @return integer	Length of string
	 */
	public static function length($string)
	{
		return strlen($string);
	}

	/**
	 * Compare two strings for equality.
	 *
	 * @param string
	 * @param string
	 * @param boolean	Enable or disable case sensitive for comparision
	 *
	 * @return boolean True, if strings are equal, false, if not.
	 */
	public static function compare($string1, $string2, $caseSensitive = false)
	{
		if($caseSensitive)
		{
			if(strcmp($string1, $string2) == 0) { return true; } else { return false; }
		}
		else
		{
			if(strcasecmp($string1, $string2) == 0) { return true; } else { return false; }
		}
	}

	/**
	 * Identical to str_replace().
	 *
	 * @param mixed		Search
	 * @param mixed		Replace
	 * @param mixed		Subject
	 *
	 * @return mixed	Returns a string or an array with the replaced values
	 */
	public static function replace($search, $replace, $subject)
	{
		return str_replace($search, $replace, $subject);
	}

	/**
	 * Returns the portion of haystack which
	 * ends at the last occurrence of needle
	 * and starts with the begin of haystack.
	 *
	 * @param string 	The string to search in
	 * @param string	Needle
	 *
	 * @return string	New String
	 */
	public static function reverse_strrchr($haystack, $needle)
	{
		$pos = strrpos($haystack, $needle);
		if($pos == false) { return $haystack; }
		return substr($haystack, 0, $pos + 1);
	}

	/**
	 * Checks if needle can be found in haystack.
	 *
	 * @param string	Needle
	 * @param string	Haystack
	 *
	 * @return boolean
	 */
	public static function inString($needle, $haystack)
	{
		$match = strpos($haystack, $needle);
		if($match === false)
		{
			return false;
		}
		return true;
	}
}
?>