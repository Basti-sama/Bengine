<?php
/**
 * Abstract application starter.
 *
 * @package Recipe 1.2
 * @author Sebastian Noll
 * @copyright Copyright (c) 2009, Sebastian Noll
 * @license Proprietary
 * @version $Id: Application.php 8 2010-10-17 20:55:04Z secretchampion $
 */

abstract class Application
{
	/**
	 * Contains all name spaces.
	 *
	 * @var array
	 */
	protected static $namespace = array();

	/**
	 * Meta application configuration.
	 *
	 * @var array
	 */
	protected static $meta = null;

	/**
	 * Controller name.
	 *
	 * @var string
	 */
	protected static $controllerName = "";

	/**
	 * Controller object.
	 *
	 * @var Recipe_Controller_Abstract
	 */
	protected static $controller = null;

	/**
	 * Constructor.
	 */
	public function __construct(Core $core)
	{
		Core::getRequest()->putArgsIntoRequestVars();
	}

	/**
	 * Class rewrites.
	 *
	 * @var array
	 */
	protected static $rewrite = array();

	/**
	 * @var array
	 */
	protected static $rewriteCache = array();

	/**
	 * Starts the application.
	 *
	 * @return void
	 */
	public function run()
	{
		Hook::event("PreRun");
		self::loadMeta();

		Core::getTPL()->assign("charset", CHARACTER_SET);
		Core::getTPL()->assign("langcode", Core::getLang()->getOpt("langcode"));
		Core::getTPL()->assign("formaction", Core::getRequest()->getRequestedUrl());

		$pageTitle = array();
		if($titlePrefix = Core::getConfig()->get("TITLE_PREFIX"))
		{
			$pageTitle[] = $titlePrefix;
		}
		$pageTitle[] = Core::getConfig()->get("pagetitle");
		if($titleSuffix = Core::getConfig()->get("TITLE_SUFFIX"))
		{
			$pageTitle[] = $titleSuffix;
		}
		Core::getTPL()->assign("pageTitle", implode(Core::getConfig()->get("TITLE_GLUE"), $pageTitle));
		Hook::event("PostRun");
		return;
	}

	/**
	 * Run dispatch process.
	 *
	 * @return mixed
	 */
	protected function dispatch()
	{
		Hook::event("PreDispatch");
		$controllerName = Core::getRequest()->getGET("controller", "index");
		$package = Core::getRequest()->getGET("package", DEFAULT_PACKAGE);
		$overridePackage = null;
		if(strpos($controllerName, "."))
		{
			list($overridePackage, $controllerName) = explode(".", $controllerName);
		}
		self::$controllerName = $controllerName;
		$config = array("action" => Core::getRequest()->getGET("action", "index"));
		$class = ($overridePackage !== null ? $overridePackage : $package)."/controller_".lcfirst($controllerName);
		self::$controller = self::factory($class, $config);
		if(!self::$controller)
		{
			self::$controller = self::factory($package."/controller_index", array("action" => "noroute"));
		}
		Hook::event("PostDispatch");
		return self::$controller->run();
	}

	/**
	 * Internal class factory to search for extensions.
	 *
	 * @param string $class	Class path
	 * @param array $args
	 *
	 * @return object|boolean	Object of the class
	 */
	public static function factory($class, $args = array())
	{
		if(isset(self::$rewriteCache[$class]))
		{
			$class = self::$rewriteCache[$class];
			return new $class($args);
		}

		$classKey = $class;
		$class = self::rewrite($class);
		$class = getClassPath($class, "/", "_");
		if(!class_exists($class, false))
		{
			foreach(self::$namespace as $namespace)
			{
				$_class = $namespace."_".$class;
				if(class_exists($_class))
				{
					self::$rewriteCache[$classKey] = $_class;
					return new $_class($args);
				}
			}
		}
		else
		{
			self::$rewriteCache[$classKey] = $class;
			return new $class($args);
		}
		return false;
	}

	/**
	 * Loads a model and instantiate it.
	 *
	 * @param string $model	Model name
	 * @param mixed $data	Initial loaded data
	 *
	 * @return Recipe_Model_Abstract
	 *
	 * @throws Recipe_Exception_Generic
	 */
	public static function getModel($model, $data = null)
	{
		list($package, $modelName)  = explode("/", $model);
		if($modelObj = self::factory("$package/model_$modelName", $data))
		{
			return $modelObj;
		}
		throw new Recipe_Exception_Generic("Unable to load model '".$model."'.");
	}

	/**
	 * Loads a resource model and instantiate it.
	 *
	 * @param string $resource	Resource name
	 * @param mixed $args		Arguments
	 *
	 * @return Recipe_Model_Resource_Abstract
	 *
	 * @throws Recipe_Exception_Generic
	 */
	public static function getResource($resource, $args = null)
	{
		list($package, $resourceName)  = explode("/", $resource);
		if($resourceObj = self::factory("$package/model_resource_$resourceName", $args))
		{
			return $resourceObj;
		}
		throw new Recipe_Exception_Generic("Unable to load resource model '".$resource."'.");
	}

	/**
	 * Retrieves a collection of models.
	 *
	 * @param string $collection	Collection name
	 * @param string $model			Model name [optional]
	 *
	 * @return Recipe_Model_Collection_Abstract
	 *
	 * @throws Recipe_Exception_Generic
	 */
	public static function getCollection($collection, $model = null)
	{
		if($model === null)
		{
			$model = $collection;
		}
		list($package, $collectionName)  = explode("/", $collection);
		if($collectionObj = self::factory("$package/model_collection_$collectionName", $model))
		{
			return $collectionObj;
		}
		throw new Recipe_Exception_Generic("Unable to load model collection '".$collection."'.");
	}

	/**
	 * Loads the meta configuration.
	 *
	 * @return void
	 */
	public static function loadMeta()
	{
		self::$meta = Core::getCache()->getMetaCache();
		foreach(self::$meta["packages"] as $namespace => $vendor)
		{
			self::addNamespace($namespace);
		}
		if(!empty(self::$meta["rewrite"]) && is_array(self::$meta["rewrite"]))
		{
			self::$rewrite = self::$meta["rewrite"];
		}
	}

	/**
	 * Retrieves the meta configuration.
	 *
	 * @return array
	 */
	public static final function getMeta()
	{
		return self::$meta;
	}

	/**
	 * Fetches class rewrite rules.
	 *
	 * @param string $class	Class path
	 *
	 * @return string	Rewrite class, given class otherwise
	 */
	protected static function rewrite($class)
	{
		if(!empty(self::$rewrite[$class]))
		{
			return self::$rewrite[$class];
		}
		return $class;
	}

	/**
	 * Adds a new namespace.
	 *
	 * @param string
	 */
	public static function addNamespace($namespace)
	{
		self::$namespace[] = $namespace;
	}
}