<?php
/**
 * Achievement collection.
 *
 * @package Bengine
 * @copyright Copyright protected by / Urheberrechtlich geschützt durch "Sebastian Noll" <snoll@4ym.org>
 */

class Bengine_Game_Model_Collection_Achievement extends Recipe_Model_Collection_Abstract
{
	/**
	 * @param int|Bengine_Game_Model_User $user
	 * @param bool $onlyWithUser
	 * @return \Bengine_Game_Model_Collection_Achievement
	 */
	public function addUserJoin($user, $onlyWithUser = false)
	{
		if($user instanceof Bengine_Game_Model_User)
		{
			$user = $user->getId();
		}
		$user = (int) $user;
		$this->getSelect()
			->join(array("a2u" => "achievement2user"), "a2u.user_id = $user AND a2u.achievement_id = a.achievement_id")
			->attributes(array("a" => array(new Recipe_Database_Expr("a.*")), "a2u" => array("user_id")));
		if($onlyWithUser)
		{
			$this->getSelect()
				->where(array("a2u" => "user_id"), "NOT NULL");
		}
		return $this;
	}

	/**
	 * @param Bengine_Game_Model_User $user
	 * @return array
	 */
	public function checkForUnlockedAchievements(Bengine_Game_Model_User $user)
	{
		$unlocked = array();
		foreach($this->getItems() as $achievement)
		{
			/* @var Bengine_Game_Model_Achievement $achievement */
			if($achievement->checkIfUnlocked($user))
			{
				$achievement->unlockForUser($user);
				$unlocked[] = $achievement;
			}
		}
		return $unlocked;
	}

	/**
	 * @return Bengine_Game_Model_Collection_Achievement
	 */
	public function addDefaultSorting()
	{
		$this->getSelect()
			->order(array("a" => "sort_index"), "ASC")
			->order(array("a" => "xp"), "ASC")
			->order(array("a" => "achievement_id"), "ASC");
		return $this;
	}
}