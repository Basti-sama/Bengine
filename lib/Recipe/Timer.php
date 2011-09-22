<?php
/**
 * Timer class. Stopwatch function.
 *
 * @package Recipe 1.2
 * @author Sebastian Noll
 * @copyright Copyright (c) 2009, Sebastian Noll
 * @license Proprietary
 * @version $Id: Timer.php 8 2010-10-17 20:55:04Z secretchampion $
 */

class Recipe_Timer
{
	/**
	 * Time When Script starts.
	 *
	 * @var float
	 */
	protected $startTime = 0;

	/**
	 * Time When Script Ends.
	 *
	 * @var float
	 */
	protected $endTime = 0;

	/**
	 * Formatted time in seconds.
	 *
	 * @var string
	 */
	protected $timeInSec = "";

	/**
	 * Number of decimal places.
	 *
	 * @var integer
	 */
	protected $decimalPlaces = 4;

	/**
	 * Time.
	 *
	 * @var mixed
	 */
	protected $time = null;

	/**
	 * Constructor initialize Timer.
	 *
	 * @param integer	Start time
	 *
	 * @return void
	 */
	public function __construct($time)
	{
		$this->time = $time;
		$this->startTimer();
		return;
	}

	/**
	 * Record time, when script is launching.
	 *
	 * @return void
	 */
	protected function startTimer()
	{
		list($usec, $sec) = explode(" ",$this->time);
		$this->startTime = ((float)$usec + (float)$sec);
		return;
	}

	/**
	 * Stop recording time.
	 *
	 * @return float	The latest micro time
	 */
	protected function stopTimer()
	{
		list($usec, $sec) = explode(" ",microtime(0));
		$this->endTimer = ((float)$usec + (float)$sec);
		return $this->endTimer;
	}

	/**
	 * Returns time of stopwatch.
	 *
	 * @param boolean	Turns of the auto-format
	 *
	 * @return string	The total generating time in seconds
	 */
	public function getTime($format = true)
	{
		$this->timeInSec = $this->stopTimer() - $this->startTime;
		if($format) { return $this->format($this->timeInSec); }
		return $this->timeInSec;
	}

	/**
	 * Format the decimal places and add dimension.
	 *
	 * @param flaot		Unformatted time
	 *
	 * @return sting	Formatted time
	 */
	protected function format($number)
	{
		return number_format($number, $this->decimalPlaces)." seconds";
	}
}
?>