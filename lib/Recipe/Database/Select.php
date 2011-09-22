<?php
/**
 * Generates SQL select statements.
 *
 * @package Recipe 1.2
 * @author Sebastian Noll
 * @copyright Copyright (c) 2009, Sebastian Noll
 * @license Proprietary
 * @version $Id: Select.php 8 2010-10-17 20:55:04Z secretchampion $
 */

class Recipe_Database_Select
{
	/**
	 * Holds the select elements,
	 *
	 * @var array|string
	 */
	protected	$where = array(),
				$having = array(),
				$limit = "",
				$joins = array(),
				$table = array(),
				$group = array(),
				$attrs = "",
				$order = array(),
				$distinct = false, $forUpdate = false;

	/**
	 * The actual SQL query string.
	 *
	 * @var string
	 */
	protected	$sql = "";

	/**
	 * Holds a constant SQL string.
	 *
	 * @var string
	 */
	protected $sqlString = "";

	/**
	 * Holds the raw attributes array.
	 *
	 * @var array
	 */
	protected $attributes = array();

	/**
	 * Creates a new select object
	 *
	 * @param Recipe_Database_Table|string	Table object [optional]
	 *
	 * @return Recipe_Database_Select
	 */
	public function __construct($arg = null)
	{
		if(!is_null($arg))
		{
			if($arg instanceof Recipe_Database_Table)
			{
				$this->initTableObj($arg);
			}
			else if(is_string($arg))
			{
				$this->setSqlString($arg);
			}
		}
		return $this;
	}

	/**
	 * Initilizes a table object.
	 *
	 * @param Recipe_Database_Table
	 *
	 * @return Recipe_Database_Select
	 */
	public function initTableObj(Recipe_Database_Table $tableObj)
	{
		$this->from($tableObj->getFrom());
		return $this;
	}

	/**
	 * Sets a constant SQL string.
	 *
	 * @param string
	 *
	 * @return Recipe_Database_Select
	 */
	public function setSqlString($sqlString)
	{
		$this->sqlString = $sqlString;
	}

	/**
	 * Assembles all select elements.
	 *
	 * @return Recipe_Database_Select
	 */
	public function render()
	{
		if(!empty($this->sqlString))
		{
			return $this;
		}
		if(empty($this->table))
		{
			throw new Recipe_Exception_Generic("The select statement has no attributes or table to fetch.", __FILE__, __LINE__);
		}
		if(empty($this->attrs))
		{
			$this->attrs = "*";
		}
		$this->sql = "SELECT ";
		if($this->distinct)
		{
			$this->sql .= "DISTINCT ";
		}
		else if($this->forUpdate)
		{
			$this->sql .= "FOR UPDATE ";
		}
		$this->sql .= $this->attrs." FROM ".implode(", ", $this->table);
		if(!empty($this->joins))
		{
			$this->sql .= " ".implode(" ", $this->joins);
		}
		if(!empty($this->where))
		{
			$where = "";
			foreach($this->where as $key => $value)
			{
				$where .= implode(" ".$key." ", $value);
			}
			$this->sql .= " WHERE ".$where;
		}
		if(!empty($this->group))
		{
			$this->sql .= " GROUP BY ".implode(", ", $this->group);
		}
		if(!empty($this->having))
		{
			$having = "";
			foreach($this->having as $key => $value)
			{
				$having .= implode(" ".$key." ", $value);
			}
			$this->sql .= " HAVING ".$having;
		}
		if(!empty($this->order))
		{
			$this->sql .= " ORDER BY ".implode(", ", $this->order);
		}
		if(!empty($this->limit))
		{
			$this->sql .= " LIMIT ".$this->limit;
		}
		return $this;
	}

	/**
	 * Executes the select query.
	 *
	 * @return resource
	 */
	public function getResource()
	{
		try {
			$resource = Core::getDB()->query($this->getSql());
		} catch(Recipe_Exception_Generic $e) {
			$e->printError();
		}
		return $resource;
	}

