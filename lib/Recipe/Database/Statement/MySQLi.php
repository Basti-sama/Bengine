<?php
/**
 * MySQLi statement database class.
 *
 * @package Recipe 1.3
 * @author Sebastian Noll
 * @copyright Copyright (c) 2012, Sebastian Noll
 * @license Proprietary
 */

class Recipe_Database_Statement_MySQLi extends Recipe_Database_Statement_Abstract
{
	/**
	 * @var mysqli_result
	 */
	protected $result;

	/**
	 * @var integer
	 */
	const DEFAULT_FETCH_TYPE = MYSQLI_ASSOC;

	/**
	 * @throws Recipe_Exception_Sql
	 * @return Recipe_Database_Statement_MySQLi
	 */
	protected function prepare()
	{
		$connection = $this->getConnection();
		$this->statement = $connection->stmt_init();
		if(!$this->statement->prepare($this->getSql()))
		{
			throw new Recipe_Exception_Sql($connection->error, $connection->errno, $this->getSql());
		}
		return $this;
	}

	/**
	 * @param array $bind
	 * @throws Recipe_Exception_Generic
	 * @throws Recipe_Exception_Sql
	 * @return Recipe_Database_Statement_MySQLi
	 */
	public function execute(array $bind = null)
	{
		$statement = $this->statement;
		if($bind !== null)
		{
			$params = array_values($bind);
			array_unshift($params, str_repeat("s", count($params)));
			$stmtParams = array();
			foreach($params as $k => &$value)
			{
				$stmtParams[$k] = &$value;
			}
			call_user_func_array(array($statement, "bind_param"), $params);
		}
		if(!$statement->execute())
		{
			$connection = $this->getConnection();
			throw new Recipe_Exception_Sql($connection->error, $connection->errno, $this->getSql());
		}
		if(!method_exists($statement, "get_result"))
		{
			throw new Recipe_Exception_Generic("Your MySQLi driver is unsupported. Consider switching to PDO.");
		}
		$this->result = $statement->get_result();
		return $this;
	}

	/**
	 * @param integer $fetchType
	 * @throws Recipe_Exception_Sql
	 * @return array|mixed
	 */
	public function fetchAll($fetchType = null)
	{
		if($fetchType === null)
		{
			$fetchType = self::DEFAULT_FETCH_TYPE;
		}
		return $this->result->fetch_all($fetchType);
	}

	/**
	 * @param integer $fetchType
	 * @throws Recipe_Exception_Sql
	 * @return array|mixed
	 */
	public function fetchRow($fetchType = null)
	{
		if($fetchType === null)
		{
			$fetchType = self::DEFAULT_FETCH_TYPE;
		}
		return $this->result->fetch_array($fetchType);
	}

	/**
	 * @param integer $column
	 * @throws Recipe_Exception_Sql
	 * @return mixed
	 */
	public function fetchColumn($column = 0)
	{
		$data = $this->result->fetch_array(MYSQLI_NUM);
		return $data[$column];
	}

	/**
	 * @return integer
	 */
	public function rowCount()
	{
		return $this->result->num_rows;
	}

	/**
	 * @return Recipe_Database_Statement_MySQLi
	 */
	public function closeCursor()
	{
		$this->statement->free_result();
		return $this;
	}

	/**
	 * @return mysqli
	 */
	public function getConnection()
	{
		return $this->getAdapter()->getConnection();
	}
}