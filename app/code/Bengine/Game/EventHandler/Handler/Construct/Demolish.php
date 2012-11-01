<?php
/**
 * Handler to demolish constructions.
 *
 * @package Bengine
 * @copyright Copyright protected by / Urheberrechtlich geschützt durch "Sebastian Noll" <snoll@4ym.org>
 * @version $Id: Demolish.php 19 2011-05-27 10:30:33Z secretchampion $
 */

class Bengine_Game_EventHandler_Handler_Construct_Demolish extends Bengine_Game_EventHandler_Handler_Abstract
{
	/**
	 * (non-PHPdoc)
	 * @see app/code/Bengine/EventHandler/Handler/Bengine_Game_EventHandler_Handler_Abstract#_execute($event, $data)
	 */
	protected function _execute(Bengine_Game_Model_Event $event, array $data)
	{
		Hook::event("EhDowngradeBuilding", array($event, &$data, $this));
		if(!$event->getPlanetid())
		{
			return $this;
		}
		$points = ($data["metal"] + $data["metal"] + $data["metal"]) / 1000;
		if($data["level"] > 0)
		{
			Core::getQuery()->update("building2planet", "level", $data["level"], "buildingid = '".$data["buildingid"]."' AND planetid = '".$event->getPlanetid()."'");
		}
		else
		{
			Core::getQuery()->delete("building2planet", "buildingid = '".$data["buildingid"]."' AND planetid = '".$event->getPlanetid()."'");
		}
		Core::getDB()->query("UPDATE ".PREFIX."user SET points = points - '".$points."' WHERE userid = '".$event->getUserid()."'");
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