	/**
	 * Returns the SQL query string.
	 *
	 * @return string
	 */
	public function getSql()
	{
		if(!empty($this->sqlString))
		{
			return $this->sqlString;
		}
		if(empty($this->sql))
		{
			$this->render();
		}
		return $this->sql;
	}

	/**
	 * Returns the attributes array.
	 *
	 * @return array
	 */
	public function getAttributes()
	{
		return $this->attributes;
	}

	/**
	 * Adds a from table element.
	 *
	 * @param array|string	Table to select
	 * @param boolean		Prepend table prefix [optional]
	 *
	 * @return Recipe_Database_Select
	 */
	public function from($table, $prefix = true)
	{
		if(is_array($table))
		{
			$key = key($table);
			if($prefix)
			{
				$table[$key] = PREFIX.$table[$key];
			}
			$table = "`".$table[$key]."` AS ".$key;
		}
		else
		{
			if($prefix)
			{
				$table = PREFIX.$table;
			}
			$table = "`".$table."`";
		}
		$this->table[] = $table;
		return $this;
	}

	/**
	 * Adds a where clause.
	 *
	 * @param array|string	Where condition
	 * @param mixed			Value to check [optional]
	 * @param string		Link (AND, OR) [optional]
	 *
	 * @return Recipe_Database_Select
	 */
	public function where($condition, $value = null, $link = "AND")
	{
		$this->where[$link][] = $this->_condition($condition, $value);
		return $this;
	}

	/**
	 * Creates a condition term.
	 *
	 * @param array|string	Where condition
	 * @param mixed			Value to check [optional]
	 *
	 * @return string		SQL condition
	 */
	protected function _condition($condition, $value = null)
	{
		if(is_integer($value))
		{
			$value = (int) $value;
		}
		else if(is_null($value))
		{
			$value = "NULL";
		}
		else if($value != "NOT NULL")
		{
			$value = "'".$value."'";
		}
		if(is_array($condition))
		{
			$alias = key($condition);
			if(is_string($alias))
			{
				if($value === "NULL" || $value === "NOT NULL")
				{
					$condition[0] = "IS";
				}
				else if(empty($condition[0]))
				{
					$condition[0] = "=";
				}
				$condition = $alias.".`".$condition[$alias]."` ".$condition[0]." ?";
			}
			else
			{
				if($value === "NULL" || $value === "NOT NULL")
				{
					$condition[1] = "IS";
				}
				else if(empty($condition[1]))
				{
					$condition[1] = "=";
				}
				$condition = "`".$condition[$alias]."` ".$condition[1]." ?";
			}
		}

		return Str::replace("?", $value, $condition);
	}

	/**
	 * Sets the attributes to select
	 *
	 * @param array|string [optional]
	 *
	 * @return Recipe_Database_Select
	 */
	public function attributes($attr = array("*"))
	{
		if(!is_array($attr))
		{
			$attr = explode(",", $attr);
			$attr = Arr::trim($attr);
		}
		$this->attributes = $attr;
		$attrs = array();
		foreach($attr as $alias => $fields)
		{
			if(is_string($alias))
			{
				foreach($fields as $fieldAlias => $field)
				{
					if(!($field instanceof Recipe_Database_Expr))
					{
						$field = $alias.".`".$field."`";
					}
					if(is_string($fieldAlias))
					{
						$attrs[] = $field." AS ".$fieldAlias;
					}
					else
					{
						$attrs[] = strval($field);
					}
				}
			}
			else
			{
				$attrs[] = strval($fields);
			}
		}
		if(!empty($this->attrs))
		{
			$attrs[] = $this->attrs;
		}
		$this->attrs = implode(", ", $attrs);
		return $this;
	}

	/**
	 * Adds an order condition.
	 *
	 * @param string|array	Order condition
	 * @param string		Sorting (ASC or DESC) [optional]
	 *
	 * @return Recipe_Database_Select
	 */
	public function order($term, $sort = "ASC")
	{
		if(is_array($term))
		{
			$key = key($term);
			$term = $key.".`".$term[$key]."`";
		}
		else if($term instanceof Recipe_Database_Expr)
		{
			$term = strval($term);
		}
		else
		{
			$term = "`".strval($term)."`";
		}
		$this->order[] = $term." ".$sort;
		return $this;
	}

