<?php

class Bengine_Game_Achievement_Requirement_Points extends Bengine_Game_Achievement_Requirement_Abstract
{
	/**
	 * @return bool
	 */
	protected function _match()
	{
		return $this->getUser()->get("points") >= $this->getValue();
	}
}