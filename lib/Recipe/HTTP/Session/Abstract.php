<?php
/**
 * HTTP request adapter.
 *
 * @package Recipe 1.2
 * @author Sebastian Noll
 * @copyright Copyright (c) 2009, Sebastian Noll
 * @license Proprietary
 * @version $Id: Abstract.php 12 2011-01-03 17:45:40Z secretchampion $
 */

abstract class Recipe_HTTP_Session_Abstract
{
	/**
	 * Default request timeout.
	 *
	 * @var integer
	 */
	const TIMEOUT = 10;

	/**
	 * The requesting url.
	 *
	 * @var string
	 */
	protected $webpage = "";

	/**
	 * Response body.
	 *
	 * @var string
	 */
	protected $response = "";

	/**
	 * The last error number.
	 *
	 * @var integer
	 */
	protected $errorNo = 0;

	/**
	 * The last error message.
	 *
	 * @var string
	 */
	protected $error = "";

	/**
	 * GET parameter.
	 *
	 * @var array
	 */
	protected $getArgs = array();

	/**
	 * POST parameter.
	 *
	 * @var array
	 */
	protected $postArgs = array();

	/**
	 * Request type (GET or POST).
	 *
	 * @var string
	 */
	protected $requestType = "GET";

	/**
	 * Constructor.
	 *
	 * @param string	URL to connect with
	 *
	 * @return Recipe_HTTP_Session_Abstract
	 */
	public function __construct($webpage)
	{
		$this->webpage = $webpage;
		return $this;
	}

	/**
	 * Returns the response.
	 *
	 * @return string
	 */
	public function getResponse()
	{
		$this->init();
		return $this->response;
	}

	/**
	 * Returns the latest error message.
	 *
	 * @return string
	 */
	public function getError()
	{
		return $this->error;
	}

	/**
	 * Returns the latest error number.
	 *
	 * @return integer
	 */
	public function getErrorNo()
	{
		return $this->errorNo;
	}

	/**
	 * Sets the destination URL.
	 *
	 * @param string
	 *
	 * @return Recipe_HTTP_Adapter
	 */
	public function setWebpage($webpage)
	{
		$this->webpage = $webpage;
		return $this;
	}

	/**
	 * Returns the GET parameter.
	 *
	 * @param boolean	Parameter as an array
	 *
	 * @return array|string
	 */
	public function getGetArgs($asArray = true)
	{
		if(!$asArray)
		{
			$query = array();
			foreach($this->getArgs as $key => $value)
			{
				$query[] = $key."=".$value;
			}
			return implode("&", $query);
		}
		return $this->getArgs;
	}

	/**
	 * Sets the GET parameter.
	 *
	 * @param array
	 *
	 * @return Recipe_HTTP_Session_Abstract
	 */
	public function setGetArgs(array $getArgs)
	{
		$this->getArgs = $getArgs;
		return $this;
	}

	/**
	 * Returns the POST parameter.
	 *
	 * @param boolean	Parameter as an array
	 *
	 * @return array|string
	 */
	public function getPostArgs($asArray = true)
	{
		if(!$asArray)
		{
			return http_build_query($this->postArgs);
		}
		return $this->postArgs;
	}

	/**
	 * Sets the request type.
	 *
	 * @param string
	 *
	 * @return Recipe_HTTP_Session_Abstract
	 */
	public function setRequestType($requestType = "GET")
	{
		$this->requestType = strtoupper($requestType);
		return $this;
	}

	/**
	 * Returns the request type.
	 *
	 * @return string
	 */
	public function getRequestType()
	{
		return $this->requestType;
	}

	/**
	 * Sets the POST parameter.
	 *
	 * @param array
	 *
	 * @return Recipe_HTTP_Session_Abstract
	 */
	public function setPostArgs(array $postArgs)
	{
		$this->postArgs = $postArgs;
		return $this;
	}

	/**
	 * Force adapting classes to implement init method.
	 *
	 * @return Recipe_HTTP_Session_Abstract
	 */
	abstract protected function init();

	/**
	 * Force adapting classes to implement the destructor.
	 *
	 * @return Recipe_HTTP_Session_Abstract
	 */
	abstract public function close();
}
?>