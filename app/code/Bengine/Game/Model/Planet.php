<?php
/**
 * Planet model.
 *
 * @package Bengine
 * @copyright Copyright protected by / Urheberrechtlich geschützt durch "Sebastian Noll" <snoll@4ym.org>
 * @version $Id: Planet.php 19 2011-05-27 10:30:33Z secretchampion $
 */

class Bengine_Game_Model_Planet extends Recipe_Model_Abstract
{
	/**
	 * @var array
	 */
	protected $buildings = array();

	/**
	 * @var array
	 */
	protected $levels = array();

	/**
	 * @var int
	 */
	protected $fields = 0;

	/**
	 * (non-PHPdoc)
	 * @see lib/Object#init()
	 */
	protected function init()
	{
		$this->setTableName("planet");
		$this->setPrimaryKey("planetid");
		$this->setModelName("game/planet");
		return parent::init();
	}

	/**
	 * Coordinates of this planet.
	 *
	 * @param boolean $link			Link or simple string
	 * @param boolean $sidWildcard	Replace session with wildcard
	 *
	 * @return string
	 */
	public function getCoords($link = true, $sidWildcard = false)
	{
		if($link)
		{
			return getCoordLink($this->getGalaxy(), $this->getSystem(), $this->getPosition(), $sidWildcard);
		}
		return $this->getGalaxy().":".$this->getSystem().":".$this->getPosition();
	}

	/**
	 * Maximum available fields.
	 *
	 * @return integer
	 */
	public function getMaxFields()
	{
		$fmax = floor(pow($this->get("diameter") / 1000, 2));
		$terraFormer = $this->getBuilding("TERRA_FORMER");
		if(null !== $terraFormer)
		{
			$terraFormer = (int) $terraFormer->get("level");
		}
		else
		{
			$terraFormer = 0;
		}
		if($terraFormer > 0)
		{
			$fmax += $terraFormer * (int) Core::getOptions()->get("TERRAFORMER_ADDITIONAL_FIELDS");
		}
		else if($this->data["ismoon"])
		{
			$fields = (int) $this->getBuilding("MOON_BASE")->get("level") * (int) Core::getOptions()->get("MOON_BASE_FIELDS") + 1;
			if($fields < $fmax)
			{
				$fmax = $fields;
			}
		}
		Hook::event("GetMaxFields", array(&$fmax, $this));
		$addition = $this->get("ismoon") ? 0 : Core::getOptions()->get("PLANET_FIELD_ADDITION");
		return $fmax + $addition;
	}

	/**
	 * Returns the number of occupied fields.
	 *
	 * @param boolean $formatted
	 *
	 * @return integer
	 */
	public function getFields($formatted = false)
	{
		$this->getBuildings();
		if($formatted)
		{
			return fNumber($this->fields);
		}
		return $this->fields;
	}

	/**
	 * Checks if a planet has still free space.
	 *
	 * @return boolean
	 */
	public function planetFree()
	{
		if($this->getFields() < $this->getMaxFields())
		{
			return true;
		}
		return false;
	}

	/**
	 * Returns the planet's debris
	 *
	 * @return Bengine_Game_Model_Debris
	 */
	public function getDebris()
	{
		if(!$this->exists("debris"))
		{
			$this->set("debris", Application::getModel("game/debris")->load($this->getId()));
		}
		return $this->get("debris");
	}

	/**
	 * Retrieves the owner user of this planet.
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
	 * Sets the owner user of this planet.
	 *
	 * @param Bengine_Game_Model_User
	 *
	 * @return Bengine_Game_Model_Planet
	 */
	public function setUser(Bengine_Game_Model_User $user)
	{
		$this->set("user", $user);
		$this->setUserid($user->getId());
		return $this;
	}

	/**
	 * Adds metal to the planet.
	 *
	 * @param integer
	 *
	 * @return Bengine_Game_Model_Planet
	 */
	public function addMetal($metal)
	{
		if($this->getId())
		{
			$metal = (int) $metal;
			$this->setMetal($this->getMetal()+$metal);
		}
		return $this;
	}

	/**
	 * Adds silicon to the planet.
	 *
	 * @param integer
	 *
	 * @return Bengine_Game_Model_Planet
	 */
	public function addSilicon($silicon)
	{
		if($this->getId())
		{
			$silicon = (int) $silicon;
			$this->setSilicon($this->getSilicon()+$silicon);
		}
		return $this;
	}

	/**
	 * Adds hydrogen to the planet.
	 *
	 * @param integer
	 *
	 * @return Bengine_Game_Model_Planet
	 */
	public function addHydrogen($hydrogen)
	{
		if($this->getId())
		{
			$hydrogen = (int) $hydrogen;
			$this->setHydrogen($this->getHydrogen()+$hydrogen);
		}
		return $this;
	}

	/**
	 * @return Bengine_Game_Model_Collection_Construction
	 */
	public function getBuildings()
	{
		if(!$this->exists("buildings"))
		{
			/* @var Bengine_Game_Model_Collection_Construction $collection */
			$collection = Application::getCollection("game/construction");
			if($this->get("ismoon"))
			{
				$collection->addTypeFilter(1, true, 5);
			}
			else
			{
				$collection->addTypeFilter(1);
			}
			$collection->addPlanetJoin($this->get("planetid"));
			/* @var Bengine_Game_Model_Construction $building */
			foreach($collection as $building)
			{
				$name = $building->get("name");
				$level = (int) $building->get("level");
				$this->buildings[$name] = $building;
				$this->levels[$name] = $level;
				$this->fields += (int) $level;
			}
			$collection->reset();
			$this->set("buildings", $collection);
		}
		return $this->get("buildings");
	}

	/**
	 * @param string $name
	 * @return Bengine_Game_Model_Construction
	 */
	public function getBuilding($name)
	{
		$this->getBuildings();
		return isset($this->buildings[$name]) ? $this->buildings[$name] : Application::getModel("game/construction");
	}

	/**
	 * @param string $name
	 * @return int
	 */
	public function getBuildingLevel($name)
	{
		$this->getBuildings();
		return isset($this->levels[$name]) ? $this->levels[$name] : 0;
	}

	/**
	 * @return Bengine_Game_Model_Collection_Fleet
	 */
	public function getFleet()
	{
		if(!$this->exists("fleet"))
		{
			/* @var Bengine_Game_Model_Collection_Fleet $collection */
			$collection = Application::getCollection("game/fleet");
			$collection->addPlanetFilter($this)
				->addTypeFilter(3);
			$this->set("fleet", $collection);
		}
		return $this->get("fleet");
	}

	/**
	 * @return Bengine_Game_Model_Collection_Fleet
	 */
	public function getDefense()
	{
		if(!$this->exists("defense"))
		{
			/* @var Bengine_Game_Model_Collection_Fleet $collection */
			$collection = Application::getCollection("game/fleet");
			$collection->addPlanetFilter($this)
				->addTypeFilter(4);
			$this->set("defense", $collection);
		}
		return $this->get("defense");
	}

	/**
	 * @return int
	 */
	public function getFreeFields()
	{
		return $this->getMaxFields() - $this->getFields();
	}
}
?>