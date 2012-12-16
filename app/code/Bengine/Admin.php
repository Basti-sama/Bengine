<?php
/**
 * Admin main class.
 *
 * @package Recipe PHP5 Admin Interface
 * @author Sebastian Noll
 * @copyright Copyright (c) 2009, Sebastian Noll
 * @license Proprietary
 */

class Admin extends Application
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
	 * @return void
	 */
	public function run()
	{
		parent::run();
		Core::getUser()->removeTheme();
		Core::getTPL()->addHTMLHeaderFile("admin.css", "css");
		Core::getLang()->load(array("AI_Global"));
		$menu = new Bengine_Admin_Menu();
		Core::getTPL()->addLoop("menu", $menu->getMenu());
		$this->dispatch();
		return;
	}

	/**
	 * Rebuilds a cache object.
	 *
	 * @param string $type
	 * @param mixed $options
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
					$result = Core::getQuery()->select("languages", array("langcode"), "", Core::getDB()->quoteInto("languageid = ?", $options["language"]));
					$langcode = $result->fetchColumn();
					if(!empty($options["group"]))
					{
						$result = Core::getQuery()->select("phrasesgroups", array("title"), "", Core::getDB()->quoteInto("phrasegroupid = ?", $options["group"]));
						$groupname = $result->fetchColumn();
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
					foreach($result->fetchAll() as $row)
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
}
?>