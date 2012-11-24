<?php
/**
 * Base collection class.
 *
 * @package Recipe 1.2
 * @author Sebastian Noll
 * @copyright Copyright (c) 2009, Sebastian Noll
 * @license Proprietary
 * @version $Id: Collection.php 8 2010-10-17 20:55:04Z secretchampion $
 */

abstract class Recipe_Collection
{
	public abstract function __construct();
	public abstract function get($var);
	public abstract function set($var, $value);

	/**
	 * Holds collection data.
	 *
	 * @var array
	 */
	protected $item = array();

	/**
	 * Supports access to collection variable as class property.
	 * ($collection->variable)
	 *
	 * @param string $var
	 *
	 * @return string
	 */
	public function __get($var)
	{
		return $this->get($var);
	}

	/**
	 * Supports access to collection variable as class property.
	 * ($collection->variable = "Value")
	 *
	 * @param string $var
	 * @param mixed $value
	 *
	 * @return Recipe_Collection
	 */
	public function __set($var, $value)
	{
		$this->set($var, $value);
		return $this;
	}

	/**
	 * Checks if the given key or index exists in the collection.
	 *
	 * @param string $var	Variable to check
	 *
	 * @return boolean	True if the given key is set in the session
	 */
	public function exists($var)
	{
		if(!$this->item)
		{
			return false;
		}
		if(array_key_exists($var, $this->item)) { return true; } else { return false; }
	}

	/**
	 * Returns the size of all collection items.
	 *
	 * @return integer
	 */
	public function size()
	{
		return count($this->item);
	}
}
?>