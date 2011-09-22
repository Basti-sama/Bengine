<?php
/**
 * MSSQL database functions. !UNTESTED!
 *
 * @package Recipe 1.2
 * @author Sebastian Noll
 * @copyright Copyright (c) 2009, Sebastian Noll
 * @license Proprietary
 * @version $Id: MSSQL.php 8 2010-10-17 20:55:04Z secretchampion $
 */

class Recipe_Database_MSSQL extends Recipe_Database_Abstract
{
	/**
	 * Pointer on current database.
	 *
	 * @var resource
	 */
	protected $dbpointer = null;

	/**
	 * State of database selection.
	 *
	 * @var boolean;
	 */
	protected $dbselect = false;

	/**
	 * Last insert id.
	 *
	 * @var integer
	 */
	protected $insert_id = 0;

	/**
	 * Affected rows by the last UPDATE/DELETE query.
	 *
	 * @var integer
	 */
	protected $affected_rows = 0;

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
		try { $this->selectDatabase(); }
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
		mssql_close($this->dbpointer);
		return;
	}

	/**
	 * Establish database connection.
	 *
	 * @return void
	 */
	protected function connectToDatabase()
	{
		$this->dbpointer = @mssql_connect($this->host.($this->port) ? ":".$this->port : "", $this->user, $this->pw);
		if($this->dbpointer === false) { throw new Recipe_Exception_Generic("Could not connect to database: ".$this->host.($this->port) ? ":".$this->port : "");  }
		return;
	}

	/**
	 * Select the database which is to used.
	 *
	 * @return void
	 */
	protected function selectDatabase()
	{
		$this->dbselect = @mssql_select_db($this->database, $this->dbpointer);
		if($this->dbselect === false) { throw new Recipe_Exception_Generic("Could not select database \"".$this->database."\"."); }
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
		$this->query = @mssql_query($sql, $this->dbpointer);
		if($this->query === false)
		{
			throw new Recipe_Exception_Sql(mssql_get_last_message(), 0, $sql);
		}
		$this->queryCount++;

		// Get last insert id
		if(preg_match("/^INSERT /i", $sql))
		{
			$result = mssql_query("SELECT @@IDENTITY as id", $this->dbpointer);
			$row = mssql_fetch_assoc($result);
			$this->insert_id = $row["id"];
			unset($row); unset($result);
		}
		// Get affected rows
		else if(preg_match("/^(DELETE|UPDATE|REPLACE|INSERT) /i", $sql))
		{
			$result = mssql_query("SELECT @@ROWCOUNT as rows", $this->dbpointer);
			$row = mssql_fetch_assoc($result);
			$this->affected_rows = $row["rows"];
			unset($row); unset($result);
		}
		return $this->query;
	}

	/**
	 * Returns the row of a query as an object.
	 *
	 * @param resource	The SQL query id
	 *
	 * @return object	The data of a row
	 */
	public function fetch_object($query)
	{
		$this->result = mssql_fetch_object($query);
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
		$this->result = mssql_fetch_array($query);
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
		$this->result = mssql_fetch_assoc($query);
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
			mssql_data_seek($query, $row);
		}
		$this->result = mssql_fetch_field($query, $field);
		return $this->result;
	}

	/**
	 * Get a row as an enumerated array.
	 *
	 * @param resource The SQL query id
	 *
	 * @return array
	 */
	public function fetch_row($query)
	{
		$this->result = mssql_fetch_row($query);
		return $this->result;
	}

	/**
	 * Returns the total row numbers of a query.
	 *
	 * @param resource	The SQL query id
	 * @return integer	The total row number
	 */
	public function num_rows($query)
	{
		if($query)
		{
			return mssql_num_rows($query);
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
		return $this->affected_rows;
	}

	/**
	 * Returns the last inserted ID of a table.
	 *
	 * @return integer	The last inserted id
	 */
	public function insert_id()
	{
		return $this->insert_id;
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
		return addslashes($string);
	}

	/**
	 * Returns used MSSQL-Verions.
	 *
	 * @return string	MSSQL-Version
	 */
	public function getVersion()
	{
		// This function is unsupported for MSSQL
		return "";
	}

	/**
	 * Type of database.
	 *
	 * @return string
	 */
	public function getDatabaseType()
	{
		return "MSSQL";
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
		return mssql_data_seek($resource, 0);
	}

	/**
	 * Frees stored result memory for the given statement handle.
	 *
	 * @param resource	The statement to free
	 *
	 * @return boolean	True on success, false on failure
	 */
	public function free_result($resource)
	{
		return mssql_free_result($resource);
	}
}
?>