<?php
/**
 * Abstract event handler.
 *
 * @package Bengine
 * @copyright Copyright protected by / Urheberrechtlich geschützt durch "Sebastian Noll" <snoll@4ym.org>
 * @version $Id: Abstract.php 19 2011-05-27 10:30:33Z secretchampion $
 */

abstract class Bengine_Game_EventHandler_Handler_Abstract
{
	/**
	 * Event ID for return flight.
	 */
	const RETURN_EVENT_ID = 20;

	/**
	 * Holds the event.
	 *
	 * @var Bengine_Game_Model_Event
	 */
	protected $event = null;

	/**
	 * Constructor.
	 *
	 * @param Bengine_Game_Model_Event $event		The event to be executed.
	 *
	 * @return Bengine_Game_EventHandler_Handler_Abstract
	 */
	public function __construct(Bengine_Game_Model_Event $event = null)
	{
		if(!is_null($event))
		{
			$this->setEvent($event);
		}
		return $this;
	}

	/**
	 * Sets the event.
	 *
	 * @param Bengine_Game_Model_Event $event
	 *
	 * @return Bengine_Game_EventHandler_Handler_Abstract
	 */
	public function setEvent(Bengine_Game_Model_Event $event)
	{
		$this->event = $event;
		return $this;
	}

	/**
	 * Returns the event.
	 *
	 * @return Bengine_Game_Model_Event
	 */
	public function getEvent()
	{
		return $this->event;
	}

	/**
	 * Removes resources from planet.
	 *
	 * @param array $data		Data array containing the resources to remove
	 *
	 * @return Bengine_Game_EventHandler_Handler_Abstract
	 */
	protected function removeResourceFromPlanet(array $data)
	{
		$planet = Game::getPlanet();
		$planet->setData("metal", $planet->getData("metal") - $data["metal"]);
		$planet->setData("silicon", $planet->getData("silicon") - $data["silicon"]);
		$planet->setData("hydrogen", $planet->getData("hydrogen") - $data["hydrogen"]);
		if(!isset($data["dont_save_resources"]) || !$data["dont_save_resources"])
		{
			Core::getQuery()->update("planet", array("metal" => $planet->getData("metal"), "silicon" => $planet->getData("silicon"), "hydrogen" => $planet->getData("hydrogen")), "planetid = ?", array(Core::getUser()->get("curplanet")));
		}
		return $this;
	}

	/**
	 * Adds resources to a planet.
	 *
	 * @param array $data		Data array containing the resources to add
	 *
	 * @return Bengine_Game_EventHandler_Handler_Abstract
	 */
	protected function addResourceToPlanet(array $data)
	{
		Core::getDB()->query("UPDATE ".PREFIX."planet SET metal = metal + ?, silicon = silicon + ?, hydrogen = hydrogen + ? WHERE planetid = ?", array($data["metal"], $data["silicon"], $data["hydrogen"], $this->getEvent()->getPlanetid()));
		return $this;
	}

	/**
	 * Sends a fleet back to its original planet.
	 *
	 * @param array $data Data array
	 * @param integer|boolean $time Time [optional]
	 * @param integer|boolean $startPlanet Start planet id [optional]
	 * @param integer|boolean $destinationPlanet Destination planet id [optional]
	 *
	 * @return Bengine_Game_EventHandler_Handler_Abstract
	 */
	protected function sendBack(array $data = null, $time = false, $startPlanet = false, $destinationPlanet = false)
	{
		$event = $this->getEvent();
		if(is_null($data))
		{
			$data = $event->getData();
		}
		$data["oldmode"] = $event->getMode();
		if(count($data["ships"]) == 0)
		{
			return $this;
		}

		$time = ($time === false) ? TIME + (TIME - $event->getStart()) : (int) $time;
		$startPlanet = ($startPlanet === false) ? $event->getDestination() : $startPlanet;
		$destinationPlanet = ($destinationPlanet === false) ? $event->getPlanetid() : $destinationPlanet;

		$rEvent = Game::getModel("game/event");
		$rEvent->setMode(self::RETURN_EVENT_ID)
			->setTime($time)
			->setPlanetid($startPlanet)
			->setDestination($destinationPlanet)
			->setUser($event->getUserid())
			->set("data", $data);
		$rEvent->save();
		return $this;
	}

