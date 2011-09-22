<?php
/**
 * Main object class.
 *
 * @package Recipe 1.2
 * @author Sebastian Noll
 * @copyright Copyright (c) 2009, Sebastian Noll
 * @license Proprietary
 * @version $Id: Object.php 8 2010-10-17 20:55:04Z secretchampion $
 */

class Object implements ArrayAccess, IteratorAggregate
{
	/**
	 * Holds the data of this object.
	 *
	 * @var array
	 */
	protected $_data = array();

	/**
	 * Creates a new object.
	 *
	 * @param array|string|integer	Initial data or id to load [optional]
	 *
	 * @return Object
	 */
	public function __construct($data = null)
	{
		$this->init();
		if(is_array($data) || $data instanceof Map)
		{
			$this->set($data);
		}
		else if(is_integer($data) || is_string($data))
		{
			$this->load($data);
		}
		return $this;
	}

	/**
	 * Initializes the object.
	 *
	 * @return Object
	 */
	protected function init()
	{
		return $this;
	}

	/**
	 * Loading object data.
	 *
	 * @param string|integer	Id to load
	 *
	 * @return Object
	 */
	public function load($id)
	{
		return $this;
	}

	/**
	 * Sets data.
	 *
	 * @param string|array	Data key or set of data
	 * @param mixed		Value [optional]
	 *
	 * @return Object
	 */
	public function set($var, $value = null)
	{
		if(is_array($var))
		{
			$this->_data = array_merge($this->_data, $var);
			return $this;
		}
		else if($var instanceof Map)
		{
			$this->_data = array_merge($this->_data, $var->getArray());
			return $this;
		}
		$this->_data[$var] = $value;
		return $this;
	}

	/**
	 * Resets the data.
	 *
	 * @return Object
	 */
	public function resetData()
	{
		$this->_data = array();
		return $this;
	}

	/**
	 * Returns data.
	 *
	 * @param string	Data key [optional]
	 * @param mixed		Default return value [optional]
	 *
	 * @return mixed	Value of the given key or set of all data pairs
	 */
	public function get($var = null, $default = null)
	{
		if(is_null($var))
		{
			return $this->_data;
		}
		return ($this->exists($var)) ? $this->_data[$var] : $default;
	}

	/**
	 * Checks a data key for existence.
	 *
	 * @param string	Data key
	 *
	 * @return boolean
	 */
	public function exists($var)
	{
		return (isset($this->_data[$var])) ? true : false;
	}

	/**
	 * Removes a data value.
	 *
	 * @param string	Data key
	 *
	 * @return Object
	 */
	public function remove($var)
	{
		if($this->exists($var))
		{
			unset($this->_data[$var]);
		}
		return $this;
	}

	/**
	 *
	 *
	 * @param string	Called function
	 * @param mixed		Function arguments [optional]
	 *
	 * @return mixed|Object
	 */
	public function __call($func, $args = array())
	{
		$name = $this->_funcToParam(substr($func, 3));
		switch(substr($func, 0, 3))
		{
			case "get":
				return $this->get($name, (isset($args[0])) ? $args[0] : null);
			break;
			case "set":
				return $this->set($name, (isset($args[0])) ? $args[0] : null);
			break;
			case "del":
				return $this->remove($name);
			break;
			case "has":
				return $this->exists($name);
			break;
			default:
				throw new Exception("Call to undefined method ".$this->toString()."::".$func.".");
			break;
		}
		return $this;
	}

	/**
	 * Formats a function name to a parameter.
	 * (someVariable >> some_variable)
	 *
	 * @param string	Function name
	 *
	 * @return string
	 */
	protected function _funcToParam($name)
	{
		return strtolower(preg_replace("/(.)([A-Z])/", "$1_$2", $name));
	}

	/**
	 * Setter for unkown member variables.
	 *
	 * @param string	Data key
	 * @param mixed		Value
	 *
	 * @return Object
	 */
	public function __set($var, $value)
	{
		return $this->set($var, $value);
	}

	/**
	 * Getter for unkown member variables.
	 *
	 * @param string	Data key
	 *
	 * @return mixed
	 */
	public function __get($var)
	{
		return $this->get($var);
	}

	/**
	 * Isset for unkown member variables.
	 *
	 * @param string	Data key
	 *
	 * @return boolean
	 */
	public function __isset($var)
	{
		return $this->exists($var);
	}

	/**
	 * Unset for unkown member variables.
	 *
	 * @param string	Data key
	 *
	 * @return Object
	 */
	public function __unset($var)
	{
		return $this->remove($var);
	}

	/**
	 * Setter for array accessed data values.
	 *
	 * @param string	Data key
	 * @param mixed		Value
	 *
	 * @return Object
	 */
	public function offsetSet($offset, $value)
	{
		return $this->set($offset, $value);
	}

	/**
	 * Isset for array accessed data values.
	 *
	 * @param string	Data key
	 *
	 * @return boolean
	 */
	public function offsetExists($offset)
	{
		return $this->exists($offset);
	}

	/**
	 * Unsert for array accessed data values.
	 *
	 * @param string	Data key
	 *
	 * @return Object
	 */
	public function offsetUnset($offset)
	{
		return $this->remove($offset);
	}

	/**
	 * Getter for array accessed data values.
	 *
	 * @param string	Data key
	 *
	 * @return mixed
	 */
	public function offsetGet($offset)
	{
		return $this->get($offset);
	}

	/**
	 * Converts the object to a string.
	 *
	 * @return string
	 */
	public function __toString()
	{
		return $this->toString();
	}

	/**
	 * Converts the object to a string.
	 *
	 * @return string
	 */
	public function toString()
	{
		return get_class($this);
	}

	/**
	 * Returns an iterator object.
	 *
	 * @return ArrayIterator
	 */
	public function getIterator()
	{
		return new ArrayIterator($this->get());
	}
}
?>