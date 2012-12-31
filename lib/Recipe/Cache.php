<?php
/**
 * Cache class. Initilize cache or return cache contents.
 *
 * @package Recipe 1.2
 * @author Sebastian Noll
 * @copyright Copyright (c) 2009, Sebastian Noll
 * @license Proprietary
 * @version $Id: Cache.php 30 2011-06-30 19:07:34Z secretchampion $
 */

class Recipe_Cache
{
	/**
	 * Default cache life time (24h).
	 *
	 * @var integer
	 */
	const DEFAULT_CACHE_LIFE_TIME = 86400;

	/**
	 * Where to find the cached files.
	 *
	 * @var string
	 */
	protected $cacheDir = "";

	/**
	 * Directory of language Cache.
	 *
	 * @var string
	 */
	protected $languageCacheDir = "";

	/**
	 * Directory of template Cache.
	 *
	 * @var string
	 */
	protected $templateCacheDir = "";

	/**
	 * Directory of session Cache.
	 *
	 * @var string
	 */
	protected $sessionCacheDir = "";

	/**
	 * Directory of permission Cache.
	 *
	 * @var string
	 */
	protected $permissionCacheDir = "";

	/**
	 * Holds all loaded cache resources.
	 *
	 * @param array
	 */
	protected $cacheStack = array();

	/**
	 * Text will be append after all cache files.
	 *
	 * @var string Closing text.
	 */
	protected $cacheFileClose = "";

	/**
	 * Constructor: Set basic variables.
	 *
	 * @return \Recipe_Cache
	 */
	public function __construct()
	{
		$this->cacheDir = APP_ROOT_DIR."var/cache/";
		$this->setLanguageCacheDir("language/");
		$this->setTemplateCacheDir("templates/");
		$this->setSessionCacheDir("sessions/");
		$this->setPermissionCacheDir("permissions/");
		$this->cacheFileClose = "\n\n// Cache-Generator finished\n?>";
		Hook::event("CacheConstruct", array($this));
		return;
	}

	/**
	 * Fetches language cache.
	 *
	 * @param string $langcode	Language code
	 * @param array $groups		Language groups to load
	 *
	 * @return string	Cache content
	 */
	public function getLanguageCache($langcode, $groups)
	{
		if(!is_array($groups))
		{
			$groups = Arr::trim(explode(",", $groups));
		}
		$langCache = array(); // Return array.
		$item = array(); // Array for cached variables.
		foreach($groups as $group)
		{
			$cacheFile = $this->getLanguageCacheDir()."lang.".$langcode.".".$group.".php";
			if(!$this->isLoaded($cacheFile))
			{
				if(!file_exists($cacheFile))
				{
					$this->cachePhraseGroup($group, $langcode);
				}
				$lifetime = 0;
				require($cacheFile);
				if($lifetime < TIME)
				{
					$this->cachePhraseGroup($group, $langcode);
				}
				$this->cacheStack[] = $cacheFile;
				if(isset($item[$group]) && is_array($item[$group]))
				{
					foreach($item[$group] as $key => $value)
					{
						$langCache[$key] = $value;
					}
				}
			}
		}
		return $langCache;
	}

	/**
	 * Fetches configuration cache.
	 *
	 * @return array
	 */
	public function getConfigCache()
	{
		$cacheFile = $this->cacheDir."options.cache.php";
		if(!file_exists($cacheFile))
		{
			$this->buildConfigCache();
		}
		$item = array();
		$lifetime = 0;
		require($cacheFile);
		if($lifetime < TIME)
		{
			$this->buildConfigCache();
		}
		return $item;
	}

	/**
	 * Fetches permission cache.
	 *
	 * @param integer $groupid	Group Id
	 *
	 * @return array
	 */
	public function getPermissionCache($groupid)
	{
		$cacheFile = $this->getPermissionCacheDir()."permission.".$groupid.".php";
		if(!file_exists($cacheFile))
		{
			$this->buildPermissionCache($groupid);
		}
		$item = array();
		if(!$this->isLoaded($cacheFile))
		{
			$this->cacheStack[] = $cacheFile;
			$lifetime = 0;
			require($cacheFile);
			if($lifetime < TIME)
			{
				$this->buildPermissionCache($groupid);
			}
		}
		return $item;
	}

