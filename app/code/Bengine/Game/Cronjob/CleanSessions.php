<?php
/**
 * Deletes all sessions from cache folder and disable sessions in database.
 *
 * @package Bengine
 * @copyright Copyright protected by / Urheberrechtlich geschützt durch "Sebastian Noll" <snoll@4ym.org>
 * @version $Id: CleanSessions.php 26 2011-06-17 13:09:26Z secretchampion $
 */

class Bengine_Game_Cronjob_CleanSessions extends Recipe_CronjobAbstract
{
	/**
	 * Deletes old sessions.
	 *
	 * @param integer $deletionDays Deletion time in days
	 * @return Bengine_Game_Cronjob_CleanSessions
	 */
	protected function clearSessions($deletionDays)
	{
		$deletionDays = (int) $deletionDays;
		Hook::event("CleanSessionsBegin");

		// Delete all contents from session cache
		$sessionCache = APP_ROOT_DIR."var/cache/sessions/";
		File::rmDirectoryContent($sessionCache);

		// Disable sessions
		Core::getQuery()->update("sessions", array("logged" => 0), "logged = '1'");

		// Delete old sessions
		$deleteTime = TIME - $deletionDays * 86400;
		Core::getQuery()->delete("sessions", "time < '".$deleteTime."'");

		// Log user count
		$sql = "INSERT INTO `".PREFIX."user_counter`
		(`count`, `day`, `week`, `day_of_week`, `month`, `day_of_month`, `year`, `time`) VALUES
		((SELECT COUNT(`userid`) FROM `".PREFIX."user` LIMIT 1), DAYOFYEAR(CURDATE()), WEEK(CURDATE()), DAYOFWEEK(CURDATE()), MONTH(CURDATE()), DAYOFMONTH(CURDATE()), YEAR(CURDATE()), UNIX_TIMESTAMP())";
		Core::getDatabase()->query($sql);
		return $this;
	}

	/**
	 * Executes this cronjob.
	 *
	 * @return Bengine_Game_Cronjob_CleanSessions
	 */
	protected function _execute()
	{
		$this->clearSessions(Core::getConfig()->get("SESSION_DELETEION_DAYS"));
		return $this;
	}
}
?>