<?php
/**
 * Date related functions.
 *
 * @package Recipe 1.2
 * @author Sebastian Noll
 * @copyright Copyright (c) 2009, Sebastian Noll
 * @license Proprietary
 * @version $Id: Date.util.php 19 2011-05-27 10:30:33Z secretchampion $
 */

class Date
{
	/**
	 * Date and time formats.
	 *
	 * @var string
	 */
	protected static $dateFormat = "", $timeFormat = "";

	/**
	 * Default date and time formats.
	 *
	 * @var string
	 */
	protected static $defaultDateFormat = "Y-m-d", $defaultTimeFormat = "H:i:s";

	/**
	 * Returns current timestamp.
	 *
	 * @return integer	Unix timestamp
	 */
	public static function getTimestamp()
	{
		return TIME;
	}

	/**
	 * Generates the timestamp of a date.
	 *
	 * @return integer	The converted timestamp or now.
	 */
	public static function strToTime($date)
	{
		if(($timestamp = strtotime($date)) !== false)
		{
			return $timestamp;
		}

		if(list($day, $month, $year, $time) = split('[/. ]', $date))
		{
			return strtotime($day."-".$month."-".$year." ".$time);
		}
		return self::getTimestamp();
	}

	public static function timeToString($type = 1, $timestamp = -1, $format = "", $replaceShortDate = 1)
	{
		$time = self::getTimestamp();
		$timestamp = (int) $timestamp;
		if($timestamp < 0)
		{
			$timestamp = $time;
		}
		if($format != "")
		{
			$type = 3;
		}
		$date = "";
		$isShortDate = false;
		Hook::event("ConvertTimestampStart", array($type, $timestamp, &$format));

		// Replace date designation, if timestamp is today or yesterday.
		// Note: Does not work with specific date format.
		if($replaceShortDate && $format == "")
		{
			$dateRepresentive = date("Y-m-d", $timestamp);
			if(date("Y-m-d", strtotime("-1 day", $time)) == $dateRepresentive)
			{
				$date = Core::getLanguage()->getItem("YESTERDAY");
				$isShortDate = true;
			}
			else if(date("Y-m-d", $time) == $dateRepresentive)
			{
				$date = Core::getLanguage()->getItem("TODAY");
				$isShortDate = true;
			}
			else if(date("Y-m-d", strtotime("+1 day", $time)) == $dateRepresentive)
			{
				$date = Core::getLanguage()->getItem("TOMORROW");
				$isShortDate = true;
			}

			if($type == 1 && $isShortDate)
			{
				return "<span class=\"cur-day\">".$date."</span> ".date(self::getTimeFormat(), $timestamp);
			}
			else if($type == 2 && $isShortDate)
			{
				return "<span class=\"cur-day\">".$date."</span>";
			}
		}

		switch($type)
		{
			// Date + Time Format
			default:
			case 1:
				$date = date(self::getDateFormat()." ".self::getTimeFormat(), $timestamp);
			break;

			// Only Date Format
			case 2:
				$date = date(self::getDateFormat(), $timestamp);
			break;

			// Specific Format
			case 3:
				$date = date($format, $timestamp);
			break;
		}
		Hook::event("ConvertTimestampClose", array($type, $timestamp, &$format, &$date));
		return $date;
	}

	public static function getDateTime($timestamp = -1)
	{
		if($timestamp < 0)
		{
			$timestamp = self::getTimestamp();
		}
		return array(
			'a' => date('a', $timestamp), // am/pm
			'A' => date('A', $timestamp), // AM/PM
			'd' => date('d', $timestamp), // Day of month, 2 digits
			'D' => date('D', $timestamp), // Day of week, 3 letters
			'F' => date('F', $timestamp), // Full name of month
			'g' => (int) date('g', $timestamp), // 12-hour format without leading zeros
			'G' => (int) date('G', $timestamp), // 24-hour format without leading zeros
			'h' => date('h', $timestamp), // 12-hour format with leading zeros
			'H' => date('H', $timestamp), // 24-hour format with leading zeros
			'i' => date('i', $timestamp), // Minutes with leading zeros
			'I' => date('I', $timestamp), // Daylight Saving Time
			'j' => (int) date('j', $timestamp), // Day of month without leading zeros
			'l' => date('l', $timestamp), // Full name of week
			'L' => date('L', $timestamp), // Leap year
			'm' => date('m', $timestamp), // Month as digit with leading zeros
			'M' => date('M', $timestamp), // Day of month, 3 letters
			'n' => (int) date('n', $timestamp), // Month as digit without leading zeros
			's' => date('s', $timestamp), // Seconds with leading zeros
			't' => (int) date('t', $timestamp), // Day number of month
			'T' => date('T', $timestamp), // Timezone abbreviation
			'w' => (int) date('w', $timestamp), // Numeric day of week (0 = Sunday, 6 = Saturday)
			'Y' => (int) date('Y', $timestamp), // Full number of year, 4 digits
			'y' => date('y', $timestamp), // Year, 2 digits
			'z' => (int) date('z', $timestamp), // Day of given year
		);
	}

	/**
	 * Returns the used date format.
	 *
	 * @return string
	 */
	public static function getDateFormat()
	{
		if(!empty(self::$dateFormat))
		{
			return self::$dateFormat;
		}
		if(Core::getLang()->exists("DATE_FORMAT"))
		{
			self::$dateFormat = Core::getLang()->get("DATE_FORMAT");
		}
		else
		{
			self::$dateFormat = self::$defaultDateFormat;
		}
		return self::$dateFormat;
	}

	/**
	 * Returns the used time format.
	 *
	 * @return string
	 */
	public static function getTimeFormat()
	{
		if(!empty(self::$timeFormat))
		{
			return self::$timeFormat;
		}
		if(Core::getLang()->exists("TIME_FORMAT"))
		{
			self::$timeFormat = Core::getLang()->get("TIME_FORMAT");
		}
		else
		{
			self::$timeFormat = self::$defaultTimeFormat;
		}
		return self::$timeFormat;
	}
}
?>