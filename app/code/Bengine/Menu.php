<?php
/**
 * Menu class: Generates the menu by XML.
 *
 * @package Bengine
 * @copyright Copyright protected by / Urheberrechtlich geschützt durch "Sebastian Noll" <snoll@4ym.org>
 * @version $Id: Menu.php 8 2010-10-17 20:55:04Z secretchampion $
 */

class Bengine_Menu implements IteratorAggregate
{
	/**
	 * The main XML menu element.
	 *
	 * @var SimpleXMLElement
	 */
	protected $xml = "";

	/**
	 * The menu config file.
	 *
	 * @var string
	 */
	protected $menuFile = "";

	/**
	 * Holds the menu items.
	 *
	 * @var array
	 */
	protected $menu = null;

	/**
	 * Constructor.
	 *
	 * @param string	Menu config file
	 *
	 * @return void
	 */
	public function __construct($file)
	{
		$this->setMenuFile($file);
		return;
	}

	/**
	 * Loads the XML file.
	 *
	 * @return Bengine_Menu
	 */
	protected function loadXML()
	{
		$this->xml = new XML($this->menuFile);
		$this->xml = $this->xml->get();
		return $this;
	}

	/**
	 * Generates the menu items.
	 *
	 * @return Bengine_Menu
	 */
	protected function generateMenu()
	{
		$this->menu = array();
		foreach($this->xml as $first)
		{
			$this->menu[] = array(
				"inner" => $this->getClass($first),
				"content" => $this->getLabel($first),
				"direct" => ""
			);
			foreach($first->getChildren() as $second)
			{
				$this->menu[] = array(
					"inner" => $this->getClass($second),
					"content" => $this->getLink($second),
					"direct" => $this->getDirectLink($second)
				);
			}
		}
		return $this;
	}

	/**
	 * Generates the label of a XML element.
	 *
	 * @param SimpleXMLElement
	 *
	 * @return string
	 */
	protected function getLabel(XMLObj $xml)
	{
		$label = strval($xml->getAttribute("label"));
		$noLangVar = (bool) $xml->getAttribute("nolangvar");
		if($noLangVar)
		{
			return $label;
		}
		return ($label != "") ? Core::getLang()->get($label) : "";
	}

	/**
	 * Returns the direct link of a XML element
	 *
	 * @param SimpleXMLElement
	 *
	 * @return string
	 */
	protected function getDirectLink(XMLObj $xml)
	{
		$link = strval($xml->getAttribute("href"));
		$external = (bool) $xml->getAttribute("external");
		if(!$external)
		{
			return BASE_URL."game.php/".SID."/".$link;
		}
		return $link;
	}

	/**
	 * Returns the CSS class generated from XML element.
	 *
	 * @param SimpleXMLElement
	 *
	 * @return string
	 */
	protected function getClass(XMLObj $xml)
	{
		$class = strval($xml->getAttribute("class"));
		return ($class != "") ? " class=\"$class\"" : "";
	}

	/**
	 * Generates the from XML element.
	 *
	 * @param SimpleXMLElement
	 *
	 * @return string
	 */
	protected function getName(XMLObj $xml)
	{
		$name = $xml->getString();
		$func = strval($xml->getAttribute("func"));
		if($func != "")
		{
			return $this->func($func, $name);
		}
		$noLangVar = (bool) $xml->getAttribute("nolangvar");
		if($noLangVar)
		{
			return $name;
		}
		return ($name != "") ? Core::getLang()->get($name) : "";
	}

	/**
	 * Generates a link from XML element.
	 *
	 * @param SimpleXMLElement
	 *
	 * @return string
	 */
	protected function getLink(XMLObj $xml)
	{
		$name = $this->getName($xml);
		$title = $this->getLabel($xml);
		$attachment = strval($xml->getAttribute("target"));
		$attachment = ($attachment != "") ? "target=\"".$attachment."\"" : "";
		$refdir = (bool) $xml->getAttribute("refdir");
		$external = (bool) $xml->getAttribute("external");
		$link = strval($xml->getAttribute("href"));
		if(!$external)
		{
			$link = "game.php/".SID."/".$link;
		}
		return Link::get($link, $name, $title, "", $attachment, false, false, $refdir);
	}

	/**
	 * Returns the menu items.
	 *
	 * @return array
	 */
	public function getMenu()
	{
		if(is_null($this->menu))
		{
			$this->loadXML()->generateMenu();
		}
		return $this->menu;
	}

	/**
	 * Sets the menu XML file.
	 *
	 * @param string	File name
	 *
	 * @return Bengine_Menu
	 */
	public function setMenuFile($file)
	{
		$this->menuFile = APP_ROOT_DIR."etc/".$file.".xml";
		return $this;
	}

	/**
	 * Parses the function attrbiute.
	 *
	 * @param string	Function name
	 * @param string	Parameter
	 *
	 * @return string
	 */
	protected function func($function, $param = null)
	{
		$function = strtolower($function);
		switch($function)
		{
			case "version":
				return "v".BENGINE_VERSION;
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
	 * Destructor.
	 *
	 * @return void
	 */
	public function kill()
	{
		unset($this);
		return;
	}
}
?>