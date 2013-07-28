<?php

class Bengine_Game_Achievement_Requirement_PlanetFields extends Bengine_Game_Achievement_Requirement_Abstract
{
	/**
	 * @return bool
	 */
	protected function _match()
	{
		$planets = $this->getUser()->getPlanets();
		foreach($planets as $planet)
		{
			/* @var Bengine_Game_Model_Planet $planet */
			if(($this->getValue() <= 0 && $planet->getFreeFields() <= 0) || ($planet->getFields() >= $this->getValue()))
			{
				return true;
			}
		}
		return false;
	}
}