<?php
/**
 * Cleans combats.
 *
 * @package Bengine
 * @copyright Copyright protected by / Urheberrechtlich geschützt durch "Sebastian Noll" <snoll@4ym.org>
 * @version $Id: CleanCombats.php 26 2011-06-17 13:09:26Z secretchampion $
 */

class Bengine_Game_Cronjob_CleanCombats extends Recipe_CronjobAbstract
{
	/**
	 * Deletes old combats.
	 *
	 * @param integer $days	Storing time of combats
	 *
	 * @return integer	Number of delteted combats
	 */
	protected function cleanCombats($days)
	{
		$days = (int) $days;
		$time = TIME - 86400 * $days;
		Core::getQuery()->delete("assault", "time <= '".$time."'");
		return Core::getDatabase()->affectedRows();
	}

	/**
	 * Executes this cronjob.
	 *
	 * @return Bengine_Game_Cronjob_CleanCombats
	 */
	protected function _execute()
	{
		$this->cleanCombats(Core::getConfig()->get("COMBAT_STORE_DAYS"));
		return $this;
	}
}
?>