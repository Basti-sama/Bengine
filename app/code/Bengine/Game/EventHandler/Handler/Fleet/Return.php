<?php
/**
 * Handler for returning fleets.
 *
 * @package Bengine
 * @copyright Copyright protected by / Urheberrechtlich geschützt durch "Sebastian Noll" <snoll@4ym.org>
 * @version $Id: Return.php 19 2011-05-27 10:30:33Z secretchampion $
 */

class Bengine_Game_EventHandler_Handler_Fleet_Return extends Bengine_Game_EventHandler_Handler_Fleet_Abstract
{
	/**
	 * (non-PHPdoc)
	 * @see app/code/Bengine/EventHandler/Handler/Bengine_Game_EventHandler_Handler_Abstract#_execute($event, $data)
	 */
	protected function _execute(Bengine_Game_Model_Event $event, array $data)
	{
		Hook::event("EhReturn", array($event, &$data, $this));
		$this->_production($event);
		foreach($data["ships"] as $ship)
		{
			$where = Core::getDB()->quoteInto("unitid = ? AND planetid = ?", array($ship["id"], $event["destination"]));
			$result = Core::getQuery()->select("unit2shipyard", "unitid", "", $where);
			if($result->rowCount() > 0)
			{
				Core::getDB()->query("UPDATE ".PREFIX."unit2shipyard SET quantity = quantity + ? WHERE unitid = ? AND planetid = ?", array($ship["quantity"], $ship["id"], $event["destination"]));
			}
			else
			{
				Core::getQuery()->insert("unit2shipyard", array("unitid" => $ship["id"], "planetid" => $event["destination"], "quantity" => $ship["quantity"]));
			}
		}
		$data["destination"] = $event["destination"];
		Core::getDB()->query("UPDATE ".PREFIX."planet SET metal = metal + ?, silicon = silicon + ?, hydrogen = hydrogen + ? WHERE planetid = ?", array($data["metal"], $data["silicon"], $data["hydrogen"], $event["destination"]));
		if(!isset($data["nomessage"]))
		{
			new Bengine_Game_AutoMsg($event["mode"], $event["userid"], $event["time"], $data);
		}
		return $this;
	}

	/**
	 * (non-PHPdoc)
	 * @see app/code/Bengine/EventHandler/Handler/Bengine_Game_EventHandler_Handler_Abstract#_add($event, $data)
	 */
	protected function _add(Bengine_Game_Model_Event $event, array $data)
	{
		return $this;
	}

	/**
	 * (non-PHPdoc)
	 * @see app/code/Bengine/EventHandler/Handler/Bengine_Game_EventHandler_Handler_Abstract#_remove($event, $data)
	 */
	protected function _remove(Bengine_Game_Model_Event $event, array $data)
	{
		return false;
	}

	/**
	 * Checks the event for validation.
	 *
	 * @return boolean
	 */
	protected function _isValid()
	{

		return false;
	}
}
?>