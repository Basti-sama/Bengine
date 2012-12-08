<?php
/**
 * Clears user cache and disables session.
 *
 * @package Bengine
 * @copyright Copyright protected by / Urheberrechtlich geschützt durch "Sebastian Noll" <snoll@4ym.org>
 * @version $Id: Logout.php 19 2011-05-27 10:30:33Z secretchampion $
 */

class Bengine_Game_Controller_Logout extends Bengine_Game_Controller_Abstract
{
	/**
	 * Deletes all sessions older than $days.
	 *
	 * @var integer
	 */
	const SESSION_SAVING_DAYS = 30;

	/**
	 * Perfom log out proccess.
	 *
	 * @return Bengine_Game_Controller_Logout
	 */
	protected function indexAction()
	{
		Hook::event("DoLogout");
		Core::getCache()->cleanUserCache(Core::getUser()->get("userid"));
		Core::getQuery()->update("sessions", array("logged" => 0), "userid = '".Core::getUser()->get("userid")."'");
		if(Core::getConfig()->exists("SESSION_SAVING_DAYS"))
		{
			$days = (int) Core::getConfig()->get("SESSION_SAVING_DAYS");
		}
		else
		{
			$days = self::SESSION_SAVING_DAYS;
		}
		$deleteTime = TIME - 86400 * $days;
		Core::getQuery()->delete("sessions", "time < '".$deleteTime."'");
		Game::unlock();
		$this->redirect(BASE_URL);
		return $this;
	}
}
?>