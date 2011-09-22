<?php
/**
 * User message folder.
 *
 * @package Bengine
 * @copyright Copyright protected by / Urheberrechtlich geschützt durch "Sebastian Noll" <snoll@4ym.org>
 * @version $Id: User.php 8 2010-10-17 20:55:04Z secretchampion $
 */

class Bengine_MessageFolder_User extends Bengine_MessageFolder_Abstract
{
	/**
	 * Formats the message.
	 *
	 * @param Bengine_Model_Message	Message model
	 *
	 * @return Bengine_MessageFolder_User
	 */
	protected function _format(Bengine_Model_Message $message)
	{
		$sender = $message->get("sender");
		if(!empty($sender))
		{
			$url = "game.php/".SID."/MSG/Write/".rawurlencode($message->get("username"))."/".rawurlencode("RE: ".$message->get("subject"));
			$reply = Link::get($url, Image::getImage("pm.gif", Core::getLanguage()->getItem("REPLY")));
			$message->set("reply_link", $reply);
			$sender = $message->get("username")." ".getCoordLink($message->get("galaxy"), $message->get("system"), $message->get("position"));
		}
		else
		{
			$sender = "System";
		}
		$message->set("sender", $sender);
		return $this;
	}
}