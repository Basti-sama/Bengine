<?php
/**
 * Profile resource model.
 *
 * @package Bengine
 * @copyright Copyright protected by / Urheberrechtlich geschützt durch "Sebastian Noll" <snoll@4ym.org>
 * @version $Id: Profile.php 8 2010-10-17 20:55:04Z secretchampion $
 */

class Bengine_Game_Model_Resource_Profile extends Recipe_Model_Resource_Abstract
{
	/**
	 * Initializes the model.
	 *
	 * @return Bengine_Game_Model_Profile
	 */
	protected function init()
	{
		$this->setMainTable(array("p" => "profile"));
		return parent::init();
	}

	/**
	 * Retrieves the select object for the model.
	 *
	 * @return Recipe_Database_Select
	 */
	public function getSelect()
	{
		$select = parent::getSelect();
		$select->attributes(array(
			"p"   => array("profile_id", "name", "type"),
		));
		return $select;
	}

	/**
	 * Loads the profile field by code.
	 *
	 * @param string					Code
	 * @param integer|Bengine_Game_Model_User	User id
	 *
	 * @return array
	 */
	public function loadByCode($code, $user)
	{
		if($user instanceof Bengine_Game_Model_User)
		{
			$user = $user->getUserid();
		}
		$select = $this->getSelect();
		$attrs = $select->getAttributes();
		$attrs["p2u"] = array(
			"user_id", "data"
		);
		$select->attributes($attrs)
			->join(
				array("p2u" => "profile2user"),
				array("p2u" => "profile_id", "p" => "profile_id",
				"AND" => array(array("p2u" => "user_id"), $user))
			)
			->where(array("p" => "name"), $code);
		return $this->fetchRow($select);
	}
}
?>