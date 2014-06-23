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
	 * @param Bengine_Game_Model_Event $event
	 * @param array $data
	 * @return Bengine_Game_EventHandler_Handler_Construct_Shipyard
	 */
	protected function _execute(Bengine_Game_Model_Event $event, array $data)
	{
		Hook::event("EhAddShip", array($event, &$data, $this));
		if(!$event->getPlanetid())
		{
			return $this;
		}
		// >>> This ensures compatibility with 0.42 and older. Will be removed in 0.50.
		if(empty($data["one"]))
		{
			return $this->_executeOld($event, $data);
		}
		// <<< End 0.42 compatibility
		$timespan = TIME - $event->get("time");
		$quantity = floor($timespan / $data["one"])+1;
		$quantity = min($quantity, $data["quantity"]);
		$data["quantity"] -= $quantity;
		$points = $data["points"] * $quantity;
		$result = Core::getQuery()->select("unit2shipyard", "quantity", "", Core::getDB()->quoteInto("unitid = ? AND planetid = ?", array($data["buildingid"], $event->getPlanetid())));
		if($result->rowCount() > 0)
		{
			Core::getDB()->query("UPDATE ".PREFIX."unit2shipyard SET quantity = quantity + ? WHERE planetid = ? AND unitid = ?", array($quantity, $event->getPlanetid(), $data["buildingid"]));
		}
		else
		{
			Core::getQuery()->insert("unit2shipyard", array("unitid" => $data["buildingid"], "planetid" => $event->getPlanetid(), "quantity" => $quantity));
		}
		$result->closeCursor();
		$fpoints = $quantity;
		$dpoints = 0;
		if($event["mode"] == 5)
		{
			$fpoints = 0;
			$dpoints = $quantity;
		}
		Core::getDB()->query("UPDATE ".PREFIX."user SET points = points + ?, fpoints = fpoints + ?, dpoints = dpoints + ? WHERE userid = ?", array($points, $fpoints, $dpoints, $event->getUserid()));
		if($data["quantity"] > 0)
		{
			$event->set(array(
				"start" => TIME,
				"time" => $event->get("time") + $data["one"] * $quantity,
				"prev_rc" => null,
				"data" => $data,
			));
			$event->save();
		}
		return $this;
	}

	/**
	 * @param Bengine_Game_Model_Event $event
	 * @param array $data
	 * @return Bengine_Game_EventHandler_Handler_Construct_Shipyard
	 * @deprecated Will be removed in 0.50
	 */
	protected function _executeOld(Bengine_Game_Model_Event $event, array $data)
	{
		$points = $data["points"];
		$result = Core::getQuery()->select("unit2shipyard", "quantity", "", Core::getDB()->quoteInto("unitid = ? AND planetid = ?", array($data["buildingid"], $event->getPlanetid())));
		if($result->rowCount() > 0)
		{
			Core::getDB()->query("UPDATE ".PREFIX."unit2shipyard SET quantity = quantity + ? WHERE planetid = ? AND unitid = ?", array(1, $event->getPlanetid(), $data["buildingid"]));
		}
		else
		{
			Core::getQuery()->insert("unit2shipyard", array("unitid" => $data["buildingid"], "planetid" => $event->getPlanetid(), "quantity" => 1));
		}
		$result->closeCursor();
		$fpoints = 1;
		$dpoints = 0;
		if($event["mode"] == 5)
		{
			$fpoints = 0;
			$dpoints = 1;
		}
		Core::getDB()->query("UPDATE ".PREFIX."user SET points = points + ?, fpoints = fpoints + ?, dpoints = dpoints + ? WHERE userid = ?", array($points, $fpoints, $dpoints, $event->getUserid()));
		return $this;
	}

	/**
	 * (non-PHPdoc)
	 * @see app/code/Bengine/EventHandler/Handler/Bengine_Game_EventHandler_Handler_Abstract#_add($event, $data)
	 */
	protected function _add(Bengine_Game_Model_Event $event, array $data)
	{
		$planet = Game::getPlanet();
		$planet->setData("metal", $planet->getData("metal") - $data["metal"] * $data["quantity"]);
		$planet->setData("silicon", $planet->getData("silicon") - $data["silicon"] * $data["quantity"]);
		$planet->setData("hydrogen", $planet->getData("hydrogen") - $data["hydrogen"] * $data["quantity"]);
		$spec = array(
			"metal" => new Recipe_Database_Expr("metal - ?"),
			"silicon" => new Recipe_Database_Expr("silicon - ?"),
			"hydrogen" => new Recipe_Database_Expr("hydrogen - ?")
		);
		$bind = array(
			$data["metal"] * $data["quantity"],
			$data["silicon"] * $data["quantity"],
			$data["hydrogen"] * $data["quantity"],
			$event->get("planetid")
		);
		$bind = array_map("intval", $bind);
		Core::getQuery()->update("planet", $spec, "planetid = ?", $bind);
		return $this;
	}

	/**
	 * (non-PHPdoc)
	 * @see app/code/Bengine/EventHandler/Handler/Bengine_Game_EventHandler_Handler_Abstract#_remove($event, $data)
	 */
	protected function _remove(Bengine_Game_Model_Event $event, array $data)
	{
		$metal = $data["quantity"] * $data["metal"] / 100 * Core::getOptions()->get("SHIPYARD_ORDER_ABORT_PERCENT");
		$silicon = $data["quantity"] * $data["silicon"] / 100 * Core::getOptions()->get("SHIPYARD_ORDER_ABORT_PERCENT");
		$hydrogen = $data["quantity"] * $data["hydrogen"] / 100 * Core::getOptions()->get("SHIPYARD_ORDER_ABORT_PERCENT");
		Core::getDB()->query("UPDATE ".PREFIX."planet SET metal = metal + ?, silicon = silicon + ?, hydrogen = hydrogen + ? WHERE planetid = ?", array($metal, $silicon, $hydrogen, $event->getPlanetid()));
		return $this;
	}
}
?>