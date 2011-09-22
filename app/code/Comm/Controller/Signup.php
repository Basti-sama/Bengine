<?php
/**
 * Sign up controller.
 *
 * @package Bengine
 * @copyright Copyright protected by / Urheberrechtlich geschützt durch "Sebastian Noll" <snoll@4ym.org>
 * @version $Id: Signup.php 22 2011-06-03 15:14:04Z secretchampion $
 */

class Comm_Controller_Signup extends Comm_Controller_Abstract
{
	/**
	 * Index action.
	 *
	 * @return Comm_Controller_Signup
	 */
	public function indexAction()
	{
		$this->page = Core::getLang()->getItem("REGISTRATION");
		$this->userCheck = sprintf(Core::getLanguage()->getItem("USER_CHECK"), Core::getOptions()->get("MIN_USER_CHARS"), Core::getOptions()->get("MAX_USER_CHARS"));
		$this->passwordCheck = sprintf(Core::getLanguage()->getItem("PASSWORD_CHECK"), Core::getOptions()->get("MIN_PASSWORD_LENGTH"), Core::getOptions()->get("MAX_PASSWORD_LENGTH"));
		return $this;
	}

	/**
	 * Checks the user data and execute registration.
	 *
	 * @return Comm_Controller_Signup
	 */
	public function checkuserAction()
	{
		if($this->_isExternal($this->getParam("universe")))
		{
			$this->_sendRemoteRequest($this->getParam("universe"), $this->getParam("username"), $this->getParam("password"), $this->getParam("email"));
		}
		$creator = new Bengine_Account_Creator($this->getParam("username"), $this->getParam("password"), $this->getParam("email"), 1);
		$creator->create();
		return $this;
	}

	/**
	 * Activate an account.
	 *
	 * @return Comm_Controller_Signup
	 */
	public function activationAction()
	{
		try {
			$activator = new Bengine_Account_Activation($this->getParam("key"));
		} catch(Recipe_Exception_Global $e) {
			$e->printError();
		}
		return $this;
	}

	/**
	 * Sends a remote request.
	 *
	 * @param string	Universe url
	 * @param string	Username
	 * @param string	Password
	 * @param string	E-Mail address
	 *
	 * @return Comm_Controller_Password
	 */
	protected function _sendRemoteRequest($url, $username, $password, $email)
	{
		$url .= Core::getLang()->getOpt("langcode")."/signup/checkuser";
		$request = new Recipe_HTTP_Request($url, "Curl");
		$request->getSession()
			->setRequestType("POST")
			->setPostArgs(array("username" => $username, "password" => $password, "email" => $email));
		Core::getTPL()->sendHeader();
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