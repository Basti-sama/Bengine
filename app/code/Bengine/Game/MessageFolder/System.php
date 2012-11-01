<?php
/**
 * System message folder.
 *
 * @package Bengine
 * @copyright Copyright protected by / Urheberrechtlich geschützt durch "Sebastian Noll" <snoll@4ym.org>
 * @version $Id: System.php 8 2010-10-17 20:55:04Z secretchampion $
 */

class Bengine_Game_MessageFolder_System extends Bengine_Game_MessageFolder_Abstract
{
	/**
	 * Formats the message.
	 *
	 * @param Bengine_Game_Model_Message $message
	 *
	 * @return Bengine_Game_MessageFolder_System
	 */
	protected function _format(Bengine_Game_Model_Message $message)
	{
		if(!$message->get("sender"))
		{
			$message->set("sender", Core::getLanguage()->getItem("FLEET_COMMAND"));
		}
		$message->set("subject", str_replace("%SID%", SID, $message->get("subject")));
		$message->set("message", str_replace("%SID%", SID, $message->get("message")));
		return $this;
	}
}