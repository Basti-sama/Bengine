<?php

class Bengine_Game_Achievement_Requirement_Unit extends Bengine_Game_Achievement_Requirement_Abstract
{
	/**
	 * @return bool
	 */
	protected function _match()
	{
		/* @var Bengine_Game_Model_Collection_Planet $planets */
		foreach($this->getUser()->getPlanets(true) as $planet)
		{
			$totalFleet = 0;
			$totalDefense = 0;
			/* @var Bengine_Game_Model_Planet $planet */
			/* @var Bengine_Game_Model_Unit $unit */
			foreach($planet->getFleet() as $unit)
			{
				$totalFleet += $unit->get("quantity");
				if(($unit->getId() == $this->getId() || $unit->getName() == $this->getId()) && $unit->get("quantity") >= $this->getValue())
				{
					return true;
				}
			}
			if($this->getId() == "_FLEET" && $totalFleet >= $this->getValue())
			{
				return true;
			}
			foreach($planet->getDefense() as $unit)
			{
				$totalDefense += $unit->get("quantity");
				if(($unit->getId() == $this->getId() || $unit->getName() == $this->getId()) && $unit->get("quantity") >= $this->getValue())
				{
					return true;
				}
			}
			if($this->getId() == "_DEFENSE" && $totalDefense >= $this->getValue())
			{
				return true;
			}
		}
		return false;
	}
}