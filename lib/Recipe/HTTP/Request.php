<?php
/**
 * Sends HTTP requests to webpages and returns their response.
 *
 * @package Recipe 1.2
 * @author Sebastian Noll
 * @copyright Copyright (c) 2009, Sebastian Noll
 * @license Proprietary
 * @version $Id: Request.php 19 2011-05-27 10:30:33Z secretchampion $
 */

class Recipe_HTTP_Request
{
	/**
	 * The request session.
	 *
	 * @var Recipe_HTTP_Session_Abstract
	 */
	protected $session = null;

	/**
	 * The session name.
	 *
	 * @var string
	 */
	protected $sessionName = "";

	/**
	 * Requested web url.
	 *
	 * @var string
	 */
	protected $url = "";

	/**
	 * Function check for the allowed sessions.
	 *
	 * @var array
	 */
	protected $functions = array(
		"Curl" => "curl_init",
		"Fsock" => "fsockopen"
	);

	/**
	 * Initializes a new HTTP request with the given session.
	 *
	 * @param string	Webpage url
	 * @param string	Session name
	 *
	 * @return Recipe_HTTP_Request
	 */
	public function __construct($url, $sessionName = null)
	{
		$this->setUrl($url);
		$this->setSessionName($sessionName);
		return $this;
	}

	/**
	 * Returns the response.
	 *
	 * @return string
	 */
	public function getResponse()
	{
		return $this->getSession()->getResponse();
	}

	/**
	 * Returns the session name.
	 *
	 * @return string
	 *
	 * @throws Recipe_Exception_Generic
	 */
	public function getSessionName()
	{
		if(empty($this->sessionName))
		{
			$this->setSessionName();
			if(empty($this->sessionName))
			{
				throw new Recipe_Exception_Generic("Unable to establish an HTTP request. Maybe the server does not support this feature.");
			}
		}
		return $this->sessionName;
	}

	/**
	 * Returns the session.
	 *
	 * @return Recipe_HTTP_Session_Abstract
	 */
	public function getSession()
	{
		if(is_null($this->session))
		{
			$sessionName = $this->getSessionName();
			$this->setSession(new $sessionName($this->getUrl()));
			Hook::event("StartHttpRequest", array($this));
		}
		return $this->session;
	}

	/**
	 * Sets the session.
	 *
	 * @param Recipe_HTTP_Session_Abstract $session
	 *
	 * @return unknown_type
	 */
	public function setSession(Recipe_HTTP_Session_Abstract $session)
	{
		$this->session = $session;
		return $this;
	}

	/**
	 * Sets the session name.
	 *
	 * @param string	The session name [optional]
	 *
	 * @return Recipe_HTTP_Request
	 *
	 * @throws Recipe_Exception_Generic
	 */
	public function setSessionName($sessionName = null)
	{
		if(!is_null($sessionName) && array_key_exists($sessionName, $this->functions))
		{
			if(!function_exists($this->functions[$sessionName]))
			{
				throw new Recipe_Exception_Generic("Couldn't start HTTP request via ".$sessionName.". The function is not available on the server.", __FILE__, __LINE__);
			}
			$this->sessionName = "Recipe_HTTP_Session_".$sessionName;
		}
		else
		{
			foreach($this->functions as $s => $f)
			{
				$s = "Recipe_HTTP_Session_".$s;
				if(function_exists($f) && class_exists($s))
				{
					$this->sessionName = $s;
					return $this;
				}
			}
		}
		return $this;
	}

	/**
	 * Returns the url
	 *
	 * @return string
	 */
	public function getUrl()
	{
		return $this->url;
	}

	/**
	 * Sets the url.
	 *
	 * @param string
	 *
	 * @return Recipe_HTTP_Request
	 */
	public function setUrl($url)
	{
		$this->url = $url;
		return $this;
	}

	/**
	 * Destructor for request.
	 *
	 * @return void
	 */
	public function kill()
	{
		$this->session->close();
		unset($this);
		return;
	}
}
?>