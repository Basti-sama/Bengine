<?php
/**
 * Program core. Gathers required program parts.
 *
 * @package Recipe 1.2
 * @author Sebastian Noll
 * @copyright Copyright (c) 2009, Sebastian Noll
 * @license Proprietary
 * @version $Id: Core.php 41 2011-07-24 11:01:31Z secretchampion $
 */

class Core
{
	/**
	 * Database object.
	 *
	 * @var Recipe_Database_Abstract
	 */
	protected static $DatabaseObj = null;

	/**
	 * Options object.
	 *
	 * @var Recipe_Options
	 */
	protected static $OptionsObj = null;

	/**
	 * Language object.
	 *
	 * @var Recipe_Language
	 */
	protected static $LanguageObj = null;

	/**
	 * Template object.
	 *
	 * @var Recipe_Template_Adapter_Abstract
	 */
	protected static $TemplateObj = null;

	/**
	 * Request object.
	 *
	 * @var Recipe_Request
	 */
	protected static $RequestObj = null;

	/**
	 * User object.
	 *
	 * @var Recipe_User
	 */
	protected static $UserObj = null;

	/**
	 * Query parser object.
	 *
	 * @var Recipe_QueryParser
	 */
	protected static $QueryObj = null;

	/**
	 * Cron object.
	 *
	 * @var Recipe_Cron
	 */
	protected static $CronObj = null;

	/**
	 * Cache object.
	 *
	 * @var Recipe_Cache
	 */
	protected static $CacheObj = null;

	/**
	 * Recipe version id.
	 *
	 * @var Array
	 */
	protected static $version = array();

	/**
	 * The time zone that will be set, if no other can be found.
	 *
	 * @var string
	 */
	protected static $defaultTimeZone = "Europe/London";

	/**
	 * The current timezone.
	 *
	 * @var string
	 */
	protected static $timezone = null;

	/**
	 * Instance of core.
	 *
	 * @var Core
	 */
	protected static $instance = null;

	/**
	 * Application name.
	 *
	 * @var Application
	 */
	protected static $application = null;

	/**
	 * Constructor. Initializes classes.
	 *
	 * @return \Core
	 */
	public function __construct()
	{
		self::$version["major"] = 1;
		self::$version["build"] = 3;
		self::$version["revesion"] = 0;
		self::$version["release"] = 130;
		define("RECIPE_VERSION", self::$version["release"]);
		Hook::event("CoreStart");
		self::$instance = $this;
		$this->setDatabase()
			->setRequest()
			->setApplication()
			->setQuery()
			->setCache()
			->setOptions()
			->setTemplate()
			->setUser()
			->setLanguage()
			->initCron();
		Hook::event("CoreFinished");
		return;
	}

	/**
	 * Initializes the request class.
	 *
	 * @return Core
	 */
	protected function setRequest()
	{
		self::$RequestObj = new Recipe_Request(REQUEST_ADAPTER);
		return $this;
	}

	/**
	 * @return Core
	 */
	protected function setApplication()
	{
		$arguments = $this->getRequest()->getRawArguments();
		if(empty($arguments[0]))
		{
			$application = DEFAULT_PACKAGE;
		}
		else
		{
			$application = $arguments[0];
		}
		$application = strtolower($application);
		if(!file_exists(APP_ROOT_DIR."app/bootstrap/".$application."/index.php"))
		{
			$application = DEFAULT_PACKAGE;
		}
		$_GET["package"] = $application;
		self::$application = require_once APP_ROOT_DIR."app/bootstrap/".$application."/index.php";
		return $this;
	}

	/**
	 * @return Application
	 */
	public static final function getApplication()
	{
		return self::$application;
	}

	/**
	 * Returns the request object.
	 *
	 * @return Recipe_Request
	 */
	public static final function getRequest()
	{
		return self::$RequestObj;
	}

	/**
	 * Initializes the database.
	 *
	 * @return Core
	 */
	protected function setDatabase()
	{
		$database = array();
		$database["port"] = null;
		require(APP_ROOT_DIR."etc/Config.inc.php");
		if(!defined("PREFIX")) define("PREFIX", $database['tableprefix']);
		$dbType = "Recipe_Database_".$database["type"];
		self::$DatabaseObj = new $dbType($database["host"], $database["user"], $database["userpw"], $database["databasename"], $database["port"]);
		return $this;
	}

	/**
	 * Returns the database object.
	 *
	 * @return Recipe_Database_Abstract
	 */
	public static final function getDatabase()
	{
		return self::$DatabaseObj;
	}

	/**
	 * Shorter Version of getDatabase().
	 *
	 * @return Recipe_Database_Abstract
	 */
	public static final function getDB()
	{
		return self::getDatabase();
	}

	/**
	 * Initializes the query object.
	 *
	 * @return Core
	 */
	protected function setQuery()
	{
		self::$QueryObj = new Recipe_QueryParser();
		return $this;
	}

