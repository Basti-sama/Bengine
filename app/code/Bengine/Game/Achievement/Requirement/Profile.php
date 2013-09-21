<?php

class Bengine_Game_Achievement_Requirement_Profile extends Bengine_Game_Achievement_Requirement_Abstract
{
	/**
	 * @return bool
	 */
	protected function _match()
	{
		Core::getLanguage()->load(array("Profile"));
		$userId = $this->getUser()->get("userid");
		/* @var Bengine_Game_Model_Profile $avatar */
		$avatar = Game::getModel("game/profile")->loadByCode("AVATAR", $userId);
		/* @var Bengine_Game_Model_Profile $about */
		$about = Game::getModel("game/profile")->loadByCode("ABOUT_ME", $userId);
		if($avatar->get("data") != "" && strip_tags($about->get("data")) != "" && $about->get("data") != Core::getLang()->get("DEFAULT_ABOUT_ME"))
		{
			return true;
		}
		return false;
	}
}