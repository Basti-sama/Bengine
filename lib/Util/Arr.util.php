<?php
/**
 * Advanced array functions.
 *
 * @package Recipe 1.2
 * @author Sebastian Noll
 * @copyright Copyright (c) 2009, Sebastian Noll
 * @license Proprietary
 * @version $Id: Arr.util.php 23 2011-06-08 20:01:56Z secretchampion $
 */

class Arr
{
	/**
	 * Remove all elements with no content.
	 *
	 * @param array $array	Array to be cleaned
	 *
	 * @return array		Cleaned array
	 */
	public static function clean($array)
	{
		$rArray = array();
		for($i = 0; $i < count($array); $i++)
		{
			if(Str::length($array[$i]) > 0) { $rArray[$i] = $array[$i]; }
		}
		return $rArray;
	}

	/**
	 * Alias to trimArray()-method.
	 *
	 * @param array $array	Array to be trimed
	 *
	 * @return array		Trimed array
	 */
	public static function trim($array)
	{
		return array_map("trim", $array);
	}

	/**
	 * Improved array_map function.
	 *
	 * @param callback $callback	The function to perform
	 * @param array $array			The array to iterate through
	 *
	 * @return array
	 */
	public static function map($callback, array $array)
	{
		$_array = array();
		foreach($array as $key => $value)
		{
			if(is_array($value))
			{
				$_array[$key] = self::map($callback, $value);
			}
			else
			{
				$_array[$key] = call_user_func_array($callback, array($value));
			}
		}
		return $_array;
	}
}
?>