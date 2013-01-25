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
	 * @param string $table
	 * @param array $spec
	 * @return \Recipe_Database_Statement_Abstract
	 */
	public function insert($table, array $spec)
	{
		$attributes = array_keys($spec);
		$attributes = $this->setBackQuotes($attributes);
		$values = array_fill(0, count($spec), "?");
		$sql = "INSERT INTO `".PREFIX.$table."` (".implode(",", $attributes).") VALUES (".implode(",", $values).")";
		return $this->send($sql, $spec);
	}

	/**
	 * Generates an insert sql query.
	 *
	 * @param string $table
	 * @param array $spec
	 * @param string $where
	 * @param array $bind
	 * @return Recipe_Database_Statement_Abstract
	 */
	public function update($table, array $spec, $where = null, array $bind = null)
	{
		if($bind !== null)
		{
			$bind = $spec + $bind;
		}
		else
		{
			$bind = $spec;
		}
		foreach($spec as $attribute => &$value)
		{
			if(!($value instanceof Recipe_Database_Expr))
			{
				$value = "?";
			}
			else
			{
				unset($bind[$attribute]);
			}
			$value = "`".$attribute."` = ".$value;
		}
		$sql = "UPDATE `".PREFIX.$table."` SET ".implode(",", $spec);

		if($where !== null)
		{
			$sql .= " WHERE ".$where;
		}

		return $this->send($sql, $bind);
	}

	/**
	 * Sends an SQL query.
	 *
	 * @param string $sql
	 * @param array $bind
	 * @return Recipe_Database_Statement_Abstract
	 */
	protected function send($sql, array $bind = null)
	{
		$statement = null;
		$this->sql = $sql;
		if($this->send)
		{
			try {
				$statement = Core::getDatabase()->query($this->sql, $bind);
			} catch(Recipe_Exception_Sql $e) {
				$e->printError();
			}
		}
		$this->send = true;
		return $statement;
	}

	/**
	 * Generates a select query.
	 *
	 * @param string $table
	 * @param string|array $select
	 * @param string $join
	 * @param string $where
	 * @param string $order
	 * @param string $limit
	 * @param string $groupby
	 * @param string $other
	 * @param array $bind
	 *
	 * @return Recipe_Database_Statement_Abstract
	 */
	public function select($table, $select, $join = "", $where = "", $order = "", $limit = "", $groupby = "", $other = "", array $bind = null)
	{
		if(!is_array($select))
		{
			$select = Arr::trim(explode(",", $select));
		}

		if(!empty($join)) { $join = " ".$join; }
		if(!empty($where)) { $where = " WHERE ".$where; }
		if(!empty($groupby)) { $groupby = " GROUP BY ".$groupby; }
		if(!empty($order)) { $order = " ORDER BY ".$order; }
		if(!empty($limit)) { $limit = " LIMIT ".$limit; }
		$other = (!empty($other)) ? " ".$other : "";
		$select = implode(", ", $select);
		$sql = "SELECT ".$select." FROM ".PREFIX.$table.$join.$where.$groupby.$order.$limit.$other;
		return $this->send($sql, $bind);
	}

	/**
	 * Generates a delete query.
	 *
	 * @param string $table
	 * @param string $where
	 * @param string $order
	 * @param string $limit
	 * @param array $bind
	 * @return Recipe_Database_Statement_Abstract
	 */
	public function delete($table, $where = null, $order = null, $limit = null, array $bind = null)
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

		return $this->send($sql, $bind);
	}

	/**
	 * Empties a table completely.
	 *
	 * @param string $table
	 *
	 * @return Recipe_Database_Statement_Abstract
	 */
	public function truncate($table)
	{
		$table = $this->setBackQuotes(PREFIX.$table);
		return $this->send("TRUNCATE TABLE ".$table);
	}

	/**
	 * Reclaims the unused space of a database file.
	 *
	 * @param mixed $tables
	 *
	 * @return Recipe_Database_Statement_Abstract
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

		return $this->send("OPTIMIZE TABLE ".$table);
	}

	/**
	 * Removes one or more tables.
	 *
	 * @param mixed $tables
	 *
	 * @return Recipe_Database_Statement_Abstract
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
		return $this->send("DROP TABLE IF EXISTS ".$table);
	}

	/**
	 * Renames a table.
	 *
	 * @param string $table
	 * @param string $newname
	 *
	 * @return Recipe_Database_Statement_Abstract
	 */
	public function rename($table, $newname)
	{
		$table = $this->setBackQuotes(PREFIX.$table);
		$newname = $this->setBackQuotes(PREFIX.$newname);
		return $this->send("RENAME TABLE ".$table." TO ".$newname);
	}

	/**
	 * Returns information about the columns in the given table.
	 *
	 * @param string $table
	 *
	 * @return Recipe_Database_Statement_Abstract
	 */
	public function showFields($table)
	{
		$table = $this->setBackQuotes(PREFIX.$table);
		return $this->send("SHOW COLUMNS FROM ".$table);
	}

	/**
	 * Surrounds an array with simple quotes.
	 *
	 * @param array $data
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
}
?>