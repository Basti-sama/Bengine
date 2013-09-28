<?php
/**
 * Achievement reward model.
 *
 * @package Bengine
 * @copyright Copyright protected by / Urheberrechtlich geschützt durch "Sebastian Noll" <snoll@4ym.org>
 */

class Bengine_Game_Model_AchievementReward extends Recipe_Model_Abstract
{
	/**
	 * @return Bengine_Game_Model_AchievementReward
	 */
	protected function init()
	{
		$this->setTableName("achievement_reward");
		$this->setPrimaryKey("achievement_reward_id");
		$this->setModelName("game/achievementReward");
		return parent::init();
	}

	/**
	 * @return Bengine_Game_Achievement_Reward_Abstract
	 * @throws Recipe_Exception_Generic
	 */
	public function getRewardObject()
	{
		if(!$this->exists("reward_object"))
		{
			/* @var Bengine_Game_Achievement_Reward_Abstract $object */
			$object = Application::factory($this->get("class"), null);
			if(!$object)
			{
				throw new Recipe_Exception_Generic("Unknown achievement reward '{$this->get("class")}'.");
			}
			$object->setValue($this->get("value"));
			$object->setType($this->get("type"));
			$this->set("reward_object", $object);
		}
		return $this->get("reward_object");
	}
}
?>