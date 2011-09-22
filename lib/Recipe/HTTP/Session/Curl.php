<?php
/**
 * Sends HTTP requests via cURL extension.
 *
 * @package Recipe 1.2
 * @author Sebastian Noll
 * @copyright Copyright (c) 2009, Sebastian Noll
 * @license Proprietary
 * @version $Id: Curl.php 19 2011-05-27 10:30:33Z secretchampion $
 */

class Recipe_HTTP_Session_Curl extends Recipe_HTTP_Session_Abstract
{
	/**
	 * The cURL handle.
	 *
	 * @var resource
	 */
	protected $resource = null;

	/**
	 * Handles the cURL session.
	 *
	 * @param string	URL to connect with
	 *
	 * @return Recipe_HTTP_Session_Curl
	 */
	public function __construct($webpage)
	{
		if(!function_exists("curl_init"))
		{
			throw new Recipe_Exception_Generic("The cURL library is not available on the server.");
		}
		parent::__construct($webpage);
		return $this;
	}

	/**
	 * Initialize a cURL session.
	 *
	 * @return Recipe_HTTP_Session_Curl
	 */
	protected function init()
	{
		$this->resource = @curl_init();
		if(!$this->resource)
		{
			throw new Recipe_Exception_Generic("Connection faild via cURL request.");
		}
		Hook::event("HttpRequestInitFirst", array($this));
		@curl_setopt($this->resource, CURLOPT_RETURNTRANSFER, true);
		@curl_setopt($this->resource, CURLOPT_TIMEOUT, self::TIMEOUT);
		$url = $this->webpage;
		if(count($this->getGetArgs()) > 0)
		{
			$url .= "?".$this->getGetArgs(false);
		}
		@curl_setopt($this->resource, CURLOPT_URL, $url);
	 	if($this->getRequestType() == "GET")
		{
			@curl_setopt($this->resource, CURLOPT_HTTPGET, true);
		}
		else if($this->getRequestType() == "POST")
		{
			@curl_setopt($this->resource, CURLOPT_POST, true);
			@curl_setopt($this->resource, CURLOPT_POSTFIELDS, $this->getPostArgs(false));
		}
		else if($this->getRequestType() == "PUT")
		{
			@curl_setopt($this->resource, CURLOPT_PUT, true);
		}

		$this->response = curl_exec($this->resource);
		$this->errorNo = curl_errno($this->resource);
		if($this->errorNo)
		{
			$this->error = curl_error($this->resource);
			throw new Recipe_Exception_Generic("There is an error occured in cURL session (".$this->errorNo."): ".$this->error);
		}
		Hook::event("HttpRequestInitLast", array($this));
		return $this;
	}

	/**
	 * Closes the current cURL session.
	 *
	 * @return void
	 */
	public function close()
	{
		curl_close($this->resource);
		unset($this);
		return;
	}
}
?>