	/**
	 * Prepares a fleet for launch (remove ships from planet, extract consumption, ...)
	 *
	 * @param array $data
	 * @throws Recipe_Exception_Generic
	 * @return Bengine_Game_EventHandler_Handler_Abstract
	 */
	protected function prepareFleet(array $data)
	{
		foreach($data["ships"] as $unit_id => $ships)
		{
			$result = Core::getQuery()->select("unit2shipyard", array("quantity"), "", "unitid = '".$unit_id."' AND planetid = '".Game::getPlanet()->getPlanetId()."'");
			$availQty = $result->fetchColumn();
			$result->closeCursor();
			if($availQty < $ships["quantity"])
			{
				throw new Recipe_Exception_Generic("Sorry, you have been attacked. Process stopped.");
			}
			Core::getDB()->query("UPDATE ".PREFIX."unit2shipyard SET quantity = quantity - ? WHERE unitid = ? AND planetid = ?", array($ships["quantity"], $unit_id, Game::getPlanet()->getPlanetId()));
		}
		$this->removeResourceFromPlanet($data);
		Core::getQuery()->delete("unit2shipyard", "quantity = '0'");
		return $this;
	}

	/**
	 * Starts the execution process.
	 *
	 * @return Bengine_Game_EventHandler_Handler_Abstract
	 */
	public function execute()
	{
		$this->_execute($this->getEvent(), $this->getEvent()->getData());
		return $this;
	}

	/**
	 * Adds an event.
	 *
	 * @param integer $mode Mode id
	 * @param integer $time Time when event will be triggered
	 * @param integer $planetid Planet where event has been triggered
	 * @param integer $userid User ID
	 * @param integer $destination Destination planet (just for fleet events)
	 * @param array $data Event-related data
	 *
	 * @return Bengine_Game_EventHandler_Handler_Abstract
	 */
	public function add($mode, $time, $planetid, $userid, $destination, array $data)
	{
		$event = Game::getModel("game/event");
		$this->setEvent($event);
		$event->setMode($mode)
			->setTime($time)
			->setPlanetid($planetid)
			->setUser($userid)
			->setDestination($destination)
			->set("data", $data);
		$rollback = $this->_add($event, $event->getData());
		Hook::event("EhAddEvent", array($event));
		if($rollback !== false)
		{
			$event->save();
		}
		return $this;
	}

	/**
	 * Removes an event.
	 *
	 * @return Bengine_Game_EventHandler_Handler_Abstract
	 */
	public function remove()
	{
		$rollback = $this->_remove($this->getEvent(), $this->getEvent()->getData());
		Hook::event("EhRemoveEvent", array($this->getEvent()));
		if($rollback !== false)
		{
			$this->getEvent()->delete();
		}
		return $this;
	}

	/**
	 * Contains the logical function to execute an event.
	 *
	 * @param Bengine_Game_Model_Event $event	Event model
	 * @param array $data						Data array
	 *
	 * @return Bengine_Game_EventHandler_Handler_Abstract
	 */
	abstract protected function _execute(Bengine_Game_Model_Event $event, array $data);

	/**
	 * Contains the logical function to add an event.
	 *
	 * @param Bengine_Game_Model_Event $event	Event model
	 * @param array $data						Data array
	 *
	 * @return Bengine_Game_EventHandler_Handler_Abstract
	 */
	abstract protected function _add(Bengine_Game_Model_Event $event, array $data);

	/**
	 * Contains the logical function to remove an event.
	 *
	 * @param Bengine_Game_Model_Event $event	Event model
	 * @param array $data						Data array
	 *
	 * @return Bengine_Game_EventHandler_Handler_Abstract
	 */
	abstract protected function _remove(Bengine_Game_Model_Event $event, array $data);
}
?>