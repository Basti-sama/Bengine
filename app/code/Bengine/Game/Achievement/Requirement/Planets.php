<?php

class Bengine_Game_Achievement_Requirement_Planets extends Bengine_Game_Achievement_Requirement_Abstract
{
	/**
	 * @return bool
	 */
	protected function _match()
	{
		return $this->getUser()->getPlanets()->getCalculatedSize(false) >= $this->getValue();
	}
}