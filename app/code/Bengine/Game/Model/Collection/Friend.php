<?php
/**
 * Friends collection.
 *
 * @package Bengine
 * @copyright Copyright protected by / Urheberrechtlich geschützt durch "Sebastian Noll" <snoll@4ym.org>
 */

class Bengine_Game_Model_Collection_Friend extends Recipe_Model_Collection_Abstract
{
	const STATUS_ACCEPTED = true;
	const STATUS_UNACCEPTED = false;

	/**
	 * @param int|Bengine_Game_Model_User $user
	 * @return Bengine_Game_Model_Collection_Friend
	 */
	public function addUserFilter($user)
	{
		if($user instanceof Bengine_Game_Model_User)
		{
			$user = $user->get("userid");
		}
		$this->getSelect()
			->where("b.friend1 = ? OR b.friend2 = ?", (int) $user);
		return $this;
	}

	/**
	 * @param bool $accepted
	 * @return Bengine_Game_Model_Collection_Friend
	 */
	public function addAcceptedFilter($accepted = self::STATUS_ACCEPTED)
	{
		$this->getSelect()
			->where("b.accepted = ?", $accepted ? 1 : 0);
		return $this;
	}
}