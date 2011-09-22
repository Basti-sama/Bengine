<?php
/**
 * Common functions to check constructions.
 *
 * @package Bengine
 * @copyright Copyright protected by / Urheberrechtlich geschützt durch "Sebastian Noll" <snoll@4ym.org>
 * @version $Id: Abstract.php 8 2010-10-17 20:55:04Z secretchampion $
 */

abstract class Bengine_Page_Construction_Abstract extends Bengine_Page_Abstract
{
	/**
	 * Holds the required resources of the current building.
	 *
	 * @var integer
	 */
	protected $requiredMetal = 0, $requiredSilicon = 0, $requiredHydrogen = 0, $requiredEnergy = 0;

	/**
	 * Stores the required resources for the current building.
	 *
	 * @param integer	Next level
	 * @param integer	Construction data
	 *
	 * @return Construction
	 */
	protected function setRequieredResources($nextLevel, $row)
	{
		if($nextLevel > 1)
		{
			if($row["basic_metal"] > 0)
			{
				$this->requiredMetal = parseFormula($row["charge_metal"], $row["basic_metal"], $nextLevel);
			}
			else { $this->requiredMetal = 0; }
			if($row["basic_silicon"] > 0)
			{
				$this->requiredSilicon = parseFormula($row["charge_silicon"], $row["basic_silicon"], $nextLevel);
			}
			else { $this->requiredSilicon = 0; }
			if($row["basic_hydrogen"] > 0)
			{
				$this->requiredHydrogen = parseFormula($row["charge_hydrogen"], $row["basic_hydrogen"], $nextLevel);
			}
			else { $this->requiredHydrogen = 0; }
			if($row["basic_energy"] > 0)
			{
				$this->requiredEnergy = parseFormula($row["charge_energy"], $row["basic_energy"], $nextLevel);
			}
			else { $this->requiredEnergy = 0; }
		}
		else
		{
			$this->requiredMetal = (int) $row["basic_metal"];
			$this->requiredSilicon = (int) $row["basic_silicon"];
			$this->requiredHydrogen = (int) $row["basic_hydrogen"];
			$this->requiredEnergy = (int) $row["basic_energy"];
		}
		return $this;
	}

	/**
	 * Checks the resources.
	 *
	 * @return boolean	True if resources suffice, fals if not
	 */
	protected function checkResources()
	{

		if($this->requiredMetal > Bengine::getPlanet()->getData("metal"))
		{
			return false;
		}
		if($this->requiredSilicon > Bengine::getPlanet()->getData("silicon"))
		{
			return false;
		}
		if($this->requiredHydrogen > Bengine::getPlanet()->getData("hydrogen"))
		{
			return false;
		}
		$energy = (Bengine::getPlanet()->getEnergy() < 0) ? 0 : Bengine::getPlanet()->getEnergy();
		if($this->requiredEnergy > $energy)
		{
			return false;
		}
		return true;
	}
}
?>