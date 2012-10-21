<?php
/**
 * User resource model.
 *
 * @package Bengine
 * @copyright Copyright protected by / Urheberrechtlich geschützt durch "Sebastian Noll" <snoll@4ym.org>
 * @version $Id: User.php 8 2010-10-17 20:55:04Z secretchampion $
 */

class Bengine_Game_Model_Resource_User extends Recipe_Model_Resource_Abstract
{
	protected function init()
	{
		$this->setMainTable(array("u" => "user"));
		return parent::init();
	}

	public function getSelect()
	{
		$select = parent::getSelect();
		$select->join(array("u2a" => "user2ally"), array("u" => "userid", "u2a" => "userid"));
		$select->join(array("a" => "alliance"), array("a" => "aid", "u2a" => "aid"));
		$select->attributes(array(
			"u.*",
			"a" => array("aid", "alliance_name" => "name", "alliance_tag" => "tag", "logo"),
		));
		return $select;
	}
}
?>