	/**
	 * Fetches user variables for a session.
	 *
	 * @param string $sid	Session Id
	 *
	 * @return array	Session data
	 */
	public function getUserCache($sid)
	{
		$cacheFile = $this->getSessionCacheDir()."session.".$sid.".php";
		if(!file_exists($cacheFile))
		{
			return array();
		}
		$item = array();
		require($cacheFile);
		return $item;
	}

	/**
	 * Checks if resources is already loaded.
	 *
	 * @param string	File name
	 *
	 * @return boolean
	 */
	protected function isLoaded($file)
	{
		if(in_array($file, $this->cacheStack)) { return true; }
		return false;
	}

	/**
	 * Checks if resource file exists.
	 *
	 * @param string	Object name
	 *
	 * @return boolean
	 */
	public function objectExists($name)
	{
		$file = $this->cacheDir.$name.".cache.php";
		if(file_exists($file))
		{
			return true;
		}
		return false;
	}

	/**
	 * Caches the language variables.
	 *
	 * @param string $langcode	Language code
	 * @param integer $lifetime	Life time [optional]
	 *
	 * @return Recipe_Cache
	 */
	public function cacheLanguage($langcode, $lifetime = self::DEFAULT_CACHE_LIFE_TIME)
	{
		$select = array("title AS grouptitle", "phrasegroupid");
		$result = Core::getQuery()->select("phrasesgroups", $select);
		foreach($result->fetchAll() as $row)
		{
			$cacheContent  = $this->setCacheFileHeader("Language [".$langcode."] Cache File");
			$cacheContent .= "//### Variables for phrase group \"".$row["grouptitle"]."\" ###//\n";
			$cacheContent .= "\$lifetime=".(TIME+$lifetime).";";
			$where  = Core::getDB()->quoteInto("l.langcode = ? AND ", $langcode);
			$where .= Core::getDB()->quoteInto("p.phrasegroupid = ?", $row["phrasegroupid"]);
			$res = Core::getQuery()->select("phrases p", array("p.title AS phrasetitle", "p.content"), "LEFT JOIN ".PREFIX."languages l ON (l.languageid = p.languageid)", $where, "p.phrasegroupid ASC, p.title ASC");
			foreach($res->fetchAll() as $col)
			{
				$compiler = new Recipe_Language_Compiler($col["content"]);
				$cacheContent .= "\$item[\"".$row["grouptitle"]."\"][\"".$col["phrasetitle"]."\"]=\"".$compiler->getPhrase()."\";";
				$compiler->shutdown();
			}
			$res->closeCursor();
			$cacheContent .= $this->cacheFileClose;
			try { $this->putCacheContent($this->getLanguageCacheDir()."lang.".$langcode.".".$row["grouptitle"].".php", $cacheContent); }
			catch(Recipe_Exception_Generic $e) { $e->printError(); }
		}
		return $this;
	}

	/**
	 * Caches only a group of language phrases.
	 *
	 * @param string $groupname	Phrase group
	 * @param integer $langcode	Languageid
	 * @param integer $lifetime	Life time [optional]
	 *
	 * @return Recipe_Cache
	 */
	public function cachePhraseGroup($groupname, $langcode, $lifetime = self::DEFAULT_CACHE_LIFE_TIME)
	{
		if(!is_array($groupname))
		{
			$groupname = explode(",", $groupname);
			$groupname = Arr::trim($groupname);
		}
		foreach($groupname as $group)
		{
			$filename = $this->getLanguageCacheDir()."lang.".$langcode.".".$group.".php";
			try {
				File::rmFile($filename);
			}
			catch(Exception $e) {

			}
			$cacheContent  = $this->setCacheFileHeader("Language [".$langcode."] Cache File");
			$cacheContent .= "//### Variables for phrase group \"".$group."\" ###//\n";
			$cacheContent .= "\$lifetime=".(TIME+$lifetime).";";
			$joins  = "LEFT JOIN ".PREFIX."languages l ON (l.languageid = p.languageid) ";
			$joins .= "LEFT JOIN ".PREFIX."phrasesgroups pg ON (pg.phrasegroupid = p.phrasegroupid)";
			$where  = Core::getDB()->quoteInto("l.langcode = ?", $langcode);
			$where .= Core::getDB()->quoteInto(" AND pg.title = ?", $group);
			$result = Core::getQuery()->select("phrases p", array("p.title AS phrasetitle", "p.content"), $joins, $where, "p.phrasegroupid ASC, p.title ASC");
			$compiler = new Recipe_Language_Compiler("");
			foreach($result->fetchAll() as $row)
			{
				$compiler->setPhrase($row["content"]);
				$cacheContent .= "\$item[\"".$group."\"][\"".$row["phrasetitle"]."\"]=\"".$compiler->getPhrase()."\";";
			}
			$result->closeCursor();
			$compiler->shutdown();
			$cacheContent .= $this->cacheFileClose;
			try { $this->putCacheContent($filename, $cacheContent); }
			catch(Recipe_Exception_Generic $e) { $e->printError(); }
		}
		return $this;
	}

