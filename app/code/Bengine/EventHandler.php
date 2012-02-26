<?php
/**
 * Handles all events.
 *
 * @package Bengine
 * @copyright Copyright protected by / Urheberrechtlich geschützt durch "Sebastian Noll" <snoll@4ym.org>
 * @version $Id: EventHandler.php 46 2011-07-30 14:38:11Z secretchampion $
 */

class Bengine_EventHandler
{
	/**
	 * Event stack holds all user-related events.
	 *
	 * @var Bengine_Model_Collection_Event
	 */
	protected $eventStack = null;

	/**
	 * Unique key to prevent race conditions.
	 *
	 * @var string
	 */
	protected $raceConditionKey = null;

	/**
	 * Stores event collections.
	 *
	 * @var array
	 */
	protected $subStacks = array();

	/**
	 * Event handler flags.
	 *
	 * @var boolean
	 */
	protected $canResearch = null, $canBuildUnits = null,
		$hasSShieldDome = null, $hasLShieldDome = null;

	/**
	 * Holds the number of missiles in the shipyard queue.
	 *
	 * @var integer
	 */
	protected $workingMissiles = null;

	/**
	 * Holds the shipyard events.
	 *
	 * @var Bengine_Model_Collection_Event
	 */
	protected $shipyardEvents = null;

	/**
	 * Constructor.
	 *
	 * @return void
	 */
	public function __construct()
	{
		$this->raceConditionKey = $this->genPrevRaceConditionKey();
		return;
	}

	/**
	 * Initializes the event handler.
	 *
	 * @return Bengine_EventHandler
	 */
	public function init()
	{
		if(!Bengine::isDbLocked())
		{
			$this->goThroughEvents();
		}
		$this->setEventStack();
		return $this;
	}

	/**
	 * Executes expired events.
	 *
	 * @return Bengine_EventHandler
	 */
	protected function goThroughEvents()
	{
		$collection = Bengine::getModel("event")->getCollection();
		$collection->addRaceConditionFilter($this->raceConditionKey);
		$collection->executeAll();
		if($collection->count() > 0)
		{
			Core::getQuery()->delete("events", "prev_rc = '".$this->raceConditionKey."'");
		}
		return $this;
	}

	/**
	 * Append an event onto the event stack.
	 *
	 * @param integer	Mode id
	 * @param integer	Time when event will be triggered
	 * @param integer	Planet where event has been triggered
	 * @param integer	Destination planet (just for fleet events)
	 * @param array		Event-related data
	 *
	 * @return Bengine_EventHandler
	 */
	public function addEvent($mode, $time, $planetid, $userid, $destination, array $data)
	{
		if(Bengine::isDbLocked())
		{
			return $this;
		}
		$handler = Bengine_EventHandler_Static::getHandlerObject($mode);
		$handler->add($mode, $time, $planetid, $userid, $destination, $data);
		return $this;
	}

	/**
	 * Removes an event from the event stack.
	 *
	 * @param integer|Bengine_Model_Event	Event id
	 *
	 * @return Bengine_EventHandler
	 */
	public function removeEvent($event)
	{
		if(!Core::getUser()->get("umode"))
		{
			if(!Bengine::isDbLocked())
			{
				if(!($event instanceof Bengine_Model_Event))
				{
					$event = Bengine::getModel("event")->load($event);
				}
				$event->removeFromHandler();
			}
		}
		return $this;
	}

	/**
	 * Generates an unique key to prevend race conditions.
	 *
	 * @return string
	 */
	protected function genPrevRaceConditionKey($size = 16)
	{
		$half = floor($size / 2);
		return Str::substring(SID, 0, $half).Str::substring(md5(microtime(true)), 0, $half);
	}

	/**
	 * Sets all user-related events.
	 *
	 * @return Bengine_EventHandler
	 */
	protected function setEventStack()
	{
		$collection = $this->getEventCollectionTemplate();
		$collection->excludeType("shipyard");
		$this->eventStack = $collection;
		return $this;
	}

	/**
	 * Returns all events for the user.
	 *
	 * @return Bengine_Model_Collection_Event
	 */
	public function getEvents()
	{
		return $this->eventStack;
	}

