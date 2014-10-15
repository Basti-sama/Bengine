<?php
/**
 * Extended functionality of the SimpleXMLElement.
 *
 * @package Recipe 1.2
 * @author Sebastian Noll
 * @copyright Copyright (c) 2009, Sebastian Noll
 * @license Proprietary
 * @version $Id: XMLObj.util.php 8 2010-10-17 20:55:04Z secretchampion $
 */

class XMLObj extends SimpleXMLElement implements ArrayAccess
{
	/**
	 * Returns the children of a XML element.
	 *
	 * @param string $name	The children's name [optional]
	 *
	 * @return XMLObj	The children
	 */
	public function getChildren($name = null)
	{
		if(null === $name)
		{
			return $this->children();
		}
		return $this->$name;
	}

	/**
	 * Returns the attributes of a XML element.
	 *
	 * @param string $name	Attribute name [optional]
	 * @param string $type
	 *
	 * @return string	Attribute content
	 */
	public function getAttribute($name = null, $type = null)
	{
		if(null === $name)
		{
			return $this->attributes();
		}
		$attribute = $this->attributes()->$name;
		switch($type)
		{
			default: case "string": case "text": case "char": case "str":
				return (string) $attribute;
			break;
			case "int": case "integer":
				return (int) $attribute;
			break;
			case "bool": case "boolean":
				return (bool) ($attribute);
			break;
			case "float": case "double":
				return floatval($attribute);
			break;
		}
	}

	/**
	 * Returns the data of a XML element.
	 *
	 * @param string $name	Element name [optional]
	 * @param string $type	Data type [optional]
	 *
	 * @return mixed	Data
	 */
	public function getData($name = null, $type = null)
	{
		if(null !== $type)
		{
			$type = strtolower($type);
			switch($type)
			{
				default: case "string": case "text": case "char": case "str":
					return $this->getString($name);
				break;
				case "int": case "integer":
					return $this->getInteger($name);
				break;
				case "bool": case "boolean":
					return $this->getBoolean($name);
				break;
				case "float": case "double":
					return $this->getFloat($name);
				break;
				case "array": case "arr":
					return $this->getArray($name);
				break;
				case "map":
					return $this->getMap($name);
				break;
			}
		}
		return $this->getString($name);
	}

	/**
	 * Returns the data of the XML element as a string.
	 *
	 * @param string $name	Element name [optional]
	 *
	 * @return string
	 */
	public function getString($name = null)
	{
		if(null === $name)
		{
			return (string) $this;
		}
		return (string) $this->$name;
	}

	/**
	 * Returns the data of the XML element as a string.
	 *
	 * @param string $name	Element name [optional]
	 *
	 * @return string
	 */
	public function getStringObj($name = null)
	{
		if(null === $name)
		{
			return new String($this);
		}
		return new String($this->$name);
	}

	/**
	 * Returns the data of the XML element as an integer value.
	 *
	 * @param string $name	Element name [optional]
	 *
	 * @return integer
	 */
	public function getInteger($name = null)
	{
		if(null === $name)
		{
			return (int) $this;
		}
		return (int) $this->$name;
	}

	/**
	 * Returns the data of the XML element as an integer value.
	 *
	 * @param string $name	Element name [optional]
	 *
	 * @return integer
	 */
	public function getIntegerObj($name = null)
	{
		if(null === $name)
		{
			return new Integer($this);
		}
		return new Integer($this->$name);
	}

	/**
	 * Returns the data of the XML element as a boolean value.
	 *
	 * @param string $name	Element name [optional]
	 *
	 * @return boolean
	 */
	public function getBoolean($name = null)
	{
		if(null === $name)
		{
			return (bool) $this;
		}
		return (bool) $this->$name;
	}

	/**
	 * Returns the data of the XML element as a boolean value.
	 *
	 * @param string $name	Element name [optional]
	 *
	 * @return boolean
	 */
	public function getBooleanObj($name = null)
	{
		if(null === $name)
		{
			return new Boolean($this);
		}
		return new Boolean($this->$name);
	}

	/**
	 * Returns the data of the XML element as a float value.
	 *
	 * @param string $name	Element name [optional]
	 *
	 * @return float
	 */
	public function getFloat($name = null)
	{
		if(null === $name)
		{
			return floatval($this);
		}
		return floatval($this->$name);
	}

	/**
	 * Returns the data of the XML element as a float object.
	 *
	 * @param string $name	Element name [optional]
	 *
	 * @return Float
	 */
	public function getFloatObj($name = null)
	{
		if(null === $name)
		{
			return new Float($this);
		}
		return new Float($this->$name);
	}

	/**
	 * Returns the data of the XML element as an array.
	 *
	 * @param string $name	Element name [optional]
	 *
	 * @return array
	 */
	public function getArray($name = null)
	{
		return Arr::trim(explode(",", $this->getString($name)));
	}

	/**
	 * Returns the data of the XML element as a map.
	 *
	 * @param string $name	Element name [optional]
	 *
	 * @return Map
	 */
	public function getMap($name = null)
	{
		$map = new Map($this->getArray($name));
		return $map->trim();
	}

	/**
	 * Assigns a value to the specified offset.
	 *
	 * @param string $offset	The offset to assign the value to
	 * @param mixed $value		The value to set
	 *
	 * @return XMLObj
	 */
	public function offsetSet($offset, $value)
	{
		$this->$offset = $value;
		return $this;
	}

	/**
	 * Whether or not an offset exists.
	 *
	 * @param string $offset	An offset to check for
	 *
	 * @return boolean
	 */
	public function offsetExists($offset)
	{
		return ($this->getString($offset) != "");
	}

	/**
	 * Unsets an offset.
	 *
	 * @param string $offset	The offset to unset
	 *
	 * @return XMLObj
	 */
	public function offsetUnset($offset)
	{
		unset($this->$offset);
		return $this;
	}

	/**
	 * Returns the value at specified offset.
	 *
	 * @param string $offset	The offset to retrieve
	 *
	 * @return string	Offset value
	 */
	public function offsetGet($offset)
	{
		return $this->getString($offset);
	}
}
?>