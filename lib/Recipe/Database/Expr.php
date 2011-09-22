<?php
/**
 * This class serves as recocnition for the SQL select object.
 * The holding string is marked as a SQL expression.
 *
 * @package Recipe 1.2
 * @author Sebastian Noll
 * @copyright Copyright (c) 2009, Sebastian Noll
 * @license Proprietary
 * @version $Id: Expr.php 8 2010-10-17 20:55:04Z secretchampion $
 */

class Recipe_Database_Expr
{
	/**
	 * Holds the expression.
	 *
	 * @var string
	 */
	protected $value = "";

	/**
	 * Creates a new SQL expression object.
     *
	 * @param string	Expression
	 *
	 * @return Recipe_Database_Expr
	 */
	public function __construct($value)
	{
		$this->value = (string) $value;
		return $this;
	}

	/**
	 * Sets and returns the expression.
	 *
	 * @param string	Method that has been called
	 * @param array		Given parameters
	 *
	 * @return string	Expression
	 */
	public function __call($func, $args = array())
	{
		if(Str::substring($func, 0, 3) == "set" && isset($args[0]))
		{
			$this->value = (string) $args[0];
		}
		return $this->value;
	}

	/**
	 * Every member variable returns the expression string.
	 *
	 * @param string	Igonored
	 *
	 * @return string	Expression
	 */
	public function __get($var)
	{
		return $this->value;
	}

	/**
	 * Every member variable sets the expression string.
	 *
	 * @param string	Ignored
	 * @param string	Expression
	 *
	 * @return string	Expression
	 */
	public function __set($var, $value)
	{
		$this->value = (string) $value;
		return $this->value;
	}

	/**
	 * Converts the object to a string.
	 *
	 * @return string	Expression
	 */
	public function __toString()
	{
		return $this->value;
	}
}
?>