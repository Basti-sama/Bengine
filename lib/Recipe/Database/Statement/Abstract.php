<?php
/**
 * Abstract statement database class.
 *
 * @package Recipe 1.3
 * @author Sebastian Noll
 * @copyright Copyright (c) 2012, Sebastian Noll
 * @license Proprietary
 */

abstract class Recipe_Database_Statement_Abstract implements IteratorAggregate
{
	/**
	 * @var mysqli_stmt|PDOStatement
	 */
	protected $statement = null;

	/**
	 * @var Recipe_Database_Abstract
	 */
	protected $adapter = null;

	/**
	 * @var string
	 */
	protected $sql = "";

	/**
	 * @param string|Recipe_Database_Select $sql
	 * @param Recipe_Database_Abstract $adapter
	 */
	public function __construct($sql, Recipe_Database_Abstract $adapter)
	{
		$this->setAdapter($adapter);
		if($sql instanceof Recipe_Database_Select)
		{
			$sql = $sql->getSql();
		}
		$this->sql = $sql;
		$this->prepare();
		return $this;
	}

	/**
	 * @return void
	 */
	public function __destruct()
	{
		$this->closeCursor();
	}

	/**
	 * @return Recipe_Database_Statement_Abstract
	 */
	protected abstract function prepare();

	/**
	 * @param array $bind
	 * @return boolean
	 */
	public abstract function execute(array $bind = null);

	/**
	 * @param integer $fetchType
	 * @return array
	 */
	public abstract function fetchAll($fetchType = null);

	/**
	 * @param integer $fetchType
	 * @return array
	 */
	public abstract function fetchRow($fetchType = null);

	/**
	 * @param integer $column
	 * @return mixed
	 */
	public abstract function fetchColumn($column = 0);

	/**
	 * @return integer
	 */
	public abstract function rowCount();

	/**
	 * @return Recipe_Database_Statement_Abstract
	 */
	public abstract function closeCursor();

	/**
	 * @param \Recipe_Database_Abstract $adapter
	 */
	public function setAdapter(Recipe_Database_Abstract $adapter)
	{
		$this->adapter = $adapter;
	}

	/**
	 * @return \Recipe_Database_Abstract
	 */
	public function getAdapter()
	{
		return $this->adapter;
	}

	/**
	 * @return \mysqli_stmt|\PDOStatement
	 */
	public function getStatement()
	{
		return $this->statement;
	}

	/**
	 * @return string
	 */
	public function getSql()
	{
		return $this->sql;
	}

	/**
	 * @return array|Traversable
	 */
	public function getIterator()
	{
		return new ArrayIterator($this->fetchAll());
	}
}