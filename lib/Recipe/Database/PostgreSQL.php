<?php
/**
 * PostgreSQL database functions. !UNTESTED!
 *
 * @package Recipe 1.2
 * @author Sebastian Noll
 * @copyright Copyright (c) 2009, Sebastian Noll
 * @license Proprietary
 * @version $Id: PostgreSQL.php 8 2010-10-17 20:55:04Z secretchampion $
 */

class Recipe_Database_PostgreSQL extends Recipe_Database_Abstract
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
	 * Close current database connection.
	 *
	 * @return void
	 */
	public function __destruct()
	{
		pg_close($this->dbpointer);
		return;
	}

	/**
	 * Establish database connection.
	 *
	 * @return void
	 */
	protected function connectToDatabase()
	{
		$this->dbpointer = @pg_connect("host=".$this->host." port=".$this->port." dbname=".$this->database." user=".$this->user." password=".$this->pw);;
		if($this->dbpointer === false) { throw new Recipe_Exception_Generic("Could not connect to database: ".pg_last_error()); }
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
		$this->query = @pg_query($this->dbpointer, $sql);
		if($this->query === false)
		{
			throw new Recipe_Exception_Sql(pg_result_error($this->query), 0, $sql);
		}
		$this->queryCount++;
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
		$this->result = pg_fetch_object($query);
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
		$this->result = pg_fetch_array($query);
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
		$this->result = pg_fetch_assoc($query);
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
		$this->result = ($row !== null) ? pg_fetch_result($query, $field) : pg_fetch_result($query, $row, $field);
		return $this->result;
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
		$this->result = pg_fetch_row($query);
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
			return pg_num_rows($query);
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
		return pg_affected_rows($this->query);
	}

	/**
	 * Returns the last inserted ID of a table.
	 *
	 * @return integer	The last inserted id
	 */
	public function insert_id()
	{
		$this->query = str_replace(array("\r", "\n", "\t"), "", $this->query);
		preg_match("#INSERT INTO ([a-zA-Z0-9_\-]+)#i", $this->query, $matches);

		$table = $matches[1];

		$result = $this->query("SELECT column_name FROM information_schema.constraint_column_usage WHERE table_name = '".$table."' and constraint_name = '".$table."_pkey' LIMIT 1");
		$field = $this->fetch_field($result, "column_name");
		if(!$field) { return false; }

		$result = $this->query("SELECT currval('".$table."_".$field."_seq') AS last_value");
		return $this->fetch_field($result, "last_value");
	}

	/**
	 * Escapes a string for a safe sql query.
	 *
	 * @param string	The string that is to be escaped
	 *
	 * @return string	Returns the escaped string, or false on error
	 */
	public function real_escape_string($string)
	{
		return pg_escape_string($string);
	}

	/**
	 * Returns used PostgreSQL-Verions.
	 *
	 * @return string	PostgreSQL-Version
	 */
	public function getVersion()
	{

		return pg_parameter_status($this->dbpointer, "server_version");
	}

	/**
	 * Type of database.
	 *
	 * @return string
	 */
	public function getDatabaseType()
	{
		return "PostgreSQL";
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
		return pg_result_seek($resource, 0);
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
		return pg_free_result($resource);
	}
}
?>