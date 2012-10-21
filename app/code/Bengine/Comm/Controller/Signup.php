<?php
/**
 * Sign up controller.
 *
 * @package Bengine
 * @copyright Copyright protected by / Urheberrechtlich geschützt durch "Sebastian Noll" <snoll@4ym.org>
 * @version $Id: Signup.php 22 2011-06-03 15:14:04Z secretchampion $
 */

class Bengine_Comm_Controller_Signup extends Bengine_Comm_Controller_Abstract
{
	/**
	 * Index action.
	 *
	 * @return Bengine_Comm_Controller_Signup
	 */
	public function indexAction()
	{
		$this->assign("page", Core::getLang()->getItem("REGISTRATION"));
		$this->assign("userCheck", sprintf(Core::getLanguage()->getItem("USER_CHECK"), Core::getOptions()->get("MIN_USER_CHARS"), Core::getOptions()->get("MAX_USER_CHARS")));
		$this->assign("passwordCheck", sprintf(Core::getLanguage()->getItem("PASSWORD_CHECK"), Core::getOptions()->get("MIN_PASSWORD_LENGTH"), Core::getOptions()->get("MAX_PASSWORD_LENGTH")));
		return $this;
	}

	/**
	 * Checks the user data and execute registration.
	 *
	 * @return Bengine_Comm_Controller_Signup
	 */
	public function checkuserAction()
	{
		if($this->_isExternal($this->getParam("universe")))
		{
			$this->_sendRemoteRequest($this->getParam("universe"), $this->getParam("username"), $this->getParam("password"), $this->getParam("email"));
		}
		$creator = new Bengine_Game_Account_Creator($this->getParam("username"), $this->getParam("password"), $this->getParam("email"), 1);
		$creator->create();
		return $this;
	}

	/**
	 * Activate an account.
	 *
	 * @return Bengine_Comm_Controller_Signup
	 */
	public function activationAction()
	{
		try {
			$activator = new Bengine_Game_Account_Activation($this->getParam("key"));
		} catch(Recipe_Exception_Global $e) {
			$e->printError();
		}
		return $this;
	}

	/**
	 * Sends a remote request.
	 *
	 * @param string $url		Universe url
	 * @param string $username	Username
	 * @param string $password	Password
	 * @param string $email		E-Mail address
	 *
	 * @return Bengine_Comm_Controller_Password
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