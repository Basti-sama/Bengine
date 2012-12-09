<?php
/**
 * Handler to position a fleet.
 *
 * @package Bengine
 * @copyright Copyright protected by / Urheberrechtlich geschützt durch "Sebastian Noll" <snoll@4ym.org>
 * @version $Id: Position.php 19 2011-05-27 10:30:33Z secretchampion $
 */

class Bengine_Game_EventHandler_Handler_Fleet_Position extends Bengine_Game_EventHandler_Handler_Fleet_Abstract
{
	/**
	 * (non-PHPdoc)
	 * @see app/code/Bengine/EventHandler/Handler/Bengine_Game_EventHandler_Handler_Abstract#_execute($event, $data)
	 */
	protected function _execute(Bengine_Game_Model_Event $event, array $data)
	{
		Hook::event("EhPosition", array($event, &$data, $this));
		$this->_production($event);
		foreach($data["ships"] as $ship)
		{
			$result = Core::getQuery()->select("unit2shipyard", "unitid", "", "unitid = '".$ship["id"]."' AND planetid = '".$event["destination"]."'");
			if($result->rowCount() > 0)
			{
				Core::getDB()->query("UPDATE ".PREFIX."unit2shipyard SET quantity = quantity + ? WHERE unitid = ? AND planetid = ?", array($ship["quantity"], $ship["id"], $event["destination"]));
			}
			else
			{
				Core::getQuery()->insert("unit2shipyard", array("unitid" => $ship["id"], "planetid" => $event["destination"], "quantity" => $ship["quantity"]));
			}
			$result->closeCursor();
		}
		$data["destination"] = $event["destination"];
		new Bengine_Game_AutoMsg($event["mode"], $event["userid"], $event["time"], $data);
		Core::getDB()->query("UPDATE ".PREFIX."planet SET metal = metal + ?, silicon = silicon + ?, hydrogen = hydrogen + ? WHERE planetid = ?", array($data["metal"], $data["silicon"], $data["hydrogen"], $event["destination"]));
		return $this;
	}

	/**
	 * (non-PHPdoc)
	 * @see app/code/Bengine/EventHandler/Handler/Bengine_Game_EventHandler_Handler_Abstract#_add($event, $data)
	 */
	protected function _add(Bengine_Game_Model_Event $event, array $data)
	{
		$this->prepareFleet($data);
		return $this;
	}

	/**
	 * (non-PHPdoc)
	 * @see app/code/Bengine/EventHandler/Handler/Bengine_Game_EventHandler_Handler_Abstract#_remove($event, $data)
	 */
	protected function _remove(Bengine_Game_Model_Event $event, array $data)
	{
		$this->sendBack($data);
		return $this;
	}

	/**
	 * Checks the event for validation.
	 *
	 * @return boolean
	 */
	protected function _isValid()
	{
		if(isset($this->_target["userid"]) && $this->_target["userid"] == Core::getUser()->get("userid") && $this->_targetType != "tf")
		{
			return true;
		}
		return false;
	}
}
?>