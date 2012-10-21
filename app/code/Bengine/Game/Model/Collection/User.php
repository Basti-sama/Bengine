<?php
/**
 * User collection.
 *
 * @package Bengine
 * @copyright Copyright protected by / Urheberrechtlich geschützt durch "Sebastian Noll" <snoll@4ym.org>
 * @version $Id: User.php 8 2010-10-17 20:55:04Z secretchampion $
 */

class Bengine_Game_Model_Collection_User extends Recipe_Model_Collection_Abstract
{
	protected function addUserNameFilter($username)
	{
		$this->getSelect()->where(array("u" => "username", "LIKE"), $username);
		return $this;
	}

	protected function addPointOrder($sort = "DESC")
	{
		$this->getSelect()->order(array("u" => "points"), $sort);
		return $this;
	}
}
?>