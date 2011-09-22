<?php
class Admin_Controller_Index extends Admin_Controller_Abstract
{
	protected function indexAction()
	{
		Core::getLanguage()->load("AI_Overview");
		$this->assign("DBType", Core::getDB()->getDatabaseType());
		$this->assign("DBVersion", Core::getDB()->getVersion());
		$this->assign("PHPVersion", phpversion());
		$this->assign("IPAddress", IPADDRESS);
		$this->assign("Host", HTTP_HOST);
		$this->assign("ServerIP", $_SERVER["SERVER_ADDR"]);
		$this->assign("RecipeVersion", Core::versionToString());
		$this->assign("RecipeAIVersion", RECIPE_AI_VERSION);
		$this->assign("ServerSoftware", $_SERVER["SERVER_SOFTWARE"]);
	}
}
?>