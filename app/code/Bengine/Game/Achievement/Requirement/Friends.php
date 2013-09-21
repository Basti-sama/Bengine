<?php

class Bengine_Game_Achievement_Requirement_Friends extends Bengine_Game_Achievement_Requirement_Abstract
{
	/**
	 * @return bool
	 */
	protected function _match()
	{
		/* @var Bengine_Game_Model_Collection_Friend $collection */
		$collection = Game::getCollection("game/friend");
		$collection->addUserFilter($this->getUser()->get("userid"))
			->addAcceptedFilter();
		return $collection->getCalculatedSize() >= $this->getValue();
	}
}