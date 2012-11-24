<?php
/**
 * Community application starter.
 *
 * @package Bengine
 * @copyright Copyright protected by / Urheberrechtlich geschützt durch "Sebastian Noll" <snoll@4ym.org>
 */

class Comm extends Application
{
	/**
	 * Internal Content Management System.
	 *
	 * @var Bengine_Comm_CMS
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
	 * @return Comm
	 */
	public function run()
	{
		parent::run();
		define("LANG", Core::getLang()->getOpt("langcode")."/");
		Core::getLang()->load("Registration");
		self::setCMS();
		self::initUniverses();
		self::initLanguage();

		Core::getTPL()->addHTMLHeaderFile("lib/jquery.js", "js");
		Core::getTPL()->addHTMLHeaderFile("lib/jquery-ui.js", "js");
		Core::getTPL()->addHTMLHeaderFile("sign.js", "js");
		Core::getTPL()->addHTMLHeaderFile("jquery-ui.css", "css");
		Core::getTPL()->addHTMLHeaderFile("comm.css", "css");
		Core::getTPL()->assign("containerClass", "content");
		Core::getTPL()->addLoop("headerMenu", self::getCMS()->getMenu("h"));
		$userCheck = sprintf(Core::getLanguage()->getItem("USER_CHECK"), Core::getOptions()->get("MIN_USER_CHARS"), Core::getOptions()->get("MAX_USER_CHARS"));
		$passwordCheck = sprintf(Core::getLanguage()->getItem("PASSWORD_CHECK"), Core::getOptions()->get("MIN_PASSWORD_LENGTH"), Core::getOptions()->get("MAX_PASSWORD_LENGTH"));
		Core::getTPL()->assign("userCheck", $userCheck);
		Core::getTPL()->assign("passwordCheck", $passwordCheck);

		Core::getTPL()->assign("uniSelection", self::getUnisAsOptionList());

		$this->dispatch();
		return $this;
	}

	/**
	 * Sets the CMS.
	 *
	 * @return Bengine_Comm_CMS
	 */
	protected static function setCMS()
	{
		self::$cmsObject = new Bengine_Comm_CMS();
		return self::$cmsObject;
	}

	/**
	 * Returns the CMS object.
	 *
	 * @return Bengine_Comm_CMS
	 */
	public static final function getCMS()
	{
		return self::$cmsObject;
	}

	/**
	 * Initializes the universes.
	 *
	 * @return array	The universes
	 */
	protected static function initUniverses()
	{
		$meta = self::getMeta();
		foreach($meta["config"]["universes"] as $uni)
		{
			self::$unis[] = new Bengine_Comm_Uni($uni["name"], $uni["domain"], $uni["external"]);
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
		/* @var Bengine_Comm_Uni $uni */
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
		Core::getTPL()->addLoop("languages", $result->fetchAll());
		Core::getTPL()->assign("langCount", $result->rowCount());
		$selfUrl = "/".Core::getRequest()->getGET("controller")."/".Core::getRequest()->getGET("action");
		Core::getTPL()->assign("selfUrl", (Core::getRequest()->getGET("controller", false)) ? $selfUrl : "");
		return;
	}
}
?>