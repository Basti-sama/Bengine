<?php
/**
 * Admin main class.
 *
 * @package Recipe PHP5 Admin Interface
 * @author Sebastian Noll
 * @copyright Copyright (c) 2009, Sebastian Noll
 * @license Proprietary
 */

require_once "SetCachePath.plugin.php";

class Admin extends Application
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
	 * @var Admin_Controller_Abstract
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
		Hook::addHook("CacheConstruct", new SetCachePathPlugin());
		parent::run();
		Core::getUser()->removeTheme();
		Core::getTPL()->setTemplatePath(APP_ROOT_DIR."ai/templates/");
		Core::getTPL()->setLayoutTemplate("interface");
		Core::getTPL()->addHTMLHeaderFile("style.css", "css");
		Core::getLang()->load(array("AI_Global"));
		$menu = new Admin_Menu();
		Core::getTPL()->addLoop("menu", $menu->getMenu());
		self::dispatch();
		return;
	}

	/**
	 * Dispatching the controller from request.
	 *
	 * @return Admin_Controller_Abstract
	 */
	protected static function dispatch()
	{
		$controllerName = Core::getRequest()->getGET("controller", "index");
		self::$controllerName = $controllerName;
		$config = array("action" => Core::getRequest()->getGET("action", "index"));
		self::$controller = self::factory("controller/".$controllerName, $config);
		if(!self::$controller)
		{
			self::$controller = self::factory("controller/index", $config);
		}
		return self::$controller->run();
	}

	/**
	 * Returns the current controller.
	 *
	 * @return Admin_Controller_Abstract
	 */
	public static function getController()
	{
		return self::$controller;
	}

	/**
	 * Rebuilds a cache object.
	 *
	 * @param string	Cache type
	 * @param mixed		Options [optional]
	 *
	 * @return void
	 */
	public static function rebuildCache($type, array $options = null)
	{
		switch($type)
		{
			case "config":
				Core::getCache()->buildConfigCache();
			break;
			case "lang":
				if($options !== null && !empty($options["language"]))
				{
					$result = Core::getQuery()->select("languages", array("langcode"), "", "languageid = '{$options["language"]}'");
					$langcode = Core::getDatabase()->fetch_field($result, "langcode");
					if(!empty($options["group"]))
					{
						$result = Core::getQuery()->select("phrasesgroups", array("title"), "", "phrasegroupid = '{$options["group"]}'");
						$groupname = Core::getDatabase()->fetch_field($result, "title");
						Core::getCache()->cachePhraseGroup($groupname, $langcode);
					}
					else
					{
						Core::getCache()->cacheLanguage($langcode);
					}
				}
				else
				{
					$result = Core::getQuery()->select("languages", "langcode");
					while($row = Core::getDB()->fetch($result))
					{
						Core::getCache()->cacheLanguage($row["langcode"]);
					}
				}
			break;
			case "perm":
				Core::getCache()->buildPermissionCache();
			break;
		}
		return;
	}

	/**
	 * Sets the cache pathes for AI.
	 *
	 * @param Recipe_Cache $cache
	 * @return void
	 */
	public static function setCachePath(Recipe_Cache $cache)
	{
		$cache->setSessionCacheDir("ai/sessions/");
		$cache->setTemplateCacheDir("ai/templates/");
		return;
	}
}
?>