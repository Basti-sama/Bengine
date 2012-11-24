<?php
/**
 * Auth controller.
 *
 * @package Recipe PHP5 Admin Interface
 * @author Sebastian Noll
 * @copyright Copyright (c) 2012, Sebastian Noll
 * @license Proprietary
 */

class Bengine_Admin_Controller_Auth extends Bengine_Admin_Controller_Abstract
{
	/**
	 * @return Bengine_Admin_Controller_Auth
	 */
	protected function indexAction()
	{
		$this->setIsAjax();
		$this->setMainTemplate(null);
		if($this->isPost())
		{
			$encryption = Core::getOptions("USE_PASSWORD_SALT") ? "md5_salt" : "md5";
			$auth = new Login($this->getParam("username"), $this->getParam("password"), "admin/index", $encryption);
			$auth->setRedirectOnFailure(false)
				->setCountLoginAttempts(false)
				->checkData();
			if($auth->getCanLogin())
			{
				$auth->startSession();
			}
			Core::getTemplate()->assign("loginErrors", $auth->getErrors());
		}
		return $this;
	}

	/**
	 * @return Bengine_Admin_Controller_Auth
	 */
	protected function logoutAction()
	{
		if(Core::getUser()->getSid())
		{
			Core::getCache()->cleanUserCache(Core::getUser()->get("userid"));
			Core::getRequest()->setCookie("sid", "", TIME - 1);
		}
		$this->redirect("auth");
		return $this;
	}
}
?>