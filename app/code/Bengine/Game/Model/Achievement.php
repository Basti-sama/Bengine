<?php
/**
 * Achievement model.
 *
 * @package Bengine
 * @copyright Copyright protected by / Urheberrechtlich geschützt durch "Sebastian Noll" <snoll@4ym.org>
 */

class Bengine_Game_Model_Achievement extends Recipe_Model_Abstract
{
	/**
	 * @return Bengine_Game_Model_Achievement
	 */
	protected function init()
	{
		$this->setTableName("achievement");
		$this->setPrimaryKey("achievement_id");
		$this->setModelName("game/achievement");
		return parent::init();
	}

	/**
	 * @return Bengine_Game_Model_Collection_AchievementRequirement
	 */
	public function getRequirements()
	{
		if(!$this->exists("requirements"))
		{
			/* @var Bengine_Game_Model_Collection_AchievementRequirement $collection */
			$collection = Application::getCollection("game/achievementRequirement");
			$collection->addAchievementFilter($this->get("achievement_id"));
			$this->set("requirements", $collection);
		}
		return $this->get("requirements");
	}

	/**
	 * @param Bengine_Game_Model_User $user
	 * @return bool
	 */
	public function checkIfUnlocked(Bengine_Game_Model_User $user)
	{
		if($this->get("user_id") == $user->get("userid"))
		{
			return false;
		}
		$unlocked = true;
		foreach($this->getRequirements() as $requirement)
		{
			/* @var Bengine_Game_Model_AchievementRequirement $requirement */
			if(!$requirement->checkIfRequirementMatched($user))
			{
				$unlocked = false;
			}
		}
		return $unlocked;
	}

	/**
	 * @param Bengine_Game_Model_User $user
	 * @return Bengine_Game_Model_Achievement
	 */
	public function unlockForUser(Bengine_Game_Model_User $user)
	{
		Core::getQuery()->insert("achievement2user", array(
			"achievement_id" => $this->get("achievement_id"),
			"user_id" => $user->get("userid"),
			"date" => TIME
		));
		$this->set("user_id", $user->get("userid"));
		$user->addXP($this->get("xp"));
		return $this;
	}
}
?>