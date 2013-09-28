<?php
/**
 * Achievement reward collection.
 *
 * @package Bengine
 * @copyright Copyright protected by / Urheberrechtlich geschützt durch "Sebastian Noll" <snoll@4ym.org>
 */

class Bengine_Game_Model_Collection_AchievementReward extends Recipe_Model_Collection_Abstract
{
	/**
	 * @param int|Bengine_Game_Model_Achievement $achievement
	 * @return \Bengine_Game_Model_Collection_AchievementReward
	 */
	public function addAchievementFilter($achievement)
	{
		if($achievement instanceof Bengine_Game_Model_Achievement)
		{
			$achievement = $achievement->getId();
		}
		$this->getSelect()
			->where("achievement_id = ?", (int) $achievement);
		return $this;
	}
}