<?php

class Bengine_Game_Achievement_Requirement_Research extends Bengine_Game_Achievement_Requirement_Abstract
{
	/**
	 * @return bool
	 */
	protected function _match()
	{
		$researchCollection = $this->getUser()->getResearch();
		foreach($researchCollection as $research)
		{
			/* @var Bengine_Game_Model_Construction $research */
			if($research->get("name") == $this->getId() || $research->get("buildingid") == $this->getId())
			{
				if($research->get("level") >= $this->getValue())
				{
					return true;
				}
			}
		}
		return false;
	}
}