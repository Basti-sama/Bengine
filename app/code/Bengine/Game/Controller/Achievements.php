<?php
/**
 * Achievements controller
 *
 * @package Bengine
 * @copyright Copyright protected by / Urheberrechtlich geschützt durch "Sebastian Noll" <snoll@4ym.org>
 */

class Bengine_Game_Controller_Achievements extends Bengine_Game_Controller_Abstract
{
	/**
	 * @return Bengine_Game_Controller_Achievements
	 */
	public function indexAction()
	{
		Core::getLanguage()->load(array("Achievements"));

		/* @var Bengine_Game_Model_User $user */
		$user = Application::getModel("game/user")->load(Core::getUser()->get("userid"));
		/* @var Bengine_Game_Model_Planet $planet */
		$planet = Application::getModel("game/planet")->load(Core::getUser()->get("curplanet"));

		/* @var Bengine_Game_Model_Collection_Achievement $achievements */
		$achievements = Application::getCollection("game/achievement");
		$achievements->addUserJoin(Core::getUser()->get("userid"))
			->addDefaultSorting();
		Core::getTemplate()->addLoop("achievements", $achievements);

		$unlocked = $achievements->checkForUnlockedAchievements($user, $planet);
		Core::getTemplate()->addLoop("unlocked", $unlocked);

		$x = $user->getRequiredXPForNextLevel() * ($user->getRequiredXPForNextLevel() - $user->getLeftXPForNextLevel());
		$percent = 0;
		if($x > 0)
		{
			$percent = 100 / $x;
		}
		Core::getLanguage()->assign("leftXP", $user->getLeftXPForNextLevel());
		Core::getLanguage()->assign("nextLevel", $user->get("level")+1);
		Core::getTemplate()->assign("user", $user);
		Core::getTemplate()->assign("percent", $percent);
		Core::getLang()->assign("xp", $user->get("xp"));
		Core::getLang()->assign("level", $user->get("level"));
		return $this;
	}
}