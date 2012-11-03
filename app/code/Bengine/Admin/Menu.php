<?php
class Bengine_Admin_Menu
{
	protected $menu = array();
	protected $moduleDir = "";
	protected $modules = null;

	public function __construct($dir = null)
	{
		$this->setModuleDir($dir);
		return $this;
	}

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

	public function render()
	{
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