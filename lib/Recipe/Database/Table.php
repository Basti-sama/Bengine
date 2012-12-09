<?php
/**
 * Represents a database table.
 *
 * @package Recipe 1.2
 * @author Sebastian Noll
 * @copyright Copyright (c) 2009, Sebastian Noll
 * @license Proprietary
 * @version $Id: Table.php 8 2010-10-17 20:55:04Z secretchampion $
 */

class Recipe_Database_Table
{
	/**
	 * The name of the table.
	 *
	 * @var string
	 */
	protected $name = "";

	/**
	 * The database object.
	 *
	 * @var Recipe_Database_Abstract
	 */
	protected $db = null;

	/**
	 * Table schema information.
	 *
	 * @var array
	 */
	protected $schema = array();

	/**
	 * Primary key of the table.
	 *
	 * @var string
	 */
	protected $primaryKey = "";

	/**
	 * The number of affected rows in a previous SQL operation.
	 *
	 * @var integer
	 */
	protected $affectedRows = 0;

	/**
	 * Last inserted ID of the last table insert operation.
	 *
	 * @var integer
	 */
	protected $insertId = 0;

	/**
	 * Creates a new table object.
	 *
	 * @param string $name	Table name [optional]
	 *
	 * @return Recipe_Database_Table
	 */
	public function __construct($name = null)
	{
		if(!is_null($name))
		{
			$this->setName($name);
		}
		$this->init();
		return $this;
	}

	/**
	 * Initilizing method.
	 *
	 * @return Recipe_Database_Table
	 */
	protected function init()
	{
		return $this;
	}

	/**
	 * Fetches all rows.
	 *
	 * @param array|string|Recipe_Database_Select $where	WHERE statement [optional]
	 * @param array $order									ORDER statement [optional]
	 * @param array $limit									LIMIT statement [optional]
	 *
	 * @return array										The rows
	 */
	public function fetchAll($where = null, array $order = null, array $limit = null)
	{
		if($where instanceof Recipe_Database_Select)
		{
			$select = $where;
		}
		else if(is_string($where))
		{
			$select = new Recipe_Database_Select($where);
		}
		else
		{
			$select = $this->getSelect();
			if(!empty($where))
			{
				$select->where($where[0], $where[1]);
			}
			if(!empty($order))
			{
				$select->order($order[0], $order[1]);
			}
			if(!empty($limit))
			{
				$select->limit($limit[0], $limit[1]);
			}
		}

		$result = $select->render()->getStatement();
		return $result->fetchAll();
	}

	/**
	 * Fetches one row.
	 *
	 * @param mixed|string|Recipe_Database_Select $where	WHERE condition [optional]
	 * @param mixed $value									Value [optional]
	 *
	 * @return array	Row data
	 */
	public function fetchRow($where = null, $value = null)
	{
		if($where instanceof Recipe_Database_Select)
		{
			$select = $where;
		}
		else if(is_string($where))
		{
			$select = new Recipe_Database_Select($where);
		}
		else
		{
			$select = $this->getSelect();
			if(!is_null($where))
			{
				$select->where($where, $value);
			}
		}
		$select->limit(1);
		return $select->render()->getStatement()->fetchRow();
	}

	/**
	 * Fetches a row by primary key.
	 *
	 * @param integer|string $id	Primary key value to find.
	 *
	 * @return array
	 */
	public function find($id)
	{
		return $this->fetchRow(array($this->getPrimaryKey()), $id);
	}

	/**
	 * Deletes existing rows.
	 *
	 * @param string|integer $where	WHERE condition or ID to delete
	 *
	 * @return Recipe_Database_Table
	 */
	public function delete($where)
	{
		$bind = null;
		if(is_numeric($where))
		{
			$bind = array((int) $where);
			$where = "`".$this->getPrimaryKey()."` = ?";
		}
		$this->_beforeDelete();
		Core::getQuery()->delete($this->getName(), $where, null, null, $bind);
		$this->affectedRows = $this->getDb()->affectedRows();
		$this->_afterDelete();
		return $this;
	}

	/**
	 * Update existing rows.
	 *
	 * @param array $data	Column-value pairs
	 *
	 * @return Recipe_Database_Table
	 */
	public function insert(array $data)
	{
		$bind = array();
		$fields = $this->getFields();
		foreach($data as $key => $value)
		{
			if(isset($fields[$key]))
			{
				$bind[$key] = $value;
			}
		}
		$this->_beforeInsert();
		Core::getQuery()->insert($this->getName(), $bind);
		$this->insertId = $this->getDb()->lastInsertId();
		$this->_afterInsert();
		return $this;
	}

