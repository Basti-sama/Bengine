<?php

class Bengine_Game_Achievement_Requirement_AssaultCount extends Bengine_Game_Achievement_Requirement_Abstract
{
	/**
	 * @return bool
	 */
	protected function _match()
	{
		/* @var Bengine_Game_Model_Collection_Assault $collection */
		$collection = Application::getCollection("game/assault");
		$collection->addAccomplishedFilter();
		switch($this->getId())
		{
			case "ATTACKER_WON":
				$collection->addParticipantFilter($this->getUser(), 1)
					->addResultFilter(1);
				define("DEBUG", true);
				return $collection->getCalculatedSize(false) >= $this->getValue();
			break;
			case "DEFENDER_WON":
				$collection->addParticipantFilter($this->getUser(), 0)
					->addResultFilter(2);
				return $collection->getCalculatedSize(false) >= $this->getValue();
			break;
			case "TOTAL_WON":
				$collection->addParticipantFilter($this->getUser())
					->addResultFilter(1);
				return $collection->getCalculatedSize(false) >= $this->getValue();
			break;
			case "TOTAL_DRAW":
				$collection->addParticipantFilter($this->getUser())
					->addResultFilter(0);
				return $collection->getCalculatedSize(false) >= $this->getValue();
			break;
			case "OVER_ALL":
				$collection->addParticipantFilter($this->getUser());
				return $collection->getCalculatedSize(false) >= $this->getValue();
			break;
		}
		return false;
	}
}