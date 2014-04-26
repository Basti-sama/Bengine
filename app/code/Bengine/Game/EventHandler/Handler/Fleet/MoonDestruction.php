<?php
/**
 * Handler for moon destruction.
 *
 * @package Bengine
 * @copyright Copyright protected by / Urheberrechtlich geschützt durch "Sebastian Noll" <snoll@4ym.org>
 * @version $Id: MoonDestruction.php 44 2011-07-26 09:46:52Z secretchampion $
 */

class Bengine_Game_EventHandler_Handler_Fleet_MoonDestruction extends Bengine_Game_EventHandler_Handler_Fleet_Abstract
{
	/**
	 * The death star id.
	 *
	 * @var integer
	 */
	const DEATH_STAR_CONSTRUCTION_ID = 42;

	/**
	 * (non-PHPdoc)
	 * @see app/code/Bengine/EventHandler/Handler/Bengine_Game_EventHandler_Handler_Abstract#_execute($event, $data)
	 */
	protected function _execute(Bengine_Game_Model_Event $event, array $data)
	{
		Hook::event("EhMoonDestruction", array($event, &$data, $this));

		// Start normal combat
		$assault = new Bengine_Game_Assault($event->getDestination(), $event->getDestinationUserId());
		$assault->addParticipant(1, $event->getUserid(), $event->getPlanetid(), $event->getTime(), $data);
		$assault->startAssault($data["galaxy"], $data["system"], $data["position"]);

		if($assault->getData("result") == 1 || $assault->getData("result") == 0)
		{
			Core::getLanguage()->load("AutoMessages");
			$defenders = $assault->getDefenders();
			while($defenders->next())
			{
				$participant = $this->defenders->current();
				if($participant instanceof Bengine_Game_Assault_Participant)
				{
					$participant->finish($assault->getData("result"));
				}
			}
			$assault->updateMainDefender($assault->getData("lostunits_defender"));

			$attacker = $assault->getAttackers()->getFirst();
			$select = new Recipe_Database_Select();
			$select->from(array("f2a" => "fleet2assault"))
				->join(array("c" => "construction"), array("c" => "buildingid", "f2a" => "unitid"))
				->attributes(array(
					"f2a" => array("unitid", "quantity"),
					"c" => array("basic_metal", "basic_silicon", "basic_hydrogen")
				))
				->where("f2a.participantid = ?", $attacker->getParticipantId());
			$result = $select->getStatement();
			$fleet = array();
			foreach($result->fetchAll() as $row)
			{
				$fleet[$row["unitid"]] = $row;
			}
			$rips = isset($fleet[self::DEATH_STAR_CONSTRUCTION_ID]["quantity"]) ? $fleet[self::DEATH_STAR_CONSTRUCTION_ID]["quantity"] : 0;
			$result->closeCursor();
			$moon = $event->getDestinationPlanet();
			$message = "";

			// Chance to destroy the moon
			$md = floor((100 - sqrt($moon->getDiameter())) * sqrt($rips));
			$rand = mt_rand(0, 100);
			Core::getLang()->assign("moonName", $moon->getPlanetname());
			Core::getLang()->assign("moonCoords", $moon->getCoords(true, true));
			if($rand <= $md)
			{
				$message = Core::getLang()->get("MD_MOON_DESTROYED");
				Core::getQuery()->update("events", array("planetid" => null), "planetid = ?", array($moon->getId()));
				$sql = "UPDATE `".PREFIX."events` e, `".PREFIX."galaxy` g SET e.`destination` = g.`planetid` WHERE g.`moonid` = ? AND e.`destination` = ?";
				Core::getDB()->query($sql, array($moon->getId(), $moon->getId()));
				deletePlanet($moon->getId(), $moon->getUserid(), 1);
			}
			else
			{
				$message = Core::getLang()->get("MD_MOON_SURVIVE");
			}

			// Chance of fleet destruction
			$fd = floor(sqrt($moon->getDiameter()) / 2);
			$rand = mt_rand(0, 100);
			if($rand <= $fd)
			{
				$points = 0;
				foreach($fleet as $ship)
				{
					$points += $ship["quantity"] * ($ship["basic_metal"] + $ship["basic_silicon"] + $ship["basic_hydrogen"]);
				}
				Core::getDB()->query("UPDATE ".PREFIX."user SET points = points - ? WHERE userid = ?", array($points/1000, $event->getUserid()));
				$message .= "<br/><br/>".Core::getLang()->get("MD_FLEET_DESTROYED");
			}
			else
			{
				$attacker->finish($assault->getData("result"));
			}

			$msgAttacker = Application::getModel("game/message");
			$msgAttacker->setReceiver($event->getDestinationUserId())
				->setMode(Bengine_Game_Model_Message::FLEET_REPORTS_FOLDER_ID)
				->setSubject(Core::getLang()->get("MD_SUBJECT"))
				->setMessage($message)
				->setSendToOutbox(false);
			$msgDefender = clone $msgAttacker;
			$msgDefender->setReceiver($event->getUserid());
			$msgAttacker->send();
			$msgDefender->send();
		}
		else
		{
			$assault->finish();
		}

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
			$this->_targetType == "moon" &&
			!$this->_target["umode"] &&
			!empty($this->_ships[self::DEATH_STAR_CONSTRUCTION_ID]) &&
			!$this->isNewbieProtected() &&
			!Game::attackingStoppageEnabled())
		{
			return true;
		}
		return false;
	}
}
?>