<?php
/**
 * Abstract model collection.
 *
 * @package Recipe 1.2
 * @author Sebastian Noll
 * @copyright Copyright (c) 2009, Sebastian Noll
 * @license Proprietary
 * @version $Id: Abstract.php 8 2010-10-17 20:55:04Z secretchampion $
 */

abstract class Recipe_Model_Collection_Abstract implements Countable, IteratorAggregate
{
	/**
	 * Holds the model name of the current collection.
	 *
	 * @var string
	 */
	protected $modelName = "";

	/**
	 * Holds the actual models.
	 *
	 * @var array
	 */
	protected $items = array();

	/**
	 * Holds a pagination object.
	 *
	 * @var Pagination
	 */
	protected $pagination = null;

	/**
	 * Loading state flag.
	 *
	 * @var boolean
	 */
	protected $isLoaded = false;

	/**
	 * Resource model.
	 *
	 * @var Recipe_Model_Resource_Abstract
	 */
	protected $resource = null;

	/**
	 * The select object.
	 *
	 * @var Recipe_Database_Select
	 */
	protected $select = null;

	/**
	 * Creates a new model collection object.
	 *
	 * @param string $modelName	Model name [optional]
	 *
	 * @return Recipe_Model_Collection_Abstract
	 */
	public function __construct($modelName = null)
	{
		if($modelName !== null)
		{
			$this->setModelName($modelName);
		}
		$this->init();
		return $this;
	}

	/**
	 * Initializing method to override.
	 *
	 * @return Recipe_Model_Collection_Abstract
	 */
	protected function init()
	{
		return $this;
	}

	/**
	 * Returns the select object.
	 *
	 * @return Recipe_Database_Select
	 */
	public function getSelect()
	{
		if($this->select === null)
		{
			$this->resetSelect();
		}
		return $this->select;
	}

	/**
	 * Sets the select object.
	 *
	 * @param Recipe_Database_Select
	 *
	 * @return Recipe_Model_Collection_Abstract
	 */
	public function setSelect(Recipe_Database_Select $select)
	{
		$this->select = $select;
		return $this;
	}

	/**
	 * Resets the select object.
	 *
	 * @return Recipe_Model_Collection_Abstract
	 */
	public function resetSelect()
	{
		$this->setSelect($this->getResource()->getSelect());
		return $this;
	}

	/**
	 * Contains the loading procedure.
	 *
	 * @param Recipe_Database_Select $select	Custom select object [optional]
	 *
	 * @return Recipe_Model_Collection_Abstract
	 */
	protected function _load(Recipe_Database_Select $select = null)
	{
		if(is_null($select))
		{
			$select = $this->getSelect();
		}
		if($this->hasPagination())
		{
			$select->limit($this->getPagination()->getStart(), $this->getPagination()->getItemsPerPage());
		}
		$result = $this->getResource()->fetchAll($select);
		$class = get_class(Application::getModel($this->getModelName()));
		foreach($result as $row)
		{
			$model = new $class($row);
			$this->add($model->afterLoad());
		}
		return $this;
	}

	/**
	 * Sets the model name.
	 *
	 * @param string
	 *
	 * @return Recipe_Model_Collection_Abstract
	 */
	public function setModelName($modelName)
	{
		$this->modelName = $modelName;
		return $this;
	}

	/**
	 * Returns the model name.
	 *
	 * @return string
	 */
	public function getModelName()
	{
		return $this->modelName;
	}

	/**
	 * Starts the loading procedure.
	 *
	 * @param Recipe_Database_Select $select
	 * @return Recipe_Model_Collection_Abstract
	 */
	public function load(Recipe_Database_Select $select = null)
	{
		if(!$this->isLoaded())
		{
			$this->isLoaded(true);
			if(is_null($select))
			{
				$select = $this->getSelect();
			}
			$this->_load($select);
			$this->_afterLoad();
		}
		return $this;
	}

	/**
	 * Returns a random collection element.
	 *
	 * @param mixed $default	Default return value [optional]
	 *
	 * @return Recipe_Model_Abstract
	 */
	public function getRandomItem($default = null)
	{
		$index = array_rand($this->items);
		return $this->get($index, $default);
	}

	/**
	 * Returns the first collection element.
	 *
	 * @param mixed $default	Default return value [optional]
	 *
	 * @return Recipe_Model_Abstract
	 */
	public function getFirstItem($default = null)
	{
		return $this->get(0, $default);
	}

	/**
	 * Returns the last collection element.
	 *
	 * @param mixed $default	Default return value [optional]
	 *
	 * @return Recipe_Model_Abstract
	 */
	public function getLastItem($default = null)
	{
		return $this->get($this->count() - 1, $default);
	}

