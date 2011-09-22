<?php
/**
 * Sets global variables.
 *
 * @package Recipe 1.2
 * @author Sebastian Noll
 * @copyright Copyright (c) 2009, Sebastian Noll
 * @license Proprietary
 * @version $Id: Options.php 19 2011-05-27 10:30:33Z secretchampion $
 */

class Recipe_Options extends Recipe_Collection
{
 	/**
 	 * Enables cache function.
 	 *
 	 * @var boolean
 	 */
	protected $cacheActive = true;

 	/**
 	 * Indicates if all variables were already cached.
 	 *
	 * @var boolean
	 */
	protected $uncached = true;

	/**
	 * Configuration file.
	 *
	 * @var string
	 */
	protected $configFile = "Config.xml";

	/**
	 * Constructor.
	 *
	 * @return void
	 */
	public function __construct()
	{
		if($this->cacheActive) { $this->cacheActive = CACHE_ACTIVE; }
		$this->loadConfigFile()->setItems();
		return;
	}

	/**
	 * Sets options.
	 *
	 * @return Recipe_Options
	 */
	protected function setItems()
	{
		if($this->cacheActive)
		{
			$this->item = array_merge($this->item, Core::getCache()->getConfigCache());
		}
		else
		{
			$result = Core::getQuery()->select("config", array("var", "value", "type"));
			while($row = Core::getDatabase()->fetch($result))
			{
				$this->item[$row["var"]] = $this->parseItemByType($row["value"], $row["type"]);
			}
			Core::getDatabase()->free_result($result);
		}
		Hook::event("SetOptionVariables", array($this));
		return $this;
	}

	/**
	 * Changes a configuration parameter.
	 *
	 * @param string	Variable name
	 * @param string	New value of the variable
	 * @param boolean	Turn off auto-caching
	 *
	 * @return Recipe_Options
	 */
	public function setValue($var, $value, $renewcache = false)
	{
		Hook::event("SetOptionVariable", array($this, &$var, &$value));
		if($this->hasVariable($var, false))
		{
			Core::getQuery()->update("config", "value", $value, "var = '".$var."'");
		}
		else
		{
			$att = array("var", "value", "type", "groupid", "islisted");
			$val = array($var, $value, "char", "1", "1");
			Core::getQuery()->insert("config", $att, $val);
		}
		if($this->cacheActive && $renewcache) { Core::getCache()->buildConfigCache(); }
		return $this;
	}

	/**
	 * Checks if variable exists.
	 *
	 * @param string	Variable name
	 * @param boolean	Turn off auto-caching
	 *
	 * @return boolean	True, if variable exists, flase, if not
	 */
	protected function hasVariable($var, $renewcache = true)
	{
		if($this->exists($var)) { return true; }
		if($this->cacheActive && $renewcache && $this->uncached)
		{
			Core::getCache()->buildConfigCache();
			$this->item = array_merge($this->item, Core::getCache()->getConfigCache());
			$this->uncached = false;
			return $this->hasVariable($var, false);
		}
		return false;
	}

	/**
	 * Returns a configuration parameter.
	 *
	 * @param string	Variable name
	 *
	 * @return mixed	Parameter content
	 */
	public function get($var)
	{
		Hook::event("GetOptionVariable", array($this, &$var));
		if($this->hasVariable($var)) { return $this->item[$var]; }
		return $var;
	}

	/**
	 * Changes a configuration parameter.
	 *
	 * @param string	Variable name
	 * @param string	New value of the variable
	 *
	 * @return Options
	 */
	public function set($var, $value)
	{
		return $this->setValue($var, $value);
	}

	/**
	 * Gets the data from the config file.
	 * The loaded configuration can be overwritten by the database
	 * entries.
	 *
	 * @return Recipe_Options
	 */
	protected function loadConfigFile()
	{
		$file = RECIPE_ROOT_DIR.$this->configFile;
		if(file_exists($file))
		{
			$xml = new XML($file);
			$config = $xml->get()->getChildren();
			$xml->kill();
			$this->item = $this->getFromXML($config);
		}
		return $this;
	}

	/**
	 * Parses XML data.
	 *
	 * @param SimpleXMLElement	XML to parse
	 *
	 * @return array			parsed XML
	 */
	protected function getFromXML(SimpleXMLElement $xml)
	{
		$item = array();
		foreach($xml as $index => $child)
		{
			$item[$index] = $this->parseItemByType($child, $child->getAttribute("type"));
		}
		return $item;
	}

	/**
	 * Parses an item by the given type.
	 *
	 * @param XMLObj	Item
	 * @param string	Type
	 *
	 * @return mixed	Parsed item
	 */
	protected function parseItemByType(XMLObj $item, $type = null)
	{
		switch($type)
		{
			case "level":
				return $this->getFromXML($item);
			break;
			case "array":
				return $item->getArray();
			break;
			case "integer":
			case "int":
				return $item->getInteger();
			break;
			case "string":
			case "char":
			case "text":
			default:
				return $item->getString();
			break;
			case "bool":
			case "boolean":
				return $item->getBooleanObj();
				$item = (bool) $item;
			break;
			case "dbquery":
				return Str::replace("PREFIX", PREFIX, $item->getString());
			break;
			case "map":
				return $item->getMap();
			break;
		}
		return $item;
	}
}
?>