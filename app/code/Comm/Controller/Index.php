<?php
/**
 * Index controller
 *
 * @package Bengine
 * @copyright Copyright protected by / Urheberrechtlich geschützt durch "Sebastian Noll" <snoll@4ym.org>
 * @version $Id: Index.php 29 2011-06-26 10:42:13Z secretchampion $
 */

class Comm_Controller_Index extends Comm_Controller_Abstract
{
	/**
	 * Index action.
	 *
	 * @return Comm_Controller_Index
	 */
	public function indexAction()
	{
		$this->errorMsg = Core::getRequest()->getGET("error");
		if($this->isPost())
		{
			$encryption = Core::getOptions("USE_PASSWORD_SALT") ? "md5_salt" : "md5";
			$login = new Bengine_Login($this->getParam("username"), $this->getParam("password"), "game.php", $encryption);
			$login->setRedirectOnFailure(false)
				->checkData();
			if($login->getCanLogin())
			{
				$login->startSession();
			}
			else
			{
				$this->errorMsg = $login->getErrors()->getFirst();
			}
		}
		if($this->errorMsg != "")
		{
			Core::getLang()->load("error");
			$this->errorMsg = Core::getLang()->get($this->errorMsg);
		}
		$this->page = Core::getLang()->getItem("LOGIN");
		if($cmsPage = Comm::getCMS()->getPage("index"))
		{
			$this->page = $cmsPage["title"];
			$this->content = $cmsPage["content"];
			$this->setTemplate("cms_page");
		}
		else
		{
			$this->showDefaultCotnent = true;
		}
		return $this;
	}

	/**
	 * Overriding no route action to check for CMS pages.
	 *
	 * @return Comm_Controller_Index
	 */
	protected function norouteAction()
	{
		if($row = Comm::getCMS()->getPage($this->_action))
		{
			$this->page = $row["title"];
			$this->content = $row["content"];
			$this->setTemplate("cms_page");
		}
		else
		{
			parent::norouteAction();
		}
		return $this;
	}
}
?>