	/**
	 * Caches the configuration variables.
	 *
	 * @param integer $lifetime	Cache lifetime [optional]
	 * @return Recipe_Cache
	 */
	public function buildConfigCache($lifetime = self::DEFAULT_CACHE_LIFE_TIME)
	{
		$result = Core::getQuery()->select("config", array("var", "value", "type"));
		$cacheContent  = $this->setCacheFileHeader("Global Configuration Variables & Options");
		$cacheContent .= "\$lifetime=".(TIME+$lifetime).";\n";
		$cacheContent .= "\$item = array(";
		foreach($result->fetchAll() as $row)
		{
			switch($row["type"])
			{
				case "bool":
				case "boolean":
					$value = $row["value"] ? "true" : "false";
				break;
				case "int":
				case "integer":
					$value = (int) $row["value"];
				break;
				case "double":
				case "float":
					$value = (float) $row["value"];
				break;
				default:
					$value = "\"".$this->compileContent($row["value"])."\"";
				break;
			}
			$cacheContent .= "\"".$row["var"]."\"=>".$value.",";
		}
		$result->closeCursor();
		$cacheContent .= ");\n";
		$cacheContent .= $this->cacheFileClose;
		try { $this->putCacheContent($this->cacheDir."options.cache.php", $cacheContent); }
		catch(Recipe_Exception_Generic $e) { $e->printError(); }
		return $this;
	}

	/**
	 * Caches the permissions.
	 *
	 * @param array|int $groupid
	 * @param int $lifetime
	 * @return Recipe_Cache
	 */
	public function buildPermissionCache($groupid = null, $lifetime = self::DEFAULT_CACHE_LIFE_TIME)
	{
		if(is_array($groupid))
		{
			foreach($groupid as $group)
			{
				$cacheContent = "\$lifetime=".(TIME+$lifetime).";";
				$result = Core::getQuery()->select("group2permission g2p", array("g2p.value", "p.permission", "g.grouptitle"), "LEFT JOIN ".PREFIX."permissions p ON (p.permissionid = g2p.permissionid) LEFT JOIN ".PREFIX."usergroup g ON (g.usergroupid = g2p.groupid)", Core::getDB()->quoteInto("g2p.groupid = ?", $group));
				foreach($result->fetchAll() as $row)
				{
					$cacheContent .= "\$item[\"".$row["permission"]."\"]=".$row["value"].";";
					$grouptitle = $row["grouptitle"];
				}
				$result->closeCursor();
				$grouptitle = empty($grouptitle) ? $group : $grouptitle;
				$cacheContent = $this->setCacheFileHeader("Permissions [".$grouptitle."]").$cacheContent;
				$cacheContent .= $this->cacheFileClose;
				try { $this->putCacheContent($this->getPermissionCacheDir()."permission.".$group.".php", $cacheContent); }
				catch(Recipe_Exception_Generic $e) { $e->printError(); }
			}
			return $this;
		}
		if($groupid !== null)
		{
			$grouptitle = "";
			$cacheContent = "\$lifetime=".(TIME+$lifetime).";";
			$result = Core::getQuery()->select("group2permission g2p", array("g2p.value", "p.permission", "g.grouptitle"), "LEFT JOIN ".PREFIX."permissions p ON (p.permissionid = g2p.permissionid) LEFT JOIN ".PREFIX."usergroup g ON (g.usergroupid = g2p.groupid)", Core::getDB()->quoteInto("g2p.groupid = ?", $groupid));
			foreach($result->fetchAll() as $row)
			{
				$cacheContent .= "\$item[\"".$row["permission"]."\"]=".$row["value"].";";
				$grouptitle = $row["grouptitle"];
			}
			$result->closeCursor();
			$cacheContent = $this->setCacheFileHeader("Permissions [".$grouptitle."]").$cacheContent;
			$cacheContent .= $this->cacheFileClose;
			try { $this->putCacheContent($this->getPermissionCacheDir()."permission.".$groupid.".php", $cacheContent); }
			catch(Recipe_Exception_Generic $e) { $e->printError(); }
			return $this;
		}
		$result = Core::getQuery()->select("usergroup", array("usergroupid", "grouptitle"));
		foreach($result->fetchAll() as $row)
		{
			$cacheContent  = $this->setCacheFileHeader("Permissions [".$row["grouptitle"]."]");
			$cacheContent .= "\$lifetime=".(TIME+$lifetime).";";
			$_result = Core::getQuery()->select("group2permission g2p", array("g2p.value", "p.permission"), "LEFT JOIN ".PREFIX."permissions p ON (p.permissionid = g2p.permissionid)", Core::getDB()->quoteInto("g2p.groupid = ?", $row["usergroupid"]));
			foreach($_result->fetchAll() as $_row)
			{
				$cacheContent .= "\$item[\"".$_row["permission"]."\"]=".$_row["value"].";";
			}
			$_result->closeCursor();
			$cacheContent .= $this->cacheFileClose;
			try { $this->putCacheContent($this->getPermissionCacheDir()."permission.".$row["usergroupid"].".php", $cacheContent); }
			catch(Recipe_Exception_Generic $e) { $e->printError(); }
		}
		$result->closeCursor();
		return $this;
	}

