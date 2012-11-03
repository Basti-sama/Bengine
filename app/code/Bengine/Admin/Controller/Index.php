<?php
class Bengine_Admin_Controller_Index extends Bengine_Admin_Controller_Abstract
{
	protected function indexAction()
	{
		Core::getLanguage()->load("AI_Overview");
		$this->assign("DBType", Core::getDB()->getDatabaseType());
		$this->assign("DBVersion", Core::getDB()->getVersion());
		$this->assign("DBServer", Core::getDB()->fetch_field(Core::getDatabase()->query("SELECT VERSION() as version"), "version"));
		$this->assign("PHPVersion", phpversion());
		$this->assign("IPAddress", IPADDRESS);
		$this->assign("Host", HTTP_HOST);
		$this->assign("ServerIP", $_SERVER["SERVER_ADDR"]);
		$this->assign("RecipeVersion", Core::versionToString());
		$meta = Admin::getMeta();
		$this->assign("RecipeAIVersion", $meta["packages"]["bengine"]["admin"]["version"]);
		$this->assign("ServerSoftware", $_SERVER["SERVER_SOFTWARE"]);
	}
}
?>