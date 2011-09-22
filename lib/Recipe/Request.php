<?php
/**
 * RequestHandler. Parses global variables.
 *
 * @package Recipe 1.2
 * @author Sebastian Noll
 * @copyright Copyright (c) 2009, Sebastian Noll
 * @license Proprietary
 * @version $Id: Request.php 19 2011-05-27 10:30:33Z secretchampion $
 */

class Recipe_Request
{
	/**
	 * The HTTP request adapter.
	 *
	 * @var Recipe_Request_Adapter
	 */
	protected $adapter = null;

	/**
	 * HTTP File Upload variables.
	 *
	 * @var array
	 */
	protected $files = array();

	/**
	 * Arguments send via URL.
	 *
	 * @var array
	 */
	protected $args = array();

	/**
	 * The complete requested URL
	 *
	 * @var string
	 */
	protected $requestedURL = "";

	/**
	 * URL path level names.
	 *
	 * @var array
	 */
	protected $levelNames = array();

	/**
	 * Constructor.
	 *
	 * @return void
	 */
	public function __construct($adapter = "default")
	{
		$this->files = $_FILES;
		$this->setLevelNames();
		$this->requestedURL = $_SERVER["REQUEST_URI"];
		$this->splitURL()->putArgsIntoRequestVars();
		$this->setAdapter($adapter);
		$this->flushGlobals();
		return;
	}

	/**
	 * Set the request adapter.
	 *
	 * @param string	Adapter name
	 *
	 * @return Recipe_Request
	 */
	protected function setAdapter($adapter)
	{
		$class = "Recipe_Request_".ucwords($adapter);
		$this->adapter = new $class();
		return $this;
	}

	/**
	 * Clear global arrays and deallocate disk space.
	 *
	 * @return Recipe_Request
	 */
	protected function flushGlobals()
	{
		unset($_REQUEST);
		return $this;
	}

	//### All functions below serve the internal rewrite engine ###//

	/**
	 * Split the URL into arguments.
	 *
	 * @return Recipe_Request
	 */
	protected function splitURL()
	{
		$requestedUrl = $this->getRequestedUrl();

		// Remove query string
		if(!empty($_SERVER["QUERY_STRING"]))
		{
			$requestedUrl = Str::replace("?".$_SERVER["QUERY_STRING"], "", $requestedUrl);
		}

		$splitted = @parse_url($requestedUrl);
		// Sometimes the first slash causes errors
		if(!$splitted)
		{
			$splitted = parse_url(Str::substring($requestedUrl, 1));
		}

		// Remove real path from virtual path.
		if(MOD_REWRITE)
		{
			$path = Str::replace(dirname($_SERVER["SCRIPT_NAME"])."/", "", $splitted["path"]);
		}
		else
		{
			$path = Str::substring(Str::replace($_SERVER["SCRIPT_NAME"], "", $splitted["path"]), 1);
		}

		if(Str::length($path) > 0)
		{
			Hook::event("SplitUrlStart", array($this, &$path));
			$path = explode("/", $path);
			foreach($path as $key => $value)
			{
				if(strpos($value, ":") > 0)
				{
					$value = explode(":", Str::replace(" ", "_", $value));
					$this->args[] = Arr::trimArray($value);
				}
				else if($value != "")
				{
					$this->args[] = $value;
				}
			}
			Hook::event("SplitUrlClose", array($this, &$path));
		}
		return $this;
	}

	/**
	 * This function puts the arguments into the
	 * respective request variables.
	 * http://www.anypage.com/GoArgument/SpecificArgument:Content
	 * get["go"] => GoArgument, get["SpecificArgument"] => Content.
	 * If the argument has no stated name, the function will use level
	 * names (These can be changed by class variable levelNames).
	 * [Note that you have to mind the right order!])
	 *
	 * @return Recipe_Request
	 */
	protected function putArgsIntoRequestVars()
	{
		for($i = 0; $i < count($this->args); $i++)
		{
			if($this->args[$i] == "")
			{
				continue;
			}
			if(is_array($this->args[$i]))
			{
				$_GET[$this->args[$i][0]] = rawurldecode($this->args[$i][1]);
			}
			else
			{
				if(isset($this->levelNames[$i]) && Str::length($this->levelNames[$i]) > 0)
				{
					$_GET[$this->levelNames[$i]] = rawurldecode($this->args[$i]);
				}
				else
				{
					$_GET[$this->args[$i]] = rawurldecode($this->args[$i]);
				}
			}
		}
		Hook::event("ArgsIntoRequestVars", array($this));
		return $this;
	}

