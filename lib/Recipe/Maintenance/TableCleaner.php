<?php
/**
 * Resets the auto-increment value of a table and resorts all entries.
 *
 * @package Recipe 1.2
 * @author Sebastian Noll
 * @copyright Copyright (c) 2009, Sebastian Noll
 * @license Proprietary
 * @version $Id: TableCleaner.php 8 2010-10-17 20:55:04Z secretchampion $
 */

class Recipe_Maintenance_TableCleaner
{
	/**
	 * Table name that shall be reset.
	 *
	 * @var string
	 */
	protected $tableName = "";

	/**
	 * Primary key of the table.
	 *
	 * @var string
	 */
	protected $primaryKey = null;

	/**
	 * Field information of the table.
	 *
	 * @var array
	 */
	protected $fields = array();

	/**
	 * Charset of the table.
	 *
	 * @var string
	 */
	protected $defaultCharset = "binary";

	/**
	 * Starts the table cleaner.
	 *
	 * @param string $tableName
	 * @param string $primaryKey
	 *
	 * @return \Recipe_Maintenance_TableCleaner
	 */
	public function __construct($tableName, $primaryKey = null)
	{
		$this->tableName = $tableName;
		$this->primaryKey = $primaryKey;
		$this->loadFields();
	}

	/**
	 * Loads the fields of a table.
	 *
	 * @return void
	 */
	protected function loadFields()
	{
		$result = Core::getQuery()->showFields($this->tableName);
		foreach($result->fetchAll() as $row)
		{
			array_push($this->fields, $row);
		}
		if(is_null($this->primaryKey))
		{
			$this->primaryKey = $this->fields[0]["Field"];
		}
		return;
	}

	/**
	 * Creates a buffer table to save the data temporarily.
	 *
	 * @return void
	 */
	protected function buildBufferTable()
	{
		$sql = "CREATE TABLE ".PREFIX.$this->tableName."_buffer (";
		foreach($this->fields as $field)
		{
			$sql .= "`".$field["Field"]."` ".$field["Type"]." ".(($field["Null"] == "YES") ? "NOT NULL" : "NULL")." ".(($field["Default"] != "") ? " default '".$field["Default"]."'" : "")." ".$field["Extra"].",\n";
		}
		$sql .= "PRIMARY KEY (`".$this->primaryKey."`)\n".
				") ENGINE=InnoDB DEFAULT CHARSET=".$this->defaultCharset." AUTO_INCREMENT=1";
		Core::getDB()->query($sql);
		return;
	}

	/**
	 * Clean the table by using a buffer table.
	 *
	 * @return void
	 */
	public function clean()
	{
		if(count($this->fields) <= 0)
		{
			return;
		}
		$this->buildBufferTable();
		$fieldList = array();
		foreach($this->fields as $field)
		{
			if($field["Field"] == $this->primaryKey) { continue; }
			array_push($fieldList, "`".$field["Field"]."`");
		}
		$fields = implode(", ", $fieldList);
		$sql = "INSERT INTO ".PREFIX.$this->tableName."_buffer (".$fields.") SELECT ".$fields." FROM ".PREFIX.$this->tableName." ORDER BY ".$this->primaryKey." ASC";
		Core::getDB()->query($sql);

		Core::getQuery()->drop($this->tableName);
		Core::getQuery()->rename($this->tableName."_buffer", $this->tableName);
		return;
	}

	/**
	 * Sets the default charset for the buffer table.
	 *
	 * @param string
	 *
	 * @return void
	 */
	public function setDefaultCharset($defaultCharset)
	{
		$this->defaultCharset = $defaultCharset;
		return;
	}
}
?>