<?php
/**
 * Handler to upgrade researches.
 *
 * @package Bengine
 * @copyright Copyright protected by / Urheberrechtlich geschützt durch "Sebastian Noll" <snoll@4ym.org>
 * @version $Id: Research.php 19 2011-05-27 10:30:33Z secretchampion $
 */

class Bengine_Game_EventHandler_Handler_Construct_Research extends Bengine_Game_EventHandler_Handler_Abstract
{
	/**
	 * (non-PHPdoc)
	 * @see app/code/Bengine/EventHandler/Handler/Bengine_Game_EventHandler_Handler_Abstract#_execute($event, $data)
	 */
	protected function _execute(Bengine_Game_Model_Event $event, array $data)
	{
		Hook::event("EbUpgradeResearch", array($event, &$data, $this));
		$points = ($data["metal"] + $data["silicon"] + $data["hydrogen"]) / 1000;

		$select = new Recipe_Database_Select();
		$select->from("research2user")
			->attributes(array("level"))
			->where("buildingid = ?", $data["buildingid"])
			->where("userid = ?", $event->getUserid());
		if($row = Core::getDB()->fetch($select->getResource()))
		{
			Core::getQuery()->update("research2user", "level", $data["level"], "buildingid = '".$data["buildingid"]."' AND userid = '".$event->getUserid()."'");
			Core::getDB()->query("UPDATE ".PREFIX."user SET points = points + '".$points."', rpoints = rpoints + '1' WHERE userid = '".$event->getUserid()."'");
		}
		else if($data["level"] == 1)
		{
			Core::getQuery()->insert("research2user", array("buildingid", "userid", "level"), array($data["buildingid"], $event->getUserid(), 1));
			Core::getDB()->query("UPDATE ".PREFIX."user SET points = points + '".$points."', rpoints = rpoints + '1' WHERE userid = '".$event->getUserid()."'");
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