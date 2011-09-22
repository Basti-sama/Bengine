<?php
/**
 * Planet model.
 *
 * @package Bengine
 * @copyright Copyright protected by / Urheberrechtlich geschützt durch "Sebastian Noll" <snoll@4ym.org>
 * @version $Id: Planet.php 19 2011-05-27 10:30:33Z secretchampion $
 */

class Bengine_Model_Planet extends Recipe_Model_Abstract
{
	/**
	 * (non-PHPdoc)
	 * @see lib/Object#init()
	 */
	protected function init()
	{
		$this->setTableName("planet");
		$this->setPrimaryKey("planetid");
		return parent::init();
	}

	/**
	 * Coordinates of this planet.
	 *
	 * @param boolean	Link or simple string
	 * @param boolean	Replace session with wildcard
	 *
	 * @return string	Coordinates
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
	 * @return integer	Max. fields
	 */
	public function getMaxFields()
	{
		$fmax = floor(pow($this->data["diameter"] / 1000, 2));
		if($this->getBuilding("TERRA_FORMER") > 0)
		{
			$fmax += $this->getBuilding("TERRA_FORMER") * 5;
		}
		else if($this->data["ismoon"])
		{
			$fields = $this->getBuilding("MOON_BASE") * 3 + 1;
			if($fields < $fmax)
			{
				return $fields;
			}
			return $fmax;
		}
		Hook::event("GetMaxPlanetFields", array(&$fmax));
		$addition = ($this->data["ismoon"]) ? 0 : Core::getOptions()->get("PLANET_FIELD_ADDITION");
		return $fmax + $addition;
	}

	/**
	 * Returns the number of occupied fields.
	 *
	 * @param boolean	Format the number
	 *
	 * @return integer	Fields
	 */
	public function getFields($formatted = false)
	{
		$fields = $this->getBuildings()->getSum();
		if($formatted)
		{
			return fNumber($fields);
		}
		return $fields;
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
	 * @return Bengine_Model_Debris
	 */
	public function getDebris()
	{
		if(!$this->exists("debris"))
		{
			$this->set("debris", Bengine::getModel("debris")->load($this->getId()));
		}
		return $this->get("debris");
	}

	/**
	 * Retrieves the owner user of this planet.
	 *
	 * @return Bengine_Model_User
	 */
	public function getUser()
	{
		if(!$this->exists("user"))
		{
			$this->set("user", Bengine::getModel("user")->load($this->getUserid()));
		}
		return $this->get("user");
	}

	/**
	 * Sets the owner user of this planet.
	 *
	 * @param Bengine_Model_User
	 *
	 * @return Bengine_Model_Planet
	 */
	public function setUser(Bengine_Model_User $user)
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
	 * @return Bengine_Model_Planet
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
	 * @return Bengine_Model_Planet
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
	 * @return Bengine_Model_Planet
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
}
?>