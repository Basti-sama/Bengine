<?php
/**
 * Handler for alliance attack.
 *
 * @package Bengine
 * @copyright Copyright protected by / Urheberrechtlich geschützt durch "Sebastian Noll" <snoll@4ym.org>
 * @version $Id: AllianceAttack.php 33 2011-07-03 20:25:39Z secretchampion $
 */

class Bengine_Game_EventHandler_Handler_Fleet_AllianceAttack extends Bengine_Game_EventHandler_Handler_Fleet_Abstract
{
	/**
	 * (non-PHPdoc)
	 * @see app/code/Bengine/EventHandler/Handler/Bengine_Game_EventHandler_Handler_Abstract#_execute($event, $data)
	 */
	protected function _execute(Bengine_Game_Model_Event $event, array $data)
	{
		Hook::event("EhAllianceAttack", array($event, &$data, $this));
		$assault = new Bengine_Game_Assault($event->getDestination(), $event->getDestinationUserId());
		$assault->addParticipant(Bengine_Game_Assault_Participant::ATTACKER_MODE, $event->getUserid(), $event->getPlanetid(), $event->getTime(), $data);
		// Load allied fleets
		$allies = Game::getEH()->getFormationFleets($event->getEventid());
		foreach($allies as $ally)
		{
			$assault->addParticipant(Bengine_Game_Assault_Participant::ATTACKER_MODE, $ally->getUserid(), $ally->getPlanetid(), $event->getTime(), $ally->getData());
		}
		$assault->startAssault($event->getGalaxy2(), $event->getSystem2(), $event->getPosition2())
			->finish();
		Core::getQuery()->delete("formation_invitation", "eventid = '".$event->getEventid()."'");
		Core::getQuery()->delete("attack_formation", "eventid = '".$event->getEventid()."'");
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
		$result = Core::getQuery()->select("events", array("eventid", "planetid", "destination", "start"), "", "parent_id = '".$event->get("eventid")."'");
		foreach($result->fetchAll() as $row)
		{
			Core::getQuery()->update("events", array("mode" => 20, "planetid" => $row["destination"], "destination" => $row["planetid"], "time" => TIME + (TIME - $row["start"])), "eventid = '".$row["eventid"]."'");
		}
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
		return false;
	}
}
?>