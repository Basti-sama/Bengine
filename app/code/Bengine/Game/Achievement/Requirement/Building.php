<?php

class Bengine_Game_Achievement_Requirement_Building extends Bengine_Game_Achievement_Requirement_Abstract
{
	/**
	 * @return bool
	 */
	protected function _match()
	{
		/* @var Bengine_Game_Model_Collection_Planet $planets */
		$planets = Application::getCollection("game/planet");
		$planets->addUserFilter($this->getUser()->get("userid"));
		foreach($planets as $planet)
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