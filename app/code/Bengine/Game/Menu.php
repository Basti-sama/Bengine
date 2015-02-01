<?php
/**
 * Menu class: Generates the menu by XML.
 *
 * @package Bengine
 * @copyright Copyright protected by / Urheberrechtlich geschützt durch "Sebastian Noll" <snoll@4ym.org>
 * @version $Id: Menu.php 8 2010-10-17 20:55:04Z secretchampion $
 */

class Bengine_Game_Menu implements IteratorAggregate
{
	/**
	 * Holds the menu items.
	 *
	 * @var array
	 */
	protected $menu = array();

	/**
	 * @var array
	 */
	protected $data = array();

	/**
	 * Constructor.
	 *
	 * @return \Bengine_Game_Menu
	 */
	public function __construct()
	{
		$meta = Game::getMeta();
		$this->setData($meta["config"]["menu"]);
		$this->generateMenu();
	}

	/**
	 * Generates the menu items.
	 *
	 * @return Bengine_Game_Menu
	 */
	protected function generateMenu()
	{
		$this->menu = array();
		uasort($this->data, array($this, "sort"));
		foreach($this->data as $first)
		{
			uasort($first["items"], array($this, "sort"));
			$subMenu = array();
			foreach($first["items"] as $second)
			{
				$subMenu[] = array(
					"attributes" => isset($second["attributes"]) ? $this->getHtmlAttributesFromArray($second["attributes"]) : "",
					"link" => $this->getLink($second)
				);
			}
			$this->menu[] = array(
				"attributes" => isset($first["attributes"]) ? $this->getHtmlAttributesFromArray($first["attributes"]) : "",
				"link" => isset($first["label"]) ? $this->getLabel($first["label"]) : "",
				"children" => $subMenu
			);
		}
		Hook::event("GenerateMenu", array(&$this->menu, $this));
		return $this;
	}

	/**
	 * @param array $x
	 * @param array $y
	 * @return bool
	 */
	protected function sort(array $x, array $y)
	{
		if(!isset($x["sort"]))
		{
			return -1;
		}
		if(!isset($y["sort"]))
		{
			return 1;
		}
		return $x["sort"] < $y["sort"] ? -1 : 1;
	}

	/**
	 * Generates the label of a menu item.
	 *
	 * @param array|string $labelConfig
	 * @return string
	 */
	protected function getLabel($labelConfig)
	{
		if(is_array($labelConfig))
		{
			if(isset($labelConfig["special"]))
			{
				$params = isset($labelConfig["special"]["params"]) ? $labelConfig["special"]["params"] : null;
				$label = $this->func($labelConfig["special"]["function"], $params);
			}
			else
			{
				$label = isset($labelConfig["value"]) ? $labelConfig["value"] : "";
				if(!empty($item["label"]["noLangVar"]))
				{
					$label = Core::getLang()->get($label);
				}
			}
		}
		else
		{
			$label = Core::getLang()->get($labelConfig);
		}
		return $label;
	}

	/**
	 * Generates a link from XML element.
	 *
	 * @param array $item
	 * @throws Recipe_Exception_Generic
	 * @return string
	 */
	protected function getLink(array $item)
	{
		$title = "";
		$class = "";
		$attachments = "";
		if(isset($item["link"]["attributes"]))
		{
			if(isset($item["link"]["attributes"]["title"]))
			{
				$title = $item["link"]["attributes"]["title"];
				unset($item["link"]["attributes"]["title"]);
			}
			if(isset($item["link"]["attributes"]["class"]))
			{
				$class = $item["link"]["attributes"]["class"];
				unset($item["link"]["attributes"]["class"]);
			}
			$attachments = $this->getHtmlAttributesFromArray($item["link"]["attributes"]);
		}
		$name = $this->getLabel($item["label"]);
		$refdir = isset($item["link"]["external"]) ? $item["link"]["external"] : false;
		if(!is_array($item["link"]))
		{
			throw new Recipe_Exception_Generic("Menu link must be an array.");
		}
		if(isset($item["link"]["href"]))
		{
			$link = $item["link"]["href"];
		}
		else
		{
			$package = isset($item["link"]["package"]) ? $item["link"]["package"] : "game";
			$controller = $item["link"]["controller"];
			$action = isset($item["link"]["action"]) ? "/".$item["link"]["action"] : "";
			$link = $package."/".SID."/".$controller.$action;
		}
		return Link::get($link, $name, $title, $class, $attachments, false, false, $refdir);
	}

	/**
	 * Returns the menu items.
	 *
	 * @return array
	 */
	public function getMenu()
	{
		if($this->menu === null)
		{
			$this->generateMenu();
		}
		return $this->menu;
	}

	/**
	 * @param string $function
	 * @param string $param
	 *
	 * @return string
	 */
	protected function func($function, $param = null)
	{
		$function = strtolower($function);
		switch($function)
		{
			case "version":
				return "v".Game::getVersion();
			break;
			case "const":
				return constant($param);
			break;
			case "config":
				return Core::getConfig()->get($param);
			break;
			case "user":
				return Core::getUser()->get($param);
			break;
			case "cookie":
				return Core::getRequest()->getCOOKIE($param);
			break;
			case "image":
				return Image::getImage($param, "");
			break;
			case "callback":
				return call_user_func_array($param["function"], $param["arguments"]);
			break;
		}
		return "";
	}

	/**
	 * Retrieves an external iterator.
	 *
	 * @return ArrayIterator
	 */
	public function getIterator()
	{
		return new ArrayIterator($this->getMenu());
	}

	/**
	 * @param array $data
	 */
	public function setData(array $data)
	{
		$this->data = $data;
	}

	/**
	 * @return array
	 */
	public function getData()
	{
		return $this->data;
	}

	/**
	 * @param array $assoc
	 * @return string
	 */
	protected function getHtmlAttributesFromArray(array $assoc)
	{
		$attributes = array();
		foreach($assoc as $attribute => $value)
		{
			$attributes[] = "$attribute=\"$value\"";
		}
		return " ".implode(" ", $attributes);
	}
}
?>