	/**
	 * Returns the query object.
	 *
	 * @return Recipe_QueryParser
	 */
	public static final function getQuery()
	{
		return self::$QueryObj;
	}

	/**
	 * Initializes the options.
	 *
	 * @return Core
	 */
	protected function setOptions()
	{
		self::$OptionsObj = new Recipe_Options();
		return $this;
	}

	/**
	 * Returns the options object.
	 *
	 * @return Recipe_Options
	 */
	public static final function getOptions()
	{
		return self::$OptionsObj;
	}

	/**
	 * Alias to getOptions().
	 *
	 * @return Recipe_Options
	 */
	public static final function getConfig()
	{
		return self::getOptions();
	}

	/**
	 * Initializes the language system.
	 *
	 * @return Core
	 */
	protected function setLanguage()
	{
		$langid = null;
		if(self::getUser()->get("languageid"))
		{
			$langid = self::getUser()->get("languageid");
		}
		else if(self::getRequest()->getCOOKIE("lang"))
		{
			$langid = self::getRequest()->getCOOKIE("lang");
		}
		else if(self::getRequest()->getGET("lang"))
		{
			$langid = self::getRequest()->getGET("lang");
		}
		self::$LanguageObj = new Recipe_Language($langid, self::getOptions()->standardlanggroups);
		return $this;
	}

	/**
	 * Returns the language object.
	 *
	 * @return Recipe_Language
	 */
	public static final function getLanguage()
	{
		return self::$LanguageObj;
	}

	/**
	 * Short form of getLanguage().
	 *
	 * @return Recipe_Language
	 */
	public static final function getLang()
	{
		return self::getLanguage();
	}

	/**
	 * Initializes the template system.
	 *
	 * @param string $engine
	 * @return Core
	 */
	public function setTemplate($engine = null)
	{
		if(is_null($engine))
		{
			$engine = ucfirst(self::getConfig()->get("template_engine"));
		}
		$engine = "Recipe_Template_Adapter_".$engine;
		self::$TemplateObj = new $engine();
		return $this;
	}

	/**
	 * Returns the template object.
	 *
	 * @return Recipe_Template_Adapter_Abstract
	 */
	public static final function getTemplate()
	{
		return self::$TemplateObj;
	}

	/**
	 * Short form of getTemplate().
	 *
	 * @return Recipe_Template_Adapter_Abstract
	 */
	public static final function getTPL()
	{
		return self::getTemplate();
	}

	/**
	 * Initializes the user class.
	 *
	 * @return Core
	 */
	protected function setUser()
	{
		if(URL_SESSION)
		{
			$sid = (self::getRequest()->getGET("sid")) ? self::getRequest()->getGET("sid") : "";
		}
		else
		{
			$sid = (self::getRequest()->getCOOKIE("sid")) ? self::getRequest()->getCOOKIE("sid") : "";
		}
		self::$UserObj = new Recipe_User($sid);
		return $this;
	}

	/**
	 * Returns the user object.
	 *
	 * @return Recipe_User
	 */
	public static final function getUser()
	{
		return self::$UserObj;
	}

	/**
	 * Initializes the Cronjob class.
	 *
	 * @return Core
	 */
	protected function initCron()
	{
		self::$CronObj = new Recipe_Cron();
		return $this;
	}

	/**
	 * Returns the Cron object.
	 *
	 * @return Recipe_Cron
	 */
	public static final function getCron()
	{
		return self::$CronObj;
	}

	/**
	 * Initializes the Cache.
	 *
	 * @return Core
	 */
	protected function setCache()
	{
		self::$CacheObj = new Recipe_Cache();
		return $this;
	}

	/**
	 * Returns the Cache object.
	 *
	 * @return Recipe_Cache
	 */
	public static final function getCache()
	{
		return self::$CacheObj;
	}

	/**
	 * Returns the version as a string.
	 *
	 * @return string Recipe version.
	 */
	public static function versionToString()
	{
		return self::$version["major"].".".self::$version["build"]."rev".self::$version["revesion"];
	}

	/**
	 * Returns an instance of core.
	 *
	 * @return Core
	 */
	public static function getInstance()
	{
		if(is_null(self::$instance))
		{
			$core = __CLASS__;
			new $core();
		}
		return self::$instance;
	}

	/**
	 * Runs the application.
	 *
	 * @return Core
	 */
	public function run()
	{
		$this->getApplication()->run();
		return $this;
	}

	/**
	 * Sets the default timezone.
	 *
	 * @param string $newTimezone	Timezone to set
	 *
	 * @return string	Timezone
	 */
	public static function setDefaultTimeZone($newTimezone = null)
	{
		if(!is_null(self::$timezone))
		{
			return date_default_timezone_get();
		}

		self::$timezone = $newTimezone;
		if(self::getOptions()->exists("timezone"))
		{
			self::$timezone = self::getOptions()->timezone;
		}
		else
		{
			self::$timezone = self::$defaultTimeZone;
		}
		date_default_timezone_set(self::$timezone);
		return date_default_timezone_get();
	}
}
?>