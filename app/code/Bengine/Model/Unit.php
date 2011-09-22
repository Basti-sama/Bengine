<?php
/**
 * Construction model.
 *
 * @package Bengine
 * @copyright Copyright protected by / Urheberrechtlich geschützt durch "Sebastian Noll" <snoll@4ym.org>
 * @version $Id: Unit.php 8 2010-10-17 20:55:04Z secretchampion $
 */

class Bengine_Model_Unit extends Bengine_Model_Construction
{
	/**
	 * (non-PHPdoc)
	 * @see lib/Object#init()
	 */
	protected function init()
	{
		$this->setTableName("construction")
			->setPrimaryKey("buildingid")
			->setModelName("unit")
			->setCollectionName("construction")
			->setResourceName("unit");
		return $this;
	}

	public function getLinkName()
	{
		return Link::get("game.php/".SID."/Unit/Info/".$this->get("buildingid"), $this->getName());
	}

	public function getImage()
	{
		return Link::get("game.php/".SID."/Unit/Info/".$this->get("buildingid"), Image::getImage("buildings/".$this->get("name").".gif", $this->getName()));
	}

	public function getEditLink()
	{
		return Link::get("game.php/".SID."/Unit/Edit/".$this->get("buildingid"), "[".Core::getLanguage()->getItem("EDIT")."]");
	}

	public function hasResources()
	{
		if($this->get("basic_metal") > Bengine::getPlanet()->getData("metal"))
		{
			return false;
		}
		if($this->get("basic_silicon") > Bengine::getPlanet()->getData("silicon"))
		{
			return false;
		}
		if($this->get("basic_hydrogen") > Bengine::getPlanet()->getData("hydrogen"))
		{
			return false;
		}
		$energy = (Bengine::getPlanet()->getEnergy() < 0) ? 0 : Bengine::getPlanet()->getEnergy();
		if($this->get("basic_energy") > $energy)
		{
			return false;
		}
		return true;
	}

	public function getProductionTime($formatted = false)
	{
		if(!$this->exists("production_time"))
		{
			$this->set("production_time", getBuildTime($this->get("basic_metal"), $this->get("basic_silicon"), $this->get("mode")));
		}
		$time = $this->get("production_time");
		return ($formatted) ? getTimeTerm($time) : $time;
	}

	/**
	 * Wrapper for getQuantity().
	 *
	 * @return integer
	 */
	public function getQty()
	{
		return $this->get("quantity");
	}

	/**
	 * Returns the formatted quantity.
	 *
	 * @return string
	 */
	public function getFormattedQty()
	{
		return fNumber($this->get("quantity"));
	}
}
?>