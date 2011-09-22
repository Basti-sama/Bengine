<?php
/**
 * Abstract database class.
 *
 * @package Recipe 1.2
 * @author Sebastian Noll
 * @copyright Copyright (c) 2009, Sebastian Noll
 * @license Proprietary
 * @version $Id: Abstract.php 8 2010-10-17 20:55:04Z secretchampion $
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
	protected $pw = "";

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
	 * Last executed query.
	 *
	 * @var resource
	 */
	protected $query = null;

	/**
	 * Last result set.
	 *
	 * @var mixed
	 */
	protected $result = null;

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
	public function __construct($host, $user, $pw, $db, $port)
	{
		$this->host = $host;
		$this->user = $user;
		$this->pw = $pw;
		$this->database = $db;
		$this->port = $port;
		return;
	}

	abstract function query($sql);
	abstract function fetch_object($resource);
	abstract function fetch_array($resource);
	abstract function fetch($resource); // fetch_assoc
	abstract function fetch_row($resource);
	abstract function fetch_field($resource, $field, $row = null);
	abstract function num_rows($sql);
	abstract function affected_rows();
	abstract function insert_id();
	abstract function __destruct();
	abstract function getVersion();
	abstract function getDatabaseType();
	abstract function free_result($resource);

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
	 * Returns an unique database row.
	 *
	 * @param string	The SQL query
	 *
	 * @return array	One database row
	 */
	public function query_unique($sql)
	{
		try { $result = $this->query($sql); }
		catch(Exception $e) { $e->printError(); }
		if($row = $this->fetch($result)) { return $row; }
		return false;
	}

	/**
	 * Returns all available tablse from the current selected database.
	 *
	 * @return array	Tables
	 */
	public function getTables()
	{
		$tables = array();
		$result = $this->query("SHOW TABLES FROM `".$this->database."`");
		while($row = $this->fetch_array($result))
		{
			array_push($tables, $row[0]);
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
		$result = $this->query("SHOW VARIABLES");
		while($row = Core::getDB()->fetch($result))
		{
			$vars[$row["Variable_name"]] = $row["Value"];
		}
		return $vars;
	}
}

?>