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

		Core::getLanguage()->assign("leftXP", $user->getLeftXPForNextLevel());
		Core::getLanguage()->assign("nextLevel", $user->get("level")+1);
		Core::getTemplate()->assign("user", $user);
		Core::getLang()->assign("xp", $user->get("xp"));
		Core::getLang()->assign("level", $user->get("level"));
		return $this;
	}

	/**
	 * @var int $user
	 * @return Bengine_Game_Controller_Achievements
	 */
	public function userAction($user)
	{
		Core::getLanguage()->load(array("Achievements"));

		$user = Application::getModel("game/user")->load((int) $user);
		/* @var Bengine_Game_Model_Collection_Achievement $achievements */
		$achievements = Application::getCollection("game/achievement");
		$achievements->addUserJoin($user->get("userid"), true)
			->addDefaultSorting();

		$this->view->addLoop("achievements", $achievements);
		$this->view->assign("user", $user);

		$this->language->assign("leftXP", $user->getLeftXPForNextLevel());
		$this->language->assign("nextLevel", $user->get("level")+1);
		$this->language->assign("xp", $user->get("xp"));
		$this->language->assign("level", $user->get("level"));
		$this->language->assign("achievementUser", Link::get("game/".SID."/Profile/Page/".$user->get("userid"), $user->get("username")));
	}
}