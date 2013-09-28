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
		if(count($this->getRequirements()))
		{
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
		return false;
	}

	/**
	 * @param Bengine_Game_Model_User $user
	 * @param Bengine_Game_Model_Planet $planet
	 * @return Bengine_Game_Model_Achievement
	 */
	public function unlockForUser(Bengine_Game_Model_User $user, Bengine_Game_Model_Planet $planet = null)
	{
		Core::getQuery()->insert("achievement2user", array(
			"achievement_id" => $this->get("achievement_id"),
			"user_id" => $user->get("userid"),
			"date" => TIME
		));
		$this->set("user_id", $user->get("userid"));
		$user->addXP($this->get("xp"));
		if(count($this->getRewards()) > 0)
		{
			if(null === $planet)
			{
				$planet = $user->getHomePlanet();
			}
			foreach($this->getRewards() as $rewardModel)
			{
				/* @var Bengine_Game_Model_AchievementReward $rewardModel */
				$reward = $rewardModel->getRewardObject();
				$reward->setUser($user)
					->setPlanet($planet);
				$reward->reward();
			}
		}
		return $this;
	}

	/**
	 * @return Bengine_Game_Model_Collection_AchievementReward
	 */
	public function getRewards()
	{
		if(!$this->exists("rewards"))
		{
			/* @var Bengine_Game_Model_Collection_AchievementReward $collection */
			$collection = Application::getCollection("game/achievementReward");
			$collection->addAchievementFilter($this);
			$this->set("rewards", $collection);
		}
		return $this->get("rewards");
	}

	/**
	 * @return string
	 */
	public function getRewardString()
	{
		$rewards = array();
		foreach($this->getRewards() as $rewardModel)
		{
			/* @var Bengine_Game_Model_AchievementReward $rewardModel */
			$reward = $rewardModel->getRewardObject();
			$rewards[] = $reward->getLabel().": ".$reward->getValue();
		}
		return implode(", ", $rewards);
	}
}
?>