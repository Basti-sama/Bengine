<?php
/**
 * Event collection.
 *
 * @package Bengine
 * @copyright Copyright protected by / Urheberrechtlich geschützt durch "Sebastian Noll" <snoll@4ym.org>
 * @version $Id: Event.php 18 2011-05-24 14:31:39Z secretchampion $
 */

class Bengine_Game_Model_Collection_Event extends Recipe_Model_Collection_Abstract
{
	/**
	 * Max. simultaneously executed events.
	 *
	 * @var integer
	 */
	const MAX_EVENT_EXECUTION = 20;

	/**
	 * Prepares expired events.
	 *
	 * @param string $raceConditionKey	Race condition key
	 * @param int $max					Maximum events to select
	 *
	 * @return Bengine_Game_Model_Collection_Event
	 */
	public function addRaceConditionFilter($raceConditionKey, $max = self::MAX_EVENT_EXECUTION)
	{
		$max = (int) $max;
		Core::getQuery()->update("events", array("prev_rc" => $raceConditionKey), "prev_rc IS NULL AND time <= '".TIME."' ORDER BY eventid ASC LIMIT ".$max);
		$this->getSelect()->where(array("e" => "prev_rc"), $raceConditionKey);
		$this->addTimeOrder();
		return $this;
	}

	/**
	 * Selects only events with no race condition.
	 *
	 * @return Bengine_Game_Model_Collection_Event
	 */
	public function addNoRaceConditionFilter()
	{
		$this->getSelect()->where(array("e" => "prev_rc"));
		return $this;
	}

	/**
	 * Orders the result by time.
	 *
	 * @param string $order
	 * @return Bengine_Game_Model_Collection_Event
	 */
	public function addTimeOrder($order = "ASC")
	{
		$this->getSelect()->order(array("e" => "time"), $order)
			->order(array("e" => "eventid"), $order);
		return $this;
	}

	/**
	 * Executes all events.
	 *
	 * @return Bengine_Game_Model_Collection_Event
	 */
	public function executeAll()
	{
		$this->load();
		/* @var Bengine_Game_Model_Event $event */
		foreach($this->getItems() as $event)
		{
			$event->execute();
		}
		return $this;
	}

	/**
	 * Selects all events from a user.
	 *
	 * @param integer|Bengine_Game_Model_User	The user to select
	 *
	 * @return Bengine_Game_Model_Collection_Event
	 */
	public function addUserFilter($user)
	{
		if($user instanceof Bengine_Game_Model_User)
		{
			$user = $user->getId();
		}
		$this->getSelect()->where("(u1.userid = ? OR u2.userid = ?)", $user);
		return $this;
	}

	/**
	 * Selects all not expired events.
	 *
	 * @param int $time
	 * @return Bengine_Game_Model_Collection_Event
	 */
	public function addTimeFilter($time = TIME)
	{
		$this->getSelect()->where(array("e" => "time", ">"), $time);
		return $this;
	}

	/**
	 * Adds a planet filter.
	 *
	 * @param integer|Bengine_Game_Model_Planet
	 *
	 * @return Bengine_Game_Model_Collection_Event
	 */
	public function addPlanetFilter($planet)
	{
		if($planet instanceof Bengine_Game_Model_Planet)
		{
			$planet = $planet->getId();
		}
		$this->getSelect()->where("(e.planetid = ? OR e.destination = ?)", (int) $planet);
		return $this;
	}

	/**
	 * Adds a base type filter.
	 *
	 * @param string $typeName	fleet or construct
	 *
	 * @return Bengine_Game_Model_Collection_Event
	 */
	public function addBaseTypeFilter($typeName)
	{
		$this->getSelect()->where(array("et" => "base_type"), $typeName);
		return $this;
	}

	/**
	 * Adds a type code filter.
	 *
	 *
	 * @param string	Type code to filter
	 * @return Bengine_Game_Model_Collection_Event
	 */
	public function addTypeFilter($typeCode)
	{
		$this->getSelect()->where(array("et" => "code"), $typeCode);
		return $this;
	}

	/**
	 * Excludes a type.
	 *
	 * @param string	Type code to filter
	 * @return Bengine_Game_Model_Collection_Event
	 */
	public function excludeType($typeCode)
	{
		$this->getSelect()->where(array("et" => "code", "!="), $typeCode);
		return $this;
	}

	/**
	 * Applies a data search filter.
	 *
	 * @param string $query The condition to search for
	 * @return Bengine_Game_Model_Collection_Event
	 */
	public function addDataSearchFilter($query)
	{
		$this->getSelect()->where(array("e" => "data", "LIKE"), "%".$query."%");
		return $this;
	}

	/**
	 * Filters events that are relevant for vacation mode check.
	 *
	 * @param string $userId User ID
	 * @return Bengine_Game_Model_Collection_Event
	 */
	public function addVacationModeFilter($userId)
	{
		$this->addTimeOrder()
			->addTimeFilter()
			->addNoRaceConditionFilter();
		$this->getSelect()->where("(u1.userid = ? OR (u2.userid = ? AND et.code != 'recycling'))", (int) $userId);
		return $this;
	}

	/**
	 * Adds a parent event id filter.
	 *
	 * @param integer|Bengine_Game_Model_Event
	 *
	 * @return Bengine_Game_Model_Collection_Event
	 */
	public function addParentIdFilter($parent)
	{
		if($parent instanceof Bengine_Game_Model_Event)
		{
			$parent = $parent->getId();
		}
		$this->getSelect()->where("e.parent_id = ?", $parent);
		return $this;
	}
}
?>