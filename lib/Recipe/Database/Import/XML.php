<?php
/**
 * Executes a database XML file.
 *
 * @package Recipe 1.2
 * @author Sebastian Noll
 * @copyright Copyright (c) 2009, Sebastian Noll
 * @license Proprietary
 * @version $Id: XML.php 8 2010-10-17 20:55:04Z secretchampion $
 */

class Recipe_Database_Import_XML extends Recipe_Database_Import_Abstract
{
	/**
	 * Starts the import procedure.
	 *
	 * @return Recipe_Database_Import_XML
	 */
	public function import()
	{
		$this->data = new XMLObj($this->data);
		foreach($this->data->getChildren() as $table)
		{
			if($table->getName() == "create")
			{
				$this->createTable($table->getString());
			}
			else
			{
				if($table->getAttribute("action") != "")
				{
					$method = strval($table->getAttribute("action"));
					$this->$method($table->getName());
				}
				else
				{
					$fields = array(); $values = array();
					foreach($table->getChildren() as $field)
					{
						$fields[] = "`".$field->getName()."`";
						$values[] = ($field->getString() != "NULL") ? "'".$field->getString()."'" : "NULL";
					}
					$sql = "INSERT INTO `".$table->getName()."` (".implode(", ", $fields).") VALUES (".implode(", ", $values).")";
					try {
						Core::getDB()->query($sql);
					} catch(Exception $e) {
						$e->printError();
					}
				}
			}
		}
		return $this;
	}
}
?>