	/**
	 * Returns the value of the given request type parameter.
	 *
	 * @param string	Request type (get, post or cookie)
	 * @param string	Parameter
	 *
	 * @return mixed	The value or false
	 */
	public function getArgument($requestType, $param)
	{
		$requestType = strtolower($requestType);
		switch($requestType)
		{
			case "get":
				return $this->getGET($param);
			break;
			case "post":
				return $this->getPOST($param);
			break;
			case "cookie":
				return $this->getCOOKIE($param);
			break;
			case "files":
				return $this->getFILES($param);
			break;
		}
		return false;
	}

	/**
	 * Returns the parameter of the GET query.
	 *
	 * @param string	Parameter
	 * @param mixed		Default return value
	 *
	 * @return mixed	The value or false
	 */
	public function getGET($param = null, $default = false)
	{
		return $this->getAdapter()->getGet($param, $default);
	}

	/**
	 * Returns the parameter of the POST query.
	 *
	 * @param string	Parameter
	 * @param mixed		Default return value
	 *
	 * @return mixed	The value or false
	 */
	public function getPOST($param = null, $default = false)
	{
		return $this->getAdapter()->getPost($param, $default);
	}

	/**
	 * Returns the parameter of the COOKIE query.
	 *
	 * @param string	Parameter
	 * @param mixed		Default return value
	 *
	 * @return mixed	The value or false
	 */
	public function getCOOKIE($param = null, $default = false)
	{
		if(!is_null($param))
		{
			$param = COOKIE_PREFIX.$param;
		}
		return $this->getAdapter()->getCookie($param, $default);
	}

	/**
	 * Returns uploaded items of the FILES query.
	 *
	 * @param string	Upload id
	 * @param mixed		Default return value
	 *
	 * @return mixed	The file upload data, false otherwise
	 */
	public function getFILES($fileid = null, $default = false)
	{
		if(is_null($fileid))
		{
			return $this->files;
		}
		return (isset($this->files[$fileid])) ? $this->files[$fileid] : $default;
	}

	/**
	 * Returns an HTTP-parameter.
	 *
	 * @param string	HTTP-type
	 * @param string	Parameter
	 *
	 * @return mixed	The value or false
	 */
	public function get($http, $param)
	{
		return $this->getArgument($http, $param);
	}

	/**
	 * Returns the requested URL.
	 *
	 * @return string
	 */
	public function getRequestedUrl()
	{
		return $this->requestedURL;
	}

	/**
	 * Sets the request parameter sequence.
	 *
	 * @param array		Parameter sequence [optional]
	 *
	 * @return Recipe_Request
	 */
	public function setLevelNames(array $levelNames = null)
	{
		if(is_null($levelNames) && defined("REQUEST_LEVEL_NAMES"))
		{
			$levelNames = explode(",", REQUEST_LEVEL_NAMES);
			$levelNames = array_map("trim", $levelNames);
		}
		$this->levelNames = $levelNames;
		return $this;
	}

	/**
	 * Sets a new cookie.
	 *
	 * @param string	Cookie name
	 * @param string	Cookie value
	 * @param integer	Cookie expires in $ days
	 *
	 * @return Recipe_Request
	 */
	public function setCookie($cookie, $value, $expires = 365)
	{
		$domain = ($_SERVER["SERVER_NAME"] != "localhost") ? Str::replace("www.", ".", $_SERVER["SERVER_NAME"]) : null;
		setcookie(COOKIE_PREFIX.$cookie, $value, TIME + 86400 * $expires, "/", $domain);
		return $this;
	}

	/**
	 * Returns the request adapter.
	 *
	 * @return Recipe_Request_Adapter
	 */
	public function getAdapter()
	{
		return $this->adapter;
	}
}
?>