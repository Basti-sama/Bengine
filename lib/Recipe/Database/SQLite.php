<?php
/**
 * SQLite database functions. !UNTESTED!
 *
 * @package Recipe 1.2
 * @author Sebastian Noll
 * @copyright Copyright (c) 2009, Sebastian Noll
 * @license Proprietary
 * @version $Id: SQLite.php 8 2010-10-17 20:55:04Z secretchampion $
 */

class Recipe_Database_SQLite extends Recipe_Database_Abstract
{
	/**
	 * Pointer on current database.
	 *
	 * @var resource
	 */
	protected $dbpointer = null;

	/**
	 * Last occurred error id.
	 *
	 * @var integer
	 */
	protected $lasterror = 0;

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
		try { $this->connectToDatabase(); }
		catch(Exception $e) { $e->printError(); }
		return;
	}

	/**
	 * Close the last database.
	 *
	 * @return void
	 */
	protected function __destruct()
	{
		sqlite_close($this->dbpointer);
		return;
	}

	/**
	 * Establish database connection.
	 *
	 * @return void
	 */
	protected function connectToDatabase()
	{
		$error = "";
		$this->dbpointer = @sqlite_open($this->database, 0666, $error);
		if($this->dbpointer === false) { throw new Recipe_Exception_Generic("Could not connect to database: ".$error); }
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
		$this->query = @sqlite_query($this->dbpointer, $sql);
		if($this->getLastErrorNo())
		{
			throw new Recipe_Exception_Sql($this->getLastError(), $this->lasterror, $sql);
		}
		$this->queryCount++;
		return $this->query;
	}

	/**
	 * Returns the error code of the last error for a database.
	 *
	 * @return integer	Last error id
	 */
	protected function getLastErrorNo()
	{
		$this->lasterror = sqlite_last_error($this->dbpointer);
		return $this->lasterror;
	}

	/**
	 * Returns the textual description of an error code.
	 *
	 * @return string	Error description
	 */
	protected function getLastError()
	{
		return sqlite_error_string($this->lasterror);
	}

	/**
	 * Returns the row of a query as an object.
	 *
	 * @param resource	The SQL query id
	 * @return object	The data of a row
	 */
	public function fetch_object($query)
	{
		$this->result = sqlite_fetch_object($query);
		return $this->result;
	}

	/**
	 * Returns the row of a query as an array.
	 *
	 * @param resource	The SQL query id
	 *
	 * @return array	The data of a row
	 */
	public function fetch_array($query)
	{
		$this->result = sqlite_fetch_array($query);
		return $this->result;
	}

	/**
	 * Fetch a result row as an associative array.
	 *
	 * @param resource	The SQL query id
	 *
	 * @return array	The data of a row
	 */
	public function fetch($query)
	{
		$this->result = sqlite_fetch_array($query, SQLITE_ASSOC);
		return $this->result;
	}

	/**
	 * Returns the value from a result resource.
	 *
	 * @param resource	The SQL query id
	 * @param string	The column name to fetch
	 * @param integer	Row number in result to fetch
	 *
	 * @return string
	 */
	public function fetch_field($query, $field, $row = null)
	{
		if($row !== null)
		{
			sqlite_seek($query, $row);
		}
		$this->result = $this->fetch_array($query);
		return $this->result[$field];
	}

	/**
	 * Get a row as an enumerated array.
	 *
	 * @param resource	The SQL query id
	 *
	 * @return array
	 */
	public function fetch_row($query)
	{
		$this->result = $this->fetch_array($query);
		return $this->result;
	}

	/**
	 * Returns the total row numbers of a query.
	 *
	 * @param resource The SQL query id
	 *
	 * @return integer	The total row number
	 */
	public function num_rows($query)
	{
		if($query)
		{
			return sqlite_num_rows($query);
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
		return sqlite_changes($this->dbpointer);
	}

	/**
	 * Returns the last inserted ID of a table.
	 *
	 * @return integer	The last inserted id
	 */
	public function insert_id()
	{
		return sqlite_last_insert_rowid();
	}

	/**
	 * Escapes a string for a safe SQL query.
	 *
	 * @param string	The string that is to be escaped
	 *
	 * @return string	Returns the escaped string, or false on error
	 */
	public function real_escape_string($string)
	{
		return sqlite_escape_string($string, $this->dbpointer);
	}

	/**
	 * Returns used SQLite library verion.
	 *
	 * @return string	MySQL-Version
	 */
	public function getVersion()
	{
		return sqlite_libversion();
	}

	/**
	 * Type of database.
	 *
	 * @return string
	 */
	public function getDatabaseType()
	{
		return "SQLite";
	}

	/**
	 * Resets a result resource to row number 0.
	 *
	 * @param resource	Resource to reset
	 *
	 * @return void
	 */
	public function reset_resource(&$resource)
	{
		return sqlite_seek($resource, 0);
	}

	/**
	 * Serves no purposes except compatibility.
	 *
	 * @param resource	The statement to free
	 *
	 * @return boolean	Always false
	 */
	public function free_result($resource)
	{
		return false;
	}
}
?>