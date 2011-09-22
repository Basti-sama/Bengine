<?php
/**
 * Provides basic function for database export.
 *
 * @package Recipe 1.2
 * @author Sebastian Noll
 * @copyright Copyright (c) 2009, Sebastian Noll
 * @license Proprietary
 * @version $Id: Abstract.php 8 2010-10-17 20:55:04Z secretchampion $
 */

abstract class Recipe_Database_Export_Abstract
{
	/**
	 * Tables to export.
	 *
	 * @var string
	 */
	protected $tables = null;

	/**
	 * Field data.
	 *
	 * @var array
	 */
	protected $tableFields = null;

	/**
	 * Export data.
	 *
	 * @var string
	 */
	protected $data = null;

	/**
	 * Table actions for import.
	 *
	 * @var array
	 */
	protected $actions = array();

	/**
	 * Flag to enable table structure in export file.
	 *
	 * @var boolean
	 */
	protected $showStructure = false;

	/**
	 * Flag to disable table data in export file.
	 *
	 * @var boolean
	 */
	protected $showData = true;

	/**
	 * Force to implement the export method.
	 *
	 * @return Recipe_Database_Export_Abstract
	 */
	public abstract function export();

	/**
	 * Creates a new export object.
	 *
	 * @param array		Tables to export [optional]
	 *
	 * @return void
	 */
	public function __construct(array $tables = null)
	{
		if(!is_null($tables))
		{
			$this->setTables($tables);
		}
		return;
	}

	/**
	 * Sets the tables to export.
	 *
	 * @param array		[optional]
	 *
	 * @return Recipe_Database_Export_Abstract
	 */
	public function setTables(array $tables = null)
	{
		$this->tables = $tables;
		return $this;
	}

	/**
	 * Prepares the export.
	 *
	 * @return Recipe_Database_Export_Abstract
	 */
	protected function prepareExport()
	{
		$this->loadTables();
		$this->loadFields();
		return $this;
	}

	/**
	 * Loads all tables.
	 *
	 * @return Recipe_Database_Export_Abstract
	 */
	public function loadTables()
	{
		if(is_null($this->tables))
		{
			$this->setTables(Core::getDB()->getTables());
		}
		return $this;
	}

	/**
	 * Loads the field data.
	 *
	 * @return Recipe_Database_Export_Abstract
	 */
	public function loadFields()
	{
		if(is_null($this->tableFields))
		{
			$this->loadTables();
			foreach($this->tables as $table)
			{
				$result = Core::getDB()->query("SHOW COLUMNS FROM ".$table);
				while($row = Core::getDB()->fetch($result))
				{
					$this->tableFields[$table][] = $row;
				}
			}
		}
		return $this;
	}

	/**
	 * Saves the export data into a file.
	 *
	 * @param string	Path to file
	 *
	 * @return Recipe_Database_Export_Abstract
	 */
	public function save($path)
	{
		if(is_null($this->data))
		{
			$this->export();
		}
		$dir = dirname($path);
		if(is_dir($dir))
		{
			file_put_contents($path, $this->data);
		}
		return $this;
	}

	/**
	 * Returns the export data.
	 *
	 * @return string
	 */
	public function getData()
	{
		if(is_null($this->data))
		{
			$this->export();
		}
		return $this->data;
	}

	/**
	 * Adds the truncate action to the tables.
	 *
	 * @param array		Tables to truncate [optional]
	 *
	 * @return Recipe_Database_Export_Abstract
	 */
	public function setTruncate(array $tables = null)
	{
		if(is_null($tables))
		{
			$this->loadTables();
			$tables = $this->tables;
		}
		foreach($tables as $table)
		{
			$this->setAction($table, "truncate");
		}
		return $this;
	}

	/**
	 * Adds table actions.
	 *
	 * @param string|array	Tables
	 * @param string		Action to execute [optional]
	 *
	 * @return Recipe_Database_Export_Abstract
	 */
	public function setAction($table, $action = null)
	{
		if(is_array($table))
		{
			$this->actions = $table;
		}
		else if(is_string($action))
		{
			$this->actions[$table] = $action;
		}
		return $this;
	}

	/**
	 * Enables / Disables the table structure.
	 *
	 * @param boolean
	 *
	 * @return Recipe_Database_Export_Abstract
	 */
	public function setShowStructure($showStructure)
	{
		$this->showStructure = $showStructure;
		return $this;
	}

	/**
	 * Enables / Disables the table data.
	 *
	 * @param boolean
	 *
	 * @return Recipe_Database_Export_Abstract
	 */
	public function setShowData($showData)
	{
		$this->showData = $showData;
		return $this;
	}

	/**
	 * Retrieves a CREATE statement for a table.
	 *
	 * @param string	Table
	 *
	 * @return string	Create statement
	 */
	protected function getTableStructure($table)
	{
		$result = Core::getDB()->query("SHOW CREATE TABLE ".$table);
		return Core::getDB()->fetch_field($result, "Create Table");
	}
}
?>