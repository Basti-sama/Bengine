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
	 * @var integer
	 */
	const DEFAULT_FETCH_TYPE = MYSQLI_ASSOC;

	/**
	 * @return Recipe_Database_Statement_MySQLi
	 */
	protected function prepare()
	{
		$this->statement = $this->getConnection()->prepare($this->getSql());
		return $this;
	}

	/**
	 * @param array $bind
	 * @return Recipe_Database_Statement_MySQLi
	 * @throws Recipe_Exception_Sql
	 */
	public function execute(array $bind = null)
	{
		$statement = $this->statement;
		$connection = $this->getConnection();
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
			throw new Recipe_Exception_Sql($connection->error, $connection->errno, $this->getSql());
		}
		return $this;
	}

	/**
	 * @param integer $fetchType
	 * @return array|mixed
	 */
	public function fetchAll($fetchType = null)
	{
		if($fetchType === null)
		{
			$fetchType = self::DEFAULT_FETCH_TYPE;
		}
		/* @var mysqli_result $result */
		$result = $this->statement->get_result();
		return $result->fetch_all($fetchType);
	}

	/**
	 * @param integer $fetchType
	 * @return array|mixed
	 */
	public function fetchRow($fetchType = null)
	{
		if($fetchType === null)
		{
			$fetchType = self::DEFAULT_FETCH_TYPE;
		}
		/* @var mysqli_result $result */
		$result = $this->statement->get_result();
		return $result->fetch_array($fetchType);
	}

	/**
	 * @param integer $column
	 * @return mixed
	 */
	public function fetchColumn($column = 0)
	{
		/* @var mysqli_result $result */
		$result = $this->statement->get_result();
		$data = $result->fetch_array(MYSQLI_NUM);
		return $data[$column];
	}

	/**
	 * @return integer
	 */
	public function rowCount()
	{
		return $this->statement->num_rows;
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