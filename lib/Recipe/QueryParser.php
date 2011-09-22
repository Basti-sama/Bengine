<?php
/**
 * Query parser. Processes queries for SQL access.
 *
 * @package Recipe 1.2
 * @author Sebastian Noll
 * @copyright Copyright (c) 2009, Sebastian Noll
 * @license Proprietary
 * @version $Id: QueryParser.php 46 2011-07-30 14:38:11Z secretchampion $
 */

class Recipe_QueryParser
{
	/**
	 * Last SQL query.
	 *
	 * @var string
	 */
	protected $sql = "";

	/**
	 * Disables auto-sending of a query.
	 *
	 * @var boolean
	 */
	protected $send = true;

	/**
	 * @ignore
	 */
	public function __construct() {}

	/**
	 * Generates an insert sql query.
	 *
	 * @param string	Table name to insert
	 * @param mixed		Attributes to insert
	 * @param mixed		Values to insert
	 *
	 * @return Recipe_QueryParser
	 *
	 * @deprecated		Use insertInto() instead
	 */
	public function insert($table, $attribute, $value)
	{
		if(!is_array($attribute))
			$attribute = array_map("trim", explode(",", $attribute));
		if(!is_array($value))
			$value = array_map("trim", explode(",", $value));
		$spec = array_combine($attribute, $value);
		$this->insertInto($table, $spec);
		return $this;
	}

	/**
	 * Generates an insert sql query.
	 *
	 * @param string	Table name to insert
	 * @param array		Attributes to insert
	 *
	 * @return Recipe_QueryParser
	 */
	public function insertInto($table, array $spec)
	{
		$attributes = array_keys($spec);
		$attributes = $this->setBackQuotes($attributes);
		$spec = $this->setSimpleQuotes($spec);
		$sql = "INSERT INTO `".PREFIX.$table."` (".implode(",", $attributes).") VALUES (".implode(",", $spec).")";
		$this->send($sql);
		return $this;
	}

	/**
	 * Generates an insert sql query.
	 *
	 * @param string	Table name to insert
	 * @param mixed		Attributes to insert
	 * @param mixed		Values to insert
	 * @param string	Where clauses
	 *
	 * @return Recipe_QueryParser
	 *
	 * @deprecated		Use updateSet() instead
	 */
	public function update($table, $attribute, $value, $where = null)
	{
		if(!is_array($attribute))
			$attribute = array_map("trim", explode(",", $attribute));
		if(!is_array($value))
			$value = array_map("trim", explode(",", $value));
		$spec = array_combine($attribute, $value);
		$this->updateSet($table, $spec, $where);
		return $this;
	}

	/**
	 * Generates an insert sql query.
	 *
	 * @param string	Table name to update
	 * @param array		Attributes to update
	 * @param string	Where clauses
	 *
	 * @return Recipe_QueryParser
	 */
	public function updateSet($table, array $spec, $where = null)
	{
		foreach($spec as $attr => &$value)
		{
			if($value === null)
			{
				$value = "NULL";
			}
			else if(!($value instanceof Recipe_Database_Expr))
			{
				$value = "'".$value."'";
			}
			$value = "`".$attr."` = ".$value;
		}

		$sql = "UPDATE `".PREFIX.$table."` SET ".implode(",", $spec);
		if($where !== null)
		{
			$sql .= " WHERE ".$where;
		}

		$this->send($sql);
		return $this;
	}

	/**
	 * Sends an SQL query.
	 *
	 * @param string	The SQL query
	 *
	 * @return Recipe_QueryParser
	 */
	protected function send($sql)
	{
		$this->sql = $sql;
		if($this->send)
		{
			try {
				return Core::getDatabase()->query($this->sql);
			} catch(Exception $e) {
				$e->printError();
			}
		}
		$this->send = true;
		return false;
	}

	/**
	 * Generates a select query.
	 *
	 * @param string	Table name to select
	 * @param mixed		Attributes to select
	 * @param string	Tables to join
	 * @param string	Where clauses
	 *
	 * @return resource	The SQL-Statement
	 */
	public function select($table, $select, $join = "", $where = "", $order = "", $limit = "", $groupby = "", $other = "")
	{
		if(!is_array($select))
		{
			$select = Arr::trimArray(explode(",", $select));
		}

		if(!empty($join)) { $join = " ".$join; }
		if(!empty($where)) { $where = " WHERE ".$where; }
		if(!empty($groupby)) { $groupby = " GROUP BY ".$groupby; }
		if(!empty($order)) { $order = " ORDER BY ".$order; }
		if(!empty($limit)) { $limit = " LIMIT ".$limit; }
		$other = (!empty($other)) ? " ".$other : "";
		$select = implode(", ", $select);

		$sql = "SELECT ".$select." FROM ".PREFIX.$table.$join.$where.$groupby.$order.$limit.$other;
		return $this->send($sql);
	}

