<?php
/**
 * Abstract pdo database class.
 *
 * @package Recipe 1.3
 * @author Sebastian Noll
 * @copyright Copyright (c) 2012, Sebastian Noll
 * @license Proprietary
 */

abstract class Recipe_Database_Pdo_Abstract extends Recipe_Database_Abstract
{
	/**
	 * @var PDO
	 */
	protected $pdo = null;

	/**
	 * @var array
	 */
	protected $pdoConnectionOptions = array(
		PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
	);

	/**
	 * @var string
	 */
	protected $driver = null;

	/**
	 * @var Recipe_Database_Statement_Pdo
	 */
	protected $lastStatement = null;

	/**
	 * @return Recipe_Database_Pdo_Abstract
	 * @throws Exception
	 */
	protected function connect()
	{
		if($this->driver === null)
		{
			die("No PDO driver detected.");
		}
		if(!in_array($this->driver, PDO::getAvailableDrivers()))
		{
			die("PDO driver '{$this->driver}' not installed.");
		}
		$dsn = $this->driver.":host=".$this->host.";dbname=".$this->database.";charset=UTF8";
		if($this->port !== null)
		{
			$dsn .= ";port=".$this->port;
		}
		try {
			$this->pdo = new PDO($dsn, $this->user, $this->password, $this->pdoConnectionOptions);
		} catch(PDOException $e) {
			die($e->getMessage());
		}
		return $this;
	}

	/**
	 * @return Recipe_Database_Pdo_Abstract
	 */
	protected function disconnect()
	{
		$this->pdo = null;
		return $this;
	}

	/**
	 * @param string|Recipe_Database_Select $sql
	 * @param array $bind
	 * @throws Recipe_Exception_Sql
	 * @return Recipe_Database_Statement_Pdo
	 */
	public function query($sql, array $bind = null)
	{
		$statement = new Recipe_Database_Statement_Pdo($sql, $this);
		$statement->execute($bind);
		$this->lastStatement = $statement;
		$this->queryCount++;
		return $statement;
	}

	/**
	 * @return Recipe_Database_Pdo_Abstract
	 */
	public function beginTransaction()
	{
		$this->pdo->beginTransaction();
		return $this;
	}

	/**
	 * @return Recipe_Database_Pdo_Abstract
	 */
	public function commit()
	{
		$this->pdo->commit();
		return $this;
	}

	/**
	 * @return Recipe_Database_Pdo_Abstract
	 */
	public function rollBack()
	{
		$this->pdo->rollBack();
		return $this;
	}

	/**
	 * @param string $name
	 * @return mixed
	 */
	public function lastInsertId($name = null)
	{
		return $this->pdo->lastInsertId($name);
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
	 * @return integer
	 */
	public function affectedRows()
	{
		return $this->lastStatement->rowCount();
	}

	/**
	 * @return PDO
	 */
	public function getConnection()
	{
		return $this->pdo;
	}

	/**
	 * @return string
	 */
	public function getClientVersion()
	{
		return $this->getConnection()->getAttribute(PDO::ATTR_CLIENT_VERSION);
	}

	/**
	 * @return string
	 */
	public function getServerVersion()
	{
		return $this->getConnection()->getAttribute(PDO::ATTR_SERVER_VERSION);
	}

	/**
	 * @return string
	 */
	public function getDatabaseType()
	{
		return "PDO (".$this->getConnection()->getAttribute(PDO::ATTR_DRIVER_NAME).")";
	}

	/**
	 * @param mixed $value
	 * @return string
	 */
	public function quote($value)
	{
		return $this->pdo->quote($value);
	}

	/**
	 * @param string $text
	 * @param mixed $value
	 * @throws Recipe_Exception_Generic
	 * @return string
	 */
	public function quoteInto($text, $value)
	{
		if(is_array($value))
		{
			foreach($value as $val)
			{
				$pos = strpos($text, "?");
				if($pos === false)
				{
					throw new Recipe_Exception_Generic("Missing placeholder for quoted text.");
				}
				$text = substr_replace($text, $this->pdo->quote($val), $pos, 1);
			}
			return $text;
		}
		return str_replace("?", $this->pdo->quote($value), $text);
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
		return substr($this->pdo->quote($value), 1, -1);
	}
}

?>