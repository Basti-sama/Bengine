<?php
/**
 * Cron class. Searches for expired cron tasks, execute them and
 * calculate next execution time.
 *
 * @package Recipe 1.2
 * @author Sebastian Noll
 * @copyright Copyright (c) 2009, Sebastian Noll
 * @license Proprietary
 * @version $Id: Cron.php 47 2011-07-31 14:33:21Z secretchampion $
 */

class Recipe_Cron
{
	/**
	 * Constructor.
	 *
	 * @return Recipe_Cron
	 */
	public function __construct()
	{
		try {
			$this->exeCron();
		} catch(Recipe_Exception_Generic $e) {
			$e->printError();
		}
		return;
	}

	/**
	 * Search in database for expired cron tasks and execute them.
	 *
	 * @throws Recipe_Exception_Generic
	 * @return Recipe_Cron
	 */
	protected function exeCron()
	{
		if(EXEC_CRON === false)
			return $this;
		$select = array("cronid", "class", "month", "day", "weekday", "hour", "minute", "xtime", "active");
		$_result = Core::getQuery()->select("cronjob", $select, "", "(xtime <= ".TIME." OR xtime IS NULL) AND active = '1'");
		foreach($_result->fetchAll() as $_row)
		{
			$cronjobObj = new $_row["class"]();
			if($cronjobObj instanceof Recipe_CronjobAbstract)
			{
				Hook::event("ExecuteCronjob", array($this, &$_row, $cronjobObj));
				$cronjobObj->execute($_row["cronid"], $_row["xtime"], $this->calcNextExeTime($_row));
			}
			else
			{
				throw new Recipe_Exception_Generic("Cannot execute cron job \"{$_row["class"]}\".", __FILE__, __LINE__);
			}
		}
		$_result->closeCursor();
		return $this;
	}

	/**
	 * Calculate next execution time.
	 *
	 * @param array $row	Database row of cron job
	 *
	 * @return integer	Next execution time as Unix timestamp
	 */
	public function calcNextExeTime($row)
	{
		$row = $this->validate($row);
		$year = (int) date("Y", $row["xtime"]);
		do {
			$nextXtime = $this->compute($row["xtime"], $row["month"], $row["day"], $row["weekday"], $row["hour"], $row["minute"], $year);
			$year++;
		} while($nextXtime === false);
		return $nextXtime;
	}

	/**
	 * Computes the time of the given date parameters.
	 *
	 * @param integer $last		Last execution time
	 * @param array $months		Month parameter
	 * @param array $days		Day parameter
	 * @param array $weekdays	Weekday parameter
	 * @param array $hours		Hour parameter
	 * @param array $minutes	Minute parameter
	 * @param integer $year		Year
	 *
	 * @return boolean|integer	Returns the timestamp or false
	 */
	public function compute($last, array $months, array $days, array $weekdays, array $hours, array $minutes, $year)
	{
		foreach($months as $month)
		{
			foreach($days as $day)
			{
				if(count($weekdays) <= 7)
				{
					$weekday = date("N", mktime(0,0,0,$month,$day,$year));
					if(!in_array($weekday, $weekdays))
					{
						continue;
					}
				}
				foreach($hours as $hour)
				{
					foreach($minutes as $minute)
					{
						$time = mktime($hour, $minute, 0, $month, $day, $year);
						if($time > $last)
						{
							return $time;
						}
					}
				}
			}
		}
		return false;
	}

	/**
	 * Check incoming cron time data and put them into separate arrays.
	 *
	 * @param array $row	Database row to validate
	 *
	 * @return array	Validated row
	 */
	protected function validate($row)
	{
		$row["minute"] = Arr::trim(explode(",", $row["minute"]));
		$row["hour"] = Arr::trim(explode(",", $row["hour"]));
		$row["day"] = Arr::trim(explode(",", $row["day"]));
		$row["weekday"] = Arr::trim(explode(",", $row["weekday"]));
		$row["month"] = Arr::trim(explode(",", $row["month"]));
		if(empty($row["xtime"]))
			$row["xtime"] = TIME;
		return $row;
	}
}
?>