<?php
/**
 * Handler for colonization.
 *
 * @package Bengine
 * @copyright Copyright protected by / Urheberrechtlich geschützt durch "Sebastian Noll" <snoll@4ym.org>
 * @version $Id: Colonize.php 19 2011-05-27 10:30:33Z secretchampion $
 */

class Bengine_Game_EventHandler_Handler_Fleet_Colonize extends Bengine_Game_EventHandler_Handler_Fleet_Abstract
{
	/**
	 * Colony ship id.
	 *
	 * @var integer
	 */
	const COLONY_SHIP_ID = 36;

	/**
	 * @param Bengine_Game_Model_Event $event
	 * @param array $data
	 * @return Bengine_Game_EventHandler_Handler_Fleet_Colonize
	 */
	protected function _execute(Bengine_Game_Model_Event $event, array $data)
	{
		$id = self::COLONY_SHIP_ID;
		Hook::event("EhColonize", array($event, &$data, $this));
		$where = Core::getDB()->quoteInto("galaxy = ? AND system = ? AND position = ? AND planetid != ?", array($data["galaxy"], $data["system"], $data["position"], 0));
		$_result = Core::getQuery()->select("galaxy", "planetid", "", $where);
		if($_result->rowCount() == 0)
		{
			$_result = Core::getQuery()->select("planet", "planetid", "", Core::getDB()->quoteInto("userid = ? AND ismoon = 0", $event["userid"]));
			if($_result->rowCount() < Core::getOptions()->get("MAX_PLANETS"))
			{
				$colony = new Bengine_Game_Planet_Creator($event["userid"], $data["galaxy"], $data["system"], $data["position"]);

				$colonyShip = $data["ships"][$id];
				$_result = Core::getQuery()->select("construction", array("basic_metal", "basic_silicon", "basic_hydrogen"), "", Core::getDB()->quoteInto("buildingid = ?", $colonyShip["id"]));
				$shipData = $_result->fetchRow();
				$_result->closeCursor();
				$points = ($shipData["basic_metal"] + $shipData["basic_silicon"] + $shipData["basic_hydrogen"]) * $colonyShip["quantity"] / 1000;
				$fpoints = $colonyShip["quantity"];
				Core::getDB()->query("UPDATE ".PREFIX."user SET points = points - ?, fpoints = fpoints - ? WHERE userid = ?", array($points, $fpoints, $event["userid"]));
				if($data["ships"][$id]["quantity"] > 1)
				{
					$data["ships"][$id]["quantity"]--;
				}
				else
				{
					$data["ships"][$id] = null;
					unset($data["ships"][$id]);
				}
				if(count($data["ships"]) > 0)
				{
					foreach($data["ships"] as $ship)
					{
						Core::getQuery()->insert("unit2shipyard", array("unitid" => $ship["id"], "planetid" => $colony->getPlanetId(), "quantity" => $ship["quantity"]));
					}
				}
			}
			else
			{
				$this->sendBack($data, $data["time"] + $event["time"], null, $event["planetid"]);
				$data["success"] = "empire";
			}
		}
		else
		{
			$this->sendBack($data, $data["time"] + $event["time"], null, $event["planetid"]);
			$data["success"] = "occupied";
		}
		new Bengine_Game_AutoMsg($event["mode"], $event["userid"], $event["time"], $data);
		return $this;
	}

	/**
	 * @param Bengine_Game_Model_Event $event
	 * @param array $data
	 * @return Bengine_Game_EventHandler_Handler_Fleet_Colonize
	 */
	protected function _add(Bengine_Game_Model_Event $event, array $data)
	{
		if($data["metal"] > 0 || $data["silicon"] > 0 || $data["hydrogen"] > 0)
		{
			Logger::addMessage("COLONIZE_RESOURCE_WARNING", "info");
		}
		$data["metal"] = 0;
		$data["silicon"] = 0;
		$data["hydrogen"] = 0;
		$event->setData($data);
		$this->prepareFleet($data);
		return $this;
	}

	/**
	 * @param Bengine_Game_Model_Event $event
	 * @param array $data
	 * @return Bengine_Game_EventHandler_Handler_Fleet_Colonize
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
		if(empty($this->_target["planetid"]) && isset($this->_ships[self::COLONY_SHIP_ID]) && $this->_targetType != "tf")
		{
			return true;
		}
		return false;
	}
}
?>