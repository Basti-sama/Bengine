<?php
/**
 * Sends HTTP requests via domain socket connection.
 *
 * @package Recipe 1.2
 * @author Sebastian Noll
 * @copyright Copyright (c) 2009, Sebastian Noll
 * @license Proprietary
 * @version $Id: Fsock.php 19 2011-05-27 10:30:33Z secretchampion $
 */

class Recipe_HTTP_Session_Fsock extends Recipe_HTTP_Session_Abstract
{
	/**
	 * Parsed url.
	 *
	 * @var array
	 */
	protected $parsedURL = array();

	/**
	 * The fsock handle.
	 *
	 * @var resource
	 */
	protected $resource = null;

	/**
	 * Handles the fsock session.
	 *
	 * @param string	URL to connect with
	 *
	 * @return Recipe_HTTP_Session_Fsock
	 */
	public function __construct($webpage)
	{
		parent::__construct($webpage);
		if(!function_exists("fsockopen") && ini_get("allow_url_fopen"))
		{
			throw new Recipe_Exception_Generic("The server does not allow to fetch contents from a different webpage.");
		}
		return $this;
	}

	/**
	 * Initialize a fsock session.
	 *
	 * @return Recipe_HTTP_Session_Fsock
	 */
	protected function init()
	{
		// Split the url
		$this->parsedURL = parseUrl($this->webpage);
		if(empty($this->parsedURL["host"]))
		{
			throw new Recipe_Exception_Generic("The supplied url is not valid.");
		}
		if(empty($this->parsedURL["port"]))
		{
			$this->parsedURL["port"] = 80;
		}
		if(empty($this->parsedURL["path"]))
		{
			$this->parsedURL["path"] = "/";
		}
		if(!empty($this->parsedURL["query"]))
		{
			$this->parsedURL["path"] .= "?".$this->parsedURL["query"];
		}
		else if(count($this->getGetArgs()) > 0)
		{
			$this->parsedURL["path"] .= "?".$this->getGetArgs(false);
		}

		$this->resource = fsockopen($this->parsedURL["host"], $this->parsedURL["port"], $this->errorNo, $this->error, self::TIMEOUT);
		if(!$this->resource)
		{
			throw new Recipe_Exception_Generic("Connection faild via fsock request: (".$this->errorNo.") ".$this->error);
		}
		@stream_set_timeout($this->resource, self::TIMEOUT);
		Hook::event("HttpRequestInitFirst", array($this));

		// Set headers
		$headers[] = $this->getRequestType()." ".$this->parsedURL["path"]." HTTP/1.1";
		$headers[] = "Host: ".$this->parsedURL["host"];
		$headers[] = "Connection: Close";
		$headers[] = "\r\n";
		$headers = implode("\r\n", $headers);
		$body = "";
		if($this->getRequestType() == "POST" && count($this->getPostArgs()) > 0)
		{
			$body = $this->getPostArgs(false);
		}

		// Send request
		if(!@fwrite($this->resource, $headers.$body))
		{
			throw new Recipe_Exception_Generic("Couldn't send request.");
		}

		// Read response
		while(!feof($this->resource))
		{
			$this->response .= fgets($this->resource, 12800);
		}
		$this->response = explode("\r\n\r\n", $this->response, 2);
		$this->response = $this->response[1];
		Hook::event("HttpRequestInitLast", array($this));
		return $this;
	}

	/**
	 * Closes the current fsock session.
	 *
	 * @return void
	 */
	public function close()
	{
		fclose($this->resource);
		unset($this);
		return;
	}
}
?>