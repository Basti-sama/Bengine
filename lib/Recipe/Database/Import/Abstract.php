<?php
/**
 * Provides basic function for database import.
 *
 * @package Recipe 1.2
 * @author Sebastian Noll
 * @copyright Copyright (c) 2009, Sebastian Noll
 * @license Proprietary
 * @version $Id: Abstract.php 8 2010-10-17 20:55:04Z secretchampion $
 */

abstract class Recipe_Database_Import_Abstract
{
	/**
	 * Holds the import data.
	 *
	 * @var string
	 */
	protected $data;

	/**
	 * Force to implement the import method.
	 *
	 * @return Recipe_Database_Import_Abstract
	 */
	public abstract function import();

	/**
	 * Creates a new import object.
	 *
	 * @param string	Import data or file path
	 *
	 * @return void
	 */
	public function __construct($data)
	{
		$this->setData($data);
		return;
	}

	/**
	 * Sets the import data of file path.
	 *
	 * @param string
	 *
	 * @return Recipe_Database_Import_Abstract
	 */
	public function setData($data)
	{
		if(file_exists($data))
		{
			$data = file_get_contents($data);
		}
		$this->data = $data;
		return $this;
	}

	/**
	 * Creates the table schema.
	 *
	 * @param string	SQL query
	 *
	 * @return Recipe_Database_Import_Abstract
	 */
	protected function createTable($sql)
	{
		try {
			Core::getDB()->query($sql);
		} catch(Exception $e) {
			$e->printError();
		}
		return $this;
	}

	/**
	 * Truncates a table.
	 *
	 * @param string	Table name
	 *
	 * @return Recipe_Database_Import_Abstract
	 */
	protected function truncate($table)
	{
		Core::getDB()->query("TRUNCATE TABLE `".$table."`");
		return $this;
	}
}
?>