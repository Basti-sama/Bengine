<?php
/**
 * Abstract database class.
 *
 * @package Recipe 1.3
 * @author Sebastian Noll
 * @copyright Copyright (c) 2012, Sebastian Noll
 * @license Proprietary
 */

abstract class Recipe_Database_Abstract
{
	/**
	 * Host or IP address of database.
	 *
	 * @var mixed
	 */
	protected $host = "";

	/**
	 * Port of database server.
	 *
	 * @var string
	 */
	protected $port = null;

	/**
	 * User of database server.
	 *
	 * @var string
	 */
	protected $user = "";

	/**
	 * Password of database user.
	 *
	 * @var string
	 */
	protected $password = "";

	/**
	 * Name of the to used database.
	 *
	 * @var string
	 */
	protected $database = "";

	/**
	 * The total number of all queries.
	 *
	 * @var int
	 */
	protected $queryCount = 0;

	/**
	 * Constructor: Set database access data.
	 *
	 * @param string $host
	 * @param string $user
	 * @param string $password
	 * @param string $database
	 * @param integer $port
	 *
	 * @return \Recipe_Database_Abstract
	 */
	public function __construct($host, $user, $password, $database, $port = null)
	{
		$this->host = $host;
		$this->user = $user;
		$this->password = $password;
		$this->database = $database;
		$this->port = $port;
		$this->connect();
	}

	/**
	 * @param string|Recipe_Database_Select $sql
	 * @param array $bind
	 * @param integer $fetchType
	 * @return array
	 */
	public function fetchAll($sql, array $bind = null, $fetchType = null)
	{
		$statement = $this->query($sql, $bind);
		return $statement->fetchAll($fetchType);
	}

	/**
	 * @param string|Recipe_Database_Select $sql
	 * @param array $bind
	 * @param integer $fetchType
	 * @return array
	 */
	public function fetchRow($sql, array $bind = null, $fetchType = null)
	{
		$statement = $this->query($sql, $bind);
		return $statement->fetchRow($fetchType);
	}

	/**
	 * @param Recipe_Database_Select|string $sql
	 * @param array $bind
	 * @return integer
	 */
	public function rowCount($sql, array $bind = null)
	{
		return $this->query($sql, $bind)->rowCount();
	}

	/**
	 * @return Recipe_Database_Abstract
	 */
	abstract protected function connect();

	/**
	 * @param string|Recipe_Database_Select $sql
	 * @param array $bind
	 * @return Recipe_Database_Statement_Abstract
	 */
	abstract public function query($sql, array $bind = null);

	/**
	 * @return Recipe_Database_Abstract
	 */
	abstract public function beginTransaction();

	/**
	 * @return Recipe_Database_Abstract
	 */
	abstract public function commit();

	/**
	 * @return Recipe_Database_Abstract
	 */
	abstract public function rollBack();

	/**
	 * @param string $name
	 * @return integer
	 */
	abstract public function lastInsertId($name = null);

	/**
	 * @return integer
	 */
	abstract public function affectedRows();

	/**
	 * @return mixed
	 */
	abstract public function getConnection();

	/**
	 * @return Recipe_Database_Abstract
	 */
	abstract protected function disconnect();

	/**
	 * @return string
	 */
	abstract public function getClientVersion();

	/**
	 * @return string
	 */
	abstract public function getServerVersion();

	/**
	 * @return mixed
	 */
	abstract public function getDatabaseType();

	/**
	 * @param mixed $value
	 * @return string
	 */
	abstract public function quote($value);

	/**
	 * @param string $text
	 * @param mixed $value
	 * @return string
	 */
	abstract public function quoteInto($text, $value);

	/**
	 * @param array|string|mixed $value
	 * @return mixed
	 */
	abstract public function escape($value);

	/**
	 * Returns the total number of all queries in a script.
	 *
	 * @return integer	The total number of queries
	 */
	public function getQueryNumber()
	{
		return $this->queryCount;
	}

	/**
	 * Returns all available tables from the current selected database.
	 *
	 * @return array
	 */
	public function getTables()
	{
		$tables = array();
		foreach($this->fetchAll("SHOW TABLES FROM `".$this->database."`") as $table)
		{
			$tables[] = $table["Tables_in_".$this->database];
		}
		return $tables;
	}

	/**
	 * Returns an array with all database configuration variables.
	 *
	 * @return array
	 */
	public function getVariables()
	{
		$vars = array();
		foreach($this->fetchAll("SHOW VARIABLES") as $row)
		{
			$vars[$row["Variable_name"]] = $row["Value"];
		}
		return $vars;
	}
}

?>