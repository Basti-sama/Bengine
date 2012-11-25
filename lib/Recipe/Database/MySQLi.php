<?php
/**
 * MySQLi database functions.
 *
 * @package Recipe 1.2
 * @author Sebastian Noll
 * @copyright Copyright (c) 2009, Sebastian Noll
 * @license Proprietary
 * @version $Id: MySQLi.php 8 2010-10-17 20:55:04Z secretchampion $
 */

class Recipe_Database_MySQLi extends Recipe_Database_Abstract
{
	/**
	 * MySQLi Object.
	 *
	 * @var mysqli
	 */
	public $mysqli = null;

	/**
	 * @return Recipe_Database_MySQLi
	 * @throws Recipe_Exception_Generic
	 */
	protected function connect()
	{
		try {
			$this->mysqli = new mysqli($this->host, $this->user, $this->password, $this->database, $this->port);
		} catch(Exception $e) {
			die("Unable to connect to database. Please try again later.");
		}
		if(mysqli_connect_errno())
		{
			throw new Recipe_Exception_Generic("Connection to database failed: ".mysqli_connect_error());
		}
		return $this;
	}

	/**
	 * @return Recipe_Database_MySQLi
	 */
	public function disconnect()
	{
		$this->mysqli->close();
		return $this;
	}

	/**
	 * @param Recipe_Database_Select|string $sql
	 * @param array $bind
	 * @return Recipe_Database_Statement_MySQLi
	 */
	public function query($sql, array $bind = null)
	{
		$statement = new Recipe_Database_Statement_MySQLi($sql, $this);
		$statement->execute($bind);
		$this->queryCount++;
		return $statement;
	}

	/**
	 * @return Recipe_Database_MySQLi
	 */
	public function beginTransaction()
	{
		$this->mysqli->autocommit(false);
		return $this;
	}

	/**
	 * @return Recipe_Database_MySQLi
	 */
	public function commit()
	{
		$this->mysqli->commit();
		$this->mysqli->autocommit(true);
		return $this;
	}

	/**
	 * @return Recipe_Database_MySQLi
	 */
	public function rollBack()
	{
		$this->mysqli->rollback();
		$this->mysqli->autocommit(true);
		return $this;
	}

	/**
	 * Returns the number of affected rows by the last query.
	 *
	 * @return integer
	 */
	public function affectedRows()
	{
		return (int) $this->mysqli->affected_rows;
	}

	/**
	 * Returns the last inserted id of a table.
	 *
	 * @param string $name
	 * @return integer
	 */
	public function lastInsertId($name = null)
	{
		return $this->mysqli->insert_id;
	}

	/**
	 * Returns used MySQL client version.
	 *
	 * @return string
	 */
	public function getClientVersion()
	{
		return $this->mysqli->get_client_info();
	}

	/**
	 * Returns used MySQL client version.
	 *
	 * @return string
	 */
	public function getServerVersion()
	{
		return $this->mysqli->server_info;
	}

	/**
	 * @return mysqli
	 */
	public function getConnection()
	{
		return $this->mysqli;
	}

	/**
	 * Type of database.
	 *
	 * @return string
	 */
	public function getDatabaseType()
	{
		return "MySQLi";
	}

	/**
	 * @param mixed $value
	 * @return mixed
	 */
	public function escape($value)
	{
		if(is_array($value))
		{
			return Arr::map(array($this, "escape"), $value);
		}
		return $this->mysqli->real_escape_string($value);
	}
}
?>