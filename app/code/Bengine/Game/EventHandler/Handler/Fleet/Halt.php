<?php
/**
 * Handler to halt a fleet at an allied planet.
 *
 * @package Bengine
 * @copyright Copyright protected by / Urheberrechtlich geschützt durch "Sebastian Noll" <snoll@4ym.org>
 * @version $Id: Halt.php 19 2011-05-27 10:30:33Z secretchampion $
 */

class Bengine_Game_EventHandler_Handler_Fleet_Halt extends Bengine_Game_EventHandler_Handler_Fleet_Abstract
{
	/**
	 * (non-PHPdoc)
	 * @see app/code/Bengine/EventHandler/Handler/Bengine_Game_EventHandler_Handler_Abstract#_execute($event, $data)
	 */
	protected function _execute(Bengine_Game_Model_Event $event, array $data)
	{
		Hook::event("EhHalt", array($event, &$data, $this));
		Core::getQuery()->update("events", array("mode", "time", "prev_rc"), array(17, $event["time"] + $data["duration"], null), "eventid = '".$event["eventid"]."'");
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
		if(!empty($this->_target["userid"]) &&
			$this->_target["userid"] != Core::getUser()->get("userid") &&
			$this->_targetType != "tf" &&
			!$this->_target["umode"] &&
			$this->_relations->hasRelation($this->_target["userid"], $this->_target["aid"]))
		{
			return true;
		}
		return false;
	}
}
?>