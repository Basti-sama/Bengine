<?php

class Bengine_Game_Achievement_Requirement_PlanetName extends Bengine_Game_Achievement_Requirement_Abstract
{
	/**
	 * @return bool
	 */
	protected function _match()
	{
		if($this->getUser()->get("hp") == Core::getUser()->get("curplanet"))
		{
			return Game::getPlanet()->getData("planetname") != Core::getLang()->get("HOME_PLANET");
		}
		$planet = Game::getModel("game/planet")->load($this->getUser()->get("hp"));
		return $planet->get("planetname") != Core::getLang()->get("HOME_PLANET");
	}
}