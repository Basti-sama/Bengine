<?php
/**
 * Generates a database dump as XML.
 *
 * @package Recipe 1.2
 * @author Sebastian Noll
 * @copyright Copyright (c) 2009, Sebastian Noll
 * @license Proprietary
 * @version $Id: XML.php 8 2010-10-17 20:55:04Z secretchampion $
 */

class Recipe_Database_Export_XML extends Recipe_Database_Export_Abstract
{
	/**
	 * Starts the export procedure.
	 *
	 * @return Recipe_Database_Export_Abstract
	 */
	public function export()
	{
		$this->prepareExport();
		$this->data  = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
		$this->data .= "<database dump=\"".Date::timeToString(1, TIME)."\">\n";
		foreach($this->tables as $table)
		{
			if($this->showStructure)
			{
				$this->data .= $this->getTableStructure($table);
			}
			if(isset($this->actions[$table]))
			{
				$this->data .= "\t<".$table." action=\"".$this->actions[$table]."\"/>\n";
			}
			if($this->showData)
			{
				$result = Core::getDB()->query("SELECT * FROM ".$table);
				while($row = Core::getDB()->fetch($result))
				{
					$this->data .= "\t<".$table.">\n";
					$this->data .= $this->getRowAsXML($row, $table);
					$this->data .= "\t</".$table.">\n";
				}
			}
		}
		$this->data .= "</database>";
		return $this;
	}

	/**
	 * Creates the XML string for a data row.
	 *
	 * @param array		Row data
	 * @param string	Table name
	 *
	 * @return string
	 */
	protected function getRowAsXML($row, $table)
	{
		$data = "";
		foreach($row as $key => $value)
		{
			if(is_null($value))
			{
				$value = "NULL";
			}
			$data .= "\t\t<".$key."><![CDATA[".$value."]]></".$key.">\n";
		}
		return $data;
	}

	/**
	 * Generates the XML string for a CREATE statement.
	 *
	 * @param string	Table name
	 *
	 * @return string	CREATE statement
	 */
	protected function getTableStructure($table)
	{
		return "\t<create><![CDATA[".parent::getTableStructure($table)."]]></create>\n";
	}
}
?>