	/**
	 * Loads the fleets of an alliance attack.
	 *
	 * @param integer|Bengine_Model_Event		Parent event
	 *
	 * @return Bengine_Model_Collection_Event	List of all formation fleets
	 */
	public function getFormationFleets($parent)
	{
		return Bengine::getCollection("event")->addParentIdFilter($parent);
	}

	/**
	 * Loads the main fleet of an alliance attack.
	 *
	 * @param integer	Event id
	 *
	 * @return Bengine_Model_Event
	 */
	public function getMainFormationFleet($eventid)
	{
		return Bengine::getModel("event")->load($eventid);
	}

	/**
	 * Building events of the current used planet.
	 *
	 * @return mixed Event data or false.
	 */
	public function getCurPlanetBuildingEvent()
	{
		$key = "cur_planet_bulding_event";
		if(!isset($this->subStacks[$key]))
		{
			$this->subStacks[$key] = null;
			foreach($this->eventStack as $event)
			{
				$mode = $event->get("mode");
				if(($mode == 1 || $mode == 2) && $event->get("planetid") == Core::getUser()->get("curplanet"))
				{
					$this->subStacks[$key] = $event;
				}
			}
			if(empty($this->subStacks[$key]))
			{
				$this->subStacks[$key] = false;
			}
		}
		return $this->subStacks[$key];
	}

	/**
	 * Building events for all planets.
	 *
	 * @return mixed Event data or false.
	 */
	public function getBuildingEvents()
	{
		$key = "bulding_events";
		if(!isset($this->subStacks[$key]))
		{
			$this->subStacks[$key] = array();
			foreach($this->eventStack as $event)
			{
				$mode = $event->get("mode");
				if($mode == 1 || $mode == 2)
				{
					$this->subStacks[$key][] = $event;
				}
			}
			if(empty($this->subStacks[$key]))
			{
				$this->subStacks[$key] = false;
			}
		}
		return $this->subStacks[$key];
	}

	/**
	 * Current research event.
	 *
	 * @return mixed Event data or false.
	 */
	public function getResearchEvent()
	{
		$key = "research_event";
		if(!isset($this->subStacks[$key]))
		{
			$this->subStacks[$key] = null;
			foreach($this->eventStack as $event)
			{
				if($event->get("mode") == 3)
				{
					$this->subStacks[$key] = $event;
				}
			}
			if(empty($this->subStacks[$key]))
			{
				$this->subStacks[$key] = false;
			}
		}
		return $this->subStacks[$key];
	}

	/**
	 * Shipyard events for the current used planet.
	 *
	 * @return Bengine_Model_Collection_Event
	 */
	public function getShipyardEvents()
	{
		if($this->shipyardEvents === null)
		{
			$collection = $this->getEventCollectionTemplate();
			$collection->addTypeFilter("shipyard")
				->addPlanetFilter(Core::getUser()->get("curplanet"));
			$this->shipyardEvents = $collection;
		}
		return $this->shipyardEvents;
	}

	/**
	 * Own Fleet events.
	 *
	 * @return mixed Event data or false.
	 */
	public function getOwnFleetEvents()
	{
		$key = "own_fleet_events";
		if(!isset($this->subStacks[$key]))
		{
			$this->subStacks[$key] = array();
			foreach($this->eventStack as $event)
			{
				if($event->get("base_type") == "fleet" && $event->getUserid() == Core::getUser()->get("userid"))
				{
					$this->subStacks[$key][] = $event;
				}
			}
			if(empty($this->subStacks[$key]))
			{
				$this->subStacks[$key] = false;
			}
		}
		return $this->subStacks[$key];
	}

	/**
	 * Fleet events.
	 *
	 * @return mixed Event data or false.
	 */
	public function getFleetEvents()
	{
		$key = "fleet_events";
		if(!isset($this->subStacks[$key]))
		{
			$this->subStacks[$key] = array();
			foreach($this->eventStack as $event)
			{
				if($event->get("base_type") == "fleet")
				{
					$mode = $event->get("mode");
					if(($mode == 20 || $mode == 9) && $event->get("userid") != Core::getUser()->get("userid"))
					{

					}
					else
					{
						$this->subStacks[$key][] = $event;
					}
				}
			}
			if(empty($this->subStacks[$key]))
			{
				$this->subStacks[$key] = false;
			}
		}
		return $this->subStacks[$key];
	}