	/**
	 * Caches a session.
	 *
	 * @param string $sid	Session Id
	 *
	 * @return Recipe_Cache
	 */
	public function buildUserCache($sid)
	{
		$select = array("u.*", "s.ipaddress");
		$joins  = "LEFT JOIN ".PREFIX."user u ON (u.userid = s.userid)";
		// Get custom user data from configuration
		if(Core::getConfig()->exists("userselect"))
		{
			$userConfigSelect = Core::getConfig()->get("userselect");
			$select = array_merge($select, $userConfigSelect["fieldsnames"]);
		}
		if(Core::getConfig()->exists("userjoins"))
		{
			$joins .= " ".str_replace("PREFIX", PREFIX, Core::getConfig()->get("userjoins"));
		}
		$result = Core::getQuery()->select("sessions s", $select, $joins, Core::getDB()->quoteInto("s.sessionid = ?", $sid), "", "1");
		$row = $result->fetchRow();
		$result->closeCursor();
		$cacheContent = $this->setCacheFileHeader("Session Cache [".$sid."]");
		$result = Core::getQuery()->showFields("user");
		foreach($result->fetchAll() as $t)
		{
			$fieldName = $t["Field"];
			$row[$fieldName] = (isset($row[$fieldName])) ? $this->compileContent($row[$fieldName]) : "";
			$cacheContent .= "\$item[\"".$fieldName."\"]=\"".$row[$fieldName]."\";";
		}
		$result->closeCursor();
		$cacheContent .= "\$item[\"ipaddress\"]=\"".$row["ipaddress"]."\";";
		if(Core::getConfig()->exists("userselect"))
		{
			foreach($userConfigSelect["indexnames"] as $index)
			{
				$cacheContent .= "\$item[\"".$index."\"]=\"".$row[$index]."\";";
			}
		}
		$cacheContent .= $this->cacheFileClose;
		$this->putCacheContent($this->getSessionCacheDir()."session.".$sid.".php", $cacheContent);
		return $this;
	}

	/**
	 * Generates header for cache file.
	 *
	 * @param string $title	Title or name for cache file
	 *
	 * @return string	Complete Header
	 */
	protected function setCacheFileHeader($title)
	{
		$header  = "<?php\n";
		$header .= "/**\n";
		$header .= " * @package Recipe PHP Framework\n";
		$header .= " * Auto-generated cache file for:\n";
		$header .= " * ".$title."\n";
		$header .= " * It is recommended to not modify anything here.\n";
		$header .= " */\n\n";
		return $header;
	}

