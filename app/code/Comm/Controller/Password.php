<?php
/**
 * Lost password controller.
 *
 * @package Bengine
 * @copyright Copyright protected by / Urheberrechtlich geschützt durch "Sebastian Noll" <snoll@4ym.org>
 * @version $Id: Password.php 8 2010-10-17 20:55:04Z secretchampion $
 */

class Comm_Controller_Password extends Comm_Controller_Abstract
{
	/**
	 * (non-PHPdoc)
	 * @see app/code/Comm/Controller/Comm_Controller_Abstract#init()
	 */
	protected function init()
	{
		$this->page = Core::getLang()->get("PASSWORD_FORGOTTEN");
		return $this;
	}

	/**
	 * Index action.
	 *
	 * @return Comm_Controller_Password
	 */
	public function indexAction()
	{
		return $this;
	}

	/**
	 * Request a new password or user name.
	 *
	 * @return Comm_Controller_Password
	 */
	public function requestAction()
	{
		if($this->_isExternal($this->getParam("universe")))
		{
			$this->_sendRemoteRequest($this->getParam("universe"), $this->getParam("username"), $this->getParam("email"));
		}
		new Bengine_Account_Password_Lost($this->getParam("username"), $this->getParam("email"));
		return $this;
	}

	/**
	 * Set a new password.
	 *
	 * @return Comm_Controller_Password
	 */
	public function setAction()
	{
		return $this;
	}

	/**
	 * Change the password.
	 *
	 * @return Comm_Controller_Password
	 */
	public function changeAction()
	{
		new Bengine_Account_Password_Changer($this->getParam("userid"), $this->getParam("key"), $this->getParam("password"));
		return $this;
	}

	/**
	 * Sends a remote request.
	 *
	 * @param string	Universe url
	 * @param string	Username
	 * @param string	E-Mail address
	 *
	 * @return Comm_Controller_Password
	 */
	protected function _sendRemoteRequest($url, $username, $email)
	{
		$url .= Core::getLang()->getOpt("langcode")."/password/request";
		$request = new Recipe_HTTP_Request($url, "Curl");
		$request->getSession()
			->setRequestType("POST")
			->setPostArgs(array("username" => $username, "email" => $email));
		terminate($request->getResponse());
		return $this;
	}

	/**
	 * Checks if the universe is an external url.
	 *
	 * @param string
	 *
	 * @return boolean
	 */
	protected function _isExternal($url)
	{
		if($url != "" && Link::isExternal($url))
		{
			return true;
		}
		return false;
	}
}
?>