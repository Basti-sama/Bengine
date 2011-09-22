<?php
/**
 * Include required files.
 *
 * @package Recipe 1.2
 * @author Sebastian Noll
 * @copyright Copyright (c) 2009, Sebastian Noll
 * @license Proprietary
 * @version $Id: AutoLoader.php 50 2011-08-13 09:29:24Z secretchampion $
 */

class AutoLoader
{
	/**
	 * Required files to include.
	 *
	 * @var array
	 */
	protected $requiredFiles = array(
		"Functions.php",
	);

	protected $pluginPath = "";

	/**
	 * Initializes the autoloader.
	 *
	 * @return void
	 */
	public function __construct()
	{
		spl_autoload_register(array($this, "autoload"));
		$this->pluginPath = APP_ROOT_DIR."ext/plugins/";
		$this->initIncludePath();
		$filesToInclude = $this->getFilesToLoad();
		$this->loadFiles($filesToInclude);
	}

	/**
	 * Callback for class autoloading.
	 *
	 * @param string $class
	 * @return AutoLoader
	 */
	public function autoload($class)
	{
		$class = str_replace("\\", "_", $class);
		$class = getClassPath($class);

		$classFile = array();
		$classFile[] = $class.".php";
		$classFile[] = "Util/".$class.".util.php";

		if($file = file_exists_inc($classFile))
		{
			require_once($file);
		}
		return $this;
	}

	/**
	 * Loads given files.
	 *
	 * @param array $includingFiles
	 */
	protected function loadFiles(array $includingFiles)
	{
		foreach($includingFiles as $inc)
		{
			if(!file_exists($inc))
				die("Unable to load required file: <b>".$inc."</b>");
			include_once($inc);
		}
		return $this;
	}

	/**
	 * Returns all files to include.
	 *
	 * @return array
	 */
	protected function getFilesToLoad()
	{
		$includingFiles = array(
			RD."Functions.php",
		);
		$plugins = $this->getPluginFiles();
		if(!empty($plugins))
			$includingFiles = array_merge($includingFiles, $plugins);
		return $includingFiles;
	}

	/**
	 * Loads the plugin files.
	 *
	 * @return array
	 */
	protected function getPluginFiles()
	{
		$pluginPath = $this->pluginPath;
		$plugins = array();
		$dirIter = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($pluginPath), RecursiveIteratorIterator::CHILD_FIRST);
		/*@var SplFileInfo $fileObj*/
		foreach($dirIter as $fileObj)
		{
			if(!$fileObj->isDir() && preg_match("#\.plugin\.php$#", $fileObj->getFilename()))
			{
				$plugins[] = $fileObj->getPathname();
			}
		}
		return $plugins;
	}

	/**
	 * Sets the include path.
	 *
	 * @return AutoLoader
	 */
	protected function initIncludePath()
	{
		$includePath = array();
		$autoloadPath = explode(",", AUTOLOAD_PATH);
		foreach($autoloadPath as $namespace)
		{
			$includePath[] = AD.$namespace."/";
		}
		set_include_path(implode(PATH_SEPARATOR, $includePath));
		return $this;
	}
}
?>