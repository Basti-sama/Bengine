<?php
/**
 * Handler to transport resources.
 *
 * @package Bengine
 * @copyright Copyright protected by / Urheberrechtlich geschützt durch "Sebastian Noll" <snoll@4ym.org>
 * @version $Id: Transport.php 19 2011-05-27 10:30:33Z secretchampion $
 */

class Bengine_EventHandler_Handler_Fleet_Transport extends Bengine_EventHandler_Handler_Fleet_Abstract
{
	/**
	 * (non-PHPdoc)
	 * @see app/code/Bengine/EventHandler/Handler/Bengine_EventHandler_Handler_Abstract#_execute($event, $data)
	 */
	protected function _execute(Bengine_Model_Event $event, array $data)
	{
		Hook::event("EhTransport", array($event, &$data, $this));
		Core::getDB()->query("UPDATE ".PREFIX."planet SET metal = metal + '".$data["metal"]."', silicon = silicon + '".$data["silicon"]."', hydrogen = hydrogen + '".$data["hydrogen"]."' WHERE planetid = '".$event["destination"]."'");

		$data["targetplanet"] = $event["destination_planetname"];
		$data["targetuser"] = $event["destination_user_id"];
		$data["startplanet"] = $event["planetname"];
		$data["startuser"] = $event["username"];

		if($event["userid"] == $data["targetuser"])
		{
			new Bengine_AutoMsg($event["mode"], $event["userid"], $event["time"], $data);
		}
		else
		{
			new Bengine_AutoMsg(21, $event["userid"], $event["time"], $data);
		}

		$data["metal"] = 0;
		$data["silicon"] = 0;
		$data["hydrogen"] = 0;

		$this->sendBack($data, $data["time"] + $event["time"]);
		return $this;
	}

	/**
	 * (non-PHPdoc)
	 * @see app/code/Bengine/EventHandler/Handler/Bengine_EventHandler_Handler_Abstract#_add($event, $data)
	 */
	protected function _add(Bengine_Model_Event $event, array $data)
	{
		$this->prepareFleet($data);
		return $this;
	}

	/**
	 * (non-PHPdoc)
	 * @see app/code/Bengine/EventHandler/Handler/Bengine_EventHandler_Handler_Abstract#_remove($event, $data)
	 */
	protected function _remove(Bengine_Model_Event $event, array $data)
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
		if(!empty($this->_target["userid"]) && $this->_targetType != "tf" && !$this->_target["umode"])
		{
			return true;
		}
		return false;
	}
}
?>