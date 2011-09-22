<?php
/**
 * Abstract resource model.
 *
 * @package Recipe 1.2
 * @author Sebastian Noll
 * @copyright Copyright (c) 2009, Sebastian Noll
 * @license Proprietary
 * @version $Id: Abstract.php 8 2010-10-17 20:55:04Z secretchampion $
 */

abstract class Recipe_Model_Resource_Abstract extends Recipe_Database_Table
{
	/**
	 * Main table.
	 *
	 * @var string|array
	 */
	protected $mainTable = array();

	/**
	 * (non-PHPdoc)
	 * @see lib/Recipe/Database/Recipe_Database_Table#getFrom()
	 */
	public function getFrom()
	{
		return $this->getMainTable();
	}

	/**
	 * Returns the main table parameter.
	 *
	 * @return array
	 */
	public function getMainTable()
	{
		return $this->mainTable;
	}

	/**
	 * Sets the main table parameter.
	 *
	 * @param array|string
	 *
	 * @return Recipe_Model_Resource_Abstract
	 */
	public function setMainTable($mainTable)
	{
		if(is_array($mainTable))
		{
			$this->mainTable = $mainTable;
		}
		else
		{
			$this->mainTable = array("main_table" => $mainTable);
		}
		return $this;
	}

	/**
	 * Fetches a row by primary key.
	 *
	 * @param integer|string	Primary key value to find.
	 *
	 * @return array
	 */
	public function find($id)
	{
		$mainTable = $this->getMainTable();
		if(is_array($mainTable))
		{
			$where = array(key($mainTable) => $this->getPrimaryKey());
		}
		else
		{
			$where = array($this->getPrimaryKey());
		}
		return $this->fetchRow($where, $id);
	}
}
?>