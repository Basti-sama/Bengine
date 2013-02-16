<?php
/**
 * Object-oriented typing: Hash Map / Array
 *
 * @package Recipe 1.2
 * @author Sebastian Noll
 * @copyright Copyright (c) 2009, Sebastian Noll
 * @license Proprietary
 * @version $Id: Map.util.php 8 2010-10-17 20:55:04Z secretchampion $
 */

class Map extends Type implements IteratorAggregate
{
	/**
	 * The actual map.
	 *
	 * @var array
	 */
	protected $map = array();

	/**
	 * Internal pointer on the current element.
	 *
	 * @var ArrayIterator
	 */
	protected $iterator = null;

	/**
	 * Creates an new map.
	 *
	 * @param mixed $map Pre-defined content [optional]
	 *
	 * @return Map
	 */
	public function __construct($map = null)
	{
		if(is_array($map))
		{
			$this->map = $map;
		}
		else if($map !== null)
		{
			$this->map = array($map);
		}
		return;
	}

	/**
	 * Returns a value.
	 *
	 * @param mixed $key	Element key [optional]
	 *
	 * @return mixed		Element value
	 */
	public function get($key = null)
	{
		if(is_null($key))
		{
			return $this->getArray();
		}
		return ($this->exists($key)) ? $this->map[$key] : false;
	}

	/**
	 * Returns the map as a normal array.
	 *
	 * @return array
	 */
	public function getArray()
	{
		return $this->map;
	}

	/**
	 * Adds an element.
	 *
	 * @param mixed $key		Map key
	 * @param mixed $value		Element value
	 *
	 * @return Map
	 */
	public function set($key, $value)
	{
		$key = $this->getFromArgument($key);
		$this->map[$key->get()] = $value;
		return $this;
	}

	/**
	 * Searches the map content for needle.
	 *
	 * @param mixed $needle		Search element
	 * @param boolean $strict	Type save search [optional]
	 *
	 * @return boolean	True, if needle has been found, false otherwise
	 */
	public function contains($needle, $strict = false)
	{
		$needle = $this->getFromArgument($needle);
		$strict = $this->getFromArgument($strict);
		return in_array($needle->get(), $this->map, $strict->get());
	}

	/**
	 * Checks if key exsits.
	 *
	 * @param mixed $key		Key to search for
	 *
	 * @return boolean	True, if key has been found, false otherwise
	 */
	public function exists($key)
	{
		return array_key_exists($key, $this->map);
	}

	/**
	 * Push an element onto the end.
	 *
	 * @param mixed $var		The pushed value
	 *
	 * @return Map
	 */
	public function push($var)
	{
		array_push($this->map, $var);
		return $this;
	}

	/**
	 * Adds an element to the beginning of the map.
	 *
	 * @param mixed $var		The added value
	 *
	 * @return Map
	 */
	public function add($var)
	{
		array_unshift($this->map, $var);
		return $this;
	}

	/**
	 * Adds an array or map.
	 *
	 * @param mixed
	 *
	 * @return Map
	 */
	public function merge($array)
	{
		$map = $this->getFromArgument($array);
		$this->map = array_merge($this->map, $map->getArray());
		return $this;
	}

	/**
	 * Sets an array or map of keys to this map.
	 *
	 * @param mixed
	 *
	 * @return Map
	 */
	public function setKeys($array)
	{
		$map = $this->getFromArgument($array);
		$this->map = array_combine($map->getArray(), $this->map);
		return $this;
	}

	/**
	 * Sets an array or map of values and make the original map to keys.
	 *
	 * @param mixed
	 *
	 * @return Map
	 */
	public function setValues($array)
	{
		$map = $this->getFromArgument($array);
		$this->map = array_combine($this->map, $map->getArray());
		return $this;
	}

	/**
	 * Shuffles this map (randomizes the order of the elements).
	 *
	 * @return Map
	 */
	public function shuffle()
	{
		shuffle($this->map);
		return $this;
	}

	/**
	 * Returns the map size.
	 *
	 * @return integer
	 */
	public function size()
	{
		return count($this->map);
	}

	/**
	 * Calculates the sum of values.
	 *
	 * @return integer
	 */
	public function sum()
	{
		return array_sum($this->map);
	}

	/**
	 * Calculates the product of values.
	 *
	 * @return integer
	 */
	public function product()
	{
		return array_product($this->map);
	}

	/**
	 * Searches the map for a given value and returns the corresponding key if successful.
	 *
	 * @param mixed $needle		The searched value
	 * @param boolean $strict	Type save search [optional]
	 *
	 * @return mixed	The key for needle, false otherwise.
	 */
	public function search($needle, $strict = false)
	{
		$needle = $this->getFromArgument($needle);
		$strict = $this->getFromArgument($strict);
		return array_search($needle->get(), $this->map, $strict->get());
	}

	/**
	 * Removes double keys.
	 *
	 * @return Map
	 */
	public function clean()
	{
		$this->map = array_unique($this->map);
		return $this;
	}

	/**
	 * Sorts the map.
	 *
	 * @param bool $reversed Reversed sort [optional]
	 *
	 * @return Map
	 */
	public function sort($reversed = false)
	{
		if($reversed)
		{
			rsort($this->map);
			return $this;
		}
		sort($this->map);
		return $this;
	}

