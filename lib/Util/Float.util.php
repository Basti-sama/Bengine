<?php
/**
 * Object-oriented typing: Float/Double
 *
 * @package Recipe 1.2
 * @author Sebastian Noll
 * @copyright Copyright (c) 2009, Sebastian Noll
 * @license Proprietary
 * @version $Id: Float.util.php 8 2010-10-17 20:55:04Z secretchampion $
 */

class Float extends Type
{
	/**
	 * This is the actual float/double value.
	 *
	 * @var float
	 */
	protected $float = 0.0;

	/**
	 * Initializes a newly created Float object.
	 *
	 * @param float
	 *
	 * @return void
	 */
	public function __construct($float = null)
	{
		if(!is_null($float))
		{
			$this->float = floatval($float);
		}
		return;
	}

	/**
	 * Sets a new float value.
	 *
	 * @return Float
	 */
	public function set($float)
	{
		$this->__construct($float);
		return $this;
	}

	/**
	 * Returns the float value.
	 *
	 * @return float
	 */
	public function get()
	{
		return $this->float;
	}

	/**
	 * Sets the absolute value.
	 *
	 * @return Float
	 */
	public function absolute()
	{
		$this->float = abs($this->float);
		return $this;
	}

	/**
	 * Sets the float value to PI.
	 *
	 * @return Float
	 */
	public function pi()
	{
		$this->float = pi();
		return $this;
	}

	/**
	 * Performs an addition.
	 *
	 * @param mixed Summand
	 *
	 * @return Float
	 */
	public function add($summand)
	{
		$number = $this->getFromArgument($summand);
		$this->float += $number->get();
		$this->float = floatval($this->float);
		return $this;
	}

	/**
	 * Performs a Subtraction.
	 *
	 * @param mixed Subtrahend
	 *
	 * @return Float
	 */
	public function subtract($subtrahend)
	{
		$number = $this->getFromArgument($subtrahend);
		$this->float -= $number->get();
		$this->float = floatval($this->float);
		return $this;
	}

	/**
	 * Performs a multiplication.
	 *
	 * @param mixed Multiplier
	 *
	 * @return Float
	 */
	public function multiply($multiplier)
	{
		$number = $this->getFromArgument($multiplier);
		$this->float *= $number->get();
		$this->float = floatval($this->float);
		return $this;
	}

	/**
	 * Performs a division.
	 *
	 * @param mxied Divisior
	 *
	 * @return Float
	 */
	public function divide($divisor)
	{
		$number = $this->getFromArgument($divisor);
		if($number->get() == 0)
		{
			throw new Recipe_Exception_Issue("Division by zero.");
		}
		$this->float = $this->float / floatval($number->get());
		return $this;
	}

	/**
	 * Performs an Exponentiation.
	 *
	 * @param mixed Exponent expression
	 *
	 * @return Float
	 */
	public function expo($expression)
	{
		$number = $this->getFromArgument($expression);
		$this->float = pow($this->float, $number->get());
		$this->float = floatval($this->float);
		return $this;
	}

	/**
	 * Returns the float value as an integer object.
	 *
	 * @return Integer
	 */
	public function getInteger()
	{
		return new Integer($this->toInteger());
	}

	/**
	 * Returns the float value as an integer value.
	 *
	 * @return integer
	 */
	public function toInteger()
	{
		return (int) round($this->float);
	}

	/**
	 * Rounds the float value to specified precision.
	 *
	 * @param The optional number of decimal digits to round to, defaults to 0.
	 *
	 * @return Float
	 */
	public function round($precision = 0)
	{
		$precision = $this->getFromArgument($precision);
		$this->float = round($this->float, $precision->get());
		return $this;
	}

	/**
	 * Round the float value up.
	 *
	 * @return Float
	 */
	public function ceil()
	{
		$this->float = ceil($this->float);
		return $this;
	}

	/**
	 * Round the float value down.
	 *
	 * @return Float
	 */
	public function floor()
	{
		$this->float = floor($this->float);
		return $this;
	}

	/**
	 * Converts the float value to a string.
	 *
	 * @return string
	 */
	public function toString($decimals = 0, $decPoint = ".", $thousandsSep = ",")
	{
		return number_format($this->float, $decimals, $decPoint, $thousandsSep);
	}

	/**
	 * Returns the float as a String.
	 *
	 * @return String
	 */
	public function getString($decimals = 0, $decPoint = ".", $thousandsSep = ",")
	{
		return new String($this->toString($decimals, $decPoint, $thousandsSep));
	}

	/**
	 * Converts the float value to a boolean.
	 *
	 * @return boolean
	 */
	public function toBoolean()
	{
		if($this->float > 0)
		{
			return true;
		}
		return false;
	}

	/**
	 * Returns the float as a Boolean.
	 *
	 * @return Boolean
	 */
	public function getBoolean()
	{
		return new Boolean($this->toBoolean());
	}

	/**
	 * Called when an unkown method has been requested.
	 * Warning: Use only functions with a float as return value!
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
			$this->float = $callback->get();
		}
		return $this;
	}
}
?>