	/**
	 * Generates a delete query.
	 *
	 * @param string	Table name to delete
	 * @param mixed		Where clauses
	 *
	 * @return Recipe_QueryParser
	 */
	public function delete($table, $where = null, $order = null, $limit = null)
	{
		$whereclause = "";
		$orderclause = "";
		$limitclause = "";
		if(!is_null($where) && Str::length($where) > 0)
		{
			$whereclause = " WHERE ".$where;
		}
		if(!is_null($order) && Str::length($order) > 0)
		{
			$orderclause = " ORDER BY ".$order;
		}
		if(!is_null($limit) && !empty($limit))
		{
			$limitclause = " LIMIT ".$limit;
		}

		$sql = "DELETE FROM ".PREFIX.$table.$whereclause.$orderclause.$limitclause;

		$this->send($sql);
		return $this;
	}

	/**
	 * Empties a table completely.
	 *
	 * @param string	Table to empty
	 *
	 * @return Recipe_QueryParser
	 */
	public function truncate($table)
	{
		$table = $this->setBackQuotes(PREFIX.$table);
		$this->send("TRUNCATE TABLE ".$table);
		return $this;
	}

	/**
	 * Reclaims the unused space of a database file.
	 *
	 * @param mixed		Tables to optimize
	 *
	 * @return Recipe_QueryParser
	 */
	public function optimize($tables)
	{
		if(is_array($tables))
		{
			$tables = $this->setPrefix($tables);
			$tables = $this->setBackQuotes($tables);
			$table = implode(", ", $tables);
		}
		else
		{
			$table = $this->setBackQuotes(PREFIX.$tables);
		}

		$this->send("OPTIMIZE TABLE ".$table);
		return $this;
	}

	/**
	 * Removes one or more tables.
	 *
	 * @param mixed		Tables
	 *
	 * @return Recipe_QueryParser
	 */
	public function drop($tables)
	{
		if(is_array($tables))
		{
			$tables = $this->setPrefix($tables);
			$tables = $this->setBackQuotes($tables);
			$table = implode(", ", $tables);
		}
		else
		{
			$table = $this->setBackQuotes(PREFIX.$tables);
		}
		$this->send("DROP TABLE IF EXISTS ".$table);
		return $this;
	}

	/**
	 * Renames a table.
	 *
	 * @param string	Table to rename
	 * @param string	New table name
	 *
	 * @return Recipe_QueryParser
	 */
	public function rename($table, $newname)
	{
		$table = $this->setBackQuotes(PREFIX.$table);
		$newname = $this->setBackQuotes(PREFIX.$newname);
		$this->send("RENAME TABLE ".$table." TO ".$newname);
		return $this;
	}

	/**
	 * Returns information about the columns in the given table.
	 *
	 * @param string	Table name
	 *
	 * @return resource	The SQL-Statement
	 */
	public function showFields($table)
	{
		$table = $this->setBackQuotes(PREFIX.$table);
		return $this->send("SHOW COLUMNS FROM ".$table);
	}

	/**
	 * Surrounds an array with simple quotes.
	 *
	 * @param array
	 *
	 * @return array
	 */
	protected function setSimpleQuotes($data)
	{
		if(is_array($data))
		{
			foreach($data as &$value)
			{
				if(!($value instanceof Recipe_Database_Expr))
				{
					$value = (is_null($value)) ? "NULL" : "'".$value."'";
				}
			}
			return $data;
		}
		return (is_null($data)) ? "NULL" : "'".$data."'";
	}

	/**
	 * Surrounds an array with back quotes.
	 *
	 * @param array
	 *
	 * @return array
	 */
	protected function setBackQuotes($data)
	{
		if(is_array($data))
		{
			foreach($data as &$value)
			{
				if(Str::substring($value, 0, 1) != "`")
				{
					$value = "`".$value."`";
				}
			}
			return $data;
		}
		if(Str::substring($data, 0, 1) == "`")
		{
			return $data;
		}
		return "`".$data."`";
	}

	/**
	 * Returns the last generated SQL query.
	 *
	 * @return string	Last SQL query
	 */
	public function getLastQuery()
	{
		return $this->sql;
	}

	/**
	 * Sets the prefix to an array of table names.
	 *
	 * @param array
	 *
	 * @return array
	 */
	protected function setPrefix($tables)
	{
		$size = count($tables);
		for($i = 0; $i < $size; $i++)
		{
			$tables[$i] = PREFIX.$tables[$i];
		}
		return $tables;
	}

	/**
	 * Sets whether the next generated query should be executed.
	 *
	 * @param boolean
	 *
	 * @return Recipe_QueryParser
	 */
	public function sendNextQuery($send)
	{
		$this->send = $send;
		return $this;
	}

	/**
	 * Executes the last query.
	 *
	 * @return Recipe_QueryParser
	 */
	public function executeLastQuery()
	{
		try {
			Core::getDB()->query($this->sql);
		}
		catch(Exception $e)
		{
			$e->printError();
		}
		return $this;
	}
}
?>