<?php

class Bengine_Game_Achievement_Requirement_AllianceMember extends Bengine_Game_Achievement_Requirement_Abstract
{
	/**
	 * @return bool
	 */
	protected function _match()
	{
		if($this->getUser()->getAlliance()->get("member") >= $this->getValue())
		{
			return true;
		}
		return false;
	}
}