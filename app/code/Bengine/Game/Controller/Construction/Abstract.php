<?php
/**
 * Bengine_Game_Common functions to check constructions.
 *
 * @package Bengine
 * @copyright Copyright protected by / Urheberrechtlich geschützt durch "Sebastian Noll" <snoll@4ym.org>
 * @version $Id: Abstract.php 8 2010-10-17 20:55:04Z secretchampion $
 */

abstract class Bengine_Game_Controller_Construction_Abstract extends Bengine_Game_Controller_Abstract
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
	 * @param integer $nextLevel
	 * @param Bengine_Game_Model_Construction $construction
	 *
	 * @return Bengine_Game_Controller_Construction_Abstract
	 */
	protected function setRequieredResources($nextLevel, Bengine_Game_Model_Construction $construction)
	{
		$basicMetal = $construction->get("basic_metal");
		$basicSilicon = $construction->get("basic_silicon");
		$basicHydrogen = $construction->get("basic_hydrogen");
		$basicEnergy = $construction->get("basic_energy");
		if($nextLevel > 1)
		{
			if($basicMetal > 0)
			{
				$this->requiredMetal = parseFormula($construction->get("charge_metal"), $basicMetal, $nextLevel);
			}
			else { $this->requiredMetal = 0; }
			if($basicSilicon > 0)
			{
				$this->requiredSilicon = parseFormula($construction->get("charge_silicon"), $basicSilicon, $nextLevel);
			}
			else { $this->requiredSilicon = 0; }
			if($basicHydrogen > 0)
			{
				$this->requiredHydrogen = parseFormula($construction->get("charge_hydrogen"), $basicHydrogen, $nextLevel);
			}
			else { $this->requiredHydrogen = 0; }
			if($basicEnergy > 0)
			{
				$this->requiredEnergy = parseFormula($construction->get("charge_energy"), $basicEnergy, $nextLevel);
			}
			else { $this->requiredEnergy = 0; }
		}
		else
		{
			$this->requiredMetal = (int) $basicMetal;
			$this->requiredSilicon = (int) $basicSilicon;
			$this->requiredHydrogen = (int) $basicHydrogen;
			$this->requiredEnergy = (int) $basicEnergy;
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

		if($this->requiredMetal > Game::getPlanet()->getData("metal"))
		{
			return false;
		}
		if($this->requiredSilicon > Game::getPlanet()->getData("silicon"))
		{
			return false;
		}
		if($this->requiredHydrogen > Game::getPlanet()->getData("hydrogen"))
		{
			return false;
		}
		$energy = (Game::getPlanet()->getEnergy() < 0) ? 0 : Game::getPlanet()->getEnergy();
		if($this->requiredEnergy > $energy)
		{
			return false;
		}
		return true;
	}
}
?>