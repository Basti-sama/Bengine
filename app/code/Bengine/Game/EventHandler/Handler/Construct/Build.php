<?php
/**
 * Handler to build constructions.
 *
 * @package Bengine
 * @copyright Copyright protected by / Urheberrechtlich geschützt durch "Sebastian Noll" <snoll@4ym.org>
 * @version $Id: Build.php 19 2011-05-27 10:30:33Z secretchampion $
 */

class Bengine_Game_EventHandler_Handler_Construct_Build extends Bengine_Game_EventHandler_Handler_Abstract
{
	/**
	 * (non-PHPdoc)
	 * @see app/code/Bengine/EventHandler/Handler/Bengine_Game_EventHandler_Handler_Abstract#_execute($event, $data)
	 */
	protected function _execute(Bengine_Game_Model_Event $event, array $data)
	{
		Hook::event("EhUpgradeBuilding", array($event, &$data, $this));
		if(!$event->getPlanetid())
		{
			return $this;
		}
		$points = ($data["metal"] + $data["silicon"] + $data["hydrogen"]) / 1000;
		$select = new Recipe_Database_Select();
		$select->from("building2planet")
			->attributes(array("level"))
			->where("buildingid = ?", $data["buildingid"])
			->where("planetid = ?", $event->getPlanetid());
		if($row = Core::getDB()->fetch($select->getResource()))
		{
			Core::getQuery()->update("building2planet", array("level"), array($data["level"]), "buildingid = '".$data["buildingid"]."' AND planetid = '".$event->getPlanetid()."'");
			Core::getDB()->query("UPDATE ".PREFIX."user SET points = points + '".$points."' WHERE userid = '".$event->getUserid()."'");
		}
		else if($data["level"] == 1)
		{
			Core::getQuery()->insert("building2planet", array("planetid", "buildingid", "level"), array($event->getPlanetid(), $data["buildingid"], 1));
			Core::getDB()->query("UPDATE ".PREFIX."user SET points = points + '".$points."' WHERE userid = '".$event->getUserid()."'");
		}
		return $this;
	}

	/**
	 * (non-PHPdoc)
	 * @see app/code/Bengine/EventHandler/Handler/Bengine_Game_EventHandler_Handler_Abstract#_add($event, $data)
	 */
	protected function _add(Bengine_Game_Model_Event $event, array $data)
	{
		$collection = Game::getCollection("game/event");
		$collection->addUserFilter($event->getUserid())
			->addPlanetFilter($event->getPlanetid())
			->addBaseTypeFilter($event->getMode);
		$_event = $collection->getFirstItem(new Object());
		if($_event->getEventid())
		{
			return false;
		}
		$this->removeResourceFromPlanet($data);
		return $this;
	}

	/**
	 * (non-PHPdoc)
	 * @see app/code/Bengine/EventHandler/Handler/Bengine_Game_EventHandler_Handler_Abstract#_remove($event, $data)
	 */
	protected function _remove(Bengine_Game_Model_Event $event, array $data)
	{
		$_event = Game::getModel("game/event")->load($event->getId());
		if(!$_event->getId())
		{
			return false;
		}
		$this->addResourceToPlanet($data);
		return $this;
	}
}
?>