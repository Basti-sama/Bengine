<?php
/**
 * Sends reminder to all inactive users.
 *
 * @package Bengine
 * @copyright Copyright protected by / Urheberrechtlich geschützt durch "Sebastian Noll" <snoll@4ym.org>
 * @version $Id: Reminder.php 26 2011-06-17 13:09:26Z secretchampion $
 */

class Bengine_Game_Cronjob_Reminder extends Recipe_CronjobAbstract
{
	/**
	 * Sends the reminder mails.
	 *
	 * @return Bengine_Game_Cronjob_Reminder
	 */
	protected function sendReminders()
	{
		$time = TIME - Core::getConfig()->get("REMINDER_MAIL_TIME") * 86400;
		$select = new Recipe_Database_Select();
		$select->from("user")
			->attributes(array("username", "email", "last"))
			->where("last < ?", $time);
		$result = $select->getResource();
		Core::getLang()->load(array("Registration"));
		while($row = Core::getDatabase()->fetch($result))
		{
			Core::getLang()->assign("username", $row["username"]);
			Core::getLang()->assign("reminderLast", Date::timeToString(2, $row["last"]));
			$template = new Recipe_Email_Template("reminder");
			$mail = new Email(array($row["email"] => $row["username"]), Core::getLang()->get("REMINDER_MAIL_SUBJECT"));
			$template->send($mail);
		}
		return $this;
	}

	/**
	 * Executes this cronjob.
	 *
	 * @return Bengine_Game_Cronjob_Reminder
	 */
	protected function _execute()
	{
		$this->sendReminders();
		return $this;
	}
}
?>