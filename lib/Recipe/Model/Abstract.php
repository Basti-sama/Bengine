<?php
/**
 * Abstract model.
 *
 * @package Recipe 1.2
 * @author Sebastian Noll
 * @copyright Copyright (c) 2009, Sebastian Noll
 * @license Proprietary
 * @version $Id: Abstract.php 8 2010-10-17 20:55:04Z secretchampion $
 */

abstract class Recipe_Model_Abstract extends Object
{
	/**
	 * Table, model, collection and resource names.
	 *
	 * @var string
	 */
	protected $_name = "", $_model = "", $_resourceName = "", $_collectionName = "";

	/**
	 * Primary key name.
	 *
	 * @var string
	 */
	protected $_primaryKey = "";

	/**
	 * Model resource.
	 *
	 * @var Recipe_Model_Resource_Abstract
	 */
	protected $_resource = null;

	/**
	 * Sets the model resource.
	 *
	 * @param Recipe_Model_Resource_Abstract	[optional]
	 *
	 * @return Recipe_Model_Abstract
	 */
	public function setResource($resource = null)
	{
		if(is_null($resource))
		{
			$this->_resource = Application::getResource($this->getResourceName(), $this->getTableName());
		}
		else
		{
			$this->_resource = $resource;
		}
		return $this;
	}

	/**
	 * Sets the resource name.
	 *
	 * @param string
	 *
	 * @return Recipe_Model_Abstract
	 */
	public function setResourceName($resourceName)
	{
		$this->_resourceName = $resourceName;
		return $this;
	}

	/**
	 * Sets the primary key.
	 *
	 * @param string
	 *
	 * @return Recipe_Model_Abstract
	 */
	public function setPrimaryKey($primaryKey)
	{
		$this->_primaryKey = $primaryKey;
		return $this;
	}

	/**
	 * Returns the resource.
	 *
	 * @return Recipe_Model_Resource_Abstract
	 */
	public function getResource()
	{
		if(is_null($this->_resource))
		{
			$this->setResource();
		}
		return $this->_resource;
	}

	/**
	 * Returns the resource name.
	 *
	 * @return string
	 */
	public function getResourceName()
	{
		return (empty($this->_resourceName)) ? $this->getModelName() : $this->_resourceName;
	}

	/**
	 * Returns the model name.
	 *
	 * @return string
	 */
	public function getModelName()
	{
		return (empty($this->_model)) ? $this->getTableName() : $this->_model;
	}

	/**
	 * Sets the model name.
	 *
	 * @param string
	 *
	 * @return Recipe_Model_Abstract
	 */
	public function setModelName($modelName)
	{
		$this->_model = $modelName;
		return $this;
	}

	/**
	 * Returns the table name
	 *
	 * @return string
	 */
	public function getTableName()
	{
		return $this->_name;
	}

	/**
	 * Sets the table name.
	 *
	 * @param string
	 *
	 * @return Recipe_Model_Abstract
	 */
	public function setTableName($tableName)
	{
		$this->_name = $tableName;
		return $this;
	}

	/**
	 * Returns the primary key.
	 *
	 * @return string
	 */
	public function getPrimaryKey()
	{
		if($this->_primaryKey === false)
		{
			$this->_primaryKey = $this->getResource()->getPrimaryKey();
		}
		else if(empty($this->_primaryKey))
		{
			$this->_primaryKey = $this->getTableName()."_id";
		}
		return $this->_primaryKey;
	}

	/**
	 * Returns the data row's id.
	 *
	 * @return mixed
	 */
	public function getId()
	{
		return $this->get($this->getPrimaryKey());
	}

	/**
	 * Sets the data row's id.
	 *
	 * @param mixed $id
	 *
	 * @return Recipe_Model_Abstract
	 */
	public function setId($id)
	{
		return $this->set($this->getPrimaryKey(), $id);
	}

	/**
	 * Loads the data record by ID.
	 *
	 * @param integer $id
	 *
	 * @return Recipe_Model_Abstract
	 */
	public function load($id)
	{
		$data = $this->getResource()->find($id);
		if(is_array($data))
		{
			$this->set($data);
		}
		$this->_afterLoad();
		return $this;
	}

	/**
	 * Deletes rows. If WHERE parameter is null, deletes current row.
	 *
	 * @param mixed $where	WHERE-condition [optional]
	 *
	 * @return Recipe_Model_Abstract
	 */
	public function delete($where = null)
	{
		if(is_null($where))
		{
			$where = $this->getId();
		}
		$this->_beforeDelete();
		$this->getResource()->delete($where);
		$this->_afterDelete();
		return $this;
	}

	/**
	 * Saves the current data row.
	 *
	 * @return Recipe_Model_Abstract
	 */
	public function save()
	{
		$this->_beforeSave();
		if($this->getId())
		{
			$this->getResource()->update($this->get(), $this->getPrimaryKey()." = ?", array($this->getId()));
		}
		else
		{
			$this->getResource()->insert($this->get());
			$this->setId($this->getResource()->getInsertId());
		}
		$this->_afterSave();
		return $this;
	}

	/**
	 * Fetches a row by primary key.
	 *
	 * @param integer|string	Primary key value to find.
	 *
	 * @return Recipe_Model_Abstract
	 */
	public function find($value)
	{
		$data = $this->getResource()->find($value);
		if(is_array($data))
		{
			$this->set($data);
		}
		return $this;
	}

	/**
	 * Returns a model collection.
	 *
	 * @return Recipe_Model_Collection_Abstract
	 */
	public function getCollection()
	{
		return Application::getCollection($this->getCollectionName(), $this->getModelName())->setResource($this->getResource());
	}

	/**
	 * Sets the collection name.
	 *
	 * @param string
	 *
	 * @return Recipe_Model_Abstract
	 */
	public function setCollectionName($collectionName)
	{
		$this->_collectionName = $collectionName;
		return $this;
	}

	/**
	 * Returns the collection name.
	 *
	 * @return string
	 */
	public function getCollectionName()
	{
		return (empty($this->_collectionName)) ? $this->getModelName() : $this->_collectionName;
	}

	/**
	 * Perform actions before object save.
	 *
	 * @return Recipe_Model_Abstract
	 */
	protected function _beforeSave()
	{
		return $this;
	}

	/**
	 * Perform actions after object save.
	 *
	 * @return Recipe_Model_Abstract
	 */
	protected function _afterSave()
	{
		return $this;
	}

	/**
	 * Perform actions before object delete.
	 *
	 * @return Recipe_Model_Abstract
	 */
	protected function _beforeDelete()
	{
		return $this;
	}

	/**
	 * Perform actions after object delete.
	 *
	 * @return Recipe_Model_Abstract
	 */
	protected function _afterDelete()
	{
		return $this;
	}

	/**
	 * Perform actions after object load.
	 *
	 * @return Recipe_Model_Abstract
	 */
	protected function _afterLoad()
	{
		return $this;
	}

	/**
	 * Perform actions after object load.
	 *
	 * @return Recipe_Model_Abstract
	 */
	public function afterLoad()
	{
		return $this->_afterLoad();
	}
}
?>