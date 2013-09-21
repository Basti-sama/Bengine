<?php

class Bengine_Game_Achievement_Requirement_Resources extends Bengine_Game_Achievement_Requirement_Abstract
{
	/**
	 * @return bool
	 */
	protected function _match()
	{
		/* @var Bengine_Game_Model_Planet $planet */
		foreach($this->getUser()->getPlanets(true) as $planet)
		{
			switch($this->getId())
			{
				case "metal":
				case "silicon":
				case "hydrogen":
					if($planet->get($this->getId()) >= $this->getValue())
					{
						return true;
					}
				break;
				default:
					$resources = $planet->get("metal") + $planet->get("silicon") + $planet->get("hydrogen");
					if($resources >= $this->getValue())
					{
						return true;
					}
				break;
			}
		}
		return false;
	}
}