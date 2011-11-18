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
	 * Constructor: Set database access data.
	 *
	 * @param string	The database host
	 * @param string	The database username
	 * @param string	The database password
	 * @param string	The database name
	 * @param integer	The database port
	 *
	 * @return void
	 */
	public function __construct($host, $user, $pw, $db, $port = null)
	{
		parent::__construct($host, $user, $pw, $db, $port);
		try { $this->init(); }
		catch(Exception $e) { $e->printError(); }
		return;
	}

	/**
	 * Close current database connection.
	 *
	 * @return void
	 */
	public function __destruct()
	{
		if(mysqli_connect_errno()) { return; }
		$this->mysqli->close();
		return;
	}

	/**
	 * Establish database connection and select database to use.
	 *
	 * @return void
	 */
	protected function init()
	{
		try {
			$this->mysqli = new mysqli($this->host, $this->user, $this->pw, $this->database, $this->port);
		} catch(Exception $e) {
			die("Unable to connect to database. Please try again later.");
		}
		if(mysqli_connect_errno())
		{
			throw new Recipe_Exception_Generic("Connection to database failed: ".mysqli_connect_error());
		}
		return;
	}

	/**
	 * Purpose a query on selected database.
	 *
	 * @param string	The SQL query
	 *
	 * @return resource	Results of the query
	 *
	 * @throws Recipe_Exception_Sql
	 */
	public function query($sql)
	{
		if($result = $this->mysqli->query($sql))
		{
			$this->queryCount++;
			return $result;
		}
		else
		{
			throw new Recipe_Exception_Sql($this->mysqli->error, $this->mysqli->errno, $sql);
		}
		return;
	}

	/**
	 * Returns the row of a query as an object.
	 *
	 * @param resource	The SQL query id
	 *
	 * @return object	The data of a row
	 */
	public function fetch_object($result)
	{
		return $result->fetch_object();
	}

	/**
	 * Returns the row of a query as an array.
	 *
	 * @param resource	The SQL query id
	 *
	 * @return array	The data of a row
	 */
	public function fetch_array($result)
	{
		return $result->fetch_array();
	}

	/**
	 * Fetch a result row as an associative array.
	 *
	 * @param resource	The SQL query id
	 *
	 * @return array	The data of a row
	 */
	public function fetch($result)
	{
		return $result->fetch_assoc();
	}

	/**
	 * Returns the value from a result resource.
	 *
	 * @param resource	The SQL query id
	 * @param string	The column name to fetch
	 * @param integer	Row number in result to fetch
	 *
	 * @return mixed
	 */
	public function fetch_field($result, $field, $row = null)
	{
		if($row !== null)
		{
			$result->data_seek($row);
		}
		$this->result = $this->fetch($result);
		return $this->result[$field];
	}

	/**
	 * Get a row as an enumerated array.
	 *
	 * @param resource	The SQL query id
	 *
	 * @return array
	 */
	public function fetch_row($result)
	{
		$this->result = $result->fetch_row();
		return $this->result;
	}

	/**
	 * Returns the total row numbers of a query.
	 *
	 * @param resource	The SQL query id
	 *
	 * @return integer	The total row number
	 */
	public function num_rows($query)
	{
		if($query)
		{
			return $query->num_rows;
		}
		return 0;
	}

	/**
	 * Returns the number of affected rows by the last query.
	 *
	 * @return integer	Affected rows
	 */
	public function affected_rows()
	{
		$affected_rows = $this->mysqli->affected_rows;
		if($affected_rows < 0) { $affected_rows = 0; }
		return $affected_rows;
	}

	/**
	 * Returns the last inserted id of a table.
	 *
	 * @return integer	The last inserted id
	 */
	public function insert_id()
	{
		return $this->mysqli->insert_id;
	}

	/**
	 * Escapes a string for a safe SQL query.
	 *
	 * @param string The string that is to be escaped.
	 *
	 * @return string Returns the escaped string, or false on error.
	 */
	public function real_escape_string($string)
	{
		return $this->mysqli->real_escape_string($string);
	}

	/**
	 * Returns used MySQL-Verions.
	 *
	 * @return string	MySQL-Version
	 */
	public function getVersion()
	{
		return $this->mysqli->get_client_info();
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
	 * Resets a mysql resource to row number 0.
	 *
	 * @param resource	Resource to reset
	 *
	 * @return void
	 */
	public function reset_resource($result)
	{
		return $result->data_seek(0);
	}

	/**
	 * Frees stored result memory for the given statement handle.
	 *
	 * @param resource	The statement to free
	 *
	 * @return void
	 */
	public function free_result($resource)
	{
		return $resource->free_result();
	}
}
?>