	/**
	 * Adds a limit condition.
	 *
	 * @param integer	Count result items
	 * @param integer	Offset result items [optional]
	 *
	 * @return Recipe_Database_Select
	 */
	public function limit($count = 1, $offset = null)
	{
		$count = (int) $count;
		$this->limit = (string) $count;
		if(!is_null($offset))
		{
			$offset = strval((int) $offset);
			$this->limit .= ", ".$offset;
		}
		return $this;
	}

	/**
	 * Adds a join to another table.
	 *
	 * @param array			The table name
	 * @param array|string	ON-condition
	 * @param boolean		Prepend table prefix [optional]
	 * @param string		Join type (LEFT, RIGHT, ...) [optional]
	 *
	 * @return Recipe_Database_Select
	 */
	public function join(array $table, $on, $prefix = true, $type = "LEFT")
	{
		$tableKey = key($table);
		if($prefix)
		{
			$table[$tableKey] = PREFIX.$table[$tableKey];
		}
		if(is_array($on))
		{
			$tableAlias = array_keys($on);
			$value = array();
			$value[0] = (is_string($tableAlias[0])) ? $tableAlias[0].".`".$on[$tableAlias[0]]."`" : "`".$on[0]."`";
			$value[1] = (is_string($tableAlias[1])) ? $tableAlias[1].".`".$on[$tableAlias[1]]."`" : "`".$on[1]."`";
			$onStatement = $value[0]." = ".$value[1];
			if(count($on) > 2)
			{
				array_shift($on);
				array_shift($on);
				foreach($on as $op => $term)
				{
					$condition = $this->_condition($term[0], $term[1]);
					$onStatement .= " ".$op." ".$condition;
				}
			}
		}
		else
		{
			$onStatement = $on;
		}
		$this->joins[] = $type." JOIN `".$table[$tableKey]."` AS ".$tableKey." ON (".$onStatement.")";
		return $this;
	}

	/**
	 * Adding a GROUP BY Clause.
	 *
	 * @param string|array	Columns
	 *
	 * @return Recipe_Database_Select
	 *
	 * @throws Recipe_Exception_Generic
	 */
	public function group($column)
	{
		if(is_string($column))
		{
			if(Str::inString(".", $column))
			{
				$this->group[] = $column;
			}
			else
			{
				$this->group[] = "`".$column."`";
			}
		}
		else if(is_array($column))
		{
			foreach($column as $key => $value)
			{
				$this->group[] = $key.".`".$value."`";
			}
		}
		else
		{
			throw new Recipe_Exception_Generic("The GROUP BY clause must be an array or string.");
		}
		return $this;
	}

	/**
	 * Adds DISTINCT modifier to the SQL query.
	 *
	 * @param boolean
	 *
	 * @return Recipe_Database_Select
	 */
	public function distinct($distinct = true)
	{
		$this->distinct = $distinct;
		return $this;
	}

	/**
	 * Adds am HAVING clause to the SQL query.
	 *
	 * @param string	Condition
	 * @param mixed		Value
	 * @param string	Link
	 *
	 * @return Recipe_Database_Select
	 */
	public function having($condition, $value, $link = "AND")
	{
		$condition = Str::replace("?", "'".$value."'", $condition);
		$this->having[$link][] = $condition;
	}

	/**
	 * Adds FOR UPDATE modifier to the SQL query.
	 *
	 * @param boolean
	 *
	 * @return Recipe_Database_Select
	 */
	public function forUpdate($forUpdate = true)
	{
		$this->forUpdate = $forUpdate;
		return $this;
	}

	/**
	 * Converts this object to a string.
	 *
	 * @return string
	 */
	public function __toString()
	{
		return $this->getSql();
	}
}
?>