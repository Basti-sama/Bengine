<?php

class Bengine_Game_Achievement_Requirement_Building extends Bengine_Game_Achievement_Requirement_Abstract
{
	/**
	 * @return bool
	 */
	protected function _match()
	{
		foreach($this->getUser()->getPlanets(true) as $planet)
		{
			/* @var Bengine_Game_Model_Planet $planet */
			$building = $planet->getBuilding($this->getId());
			if($building !== null && $building->get("level") >= $this->getValue())
			{
				return true;
			}
		}
		return false;
	}
}