	/**
	 * Returns collection elements.
	 *
	 * @param integer $index	Index key [optional]
	 * @param mixed $default	Default return value [optional]
	 *
	 * @return array|Recipe_Model_Abstract
	 */
	public function get($index = null, $default = null)
	{
		if(!is_null($index))
		{
			$this->load();
			return ($this->indexExists($index)) ? $this->items[$index] : $default;
		}
		return $this->getItems();
	}

	/**
	 * Checks an index key for existence.
	 *
	 * @param integer $index	Index key
	 *
	 * @return boolean
	 */
	public function indexExists($index)
	{
		$this->load();
		return (isset($this->items[$index])) ? true : false;
	}

	/**
	 * Returns all collection elements.
	 *
	 * @return array
	 */
	public function getItems()
	{
		$this->load();
		return $this->items;
	}

	/**
	 * Counts all elements of the collection.
	 *
	 * @return integer
	 */
	public function count()
	{
		return count($this->getItems());
	}

	/**
	 * Alias to count() method.
	 *
	 * @return integer
	 */
	public function size()
	{
		return $this->count();
	}

	/**
	 * Retrieves an element iterator.
	 *
	 * @return ArrayIterator
	 */
	public function getIterator()
	{
		return new ArrayIterator($this->getItems());
	}

	/**
	 * Sets a pagination object.
	 *
	 * @param Pagination
	 *
	 * @return Recipe_Model_Collection_Abstract
	 */
	public function setPagination(Pagination $pagination)
	{
		$this->pagination = $pagination;
		return $this;
	}

	/**
	 * Returns the pagination object.
	 *
	 * @return Pagination
	 */
	public function getPagination()
	{
		return $this->pagination;
	}

	/**
	 * Checks if the collection holds a pagination object.
	 *
	 * @return boolean
	 */
	public function hasPagination()
	{
		return ($this->pagination instanceof Pagination) ? true : false;
	}

	/**
	 * Returns the loading state.
	 *
	 * @param bool $isLoaded
	 * @return boolean
	 */
	public function isLoaded($isLoaded = null)
	{
		if(!is_null($isLoaded))
		{
			$this->isLoaded = (bool) $isLoaded;
		}
		return $this->isLoaded;
	}

	/**
	 * Returns the model resource object.
	 *
	 * @return Recipe_Model_Resource_Abstract
	 */
	public function getResource()
	{
		if($this->resource === null)
		{
			$this->setResource($this->getModel()->getResource());
		}
		return $this->resource;
	}

	/**
	 * Sets the model resource object.
	 *
	 * @param Recipe_Model_Resource_Abstract
	 *
	 * @return Recipe_Model_Collection_Abstract
	 */
	public function setResource(Recipe_Model_Resource_Abstract $resource)
	{
		$this->resource = $resource;
		return $this;
	}

	/**
	 * Returns an empty model of this collection.
	 *
	 * @return Recipe_Model_Abstract
	 */
	public function getModel()
	{
		return Application::getModel($this->getModelName());
	}

	/**
	 * Removes the first element.
	 *
	 * @return Recipe_Model_Abstract		The removed element
	 */
	public function removeFirst()
	{
		return array_shift($this->items);
	}

	/**
	 * Removes the last element.
	 *
	 * @return Recipe_Model_Abstract		The removed element
	 */
	public function removeLast()
	{
		return array_pop($this->items);
	}

	/**
	 * Resets the items state.
	 *
	 * @return Recipe_Model_Collection_Abstract
	 */
	public function reset()
	{
		$this->items = array();
		$this->isLoaded(false);
		return $this;
	}

	/**
	 * Adds an element.
	 *
	 * @param Recipe_Model_Abstract
	 *
	 * @return Recipe_Model_Collection_Abstract
	 */
	public function add(Recipe_Model_Abstract $item)
	{
		$this->items[] = $item;
		return $this;
	}

	/**
	 * Performs action after loading procedure.
	 *
	 * @return Recipe_Model_Collection_Abstract
	 */
	protected function _afterLoad()
	{
		return $this;
	}

	/**
	 * Returns the collection size from database before execution the fetching process.
	 *
	 * @param boolean|string|array $groupBy
	 * @return integer
	 */
	public function getCalculatedSize($groupBy = true)
	{
		$select = clone $this->getSelect();
		$select->attributes("COUNT(*) AS collection_count");

		if($groupBy)
		{
			if($groupBy === true)
			{
				$resourceModel = $this->getResource();
				$groupBy = $resourceModel->getPrimaryKey();
				$mainTable = $resourceModel->getMainTable();
				if(!empty($mainTable))
				{
					$alias = key($mainTable);
					$groupBy = array($alias => $groupBy);
				}
			}
			$select->group($groupBy);
		}

		$result = $select->getStatement();
		$row = $result->fetchRow();
		return (int) $row["collection_count"];
	}
}
?>