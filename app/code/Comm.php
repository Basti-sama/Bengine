<?php
/**
 * Community application starter.
 *
 * @package Bengine
 * @copyright Copyright protected by / Urheberrechtlich geschützt durch "Sebastian Noll" <snoll@4ym.org>
 * @version $Id: Comm.php 29 2011-06-26 10:42:13Z secretchampion $
 */

class Comm extends Application
{
	/**
	 * Controller name.
	 *
	 * @var string
	 */
	protected static $controllerName = "";

	/**
	 * Controller object.
	 *
	 * @var Comm_Controller_Abstract
	 */
	protected static $controller = null;

	/**
	 * Internal Content Management System.
	 *
	 * @var Comm_CMS
	 */
	protected static $cmsObject = null;

	/**
	 * Holds all universes.
	 *
	 * @var array
	 */
	protected static $unis = array();

	/**
	 * Runs the community application.
	 *
	 * @return void
	 */
	public static function run()
	{
		parent::run();
		define("LANG", Core::getLang()->getOpt("langcode")."/");
		Core::getLang()->load("Registration");
		self::setCMS();
		self::initUniverses();
		self::initLanguage();

		Core::getTPL()->addHTMLHeaderFile("lib/jquery.js", "js");
		Core::getTPL()->addHTMLHeaderFile("lib/jquery-ui.js", "js");
		Core::getTPL()->addHTMLHeaderFile("lib/jquery.lightbox.js", "js");
		Core::getTPL()->addHTMLHeaderFile("lib/jquery.flash.js", "js");
		Core::getTPL()->addHTMLHeaderFile("sign.js", "js");
		Core::getTPL()->addHTMLHeaderFile("reset.css", "css");
		Core::getTPL()->addHTMLHeaderFile("jquery-ui.css", "css");
		Core::getTPL()->addHTMLHeaderFile("preview.css", "css");
		Core::getTPL()->assign("containerClass", "content");
		Core::getTPL()->addLoop("headerMenu", self::getCMS()->getMenu("h"));
		$userCheck = sprintf(Core::getLanguage()->getItem("USER_CHECK"), Core::getOptions()->get("MIN_USER_CHARS"), Core::getOptions()->get("MAX_USER_CHARS"));
		$passwordCheck = sprintf(Core::getLanguage()->getItem("PASSWORD_CHECK"), Core::getOptions()->get("MIN_PASSWORD_LENGTH"), Core::getOptions()->get("MAX_PASSWORD_LENGTH"));
		Core::getTPL()->assign("userCheck", $userCheck);
		Core::getTPL()->assign("passwordCheck", $passwordCheck);

		Core::getTPL()->assign("uniSelection", self::getUnisAsOptionList());

		self::dispatch();
		return;
	}

	/**
	 * Sets the CMS.
	 *
	 * @return Comm_CMS
	 */
	protected static function setCMS()
	{
		self::$cmsObject = new Comm_CMS();
		return self::$cmsObject;
	}

	/**
	 * Returns the CMS object.
	 *
	 * @return Comm_CMS
	 */
	public static final function getCMS()
	{
		return self::$cmsObject;
	}

	/**
	 * Dispatching the controller from request.
	 *
	 * @return Comm_Controller_Abstract
	 */
	protected static function dispatch()
	{
		$controllerName = Core::getRequest()->getGET("controller", "index");
		self::$controllerName = $controllerName;
		$config = array("action" => Core::getRequest()->getGET("action", "index"));
		self::$controller = self::factory("controller/".$controllerName, $config);
		if(!self::$controller)
		{
			self::$controller = self::factory("controller/index", array("action" => "noroute"));
		}
		return self::$controller->run();
	}

	/**
	 * Initializes the universes.
	 *
	 * @return array	The universes
	 */
	protected static function initUniverses()
	{
		$xml = new XMLObj(file_get_contents(AD."etc/Unis.xml"));
		foreach($xml->getChildren() as $uni)
		{
			self::$unis[] = new Comm_Uni($uni->getString("name"), $uni->getString("domain"), $uni->getBoolean("external"));
		}
		return self::$unis;
	}

	/**
	 * Generates a select list with all universes.
	 *
	 * @return string
	 */
	protected static function getUnisAsOptionList()
	{
		$options = "";
		foreach(self::$unis as $uni)
		{
			if($uni->isSelected())
			{
				$selected = " selected=\"selected\"";
			}
			else
			{
				$selected = "";
			}
			$options .= "<option value=\"".$uni->getDomain()."\"".$selected.">".$uni->getName()."</option>";
		}
		return $options;
	}

	/**
	 * Initializes the languages.
	 *
	 * @return void
	 */
	protected static function initLanguage()
	{
		$result = Core::getQuery()->select("languages", array("langcode", "title"));
		Core::getTPL()->addLoop("languages", $result);
		Core::getTPL()->assign("langCount", Core::getDB()->num_rows($result));
		$selfUrl = "/".Core::getRequest()->getGET("controller")."/".Core::getRequest()->getGET("action");
		Core::getTPL()->assign("selfUrl", (Core::getRequest()->getGET("controller", false)) ? $selfUrl : "");
		return;
	}
}
?>