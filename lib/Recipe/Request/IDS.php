<?php
/**
 * IDS request adapter.
 *
 * @package Recipe 1.2
 * @author Sebastian Noll
 * @copyright Copyright (c) 2009, Sebastian Noll
 * @license Proprietary
 * @version $Id: IDS.php 8 2010-10-17 20:55:04Z secretchampion $
 */

class Recipe_Request_IDS extends Recipe_Request_Adapter
{
	/**
	 * Holds the IDS object.
	 *
	 * @var IDS_Monitor
	 */
	protected $ids = null;

	/**
	 * Initializing method: Removes slashes from GPC.
	 *
	 * @return Recipe_Request_IDS
	 */
	protected function init()
	{
		parent::init();
		$this->setIds(new IDS_Monitor(array(
			"GET" => $_GET,
			"POST" => $_POST,
			"COOKIE" => $_COOKIE
		), IDS_Init::init(RD."IDS/Config/Config.ini")), array("sqli", "spam", "dt"));
		$result = $this->getIds()->run();
		if(!$result->isEmpty())
		{
			$report  = $result->__toString();
			$report .= "<br/>URI: ".$_SERVER["REQUEST_URI"]."<br/>IP-Address: ".IPADDRESS;
			echo $report;
			$file = randString(8).".html";
			file_put_contents(AD."var/reports/injection_".$file, $report);
			exit;
		}
		return $this;
	}

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
			return $_GET;
		}
		return (isset($_GET[$var])) ? $_GET[$var] : $default;
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
			return $_POST;
		}
		return (isset($_POST[$var])) ? $_POST[$var] : $default;
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
			return $_COOKIE;
		}
		return (isset($_COOKIE[COOKIE_PREFIX.$var])) ? $_COOKIE[COOKIE_PREFIX.$var] : $default;
	}

	/**
	 * Sets the IDS object.
	 *
	 * @param IDS_Monitor
	 *
	 * @return Recipe_Request_IDS
	 */
	public function setIds(IDS_Monitor $ids)
	{
		$this->ids = $ids;
		return $this;
	}

	/**
	 * Returns the IDS object.
	 *
	 * @return IDS_Monitor
	 */
	public function getIds()
	{
		return $this->ids;
	}
}
?>