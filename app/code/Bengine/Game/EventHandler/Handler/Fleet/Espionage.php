<?php
/**
 * Handler for espionage.
 *
 * @package Bengine
 * @copyright Copyright protected by / Urheberrechtlich geschützt durch "Sebastian Noll" <snoll@4ym.org>
 * @version $Id: Espionage.php 19 2011-05-27 10:30:33Z secretchampion $
 */

class Bengine_Game_EventHandler_Handler_Fleet_Espionage extends Bengine_Game_EventHandler_Handler_Fleet_Abstract
{
	/**
	 * Construction ID of espionage probes.
	 *
	 * @var integer
	 */
	const ESPIONAGE_PROBE_ID = 38;

	/**
	 * (non-PHPdoc)
	 * @see app/code/Bengine/EventHandler/Handler/Bengine_Game_EventHandler_Handler_Abstract#_execute($event, $data)
	 */
	protected function _execute(Bengine_Game_Model_Event $event, array $data)
	{
		Hook::event("EhEspionage", array($event, &$data, $this));
		$espReport = new Bengine_Game_Espionage_Report($event["destination"], $event["userid"], $event["destination_user_id"], $event["destination_username"], $data["ships"][38]["quantity"]);
		$data["destinationplanet"] = $espReport->getPlanetname();
		$data["suser"] = $event["username"];
		$data["planetname"] = $event["planetname"];
		$data["defending_chance"] = $espReport->getChance();
		$data["probes_lost"] = $espReport->getProbesLost();
		$data["event"] = $event;
		if($event["destination_user_id"])
		{
			new Bengine_Game_AutoMsg($event["mode"], $event["destination_user_id"], $event["time"], $data);
		}
		if($espReport->getProbesLost())
		{
			$points = 0; $fpoints = 0;
			$tfMetal = 0; $tfSilicon = 0;
			$intoTF = floatval(Core::getOptions()->get("FLEET_INTO_DEBRIS"));
			foreach($data["ships"] as $key => $ship)
			{
				$_result = Core::getQuery()->select("construction", array("basic_metal", "basic_silicon", "basic_hydrogen"), "", Core::getDB()->quoteInto("buildingid = ?", $key));
				$shipData = $_result->fetchRow();
				$_result->closeCursor();
				$points += ($shipData["basic_metal"] + $shipData["basic_silicon"] + $shipData["basic_hydrogen"]) * $ship["quantity"] / 1000;
				$fpoints += $ship["quantity"];
				$tfMetal = $shipData["basic_metal"] * $ship["quantity"] * $intoTF;
				$tfSilicon = $shipData["basic_silicon"] * $ship["quantity"] * $intoTF;
			}
			$what = $event->get("destination_ismoon") ? "moonid" : "planetid";
			Core::getDB()->query("UPDATE ".PREFIX."galaxy SET metal = metal + ?, silicon = silicon + ? WHERE ".$what." = ?", array($tfMetal, $tfSilicon, $event["destination"]));
			Core::getDB()->query("UPDATE ".PREFIX."user SET points = points - ?, fpoints = fpoints - ? WHERE userid = ?", array($points, $fpoints, $event["userid"]));
		}
		else
		{
			$data["nomessage"] = true;
			$this->sendBack($data, $data["time"] + $event["time"], $event["destination"], $event["planetid"]);
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
		if(!empty($this->_target["userid"]) && !$this->isNewbieProtected() &&
			$this->_target["userid"] != Core::getUser()->get("userid") &&
			$this->_targetType != "tf" && $this->_checkProbes() &&
			!$this->_target["umode"] &&
			Core::getOptions()->get("ATTACKING_STOPPAGE") != 1)
		{
			return true;
		}
		return false;
	}

	/**
	 * Checks the assigned ships for espionage probes.
	 *
	 * @return boolean
	 */
	protected function _checkProbes()
	{
		if(count($this->_ships) > 1 || empty($this->_ships[self::ESPIONAGE_PROBE_ID]) || $this->_ships[self::ESPIONAGE_PROBE_ID]["quantity"] <= 0)
		{
			return false;
		}
		return true;
	}
}
?>