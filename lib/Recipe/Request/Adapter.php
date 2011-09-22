<?php
/**
 * Abstract request adapter.
 *
 * @package Recipe 1.2
 * @author Sebastian Noll
 * @copyright Copyright (c) 2009, Sebastian Noll
 * @license Proprietary
 * @version $Id: Adapter.php 8 2010-10-17 20:55:04Z secretchampion $
 */

abstract class Recipe_Request_Adapter
{
	/**
	 * Creates a new request adapter object.
	 *
	 * @return Recipe_Request_Adapter
	 */
	public function __construct()
	{
		$this->init();
		return $this;
	}

	/**
	 * Initializing method: Removes slashes from GPC.
	 *
	 * @return Recipe_Request_Adapter
	 */
	protected function init()
	{
		if($this->hasMagicQuotes())
		{
			if(is_array($_GET) && count($_GET) > 0)
			{
				$_GET = Arr::map("stripslashes", $_GET);
			}
			if(is_array($_POST) && count($_POST) > 0)
			{
				$_POST = Arr::map("stripslashes", $_POST);
			}
			if(is_array($_COOKIE) && count($_COOKIE) > 0)
			{
				$_COOKIE = Arr::map("stripslashes", $_COOKIE);
			}
		}
		return $this;
	}

	/**
	 * Retrieves a _GET variable.
	 *
	 * @param string	Variable name
	 * @param mixed		Default return value [optional]
	 *
	 * @return mixed
	 */
	abstract public function getGet($var = null, $default = null);

	/**
	 * Retrieves a _POST variable.
	 *
	 * @param string	Variable name
	 * @param mixed		Default return value [optional]
	 *
	 * @return mixed
	 */
	abstract public function getPost($var = null, $default = null);

	/**
	 * Retrieves a _COOKIE variable.
	 *
	 * @param string	Variable name
	 * @param mixed		Default return value [optional]
	 *
	 * @return mixed
	 */
	abstract public function getCookie($var = null, $default = null);

	/**
	 * Checks if Magic quotes are enabled.
	 *
	 * @return boolean
	 */
	protected function hasMagicQuotes()
	{
		return (function_exists("get_magic_quotes_gpc") && get_magic_quotes_gpc() != 0);
	}
}
?>