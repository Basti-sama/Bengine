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
	 * Local application configuration.
	 *
	 * @var XMLObj
	 */
	protected static $local = null;

	/**
	 * Class rewrites.
	 *
	 * @var array
	 */
	protected static $rewrite = array();

	protected static $rewriteCache = array();

	/**
	 * Starts the application.
	 *
	 * @return void
	 */
	public static function run()
	{
		new Core();
		self::loadLocal();
		self::addNamespace(self::getLocal()->getChildren("namespace"));
		self::addRewrites(self::getLocal()->getChildren("rewrite"));

		Core::getTPL()->assign("charset", Core::getLang()->getOpt("charset"));
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

		return;
	}

	/**
	 * Internal class factory to search for extensions.
	 *
	 * @param string	Class path
	 *
	 * @return instance	Object of the class
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
	 * @param string	Model name
	 * @param mixed		Initial loaded data
	 *
	 * @return Recipe_Model_Abstract
	 *
	 * @throws Recipe_Exception_Generic
	 */
	public static function getModel($model, $data = null)
	{
		if($modelObj = self::factory("model/".$model, $data))
		{
			return $modelObj;
		}
		throw new Recipe_Exception_Generic("Unable to load model '".$model."'.");
	}

	/**
	 * Loads a resource model and instantiate it.
	 *
	 * @param string	Resource name
	 * @param mixed		Arguments
	 *
	 * @return Recipe_Model_Resource_Abstract
	 *
	 * @throws Recipe_Exception_Generic
	 */
	public static function getResource($resource, $args = null)
	{
		if($resourceObj = self::factory("model/resource_".$resource, $args))
		{
			return $resourceObj;
		}
		throw new Recipe_Exception_Generic("Unable to load resource model '".$resource."'.");
	}

	/**
	 * Retrieves a collection of models.
	 *
	 * @param string	Collection name
	 * @param string	Model name [optional]
	 *
	 * @return Recipe_Model_Collection_Abstract
	 *
	 * @throws Recipe_Exception_Generic
	 */
	public static function getCollection($collection, $model = null)
	{
		if(is_null($model))
		{
			$model = $collection;
		}
		if($collectionObj = self::factory("model/collection_".$collection, $model))
		{
			return $collectionObj;
		}
		throw new Recipe_Exception_Generic("Unable to load model collection '".$collection."'.");
	}

	/**
	 * Loads the local configuration.
	 *
	 * @return void
	 */
	protected static function loadLocal()
	{
		self::$local = new XMLObj(file_get_contents(AD."etc/Local.xml"));
		return;
	}

	/**
	 * Retrieves the local configuration.
	 *
	 * @return XMLObj
	 */
	public static final function getLocal()
	{
		return self::$local->getChildren();
	}

	/**
	 * Fetches class rewrite rules.
	 *
	 * @param string	Class path
	 *
	 * @return string	Rewrite class, given class otherwise
	 */
	protected static function rewrite($class)
	{
		$split = explode("/", $class);
		if(count($split) >= 2)
		{
			$split[0] = strtolower($split[0]);
			$split[1] = strtolower($split[1]);
			if(isset(self::$rewrite[$split[0]][$split[1]]))
			{
				return self::$rewrite[$split[0]][$split[1]];
			}
		}
		return $class;
	}

	/**
	 * Adds a new namespace.
	 *
	 * @param string|XMLObj
	 *
	 * @return array	Namespace stack
	 */
	public static function addNamespace($namespace = null)
	{
		if(!is_null($namespace))
		{
			if($namespace instanceof XMLObj)
			{
				foreach($namespace->getChildren() as $ns)
				{
					array_unshift(self::$namespace, $ns->getName());
				}
			}
			else
			{
				array_unshift(self::$namespace, $namespace);
			}
		}
		return self::$namespace;
	}

	/**
	 * Adds a new rewrite class.
	 *
	 * @param string|XMLObj
	 *
	 * @return array	rewrite stack
	 */
	public static function addRewrites($rewrite)
	{
		if(!is_null($rewrite))
		{
			if($rewrite instanceof XMLObj)
			{
				foreach($rewrite->getChildren() as $resource)
				{
					foreach($resource->getChildren() as $class)
					{
						self::$rewrite[$resource->getName()][$class->getName()] = $class->getString();
					}
				}
			}
			else
			{
				self::$rewrite[] = $rewrite;
			}
		}
		return self::$rewrite;
	}
}