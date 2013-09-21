<?php
/**
 * friend model.
 *
 * @package Bengine
 * @copyright Copyright protected by / Urheberrechtlich geschützt durch "Sebastian Noll" <snoll@4ym.org>
 */

class Bengine_Game_Model_Friend extends Recipe_Model_Abstract
{
	/**
	 * @return Bengine_Game_Model_Friend
	 */
	protected function init()
	{
		$this->setTableName("buddylist");
		$this->setPrimaryKey("relid");
		$this->setModelName("game/friend");
		return parent::init();
	}
}
?>