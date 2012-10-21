<?php
/**
 * Profile collection.
 *
 * @package Bengine
 * @copyright Copyright protected by / Urheberrechtlich geschützt durch "Sebastian Noll" <snoll@4ym.org>
 * @version $Id: Profile.php 8 2010-10-17 20:55:04Z secretchampion $
 */

class Bengine_Game_Model_Collection_Profile extends Recipe_Model_Collection_Abstract
{
	/**
	 * Adds a user filter.
	 *
	 * @param integer|Bengine_Game_Model_User
	 *
	 * @return Bengine_Game_Model_Collection_Profile
	 */
	public function addUserFilter($user)
	{
		if($user instanceof Bengine_Game_Model_User)
		{
			$user = $user->getId();
		}
		$select = $this->getSelect();
		$attrs = $select->getAttributes();
		$attrs["p2u"] = array(
			"user_id", "data"
		);
		$select->join(
			array("p2u" => "profile2user"),
			array("p2u" => "profile_id", "p" => "profile_id",
			"AND" => array(array("p2u" => "user_id"), $user))
		)
		->attributes($attrs);

		return $this;
	}

	/**
	 * Adds sort index order.
	 *
	 * @return Bengine_Game_Model_Collection_Profile
	 */
	public function addSortIndex()
	{
		$this->getSelect()
			->order(array("p" => "sort_index"), "ASC")
			->order(array("p" => "name"), "ASC");
		return $this;
	}

	/**
	 * Adds a list filter.
	 *
	 * @param boolean	true = list view items, false = none-list view items
	 *
	 * @return Bengine_Game_Model_Collection_Profile
	 */
	public function addListFilter($list_view = true)
	{
		$this->getSelect()->where(array("p" => "list_view"), (int) $list_view);
		return $this;
	}
}
?>