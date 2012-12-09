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
	 * @param string $var		Variable name [optional]
	 * @param mixed $default	Default return value [optional]
	 *
	 * @return mixed
	 */
	public function getGet($var = null, $default = null)
	{
		if(is_null($var))
		{
			return $_GET;
		}
		if(isset($_GET[$var]))
		{
			return $_GET[$var];
		}
		return $default;
	}

	/**
	 * Retrieves a _POST variable.
	 *
	 * @param string $var		Variable name [optional]
	 * @param mixed $default	Default return value [optional]
	 *
	 * @return mixed
	 */
	public function getPost($var = null, $default = null)
	{
		if($var === null)
		{
			return $_POST;
		}
		if(isset($_POST[$var]))
		{
			return $_POST[$var];
		}
		return $default;
	}

	/**
	 * Retrieves a _COOKIE variable.
	 *
	 * @param string $var		Variable name [optional]
	 * @param mixed $default	Default return value [optional]
	 *
	 * @return mixed
	 */
	public function getCookie($var = null, $default = null)
	{
		if($var === null)
		{
			return $_COOKIE;
		}
		if(isset($_COOKIE[$var]))
		{
			return $_COOKIE[$var];
		}
		return $default;
	}
}
?>