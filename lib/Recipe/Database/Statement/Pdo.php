<?php
/**
 * PDO statement database class.
 *
 * @package Recipe 1.3
 * @author Sebastian Noll
 * @copyright Copyright (c) 2012, Sebastian Noll
 * @license Proprietary
 */

class Recipe_Database_Statement_Pdo extends Recipe_Database_Statement_Abstract
{
	/**
	 * @var integer
	 */
	const DEFAULT_FETCH_TYPE = PDO::FETCH_ASSOC;

	/**
	 * @throws Recipe_Exception_Sql
	 * @return Recipe_Database_Statement_Pdo
	 */
	protected function prepare()
	{
		try {
			$this->statement = $this->getConnection()->prepare($this->getSql());
		} catch(PDOException $e) {
			throw new Recipe_Exception_Sql($e->getMessage(), $e->getCode(), $this->getSql());
		}
		return $this;
	}

	/**
	 * @param array $bind
	 * @return Recipe_Database_Statement_Pdo
	 * @throws Recipe_Exception_Sql
	 */
	public function execute(array $bind = null)
	{
		$statement = $this->statement;
		if($bind !== null)
		{
			$bind = array_values($bind);
		}
		if(!$statement->execute($bind))
		{
			$errorInfo = $statement->errorInfo();
			throw new Recipe_Exception_Sql($errorInfo[2], $errorInfo[1], $this->getSql());
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
		return $this->statement->fetchAll($fetchType);
	}

	/**
	 * @param integer $fetchType
	 * @return array|mixed
	 */
	public function fetchRow($fetchType = null)
	{
		$result = $this->fetchAll($fetchType);
		return isset($result[0]) ? $result[0] : null;
	}

	/**
	 * @param integer $column
	 * @return mixed
	 */
	public function fetchColumn($column = 0)
	{
		return $this->statement->fetchColumn($column);
	}

	/**
	 * @return integer
	 */
	public function rowCount()
	{
		return $this->statement->rowCount();
	}

	/**
	 * @return Recipe_Database_Statement_Pdo
	 */
	public function closeCursor()
	{
		$this->statement->closeCursor();
		return $this;
	}

	/**
	 * @return PDO
	 */
	public function getConnection()
	{
		return $this->getAdapter()->getConnection();
	}
}