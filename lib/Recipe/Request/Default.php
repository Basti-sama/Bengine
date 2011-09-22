<?php
/**
 * Default request adapter.
 *
 * @package Recipe 1.2
 * @author Sebastian Noll
 * @copyright Copyright (c) 2009, Sebastian Noll
 * @license Proprietary
 * @version $Id: Default.php 8 2010-10-17 20:55:04Z secretchampion $
 */

class Recipe_Request_Default extends Recipe_Request_Adapter
{
	/**
	 * Retrieves a _GET variable.
	 *
	 * @param string	Variable name [optional]
	 * @param mixed		Default return value [optional]
	 *
	 * @return mixed
	 */
	public function getGet($var = null, $default = null)
	{
		if(is_null($var))
		{
			return Arr::map(array(Core::getDB(), "real_escape_string"), $_GET);
		}
		if(isset($_GET[$var]))
		{
			if(is_array($_GET[$var]))
			{
				return Arr::map(array(Core::getDB(), "real_escape_string"), $_GET[$var]);
			}
			return Core::getDB()->real_escape_string($_GET[$var]);
		}
		return $default;
	}

	/**
	 * Retrieves a _POST variable.
	 *
	 * @param string	Variable name [optional]
	 * @param mixed		Default return value [optional]
	 *
	 * @return mixed
	 */
	public function getPost($var = null, $default = null)
	{
		if(is_null($var))
		{
			return Arr::map(array(Core::getDB(), "real_escape_string"), $_POST);
		}
		if(isset($_POST[$var]))
		{
			if(is_array($_POST[$var]))
			{
				return Arr::map(array(Core::getDB(), "real_escape_string"), $_POST[$var]);
			}
			return Core::getDB()->real_escape_string($_POST[$var]);
		}
		return $default;
	}

	/**
	 * Retrieves a _COOKIE variable.
	 *
	 * @param string	Variable name [optional]
	 * @param mixed		Default return value [optional]
	 *
	 * @return mixed
	 */
	public function getCookie($var = null, $default = null)
	{
		if(is_null($var))
		{
			return Arr::map(array(Core::getDB(), "real_escape_string"), $_COOKIE);
		}
		if(isset($_COOKIE[$var]))
		{
			if(is_array($_COOKIE[$var]))
			{
				return Arr::map(array(Core::getDB(), "real_escape_string"), $_COOKIE[$var]);
			}
			return Core::getDB()->real_escape_string($_COOKIE[$var]);
		}
		return $default;
	}
}
?>