	/**
	 * Returns the current value.
	 *
	 * @throws Recipe_Exception_Issue
	 * @return mixed    The value, false otherwise
	 */
	public function current()
	{
		$this->initializeIterator();
		return $this->iterator->current();
	}

	/**
	 * Sets the internal pointer on the next element.
	 *
	 * @return boolean	True, if the map has a next element, false otherwise
	 */
	public function next()
	{
		if($this->initializeIterator())
		{
			$this->iterator->next();
		}
		return $this->iterator->valid();
	}

	/**
	 * Returns the current pointer.
	 *
	 * @throws Recipe_Exception_Issue
	 * @return integer
	 */
	public function key()
	{
		$this->initializeIterator();
		return $this->iterator->key();
	}

	/**
	 * Resets the iterator.
	 *
	 * @return Map
	 */
	public function rewind()
	{
		$this->initializeIterator();
		$this->iterator->rewind();
		return $this;
	}

	/**
	 * Sets the iterator on the last element.
	 *
	 * @return Map
	 */
	public function end()
	{
		$this->initializeIterator();
		$this->iterator->seek($this->getLastIndex() + 1);
		return $this;
	}

	/**
	 * Returns the first map element.
	 *
	 * @return mixed	Value or false
	 */
	public function getFirst()
	{
		if(isset($this->map[0]))
		{
			return $this->map[0];
		}
		return false;
	}

	/**
	 * Returns the last map index.
	 *
	 * @return integer
	 */
	public function getLastIndex()
	{
		return $this->size() - 1;
	}

	/**
	 * Returns the last map element.
	 *
	 * @return mixed	Value or false
	 */
	public function getLast()
	{
		$last = $this->getLastIndex();
		if(isset($this->map[$last]))
		{
			return $this->map[$last];
		}
		return false;
	}

	/**
	 * Retrieves an external iterator.
	 *
	 * @return ArrayIterator
	 */
	public function getIterator()
	{
		$this->initializeIterator();
		return $this->iterator;
	}

	/**
	 * Creates a string from each element.
	 *
	 * @param string $glue	Glue string to join the elements [optional]
	 *
	 * @return string
	 */
	public function toString($glue = null)
	{
		if($glue !== null)
		{
			$glue = $this->getFromArgument($glue);
			return implode($glue->get(), $this->map);
		}
		return implode($this->map);
	}

	/**
	 * Returns a String from each map element.
	 *
	 * @param string $glue	Glue string to join the elements [optional]
	 *
	 * @return String
	 */
	public function getString($glue = null)
	{
		return new String($this->toString($glue));
	}

	/**
	 * Applies trim() to each element.
	 *
	 * @return Map
	 */
	public function trim()
	{
		$this->map = Arr::trim($this->map);
		return $this;
	}

	/**
	 * Removes the element off the end of map.
	 *
	 * @return Map
	 */
	public function removeLast()
	{
		array_pop($this->map);
		return $this;
	}

	/**
	 * Removes an element off the beginning of map.
	 *
	 * @return Map
	 */
	public function removeFirst()
	{
		array_shift($this->map);
		return $this;
	}

	/**
	 * Extracts a slice of the map.
	 *
	 * @param integer $offset		Start of sequence
	 * @param integer $length		End of sequence [optional]
	 * @param boolean $preserveKeys	Rescue Keys [optional]
	 *
	 * @return Map
	 */
	public function slice($offset, $length = null, $preserveKeys = false)
	{
		$offset = $this->getFromArgument($offset);
		$length = (is_null($length)) ? new Integer($this->size()) : $this->getFromArgument($length);
		$preserveKeys = $this->getFromArgument($preserveKeys);
		$this->map = array_slice($this->map, $offset->get(), $length->get(), $preserveKeys->get());
		return $this;
	}

	/**
	 * Empties the map.
	 *
	 * @return Map
	 */
	public function reset()
	{
		$this->map = array();
		return $this;
	}

	/**
	 * Returns a random element of the map.
	 *
	 * @return mixed	Map element
	 */
	public function getRandomElement()
	{
		$keys = array_keys($this->map);
		$rand = mt_rand(0, $this->size() - 1);
		$randKey = $keys[$rand];
		return isset($this->map[$randKey]) ? $this->map[$randKey] : "";
	}

	/**
	 * Returns a random map index.
	 *
	 * @return mixed	Key
	 */
	public function getRandomIndex()
	{
		$keys = new Map(array_keys($this->map));
		return $keys->getRandomElement();
	}

	/**
	 * @return boolean
	 */
	protected function initializeIterator()
	{
		if($this->iterator === null)
		{
			$this->iterator = new ArrayIterator($this->map);
			return false;
		}
		return true;
	}

	/**
	 * Called when an unkown method has been requested.
	 * Warning: Use only functions with an array as return value!
	 *
	 * @param string $method	Method name
	 * @param array $args		Arguments
	 *
	 * @return Map
	 */
	public function __call($method, array $args = null)
	{
		$callback = $this->call($method, $args);
		if($callback !== false)
		{
			$this->map = $callback->get();
		}
		return $this;
	}

	/**
	 * Returns the array as a string when trying to invoke the class by echo:
	 * echo $stringObj;
	 *
	 * @return string
	 */
	public function __toString()
	{
		return $this->toString();
	}
}
?>