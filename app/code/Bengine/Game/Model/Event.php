<?php
/**
 * Event model.
 *
 * @package Bengine
 * @copyright Copyright protected by / Urheberrechtlich geschützt durch "Sebastian Noll" <snoll@4ym.org>
 * @version $Id: Event.php 8 2010-10-17 20:55:04Z secretchampion $
 */

class Bengine_Game_Model_Event extends Recipe_Model_Abstract
{
	/**
	 * (non-PHPdoc)
	 * @see lib/Object#init()
	 */
	protected function init()
	{
		$this->setTableName("events");
		$this->setPrimaryKey("eventid");
		$this->setModelName("game/event");
		return parent::init();
	}

	/**
	 * Retrieves the event handler for this event.
	 *
	 * @return Bengine_Game_EventHandler_Handler_Abstract
	 */
	public function getEventHandler()
	{
		if(!$this->exists("event_handler"))
		{
			$this->set("event_handler", Bengine_Game_EventHandler_Static::getHandlerObject($this->getMode(), $this));
		}
		return $this->get("event_handler");
	}

	/**
	 * Executes this event.
	 *
	 * @return Bengine_Game_Model_Event
	 */
	public function execute()
	{
		try {
			$this->getEventHandler()
				->execute();
		} catch (Exception $e) {
			$this->set("prev_rc", "error");
			$this->save();
			Logger::addMessage($e->getMessage());
		}
		return $this;
	}

	/**
	 * Removes this event from handler.
	 *
	 * @return Bengine_Game_Model_Event
	 */
	public function removeFromHandler()
	{
		if($this->getEventid())
		{
			$this->getEventHandler()
				->remove();
		}
		return $this;
	}

	/**
	 * Retrieves a model of the origin planet.
	 *
	 * @return Bengine_Game_Model_Planet
	 */
	public function getPlanet()
	{
		if(!$this->exists("planet"))
		{
			$this->set("planet", Application::getModel("game/planet")->load($this->getPlanetid()));
		}
		return $this->get("planet");
	}

	/**
	 * Retrieves a model of the user.
	 *
	 * @return Bengine_Game_Model_User
	 */
	public function getUser()
	{
		if(!$this->exists("user"))
		{
			$this->set("user", Application::getModel("game/user")->load($this->getUserid()));
		}
		return $this->get("user");
	}

	/**
	 * Retrieves a model of the destination planet.
	 *
	 * @return Bengine_Game_Model_Planet
	 */
	public function getDestinationPlanet()
	{
		if(!$this->exists("destination_planet"))
		{
			$this->set("destination_planet", Application::getModel("game/planet")->load($this->getDestination()));
		}
		return $this->get("destination_planet");
	}

	/**
	 * Retrieves a model of the destination user.
	 *
	 * @return Bengine_Game_Model_User
	 */
	public function getDestinationUser()
	{
		if(!$this->exists("destination_user"))
		{
			$this->set("destination_user", Application::getModel("game/user")->load($this->getDestinationUserId()));
		}
		return $this->get("destination_user");
	}

	/**
	 * Wrapper for setUser().
	 *
	 * @param integer	User id
	 *
	 * @return Bengine_Game_Model_Event
	 */
	public function setUserid($userid)
	{
		$this->set("user", $userid);
		return $this;
	}

	/**
	 * Returns all ships as a readable string
	 *
	 * @return string
	 */
	public function getFleetString()
	{
		if(!$this->exists("fleet_string"))
		{
			$str = "";
			$ships = $this->getData("ships");
			if(is_array($ships))
			{
				foreach($ships as $ship)
				{
					$str .= Core::getLanguage()->getItem($ship["name"]).": ".$ship["quantity"]." ";
				}
				$str = trim($str);
			}
			$this->set("fleet_string", $str);
		}
		return $this->get("fleet_string");
	}

	/**
	 * Returns the full fleet quantity.
	 *
	 * @return integer
	 */
	public function getFleetQuantity()
	{
		if(!$this->exists("fleet_quantity"))
		{
			$qty = 0;
			$ships = $this->getData("ships");
			if(is_array($ships))
			{
				foreach($ships as $ship)
				{
					$qty += (int) $ship["quantity"];
				}
			}
			$this->set("fleet_quantity", $qty);
		}
		return $this->get("fleet_quantity");
	}

	/**
	 * Returns the formatted fleet quantity.
	 *
	 * @return string
	 */
	public function getFormattedFleetQuantity()
	{
		return fNumber($this->getFleetQuantity());
	}

	/**
	 * Returns the translated event mode name.
	 *
	 * @return string
	 */
	public function getModeName()
	{
		return Game::getMissionName($this->getMode());
	}