	/**
	 * Write new cache content into file.
	 *
	 * @param string $file		Filename
	 * @param string $content	Content
	 *
	 * @throws Recipe_Exception_Generic
	 * @return Recipe_Cache
	 */
	protected function putCacheContent($file, $content)
	{
		$dir = dirname($file);
		if(!is_dir($dir))
		{
			mkdir($dir, 0777, true); // Try to create requested directory
		}
		if(!is_writable($dir))
		{
			throw new Recipe_Exception_Generic("Couldn't write cache file \"".$file."\". Make sure that cache directory is writable and accessible.");
		}
		file_put_contents($file, $content);
		try {
			@chmod($file, 0777);
		} catch(Exception $e) {

		}
		return $this;
	}

	/**
	 * Parse cache content concerning the quotes.
	 *
	 * @param string $content	Content to parse
	 *
	 * @return string	Parsed content
	 */
	protected function compileContent($content)
	{
		return Str::replace("\"", "\\\"", $content);
	}

	/**
	 * Return full path of a cached template.
	 *
	 * @param string $template	Template name
	 * @param string $type		Template type
	 *
	 * @return string	Path to template
	 */
	public function getTemplatePath($template, $type)
	{
		$template = Core::getTemplate()->getTemplatePath($template, $type);
		$dir = $this->getTemplateCacheDir();
		if(!is_dir($dir))
		{
			@mkdir($dir, 0777, true);
		}
		$template = str_replace("/", ".", $template);
		return $dir.$template.".cache.php";
	}

	/**
	 * Deletes old sessions in the cache.
	 *
	 * @param integer $userid	User Id
	 *
	 * @return Recipe_Cache
	 */
	public function cleanUserCache($userid)
	{
		$result = Core::getQuery()->select("sessions", "sessionid", "", Core::getDB()->quoteInto("userid = ?", $userid));
		foreach($result->fetchAll() as $row)
		{
			$cacheFile = $this->getSessionCacheDir()."session.".$row["sessionid"].".php";
			if(file_exists($cacheFile))
			{
				try { File::rmFile($cacheFile); }
				catch(Recipe_Exception_Generic $e) { $e->printError(); }
			}
		}
		$result->closeCursor();
		return $this;
	}

	/**
	 * Sets a new cache directory.
	 *
	 * @param string	Directory
	 *
	 * @return Recipe_Cache
	 */
	public function setCacheDir($dir)
	{
		$this->cacheDir = $dir;
		return $this;
	}

	/**
	 * Builds an unkown cache object.
	 *
	 * @param string $name		Object name
	 * @param Recipe_Database_Statement_Abstract $result	SQL-Statement
	 * @param string $index		Index name of the table
	 * @param string $itype		Index type (int or char)
	 *
	 * @return Recipe_Cache
	 */
	public function buildObject($name, $result, $index = null, $itype = "int")
	{
		$cacheContent = $this->setCacheFileHeader("Cache Object [".$name."]");
		$data = array();
		foreach($result->fetchAll() as $row)
		{
			if(is_null($index))
			{
				array_push($data, $row);
			}
			else
			{
				if($itype == "int" || $itype == "integer")
				{
					$data[(int) $row[$index]][] = $row;
				}
				else if($itype == "string" || $itype == "char")
				{
					$data[(string) $row[$index]][] = $row;
				}
			}
		}
		if(count($data) > 0)
		{
			$cacheContent .= "\$data = \"".$this->compileContent(serialize($data))."\";\n";
			$cacheContent .= $this->cacheFileClose;
			try { $this->putCacheContent($this->cacheDir.$name.".cache.php", $cacheContent); }
			catch(Recipe_Exception_Generic $e) { $e->printError(); }
		}
		return $this;
	}

	/**
	 * Reads a cache object.
	 *
	 * @param string $name Object name
	 *
	 * @throws Recipe_Exception_Generic
	 * @return array    Cache data
	 */
	public function readObject($name)
	{
		$file = $this->cacheDir.$name.".cache.php";
		if(!file_exists($file))
		{
			throw new Recipe_Exception_Generic("The requested cache object (\"".$file."\") does not exist. Check name or try to cache object first.");
		}
		array_push($this->cacheStack, $file);
		$data = "";
		require($file);
		$data = unserialize($data);
		return $data;
	}

	/**
	 * Deletes a cache object.
	 *
	 * @param string $name	Object name
	 *
	 * @return boolean	True on success, false on failure
	 */
	public function flushObject($name)
	{
		$file = $this->cacheDir.$name.".cache.php";
		if(!file_exists($file))
		{
			return false;
		}
		try { File::rmFile($file); }
		catch(Recipe_Exception_Generic $e) { $e->printError(); }
		return true;
	}