	/**
	 * Return fleet events of the current planet.
	 * (Used to check if colony can be deleted).
	 *
	 * @return mixed The events or false.
	 */
	public function getPlanetFleetEvents()
	{
		$key = "planet_fleet_events";
		if(!isset($this->subStacks[$key]))
		{
			$this->subStacks[$key] = array();
			foreach($this->eventStack as $event)
			{
				if(($event->get("base_type") == "fleet") && ($event->getPlanetid() == Core::getUser()->get("curplanet") || $event->getDestination() == Core::getUser()->get("curplanet")))
				{
					$this->subStacks[$key][] = $event;
				}
			}
			if(empty($this->subStacks[$key]))
			{
				$this->subStacks[$key] = false;
			}
		}
		return $this->subStacks[$key];
	}

	/**
	 * Checks if research lab is currently upgrading.
	 *
	 * @return boolean
	 */
	public function canReasearch()
	{
		if(is_null($this->canResearch))
		{
			$this->canResearch = true;
			foreach($this->eventStack as $event)
			{
				$data = $event->getData();
				if(isset($data["buildingid"]) && $data["buildingid"] == 12 && $event->get("mode") == 1)
				{
					$this->canResearch = false;
					break;
				}
			}
		}
		return $this->canResearch;
	}

	/**
	 * Checks if shipyard or nanit factory is currently upgrading.
	 *
	 * @return boolean
	 */
	public function canBuildUnits()
	{
		if(is_null($this->canBuildUnits))
		{
			$this->canBuildUnits = true;
			foreach($this->eventStack as $event)
			{
				$data = $event->getData();
				if(isset($data["buildingid"]) && ($data["buildingid"] == 7 || $data["buildingid"] == 8 || $data["buildingid"] == 59) && $event->get("planetid") == Core::getUser()->get("curplanet") && $event->get("mode") == 1)
				{
					$this->canBuildUnits = false;
					break;
				}
			}
		}
		return $this->canBuildUnits;
	}

	/**
	 * Returns an event collection default schema for the current user.
	 *
	 * @return Bengine_Model_Collection_Event
	 */
	public function getEventCollectionTemplate()
	{
		$collection = Application::getCollection("event");
		$collection->addUserFilter(Core::getUser()->get("userid"))
			->addTimeOrder()
			->addTimeFilter()
			->addNoRaceConditionFilter();
		return $collection;
	}

	/**
	 * Checks if small shield dome is in the event queue.
	 *
	 * @return boolean
	 */
	public function hasSShieldDome()
	{
		if(is_null($this->hasSShieldDome))
		{
			$collection = $this->getEventCollectionTemplate();
			$collection->addTypeFilter("shipyard")
				->addPlanetFilter(Core::getUser()->get("curplanet"))
				->addDataSearchFilter("SMALL_SHIELD");
			$this->hasSShieldDome = count($collection);
		}
		return $this->hasSShieldDome;
	}

	/**
	 * Checks if large shield dome is in the event queue.
	 *
	 * @return boolean
	 */
	public function hasLShieldDome()
	{
		if(is_null($this->hasLShieldDome))
		{
			$collection = $this->getEventCollectionTemplate();
			$collection->addTypeFilter("shipyard")
				->addPlanetFilter(Core::getUser()->get("curplanet"))
				->addDataSearchFilter("LARGE_SHIELD");
			$this->hasLShieldDome = count($collection);
		}
		return $this->hasLShieldDome;
	}

	/**
	 * Returns working missiles.
	 *
	 * @return integer Number of working missiles in shipyard queue
	 */
	public function getWorkingRockets()
	{
		if(is_null($this->workingMissiles))
		{
			$this->workingMissiles = 0;
			$collection = $this->getEventCollectionTemplate();
			$collection->addTypeFilter("shipyard")
				->addPlanetFilter(Core::getUser()->get("curplanet"))
				->addDataSearchFilter("INTERCEPTOR_ROCKET");
			$this->workingMissiles = count($collection);

			$collection = $this->getEventCollectionTemplate();
			$collection->addTypeFilter("shipyard")
				->addPlanetFilter(Core::getUser()->get("curplanet"))
				->addDataSearchFilter("INTERPLANETARY_ROCKET");
			$this->workingMissiles += count($collection)*2;
		}
		return $this->workingMissiles;
	}
}
?>