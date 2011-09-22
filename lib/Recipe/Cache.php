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
	 * @return void
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
	 * @param string	Language code
	 * @param array		Language groups to load
	 *
	 * @return string	Cache content
	 */
	public function getLanguageCache($langcode, $groups)
	{
		if(!is_array($groups))
		{
			$groups = Arr::trimArray(explode(",", $groups));
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
	 * @param integer	Group Id
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
	 * @param string	Session Id
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
	 * @param string	Language code
	 * @param integer	Life time [optional]
	 *
	 * @return Recipe_Cache
	 */
	public function cacheLanguage($langcode, $lifetime = self::DEFAULT_CACHE_LIFE_TIME)
	{
		$select = array("title AS grouptitle", "phrasegroupid");
		$result = Core::getQuery()->select("phrasesgroups", $select, "", "");
		while($row = Core::getDB()->fetch($result))
		{
			$cacheContent  = $this->setCacheFileHeader("Language [".$langcode."] Cache File");
			$cacheContent .= "//### Variables for phrase group \"".$row["grouptitle"]."\" ###//\n";
			$cacheContent .= "\$lifetime=".(TIME+$lifetime).";";
			$res = Core::getQuery()->select("phrases p", array("p.title AS phrasetitle", "p.content"), "LEFT JOIN ".PREFIX."languages l ON (l.languageid = p.languageid)", "l.langcode = '".$langcode."' AND p.phrasegroupid = '".$row["phrasegroupid"]."'", "p.phrasegroupid ASC, p.title ASC");
			while($col = Core::getDB()->fetch($res))
			{
				$compiler = new Recipe_Language_Compiler($col["content"]);
				$cacheContent .= "\$item[\"".$row["grouptitle"]."\"][\"".$col["phrasetitle"]."\"]=\"".$compiler->getPhrase()."\";";
				$compiler->shutdown();
			}
			Core::getDB()->free_result($res);
			$cacheContent .= $this->cacheFileClose;
			try { $this->putCacheContent($this->getLanguageCacheDir()."lang.".$langcode.".".$row["grouptitle"].".php", $cacheContent); }
			catch(Exception $e) { $e->printError(); }
		}
		Core::getDB()->free_result($result);
		return $this;
	}

	/**
	 * Caches only a group of language phrases.
	 *
	 * @param string	Phrase group
	 * @param integer	Languageid
	 * @param integer	Life time [optional]
	 *
	 * @return Recipe_Cache
	 */
	public function cachePhraseGroup($groupname, $langcode, $lifetime = self::DEFAULT_CACHE_LIFE_TIME)
	{
		if(!is_array($groupname))
		{
			$groupname = explode(",", $groupname);
			$groupname = Arr::trimArray($groupname);
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
			$result = Core::getQuery()->select("phrases p", array("p.title AS phrasetitle", "p.content"), $joins, "l.langcode = '".$langcode."' AND pg.title = '".$group."'", "p.phrasegroupid ASC, p.title ASC");
			$compiler = new Recipe_Language_Compiler("");
			while($row = Core::getDB()->fetch($result))
			{
				$compiler->setPhrase($row["content"]);
				$cacheContent .= "\$item[\"".$group."\"][\"".$row["phrasetitle"]."\"]=\"".$compiler->getPhrase()."\";";
			}
			Core::getDB()->free_result($result);
			$compiler->shutdown();
			$cacheContent .= $this->cacheFileClose;
			try { $this->putCacheContent($filename, $cacheContent); }
			catch(Exception $e) { $e->printError(); }
		}
		return $this;
	}

	/**
	 * Caches the configuration variables.
	 *
	 * @param integer	Cache lifetime [optional]
	 * @return Recipe_Cache
	 */
	public function buildConfigCache($lifetime = self::DEFAULT_CACHE_LIFE_TIME)
	{
		$result = Core::getQuery()->select("config", array("var", "value"));
		$cacheContent  = $this->setCacheFileHeader("Global Configuration Variables & Options");
		$cacheContent .= "\$lifetime=".(TIME+$lifetime).";\n";
		$cacheContent .= "\$item = array(";
		while($row = Core::getDatabase()->fetch($result))
		{
			$row["value"] = $this->compileContent($row["value"]);
			$cacheContent .= "\"".$row["var"]."\"=>\"".$row["value"]."\",";
		}
		Core::getDB()->free_result($result);
		$cacheContent .= ");\n";
		$cacheContent .= $this->cacheFileClose;
		try { $this->putCacheContent($this->cacheDir."options.cache.php", $cacheContent); }
		catch(Exception $e) { $e->printError(); }
		return $this;
	}

	/**
	 * Caches the permissions.
	 *
	 * @return Recipe_Cache
	 */
	public function buildPermissionCache($groupid = null, $lifetime = self::DEFAULT_CACHE_LIFE_TIME)
	{
		if(is_array($groupid))
		{
			foreach($groupid as $group)
			{
				$cacheContent = "\$lifetime=".(TIME+$lifetime).";";
				$result = Core::getQuery()->select("group2permission g2p", array("g2p.value", "p.permission", "g.grouptitle"), "LEFT JOIN ".PREFIX."permissions p ON (p.permissionid = g2p.permissionid) LEFT JOIN ".PREFIX."usergroup g ON (g.usergroupid = g2p.groupid)", "g2p.groupid = '".$group."'");
				while($row = Core::getDB()->fetch($result))
				{
					$cacheContent .= "\$item[\"".$row["permission"]."\"]=".$row["value"].";";
					$grouptitle = $row["grouptitle"];
				}
				Core::getDB()->free_result($result);
				$grouptitle = ($grouptitle == "") ? $group : $grouptitle;
				$cacheContent = $this->setCacheFileHeader("Permissions [".$grouptitle."]").$cacheContent;
				$cacheContent .= $this->cacheFileClose;
				try { $this->putCacheContent($this->getPermissionCacheDir()."permission.".$group.".php", $cacheContent); }
				catch(Exception $e) { $e->printError(); }
			}
			return $this;
		}
		if($groupid !== null)
		{
			$grouptitle = "";
			$cacheContent = "\$lifetime=".(TIME+$lifetime).";";
			$result = Core::getQuery()->select("group2permission g2p", array("g2p.value", "p.permission", "g.grouptitle"), "LEFT JOIN ".PREFIX."permissions p ON (p.permissionid = g2p.permissionid) LEFT JOIN ".PREFIX."usergroup g ON (g.usergroupid = g2p.groupid)", "g2p.groupid = '".$groupid."'");
			while($row = Core::getDB()->fetch($result))
			{
				$cacheContent .= "\$item[\"".$row["permission"]."\"]=".$row["value"].";";
				$grouptitle = $row["grouptitle"];
			}
			Core::getDB()->free_result($result);
			$cacheContent = $this->setCacheFileHeader("Permissions [".$grouptitle."]").$cacheContent;
			$cacheContent .= $this->cacheFileClose;
			try { $this->putCacheContent($this->getPermissionCacheDir()."permission.".$groupid.".php", $cacheContent); }
			catch(Exception $e) { $e->printError(); }
			return $this;
		}
		$result = Core::getQuery()->select("usergroup", array("usergroupid", "grouptitle"));
		while($row = Core::getDB()->fetch($result))
		{
			$cacheContent  = $this->setCacheFileHeader("Permissions [".$row["grouptitle"]."]");
			$cacheContent .= "\$lifetime=".(TIME+$lifetime).";";
			$_result = Core::getQuery()->select("group2permission g2p", array("g2p.value", "p.permission"), "LEFT JOIN ".PREFIX."permissions p ON (p.permissionid = g2p.permissionid)", "g2p.groupid = '".$row["usergroupid"]."'");
			while($_row = Core::getDB()->fetch($_result))
			{
				$cacheContent .= "\$item[\"".$_row["permission"]."\"]=".$_row["value"].";";
			}
			Core::getDB()->free_result($_result);
			$cacheContent .= $this->cacheFileClose;
			try { $this->putCacheContent($this->getPermissionCacheDir()."permission.".$row["usergroupid"].".php", $cacheContent); }
			catch(Exception $e) { $e->printError(); }
		}
		Core::getDB()->free_result($result);
		return $this;
	}

	/**
	 * Caches a session.
	 *
	 * @param string	Session Id
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
			$joins .= " ".Core::getConfig()->get("userjoins");
		}
		$result = Core::getQuery()->select("sessions s", $select, $joins, "s.sessionid = '".$sid."'", "", "1");
		$row = Core::getDB()->fetch($result);
		$cacheContent = $this->setCacheFileHeader("Session Cache [".$sid."]");
		$result = Core::getQuery()->showFields("user");
		while($t = Core::getDB()->fetch($result))
		{
			$row[$t["Field"]] = (isset($row[$t["Field"]])) ? $this->compileContent($row[$t["Field"]]) : "";
			$cacheContent .= "\$item[\"".$t["Field"]."\"]=\"".$row[$t["Field"]]."\";";
		}
		Core::getDB()->free_result($result);
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
	 * @param string	Title or name for cache file
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
	 * @param string	Filename
	 * @param string	Content
	 *
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
		chmod($file, 0777);
		return $this;
	}

	/**
	 * Parse cache content concerning the quotes.
	 *
	 * @param string	Content to parse
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
	 * @param string	Template name
	 *
	 * @return string	Path to template
	 */
	public function getTemplatePath($template)
	{
		$dir = $this->getTemplateCacheDir().Core::getTemplate()->getTemplatePackage();
		if(!is_dir($dir))
		{
			@mkdir($dir, 0777, true);
		}
		return $dir.$template.".cache.php";
	}

	/**
	 * Deletes old sessions in the cache.
	 *
	 * @param integer	User Id
	 *
	 * @return Recipe_Cache
	 */
	public function cleanUserCache($userid)
	{
		$result = Core::getQuery()->select("sessions", "sessionid", "", "userid = '".$userid."'");
		while($row = Core::getDB()->fetch($result))
		{
			$cacheFile = $this->getSessionCacheDir()."session.".$row["sessionid"].".php";
			if(file_exists($cacheFile))
			{
				try { File::rmFile($cacheFile); }
				catch(Exception $e) { $e->printError(); }
			}
		}
		Core::getDB()->free_result($result);
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
	 * @param string	Object name
	 * @param resource	SQL-Query
	 * @param string	Index name of the table
	 * @param string	Index type (int or char)
	 *
	 * @return Recipe_Cache
	 */
	public function buildObject($name, $result, $index = null, $itype = "int")
	{
		$cacheContent = $this->setCacheFileHeader("Cache Object [".$name."]");
		$data = array();
		while($row = Core::getDB()->fetch($result))
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
			catch(Exception $e) { $e->printError(); }
		}
		return $this;
	}

	/**
	 * Reads a cache object.
	 *
	 * @param string	Object name
	 *
	 * @return array	Cache data
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
	 * @param string	Object name
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
		catch(Exception $e) { $e->printError(); }
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
}
?>