	/**
	 * Returns the language cache directory.
	 *
	 * @return string
	 */
	public function getLanguageCacheDir()
	{
		return $this->cacheDir.$this->languageCacheDir;
	}

	/**
	 * Returns the template cache directory.
	 *
	 * @return string
	 */
	public function getTemplateCacheDir()
	{
		return $this->cacheDir.$this->templateCacheDir;
	}

	/**
	 * Returns the session cache directory.
	 *
	 * @return string
	 */
	public function getSessionCacheDir()
	{
		return $this->cacheDir.$this->sessionCacheDir;
	}

	/**
	 * Returns the permission cache directory.
	 *
	 * @return string
	 */
	public function getPermissionCacheDir()
	{
		return $this->cacheDir.$this->permissionCacheDir;
	}

	/**
	 * Sets the $this->languageCacheDir.
	 *
	 * @param string $languageCacheDir
	 * @return Recipe_Cache
	 */
	public function setLanguageCacheDir($languageCacheDir)
	{
		$this->languageCacheDir = $languageCacheDir;
		return $this;
	}

	/**
	 * Sets the $this->templateCacheDir.
	 *
	 * @param string $templateCacheDir
	 * @return Recipe_Cache
	 */
	public function setTemplateCacheDir($templateCacheDir)
	{
		$this->templateCacheDir = $templateCacheDir;
		return $this;
	}

	/**
	 * Sets the $this->sessionCacheDir.
	 *
	 * @param string $sessionCacheDir
	 * @return Recipe_Cache
	 */
	public function setSessionCacheDir($sessionCacheDir)
	{
		$this->sessionCacheDir = $sessionCacheDir;
		return $this;
	}

	/**
	 * Sets the $this->permissionCacheDir.
	 *
	 * @param string $permissionCacheDir
	 * @return Recipe_Cache
	 */
	public function setPermissionCacheDir($permissionCacheDir)
	{
		$this->permissionCacheDir = $permissionCacheDir;
		return $this;
	}

	/**
	 * Build meta data cache.
	 *
	 * @param int $lifetime
	 * @return \Recipe_Cache
	 */
	public function buildMetaCache($lifetime = self::DEFAULT_CACHE_LIFE_TIME)
	{
		$data = array();
		$applicationDirectory = APP_ROOT_DIR."app/bootstrap";
		$moduleDirectory = APP_ROOT_DIR."etc/modules";
		/* @var DirectoryIterator $fileObj */
		foreach(new DirectoryIterator($applicationDirectory) as $fileObj)
		{
			if(!$fileObj->isDot() && $fileObj->isDir())
			{
				$metaFile = $fileObj->getPathname()."/meta.json";
				if(file_exists($metaFile))
				{
					$data = array_merge_recursive($data, json_decode(file_get_contents($metaFile), true));
				}
			}
		}
		try {
			foreach(new DirectoryIterator($moduleDirectory) as $fileObj)
			{
				if(!$fileObj->isDot() && $fileObj->isFile())
				{
					$data = array_merge_recursive($data, json_decode(file_get_contents($fileObj->getPathname()), true));
				}
			}
		} catch(UnexpectedValueException $e) {

		}

		$data = array_merge_recursive($data, json_decode(file_get_contents(APP_ROOT_DIR.'etc/local.json'), true));

		$cacheContent  = $this->setCacheFileHeader("Meta data");
		$cacheContent .= "\$lifetime=".(TIME+$lifetime).";\n";
		$cacheContent .= "\$data = \"".$this->compileContent(serialize($data))."\";\n";
		$cacheContent .= $this->cacheFileClose;
		$this->putCacheContent($this->cacheDir."meta.cache.php", $cacheContent);
		return $this;
	}

	/**
	 * Fetches meta cache.
	 *
	 * @return array
	 */
	public function getMetaCache()
	{
		$cacheFile = $this->cacheDir."meta.cache.php";
		if(!file_exists($cacheFile))
		{
			$this->buildMetaCache();
		}
		$data = "";
		$lifetime = 0;
		require $cacheFile;
		if($lifetime < TIME)
		{
			$this->buildMetaCache();
		}
		$data = unserialize($data);
		return $data;
	}
}
?>