<?php
/**
 * Admin menu.
 *
 * @package Recipe PHP5 Admin Interface
 * @author Sebastian Noll
 * @copyright Copyright (c) 2009, Sebastian Noll
 * @license Proprietary
 */

class Bengine_Admin_Menu
{
	/**
	 * @var array
	 */
	protected $menu = array();

	/**
	 * @var array
	 */
	protected $modules = null;

	/**
	 * @return \Bengine_Admin_Menu
	 */
	public function __construct()
	{
		return $this;
	}

	/**
	 * @return array
	 */
	public function render()
	{
		foreach($this->getModules() as $module)
		{
			if($module["enable"])
			{
				$name = $module["name"];
				$translate = true;
				if(is_array($name))
				{
					$translate = $name["noLangVar"];
					$name = $name["value"];
				}
				if($translate)
				{
					$name = Core::getLang()->get($name);
				}
				$uri = $module["link"];
				if(is_array($uri))
				{
					$package = isset($uri["package"]) ? $uri["package"] : "admin";
					$controller = isset($uri["controller"]) ? $uri["controller"] : "index";
					$action = isset($uri["action"]) ? $uri["action"] : "index";
					$uri = "$package/$controller/$action";
				}
				$this->menu[] = Link::get($uri, $name, "", isset($module["class"]) ? $module["class"] : "");
			}
		}
		return $this->menu;
	}

	/**
	 * @return array
	 */
	public function getModules()
	{
		if($this->modules === null)
		{
			$meta = Admin::getMeta();
			$this->modules = $meta["config"]["adminModules"];
		}
		return $this->modules;
	}

	/**
	 * @return array
	 */
	public function getMenu()
	{
		if(count($this->menu) <= 0)
		{
			$this->render();
		}
		return $this->menu;
	}
}
?>