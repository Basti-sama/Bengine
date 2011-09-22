<?php
/**
 * Handler for returning fleets.
 *
 * @package Bengine
 * @copyright Copyright protected by / Urheberrechtlich geschützt durch "Sebastian Noll" <snoll@4ym.org>
 * @version $Id: Return.php 19 2011-05-27 10:30:33Z secretchampion $
 */

class Bengine_EventHandler_Handler_Fleet_Return extends Bengine_EventHandler_Handler_Fleet_Abstract
{
	/**
	 * (non-PHPdoc)
	 * @see app/code/Bengine/EventHandler/Handler/Bengine_EventHandler_Handler_Abstract#_execute($event, $data)
	 */
	protected function _execute(Bengine_Model_Event $event, array $data)
	{
		Hook::event("EhReturn", array($event, &$data, $this));
		foreach($data["ships"] as $ship)
		{
			$result = Core::getQuery()->select("unit2shipyard", "unitid", "", "unitid = '".$ship["id"]."' AND planetid = '".$event["destination"]."'");
			if(Core::getDB()->num_rows($result) > 0)
			{
				Core::getDB()->query("UPDATE ".PREFIX."unit2shipyard SET quantity = quantity + '".$ship["quantity"]."' WHERE unitid = '".$ship["id"]."' AND planetid = '".$event["destination"]."'");
			}
			else
			{
				Core::getQuery()->insert("unit2shipyard", array("unitid", "planetid", "quantity"), array($ship["id"], $event["destination"], $ship["quantity"]));
			}
		}
		$data["destination"] = $event["destination"];
		Core::getDB()->query("UPDATE ".PREFIX."planet SET metal = metal + '".$data["metal"]."', silicon = silicon + '".$data["silicon"]."', hydrogen = hydrogen + '".$data["hydrogen"]."' WHERE planetid = '".$event["destination"]."'");
		if(!isset($data["nomessage"]))
		{
			new Bengine_AutoMsg($event["mode"], $event["userid"], $event["time"], $data);
		}
		return $this;
	}

	/**
	 * (non-PHPdoc)
	 * @see app/code/Bengine/EventHandler/Handler/Bengine_EventHandler_Handler_Abstract#_add($event, $data)
	 */
	protected function _add(Bengine_Model_Event $event, array $data)
	{
		return $this;
	}

	/**
	 * (non-PHPdoc)
	 * @see app/code/Bengine/EventHandler/Handler/Bengine_EventHandler_Handler_Abstract#_remove($event, $data)
	 */
	protected function _remove(Bengine_Model_Event $event, array $data)
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