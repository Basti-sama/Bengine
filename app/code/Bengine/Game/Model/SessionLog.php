<?php
/**
 * Session log model.
 *
 * @package Bengine
 * @copyright Copyright protected by / Urheberrechtlich geschützt durch "Sebastian Noll" <snoll@4ym.org>
 * @version $Id: SessionLog.php 8 2010-10-17 20:55:04Z secretchampion $
 */

class Bengine_Game_Model_SessionLog extends Recipe_Model_Abstract
{
	/**
	 * Initializes the model.
	 *
	 * @return Bengine_Game_Model_SessionLog
	 */
	protected function init()
	{
		$this->setTableName("sessions")
			->setModelName("game/sessionLog")
			->setPrimaryKey("sessionid");
		return $this;
	}

	/**
	 * Returns all browser infos of the session.
	 *
	 * @return array
	 */
	public function getBrowserInfo()
	{
		if(!$this->exists("browser_info"))
		{
			$browserInfo = array();
			if(ini_get("browscap"))
			{
				$browserInfo = get_browser($this->get("useragent"), true);
			}
			$this->set("browser_info", $browserInfo);
		}
		return $this->get("browser_info");
	}
}