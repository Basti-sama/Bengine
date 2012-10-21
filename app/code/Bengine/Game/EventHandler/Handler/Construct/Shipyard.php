<?php
/**
 * Handler to build ships.
 *
 * @package Bengine
 * @copyright Copyright protected by / Urheberrechtlich geschützt durch "Sebastian Noll" <snoll@4ym.org>
 * @version $Id: Shipyard.php 19 2011-05-27 10:30:33Z secretchampion $
 */

class Bengine_Game_EventHandler_Handler_Construct_Shipyard extends Bengine_Game_EventHandler_Handler_Abstract
{
	/**
	 * (non-PHPdoc)
	 * @see app/code/Bengine/EventHandler/Handler/Bengine_Game_EventHandler_Handler_Abstract#_execute($event, $data)
	 */
	protected function _execute(Bengine_Game_Model_Event $event, array $data)
	{
		Hook::event("EhAddShip", array($event, &$data, $this));
		if(!$event->getPlanetid())
		{
			return $this;
		}
		$points = $data["points"];
		$result = Core::getQuery()->select("unit2shipyard", "quantity", "", "unitid = '".$data["buildingid"]."' AND planetid = '".$event->getPlanetid()."'");
		if(Core::getDB()->num_rows($result) > 0)
		{
			Core::getDB()->query("UPDATE ".PREFIX."unit2shipyard SET quantity = quantity + '1' WHERE planetid = '".$event->getPlanetid()."' AND unitid = '".$data["buildingid"]."'");
		}
		else
		{
			Core::getQuery()->insert("unit2shipyard", array("unitid", "planetid", "quantity"), array($data["buildingid"], $event->getPlanetid(), 1));
		}
		Core::getDB()->free_result($result);
		if($event["mode"] == 4) { $fpoints = 1; } else { $fpoints = 0; }
		Core::getDB()->query("UPDATE ".PREFIX."user SET points = points + '".$points."', fpoints = fpoints + '".$fpoints."' WHERE userid = '".$event->getUserid()."'");
		return $this;
	}

	/**
	 * (non-PHPdoc)
	 * @see app/code/Bengine/EventHandler/Handler/Bengine_Game_EventHandler_Handler_Abstract#_add($event, $data)
	 */
	protected function _add(Bengine_Game_Model_Event $event, array $data)
	{
		$this->removeResourceFromPlanet($data);
		return $this;
	}

	/**
	 * (non-PHPdoc)
	 * @see app/code/Bengine/EventHandler/Handler/Bengine_Game_EventHandler_Handler_Abstract#_remove($event, $data)
	 */
	protected function _remove(Bengine_Game_Model_Event $event, array $data)
	{
		$data["metal"] = $data["metal"] / 100 * Core::getOptions()->get("SHIPYARD_ORDER_ABORT_PERCENT");
		$data["silicon"] = $data["silicon"] / 100 * Core::getOptions()->get("SHIPYARD_ORDER_ABORT_PERCENT");
		$data["hydrogen"] = $data["hydrogen"] / 100 * Core::getOptions()->get("SHIPYARD_ORDER_ABORT_PERCENT");
		Core::getDB()->query("UPDATE ".PREFIX."planet SET metal = metal + '".$data["metal"]."', silicon = silicon + '".$data["silicon"]."', hydrogen = hydrogen + '".$data["hydrogen"]."' WHERE planetid = '".$event->getPlanetid()."'");
		return $this;
	}
}
?>