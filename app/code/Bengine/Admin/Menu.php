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
	 * @var string
	 */
	protected $moduleDir = "";

	/**
	 * @var array
	 */
	protected $modules = null;

	/**
	 * @param string $dir
	 */
	public function __construct($dir = null)
	{
		$this->setModuleDir($dir);
		return $this;
	}

	/**
	 * @param string $dir
	 * @return Bengine_Admin_Menu
	 */
	public function setModuleDir($dir = null)
	{
		if(is_null($dir) || !is_dir($dir))
		{
			$this->moduleDir = APP_ROOT_DIR."etc/admin";
		}
		else
		{
			$this->moduleDir = $dir;
		}
		return $this;
	}

	/**
	 * @return array
	 */
	public function render()
	{
		/* @var XMLObj $module */
		foreach($this->getModules() as $module)
		{
			if($module->getBoolean("enable"))
			{
				$title = ($module->getChildren("name")->getAttribute("nolangvar", "bool")) ? $module->getString("name") : Core::getLang()->get($module->getString("name"));
				$this->menu[] = array(
					"link"	=> Link::get("admin/".$module->getString("controller"), $title),
				);
			}
		}
		return $this->menu;
	}

	/**
	 * @return array
	 */
	public function getModules()
	{
		if(is_null($this->modules))
		{
			if(is_null($this->moduleDir))
			{
				$this->setModuleDir();
			}
			$this->modules = array();
			$dir = new DirectoryIterator($this->moduleDir);
			/* @var DirectoryIterator $file */
			foreach($dir as $file)
			{
				if(!$file->isDot() && !$file->isDir())
				{
					$module = new XMLObj(file_get_contents($file->getPathname()));
					$this->modules[] = $module->getChildren();
				}
			}
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