	/**
	 * Update existing rows.
	 *
	 * @param array $data    Column-value pairs
	 * @param string $where    Where clause
	 * @param array $bind
	 * @return Recipe_Database_Table
	 */
	public function update(array $data, $where, array $bind = null)
	{
		$spec = array();
		$fields = $this->getFields();
		foreach($data as $key => $value)
		{
			if(isset($fields[$key]))
			{
				$spec[$key] = $value;
			}
		}
		$this->_beforeUpdate();
		Core::getQuery()->update($this->getName(), $spec, $where, $bind);
		$this->affectedRows = Core::getDB()->affectedRows();
		$this->_afterUpdate();
		return $this;
	}

	/**
	 * Retrieves the primary key of this table.
	 *
	 * @return string
	 *
	 * @throws Recipe_Exception_Generic
	 */
	public function getPrimaryKey()
	{
		if(empty($this->primaryKey))
		{
			foreach($this->getSchema() as $field)
			{
				if($field["Key"] == "PRI")
				{
					$this->setPrimaryKey($field["Field"]);
					return $this->getPrimaryKey();
				}
			}
			throw new Recipe_Exception_Generic("Table <b>".$this->getName()."</b> has no primary key defined.");
		}
		return $this->primaryKey;
	}

	/**
	 * Sets the primary key of this table.
	 *
	 * @param string
	 *
	 * @return Recipe_Database_Table
	 */
	public function setPrimaryKey($primaryKey)
	{
		$this->primaryKey = $primaryKey;
		return $this;
	}

	/**
	 * Retrieves all fields of this table.
	 *
	 * @return array
	 */
	public function getFields()
	{
		$fields = array();
		$schema = $this->getSchema();
		foreach($schema as $field)
		{
			$fields[$field["Field"]] = $field["Field"];
		}
		return $fields;
	}

	/**
	 * Retrieves the table schema.
	 *
	 * @return array
	 */
	public function getSchema()
	{
		if(empty($this->schema))
		{
			$result = Core::getQuery()->showFields($this->getName());
			foreach($result->fetchAll() as $field)
			{
				$this->schema[] = $field;
			}
		}
		return $this->schema;
	}

	/**
	 * Sets the table name.
	 *
	 * @param string $name
	 * @return Recipe_Database_Table
	 */
	public function setName($name)
	{
		$this->name = $name;
		return $this;
	}

	/**
	 * Returns the table name.
	 *
	 * @return string
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * Retrieves a raw select object for the table.
	 *
	 * @return Recipe_Database_Select
	 */
	public function getSelect()
	{
		return new Recipe_Database_Select($this);
	}

	/**
	 * Returns the database object.
	 *
	 * @return Recipe_Database_Abstract
	 */
	public function getDb()
	{
		if(is_null($this->db))
		{
			$this->db = Core::getDB();
		}
		return $this->db;
	}

	/**
	 * Returns the number of affected rows in a previous SQL operation.
	 *
	 * @return integer
	 */
	public function getAffectedRows()
	{
		return $this->affectedRows;
	}

	/**
	 * Returns the ID generated from the previous INSERT operation.
	 *
	 * @return integer
	 */
	public function getInsertId()
	{
		return $this->insertId;
	}

	/**
	 * Converts this class to a string.
	 *
	 * @return string
	 */
	public function __toString()
	{
		return $this->getName();
	}

	/**
	 * Returns the FROM-condition.
	 *
	 * @return string
	 */
	public function getFrom()
	{
		return $this->getName();
	}

	/**
	 * Perform actions before object delete.
	 *
	 * @return Recipe_Database_Table
	 */
	protected function _beforeDelete()
	{
		return $this;
	}

	/**
	 * Perform actions after object delete.
	 *
	 * @return Recipe_Database_Table
	 */
	protected function _afterDelete()
	{
		return $this;
	}

	/**
	 * Perform actions before object insert.
	 *
	 * @return Recipe_Database_Table
	 */
	protected function _beforeInsert()
	{
		return $this;
	}

	/**
	 * Perform actions after object insert.
	 *
	 * @return Recipe_Database_Table
	 */
	protected function _afterInsert()
	{
		return $this;
	}

	/**
	 * Perform actions before object update.
	 *
	 * @return Recipe_Database_Table
	 */
	protected function _beforeUpdate()
	{
		return $this;
	}

	/**
	 * Perform actions after object update.
	 *
	 * @return Recipe_Database_Table
	 */
	protected function _afterUpdate()
	{
		return $this;
	}
}
?>