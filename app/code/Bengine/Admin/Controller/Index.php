<?php
/**
 * Index controller.
 *
 * @package Recipe PHP5 Admin Interface
 * @author Sebastian Noll
 * @copyright Copyright (c) 2012, Sebastian Noll
 * @license Proprietary
 */

class Bengine_Admin_Controller_Index extends Bengine_Admin_Controller_Abstract
{
	/**
	 * @return Bengine_Admin_Controller_Index
	 */
	protected function indexAction()
	{
		Core::getLanguage()->load("AI_Overview");
		$this->assign("DBType", Core::getDB()->getDatabaseType());
		$this->assign("DBVersion", Core::getDB()->getClientVersion());
		$this->assign("DBServer", Core::getDB()->getServerVersion());
		$this->assign("PHPVersion", phpversion());
		$this->assign("IPAddress", IPADDRESS);
		$this->assign("Host", HTTP_HOST);
		$this->assign("ServerIP", $_SERVER["SERVER_ADDR"]);
		$this->assign("RecipeVersion", Core::versionToString());
		$meta = Admin::getMeta();
		$this->assign("RecipeAIVersion", $meta["packages"]["bengine"]["admin"]["version"]);
		$this->assign("ServerSoftware", $_SERVER["SERVER_SOFTWARE"]);
		return $this;
	}
}
?>