<?php
/**
 * Handler for attacks.
 *
 * @package Bengine
 * @copyright Copyright protected by / Urheberrechtlich geschützt durch "Sebastian Noll" <snoll@4ym.org>
 * @version $Id: Attack.php 19 2011-05-27 10:30:33Z secretchampion $
 */

class Bengine_Game_EventHandler_Handler_Fleet_Attack extends Bengine_Game_EventHandler_Handler_Fleet_Abstract
{
	/**
	 * (non-PHPdoc)
	 * @see app/code/Bengine/EventHandler/Handler/Bengine_Game_EventHandler_Handler_Abstract#_execute($event, $data)
	 */
	protected function _execute(Bengine_Game_Model_Event $event, array $data)
	{
		Hook::event("EhAttack", array($event, &$data, $this));
		$assault = new Bengine_Game_Assault($event["destination"], $event["destination_user_id"]);
		$assault->addParticipant(1, $event["userid"], $event["planetid"], $event["time"], $data)
			->startAssault($data["galaxy"], $data["system"], $data["position"])
			->finish();
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
		if(empty($this->_target["planetid"]))
		{
			return false;
		}
		if( $this->_targetType != "tf" &&
			(empty($this->_target["userid"]) || (
			$this->_target["userid"] != Core::getUser()->get("userid") &&
			!$this->isNewbieProtected() &&
			!$this->_target["umode"])) &&
			Core::getOptions()->get("ATTACKING_STOPPAGE") != 1 &&
			$this->_checkShips())
		{
			return true;
		}
		return false;
	}

	/**
	 * Checks the ships for attack.
	 *
	 * @return boolean
	 */
	protected function _checkShips()
	{
		if(count($this->_ships) > 1)
		{
			return true;
		}
		$espProbeId = Bengine_Game_EventHandler_Handler_Fleet_Espionage::ESPIONAGE_PROBE_ID;
		if(!empty($this->_ships[$espProbeId]) && $this->_ships[$espProbeId]["quantity"] > 0)
		{
			return false;
		}
		return true;
	}
}
?>