	/**
	 * Returns the original mode name (if return fly).
	 *
	 * @return integer
	 */
	public function getOrgMode()
	{
		return $this->getData("oldmode");
	}

	/**
	 * Returns a data parameter.
	 *
	 * @param string	Parameter name [optional]
	 * @param mixed		Default return value [optional]
	 *
	 * @return array|mixed
	 */
	public function getData($param = null, $default = null)
	{
		if(!is_array($this->get("data")))
		{
			$this->set("data", unserialize($this->get("data")));
		}
		if(is_null($param))
		{
			return $this->get("data");
		}
		return (isset($this->_data["data"][$param])) ? $this->_data["data"][$param] : $default;
	}

	/**
	 * Returns the planet's coordinates.
	 *
	 * @param boolean	As link [optional]
	 * @param boolean	Session id wildcard [optional]
	 *
	 * @return string
	 */
	public function getPlanetCoords($link = true, $sidWc = false)
	{
		$galaxy = $this->getGalaxy();
		$system = $this->getSystem();
		$position = $this->getPosition();
		if(!$galaxy || !$system || !$position)
		{
			$galaxy = $this->getData("sgalaxy");
			$system = $this->getData("ssystem");
			$position = $this->getData("sposition");
		}
		if($link)
		{
			return getCoordLink($galaxy, $system, $position, $sidWc);
		}
		return $galaxy.":".$system.":".$position;
	}

	/**
	 * Returns the destination's coordinates.
	 *
	 * @param boolean	As link [optional]
	 * @param boolean	Session id wildcard [optional]
	 *
	 * @return string
	 */
	public function getDestinationCoords($link = true, $sidWc = false)
	{
		$galaxy = $this->getGalaxy2();
		$system = $this->getSystem2();
		$position = $this->getPosition2();
		if(!$galaxy || !$system || !$position)
		{
			$galaxy = $this->getData("galaxy");
			$system = $this->getData("system");
			$position = $this->getData("position");
		}
		if($link)
		{
			return getCoordLink($galaxy, $system, $position, $sidWc);
		}
		return $galaxy.":".$system.":".$position;
	}

	/**
	 * Returns the formatted time, when event will be finished.
	 *
	 * @return string
	 */
	public function getFormattedTime()
	{
		return Date::timeToString(1, $this->getTime());
	}

	/**
	 * Returns the formatted time, when event will be finished.
	 *
	 * @return string
	 */
	public function getFormattedStartTime()
	{
		return Date::timeToString(1, $this->getStart());
	}

	/**
	 * Returns the time of a fleet when returning.
	 *
	 * @return int
	 */
	public function getReturnTime()
	{
		return $this->getEventHandler()->getReturnTime();
	}

	/**
	 * Returns the time left before event will be completed.
	 *
	 * @return integer
	 */
	public function getTimeLeft()
	{
		return $this->getTime() - TIME;
	}

	/**
	 * Returns the formatted time left before event will be completed.
	 *
	 * @return string
	 */
	public function getFormattedTimeLeft()
	{
		return getTimeTerm($this->getTimeLeft());
	}

	/**
	 * Returns the translated original mode name.
	 *
	 * @return string
	 */
	public function getOrgModeName()
	{
		return Game::getMissionName($this->getOrgMode());
	}

	/**
	 * (non-PHPdoc)
	 * @see app/code/Bengine/Model/Bengine_Game_Model_Abstract#_beforeSave()
	 * TODO
	 */
	protected function _beforeSave()
	{
		$this->set("data", serialize($this->get("data")));
		$this->setStart(TIME);
		return parent::_beforeSave();
	}

	/**
	 * (non-PHPdoc)
	 * @see app/code/Bengine/Model/Bengine_Game_Model_Abstract#_afterSave()
	 * TODO
	 */
	protected function _afterSave()
	{
		$this->set("data", unserialize($this->get("data")));
		return parent::_afterSave();
	}

	/**
	 * Returns the CSS class.
	 *
	 * @return string
	 */
	public function getCssClass()
	{
		$code = explode("/", $this->getCode());
		$scope = ($this->getUserid() == Core::getUser()->get("userid")) ? "own-fleet" : "foreign-fleet";
		return strtolower($scope." ".$code[1]);
	}

	/**
	 * @return string
	 */
	public function getDestinationPlanetname()
	{
		if($this->get("destination_destroyed"))
		{
			return Core::getLang()->get("DESTROYED_PLANET");
		}
		return $this->get("destination_planetname");
	}

	/**
	 * @return string
	 */
	public function getPlanetname()
	{
		if($this->get("destroyed"))
		{
			return Core::getLang()->get("DESTROYED_PLANET");
		}
		return $this->get("planetname");
	}
}
?>