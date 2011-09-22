<?php
/**
 * Object-oriented typing: Integer
 *
 * @package Recipe 1.2
 * @author Sebastian Noll
 * @copyright Copyright (c) 2009, Sebastian Noll
 * @license Proprietary
 * @version $Id: Integer.util.php 8 2010-10-17 20:55:04Z secretchampion $
 */

class Integer extends Type
{
	/**
	 * This is the actual integer value.
	 *
	 * @var integer
	 */
	protected $integer = 0;

	/**
	 * Initializes a newly created Integer object.
	 *
	 * @param integer
	 *
	 * @return void
	 */
	public function __construct($integer)
	{
		$this->integer = (int) $integer;
		return;
	}

	/**
	 * Returns the integer value.
	 *
	 * @return integer
	 */
	public function get()
	{
		return $this->integer;
	}

	/**
	 * Overwrites this integer.
	 *
	 * @param integer
	 *
	 * @return Integer
	 */
	public function set($integer)
	{
		$this->integer = (int) $integer;
		return $this;
	}

	/**
	 * Performs an addition.
	 *
	 * @param mixed Summand
	 *
	 * @return Integer
	 */
	public function add($summand)
	{
		$number = $this->getFromArgument($summand);
		$this->integer += $number->get();
		$this->integer = (int) $this->integer;
		return $this;
	}

	/**
	 * Performs a Subtraction.
	 *
	 * @param mixed Subtrahend
	 *
	 * @return Integer
	 */
	public function subtract($subtrahend)
	{
		$number = $this->getFromArgument($subtrahend);
		$this->integer -= $number->get();
		$this->integer = (int) $this->integer;
		return $this;
	}

	/**
	 * Performs a multiplication.
	 *
	 * @param mixed Multiplier
	 *
	 * @return Integer
	 */
	public function multiply($multiplier)
	{
		$number = $this->getFromArgument($multiplier);
		$this->integer *= $number->get();
		$this->integer = (int) $this->integer;
		return $this;
	}

	/**
	 * Performs a division.
	 *
	 * @param mxied Divisior
	 *
	 * @return Integer
	 */
	public function divide($divisor)
	{
		$number = $this->getFromArgument($divisor);
		if($number->get() == 0)
		{
			throw new Recipe_Exception_Issue("Division by zero.");
		}
		$this->integer = (int) round($this->integer / $number->get());
		return $this;
	}

	/**
	 * Performs an Exponentiation.
	 *
	 * @param mixed Exponent expression
	 *
	 * @return Integer
	 */
	public function expo($expression)
	{
		$number = $this->getFromArgument($expression);
		$this->integer = pow($this->integer, $number->get());
		$this->integer = (int) $this->integer;
		return $this;
	}

	/**
	 * Sets the absolute value.
	 *
	 * @return Integer
	 */
	public function absolute()
	{
		$this->integer = abs($this->integer);
		return $this;
	}

	/**
	 * Converts the integer value to a string.
	 *
	 * @return string
	 */
	public function toString($decPoint = ".", $thousandsSep = ",")
	{
		return number_format($this->integer, 0, $decPoint, $thousandsSep);
	}

	/**
	 * Returns the integer as a String.
	 *
	 * @return String
	 */
	public function getString($decPoint = ".", $thousandsSep = ",")
	{
		return new String($this->toString($decPoint, $thousandsSep));
	}

	/**
	 * Converts the integer value to a boolean.
	 *
	 * @return boolean
	 */
	public function toBoolean()
	{
		if($this->integer > 0)
		{
			return true;
		}
		return false;
	}

	/**
	 * Returns the integer as a Boolean.
	 *
	 * @return Boolean
	 */
	public function getBoolean()
	{
		return new Boolean($this->toBoolean());
	}

	/**
	 * Converts the integer value to a float.
	 *
	 * @return float
	 */
	public function toFloat()
	{
		return floatval($this->integer);
	}

	/**
	 * Returns the integer as a Float.
	 *
	 * @return Float
	 */
	public function getFloat()
	{
		return new Float($this->toFloat());
	}

	/**
	 * Called when an unkown method has been requested.
	 * Warning: Use only functions with an integer as return value!
	 *
	 * @param string	Method name
	 * @param array		Arguments
	 *
	 * @return Map
	 */
	public function __call($method, array $args = null)
	{
		$callback = $this->call($method, $args);
		if($callback !== false)
		{
			$this->integer = $callback->get();
		}
		return $this;
	}
}
?>