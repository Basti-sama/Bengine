<?php
/**
 * Handles XML data.
 *
 * @package Recipe 1.2
 * @author Sebastian Noll
 * @copyright Copyright (c) 2009, Sebastian Noll
 * @license Proprietary
 * @version $Id: XML.util.php 19 2011-05-27 10:30:33Z secretchampion $
 */

class XML
{
	/**
	 * Contains the raw xml data.
	 *
	 * @var string
	 */
	protected $data = "";

	/**
	 * Holds the parsed xml data.
	 *
	 * @var array
	 */
	protected $parsedData = array ();

	/**
	 * The XML object.
	 *
	 * @var XMLObj
	 */
	protected $xmlObj = null;

	/**
	 * Creates a new xml object and sets the raw document data.
	 *
	 * @param string	File name or xml data
	 *
	 * @return void
	 */
	public function __construct($data)
	{
		$this->setData($data);
		return;
	}

	/**
	 * Parse the xml data and generate an xml object.
	 *
	 * @return XML
	 */
	protected function initParser()
	{
		$this->xmlObj = new XMLObj($this->data);
		if ($this->xmlObj === false)
		{
			throw new Recipe_Exception_Generic("The supplied data is not a valid XML file.");
		}
		Hook::event("XmlParserObject", array($this));
		return $this;
	}

	/**
	 * Returns an array with all elements of an SimpleXML Object.
	 *
	 * @param string			Tree name
	 * @param SimpleXMLElement	XML-Object
	 *
	 * @return array			XML data as array
	 */
	protected function getElementTree($name, XMLObj $xmlObj)
	{
		$element = array (
			"name" => $name
		);

		$element["attrs"] = $this->getAttributes($xmlObj);
		$element["cdata"] = $this->getCDATA($xmlObj);
		$element["children"] = $this->getChildren($xmlObj, true);

		return $element;
	}

	/**
	 * Returns the CDATA of an XML element.
	 *
	 * @param SimpleXMLElement	XML-Object
	 *
	 * @return string			The CDATA
	 */
	protected function getCDATA(XMLObj $xmlObj)
	{
		if(trim((string) $xmlObj) != "")
		{
			return (string) $xmlObj;
		}
		return "";
	}

	/**
	 * Returns an array of sub elements.
	 *
	 * @param SimpleXMLElement	XML-Object
	 * @param boolean
	 *
	 * @return array			Children array
	 */
	protected function getChildren(XMLObj $xmlObj, $tree = false)
	{
		$childrenArray = array ();
		$children = $xmlObj->children();
		foreach($children as $key => $childObj)
		{
			if($tree)
			{
				$childrenArray[] = $this->getElementTree($key, $childObj);
			}
			else
			{
				$childrenArray[] = $childObj;
			}
		}
		return $childrenArray;
	}

	/**
	 * Returns an associative array with attributes of an XML element.
	 *
	 * @param SimpleXMLElement	XML-Object
	 *
	 * @return array			Attributes
	 */
	protected function getAttributes(XMLObj $xmlObj)
	{
		$attributesArray = array ();
		$attributes = $xmlObj->attributes();
		foreach($attributes as $key => $val)
		{
			$attributesArray[$key] = (string) $val;
		}

		return $attributesArray;
	}

	/**
	 * Sets a new data to parse.
	 *
	 * @param string	File name or xml data
	 *
	 * @return void
	 */
	public function setData($data)
	{
		if(file_exists($data))
		{
			$data = file_get_contents($data);
		}
		$this->data = $data;
		Hook::event("XmlParser", array($this));
		$this->initParser();
		return $this->initParser();
	}

	/**
	 * Returns the parsed xml data.
	 *
	 * @param string	Tree name
	 *
	 * @return array
	 */
	public function getData($name = null)
	{
		if(count($this->parsedData) <= 0 && !is_null($name))
		{
			$this->parsedData = $this->getElementTree($name, $this->xmlObj);
		}
		return $this->parsedData;
	}

	/**
	 * Returns the XML as an object.
	 *
	 * @return XMLObj
	 */
	public function get()
	{
		if($this->xmlObj instanceof XMLObj)
		{
			return $this->xmlObj;
		}
		return false;
	}

	/**
	 * Returns a well-formed XML string based on SimpleXML element.
	 *
	 * @return string
	 */
	public function toXML()
	{
		if($this->xmlObj instanceof XMLObj)
		{
			return $this->xmlObj->asXML();
		}
		return false;
	}

	/**
	 * Destructor.
	 *
	 * @return void
	 */
	public function kill()
	{
		unset($this);
	}
}
?>